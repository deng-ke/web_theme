<?php
namespace Zib\OAuthLogin\Douyin;

use Yurun\OAuthLogin\ApiException;
use Yurun\OAuthLogin\Base;

class OAuth2 extends Base
{
    /**
     * 授权接口地址
     */
    const AUTH_URL = 'https://open.douyin.com/platform/oauth/connect';

    /**
     * 获取 access_token 接口地址
     */
    const TOKEN_URL = 'https://open.douyin.com/oauth/access_token/';

    /**
     * 获取用户公开信息接口地址
     */
    const USERINFO_URL = 'https://open.douyin.com/oauth/userinfo/';

    /**
     * 第一步：获取登录页面跳转 url
     *
     * @param string|null $callbackUrl
     * @param string|null $state
     * @param string|null $scope
     * @return string
     */
    public function getAuthUrl($callbackUrl = null, $state = null, $scope = null)
    {
        if (null === $scope && null === $this->scope) {
            $scope = 'user_info';
        }

        $option = array(
            'client_key'    => $this->appid,
            'redirect_uri'  => null === $callbackUrl ? $this->callbackUrl : $callbackUrl,
            'response_type' => 'code',
            'scope'         => null === $scope ? $this->scope : $scope,
            'state'         => $this->getState($state),
        );

        if (null === $this->loginAgentUrl) {
            return static::AUTH_URL . '?' . $this->http_build_query($option);
        }

        return $this->loginAgentUrl . '?' . $this->http_build_query($option);
    }

    /**
     * 抖音不允许 redirect_uri 携带 query，代理模式只用干净的代理地址
     *
     * @return string
     */
    public function getRedirectUri()
    {
        return null === $this->loginAgentUrl ? $this->callbackUrl : $this->loginAgentUrl;
    }

    /**
     * 第二步：处理回调并获取 access_token
     *
     * @param string $storeState
     * @param string|null $code
     * @param string|null $state
     * @return string
     */
    protected function __getAccessToken($storeState, $code = null, $state = null)
    {
        $response = wp_remote_post(static::TOKEN_URL, array(
            'timeout' => 20,
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
            ),
            'body'    => array(
                'client_key'    => $this->appid,
                'client_secret' => $this->appSecret,
                'code'          => isset($code) ? $code : (isset($_GET['code']) ? $_GET['code'] : ''),
                'grant_type'    => 'authorization_code',
            ),
        ));

        if (is_wp_error($response)) {
            throw new ApiException($response->get_error_message(), 0);
        }

        $data = $this->parseApiResult(wp_remote_retrieve_body($response), __('获取抖音授权令牌失败，请检查服务器是否能访问抖音开放平台接口', 'zib_language'));

        if (empty($data['access_token'])) {
            throw new ApiException(__('未获取到抖音授权令牌', 'zib_language'), 0);
        }

        if (!empty($data['open_id'])) {
            $this->openid = $data['open_id'];
        }

        return $this->accessToken = $data['access_token'];
    }

    /**
     * 获取用户资料
     *
     * @param string|null $accessToken
     * @return array
     */
    public function getUserInfo($accessToken = null)
    {
        $token = null === $accessToken ? $this->accessToken : $accessToken;

        if (!$this->openid) {
            throw new ApiException(__('未获取到抖音用户标识', 'zib_language'), 0);
        }

        $response = wp_remote_post(static::USERINFO_URL, array(
            'timeout' => 20,
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
            ),
            'body'    => array(
                'access_token' => $token,
                'open_id'      => $this->openid,
            ),
        ));

        if (is_wp_error($response)) {
            throw new ApiException($response->get_error_message(), 0);
        }

        $data = $this->parseApiResult(wp_remote_retrieve_body($response), __('获取抖音用户信息失败，请检查服务器是否能访问抖音开放平台接口', 'zib_language'));

        $openid = !empty($data['open_id']) ? $data['open_id'] : $this->openid;

        if (!$openid) {
            throw new ApiException(__('未获取到抖音用户标识', 'zib_language'), 0);
        }

        $this->result['name'] = $this->parseDisplayName($data);
        $this->openid         = $openid;
        return $this->result;
    }

    /**
     * 解析抖音用户显示名称
     *
     * @param array $userInfo
     * @return string
     */
    protected function parseDisplayName(array $userInfo)
    {
        $name = '';

        if (!empty($userInfo['nickname'])) {
            $name = trim($userInfo['nickname']);
        } elseif (!empty($userInfo['name'])) {
            $name = trim($userInfo['name']);
        }

        $name = trim($name);

        if ($name && function_exists('zib_new_strlen')) {
            while (zib_new_strlen($name) > 16) {
                $name = function_exists('mb_substr') ? mb_substr($name, 0, -1, 'UTF-8') : substr($name, 0, -1);
            }
        }

        return $name;
    }

    /**
     * 解析抖音开放平台接口响应
     *
     * @param string $body
     * @param string $failMessage
     * @return array
     */
    protected function parseApiResult($body, $failMessage)
    {
        $decoded = json_decode($body, true);

        if (!is_array($decoded)) {
            throw new ApiException($failMessage, 0);
        }

        $data = (!empty($decoded['data']) && is_array($decoded['data'])) ? $decoded['data'] : $decoded;

        $error_code = 0;
        if (isset($data['error_code'])) {
            $error_code = (int) $data['error_code'];
        } elseif (isset($decoded['error_code'])) {
            $error_code = (int) $decoded['error_code'];
        }

        if ($error_code) {
            $message = '';
            if (!empty($data['description'])) {
                $message = $data['description'];
            } elseif (!empty($decoded['message']) && 'success' !== $decoded['message']) {
                $message = $decoded['message'];
            }
            throw new ApiException($message ? $message : $failMessage, $error_code);
        }

        $this->result = $data;
        return $data;
    }

    /**
     * 刷新 AccessToken 续期
     *
     * @param string $refreshToken
     * @return bool
     */
    public function refreshToken($refreshToken)
    {
        return false;
    }

    /**
     * 检验授权凭证 AccessToken 是否有效
     *
     * @param string|null $accessToken
     * @return bool
     */
    public function validateAccessToken($accessToken = null)
    {
        try {
            $this->getUserInfo($accessToken);
            return true;
        } catch (ApiException $e) {
            return false;
        }
    }
}

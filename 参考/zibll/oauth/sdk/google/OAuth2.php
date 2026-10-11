<?php
namespace Zib\OAuthLogin\Google;

use Yurun\OAuthLogin\ApiException;
use Yurun\OAuthLogin\Base;

class OAuth2 extends Base
{
    /**
     * 授权接口地址
     */
    const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

    /**
     * 获取 access_token 接口地址
     */
    const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    /**
     * api 接口域名
     */
    const API_DOMAIN = 'https://www.googleapis.com/';

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
            $scope = 'openid email profile';
        }

        $option = array(
            'client_id'     => $this->appid,
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
            'body'    => array(
                'client_id'     => $this->appid,
                'client_secret' => $this->appSecret,
                'code'          => isset($code) ? $code : (isset($_GET['code']) ? $_GET['code'] : ''),
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => $this->getRedirectUri(),
            ),
        ));

        if (is_wp_error($response)) {
            throw new ApiException($response->get_error_message(), 0);
        }

        $this->result = json_decode(wp_remote_retrieve_body($response), true);

        if (!is_array($this->result)) {
            throw new ApiException(__('获取 Google 授权令牌失败，请检查服务器是否能访问 Google 接口', 'zib_language'), 0);
        }

        if (isset($this->result['error'])) {
            $message = !empty($this->result['error_description']) ? $this->result['error_description'] : $this->result['error'];
            throw new ApiException($message, 0);
        }

        if (empty($this->result['access_token'])) {
            throw new ApiException(__('未获取到 Google 授权令牌', 'zib_language'), 0);
        }

        return $this->accessToken = $this->result['access_token'];
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

        $response = wp_remote_get(static::API_DOMAIN . 'oauth2/v2/userinfo', array(
            'timeout' => 20,
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
            ),
        ));

        if (is_wp_error($response)) {
            throw new ApiException($response->get_error_message(), 0);
        }

        $this->result = json_decode(wp_remote_retrieve_body($response), true);

        if (!is_array($this->result)) {
            throw new ApiException(__('获取 Google 用户信息失败，请检查服务器是否能访问 Google 接口', 'zib_language'), 0);
        }

        if (isset($this->result['error'])) {
            $message = is_array($this->result['error']) ? ($this->result['error']['message'] ?? '') : $this->result['error'];
            throw new ApiException($message, 0);
        }

        $openid = !empty($this->result['id']) ? $this->result['id'] : (!empty($this->result['sub']) ? $this->result['sub'] : '');

        if (!$openid) {
            throw new ApiException(__('未获取到 Google 用户标识', 'zib_language'), 0);
        }

        $this->result['name'] = $this->parseDisplayName($this->result);
        $this->openid         = $openid;
        return $this->result;
    }

    /**
     * 解析 Google 用户显示名称
     *
     * @param array $userInfo
     * @return string
     */
    protected function parseDisplayName(array $userInfo)
    {
        $name = '';

        if (!empty($userInfo['name'])) {
            $name = trim($userInfo['name']);
        } elseif (!empty($userInfo['given_name'])) {
            $name = trim($userInfo['given_name'] . ' ' . (!empty($userInfo['family_name']) ? $userInfo['family_name'] : ''));
        } elseif (!empty($userInfo['email'])) {
            $name = strstr($userInfo['email'], '@', true);
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

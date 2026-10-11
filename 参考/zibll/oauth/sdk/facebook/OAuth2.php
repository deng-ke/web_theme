<?php
namespace Zib\OAuthLogin\Facebook;

use Yurun\OAuthLogin\ApiException;
use Yurun\OAuthLogin\Base;

class OAuth2 extends Base
{
    const API_VERSION = 'v25.0';

    const AUTH_URL = 'https://www.facebook.com/v25.0/dialog/oauth';

    const TOKEN_URL = 'https://graph.facebook.com/v25.0/oauth/access_token';

    const API_DOMAIN = 'https://graph.facebook.com/v25.0/';

    public function getAuthUrl($callbackUrl = null, $state = null, $scope = null)
    {
        if (null === $scope && null === $this->scope) {
            $scope = 'public_profile,email';
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

    protected function __getAccessToken($storeState, $code = null, $state = null)
    {
        $response = wp_remote_post(static::TOKEN_URL, array(
            'timeout' => 20,
            'body'    => array(
                'client_id'     => $this->appid,
                'client_secret' => $this->appSecret,
                'code'          => isset($code) ? $code : (isset($_GET['code']) ? $_GET['code'] : ''),
                'redirect_uri'  => $this->getRedirectUri(),
            ),
        ));

        if (is_wp_error($response)) {
            throw new ApiException($response->get_error_message(), 0);
        }

        $this->result = json_decode(wp_remote_retrieve_body($response), true);

        if (!is_array($this->result)) {
            throw new ApiException(__('获取 Facebook 授权令牌失败，请检查服务器是否能访问 Facebook 接口', 'zib_language'), 0);
        }

        if (isset($this->result['error'])) {
            $message = !empty($this->result['error']['message']) ? $this->result['error']['message'] : (is_string($this->result['error']) ? $this->result['error'] : '');
            throw new ApiException($message ?: __('Facebook 授权失败', 'zib_language'), 0);
        }

        if (empty($this->result['access_token'])) {
            throw new ApiException(__('未获取到 Facebook 授权令牌', 'zib_language'), 0);
        }

        return $this->accessToken = $this->result['access_token'];
    }

    public function getUserInfo($accessToken = null)
    {
        $token = null === $accessToken ? $this->accessToken : $accessToken;

        $response = wp_remote_get(add_query_arg(array(
            'fields'       => 'id,name,email,picture.width(200).height(200)',
            'access_token' => $token,
        ), static::API_DOMAIN . 'me'), array(
            'timeout' => 20,
        ));

        if (is_wp_error($response)) {
            throw new ApiException($response->get_error_message(), 0);
        }

        $this->result = json_decode(wp_remote_retrieve_body($response), true);

        if (!is_array($this->result)) {
            throw new ApiException(__('获取 Facebook 用户信息失败', 'zib_language'), 0);
        }

        if (isset($this->result['error'])) {
            $message = !empty($this->result['error']['message']) ? $this->result['error']['message'] : '';
            throw new ApiException($message ?: __('获取 Facebook 用户信息失败', 'zib_language'), 0);
        }

        $openid = !empty($this->result['id']) ? $this->result['id'] : '';

        if (!$openid) {
            throw new ApiException(__('未获取到 Facebook 用户标识', 'zib_language'), 0);
        }

        $this->result['name'] = $this->parseDisplayName($this->result);
        $this->openid         = $openid;
        return $this->result;
    }

    public function getAvatarUrl(array $userInfo)
    {
        return !empty($userInfo['picture']['data']['url']) ? $userInfo['picture']['data']['url'] : '';
    }

    protected function parseDisplayName(array $userInfo)
    {
        $name = '';

        if (!empty($userInfo['name'])) {
            $name = trim($userInfo['name']);
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

    public function refreshToken($refreshToken)
    {
        return false;
    }

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

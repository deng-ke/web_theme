<?php
namespace Zib\OAuthLogin\Microsoft;

use Yurun\OAuthLogin\ApiException;
use Yurun\OAuthLogin\Base;

class OAuth2 extends Base
{
    const AUTH_URL = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';

    const TOKEN_URL = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';

    const USERINFO_URL = 'https://graph.microsoft.com/oidc/userinfo';

    const ME_URL = 'https://graph.microsoft.com/v1.0/me';

    public function getAuthUrl($callbackUrl = null, $state = null, $scope = null)
    {
        if (null === $scope && null === $this->scope) {
            $scope = 'openid profile email User.Read';
        }

        $option = array(
            'client_id'     => $this->appid,
            'redirect_uri'  => null === $callbackUrl ? $this->callbackUrl : $callbackUrl,
            'response_type' => 'code',
            'scope'         => null === $scope ? $this->scope : $scope,
            'response_mode' => 'query',
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
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => $this->getRedirectUri(),
                'scope'         => 'openid profile email User.Read',
            ),
        ));

        if (is_wp_error($response)) {
            throw new ApiException($response->get_error_message(), 0);
        }

        $this->result = json_decode(wp_remote_retrieve_body($response), true);

        if (!is_array($this->result)) {
            throw new ApiException(__('获取 Microsoft 授权令牌失败，请检查服务器是否能访问 Microsoft 接口', 'zib_language'), 0);
        }

        if (isset($this->result['error'])) {
            $message = !empty($this->result['error_description']) ? $this->result['error_description'] : $this->result['error'];
            throw new ApiException($message, 0);
        }

        if (empty($this->result['access_token'])) {
            throw new ApiException(__('未获取到 Microsoft 授权令牌', 'zib_language'), 0);
        }

        return $this->accessToken = $this->result['access_token'];
    }

    public function getUserInfo($accessToken = null)
    {
        $token = null === $accessToken ? $this->accessToken : $accessToken;

        $response = wp_remote_get(static::ME_URL, array(
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
            throw new ApiException(__('获取 Microsoft 用户信息失败', 'zib_language'), 0);
        }

        if (isset($this->result['error'])) {
            $message = !empty($this->result['error']['message']) ? $this->result['error']['message'] : '';
            throw new ApiException($message ?: __('获取 Microsoft 用户信息失败', 'zib_language'), 0);
        }

        $openid = !empty($this->result['id']) ? $this->result['id'] : '';

        if (!$openid) {
            throw new ApiException(__('未获取到 Microsoft 用户标识', 'zib_language'), 0);
        }

        $this->result['name']  = $this->parseDisplayName($this->result);
        $this->result['email'] = !empty($this->result['mail']) ? $this->result['mail'] : (!empty($this->result['userPrincipalName']) ? $this->result['userPrincipalName'] : '');
        $this->openid          = $openid;
        return $this->result;
    }

    protected function parseDisplayName(array $userInfo)
    {
        $name = '';

        if (!empty($userInfo['displayName'])) {
            $name = trim($userInfo['displayName']);
        } elseif (!empty($userInfo['givenName'])) {
            $name = trim($userInfo['givenName'] . ' ' . (!empty($userInfo['surname']) ? $userInfo['surname'] : ''));
        } elseif (!empty($userInfo['mail'])) {
            $name = strstr($userInfo['mail'], '@', true);
        } elseif (!empty($userInfo['userPrincipalName'])) {
            $name = strstr($userInfo['userPrincipalName'], '@', true);
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

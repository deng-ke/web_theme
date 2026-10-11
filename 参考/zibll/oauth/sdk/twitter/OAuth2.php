<?php
namespace Zib\OAuthLogin\Twitter;

use Yurun\OAuthLogin\ApiException;
use Yurun\OAuthLogin\Base;

class OAuth2 extends Base
{
    const AUTH_URL = 'https://x.com/i/oauth2/authorize';

    const TOKEN_URL = 'https://api.x.com/2/oauth2/token';

    const USER_URL = 'https://api.x.com/2/users/me';

    /**
     * PKCE code_verifier
     *
     * @var string
     */
    public $codeVerifier = '';

    public function setCodeVerifier($codeVerifier)
    {
        $this->codeVerifier = (string) $codeVerifier;
    }

    public static function generateCodeVerifier()
    {
        return rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '=');
    }

    public static function generateCodeChallenge($codeVerifier)
    {
        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }

    public function getAuthUrl($callbackUrl = null, $state = null, $scope = null)
    {
        if (null === $scope && null === $this->scope) {
            $scope = 'tweet.read users.read offline.access';
        }

        if (!$this->codeVerifier) {
            throw new ApiException(__('Twitter 登录缺少 PKCE 参数', 'zib_language'), 0);
        }

        $option = array(
            'response_type'         => 'code',
            'client_id'             => $this->appid,
            'redirect_uri'          => null === $callbackUrl ? $this->callbackUrl : $callbackUrl,
            'scope'                 => null === $scope ? $this->scope : $scope,
            'state'                 => $this->getState($state),
            'code_challenge'        => static::generateCodeChallenge($this->codeVerifier),
            'code_challenge_method' => 'S256',
        );

        if (null === $this->loginAgentUrl) {
            return static::AUTH_URL . '?' . $this->http_build_query($option);
        }

        $option['code_verifier'] = $this->codeVerifier;
        unset($option['code_challenge'], $option['code_challenge_method']);

        return $this->loginAgentUrl . '?' . $this->http_build_query($option);
    }

    protected function __getAccessToken($storeState, $code = null, $state = null)
    {
        if (!$this->codeVerifier) {
            throw new ApiException(__('Twitter 登录缺少 PKCE 参数', 'zib_language'), 0);
        }

        $basicAuth = base64_encode($this->appid . ':' . $this->appSecret);

        $response = wp_remote_post(static::TOKEN_URL, array(
            'timeout' => 20,
            'headers' => array(
                'Authorization' => 'Basic ' . $basicAuth,
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ),
            'body'    => array(
                'grant_type'    => 'authorization_code',
                'code'          => isset($code) ? $code : (isset($_GET['code']) ? $_GET['code'] : ''),
                'redirect_uri'  => $this->getRedirectUri(),
                'code_verifier' => $this->codeVerifier,
            ),
        ));

        if (is_wp_error($response)) {
            throw new ApiException($response->get_error_message(), 0);
        }

        $this->result = json_decode(wp_remote_retrieve_body($response), true);

        if (!is_array($this->result)) {
            throw new ApiException(__('获取 Twitter 授权令牌失败，请检查服务器是否能访问 X 接口', 'zib_language'), 0);
        }

        if (isset($this->result['error'])) {
            $message = !empty($this->result['error_description']) ? $this->result['error_description'] : $this->result['error'];
            throw new ApiException($message, 0);
        }

        if (empty($this->result['access_token'])) {
            throw new ApiException(__('未获取到 Twitter 授权令牌', 'zib_language'), 0);
        }

        return $this->accessToken = $this->result['access_token'];
    }

    public function getUserInfo($accessToken = null)
    {
        $token = null === $accessToken ? $this->accessToken : $accessToken;

        $response = wp_remote_get(add_query_arg(array(
            'user.fields' => 'id,name,username,profile_image_url',
        ), static::USER_URL), array(
            'timeout' => 20,
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
            ),
        ));

        if (is_wp_error($response)) {
            throw new ApiException($response->get_error_message(), 0);
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!is_array($body)) {
            throw new ApiException(__('获取 Twitter 用户信息失败', 'zib_language'), 0);
        }

        if (isset($body['errors'][0]['message'])) {
            throw new ApiException($body['errors'][0]['message'], 0);
        }

        $this->result = !empty($body['data']) ? $body['data'] : array();

        $openid = !empty($this->result['id']) ? $this->result['id'] : '';

        if (!$openid) {
            throw new ApiException(__('未获取到 Twitter 用户标识', 'zib_language'), 0);
        }

        $this->result['name'] = $this->parseDisplayName($this->result);
        $this->openid         = $openid;
        return $this->result;
    }

    public function getAvatarUrl(array $userInfo)
    {
        if (empty($userInfo['profile_image_url'])) {
            return '';
        }

        return str_replace('_normal', '_400x400', $userInfo['profile_image_url']);
    }

    protected function parseDisplayName(array $userInfo)
    {
        $name = '';

        if (!empty($userInfo['name'])) {
            $name = trim($userInfo['name']);
        } elseif (!empty($userInfo['username'])) {
            $name = trim($userInfo['username']);
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

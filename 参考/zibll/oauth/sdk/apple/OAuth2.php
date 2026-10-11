<?php
namespace Zib\OAuthLogin\Apple;

use Yurun\OAuthLogin\ApiException;
use Yurun\OAuthLogin\Base;

class OAuth2 extends Base
{
    const AUTH_URL = 'https://appleid.apple.com/auth/authorize';

    const TOKEN_URL = 'https://appleid.apple.com/auth/token';

    /**
     * @var string
     */
    public $teamId = '';

    /**
     * @var string
     */
    public $keyId = '';

    /**
     * @var string
     */
    public $privateKey = '';

    /**
     * @var string
     */
    public $nonce = '';

    public function setAppleConfig(array $config)
    {
        $this->teamId     = !empty($config['team_id']) ? $config['team_id'] : '';
        $this->keyId      = !empty($config['key_id']) ? $config['key_id'] : '';
        $this->privateKey = !empty($config['appkrivatekey']) ? $config['appkrivatekey'] : '';
    }

    public function setNonce($nonce)
    {
        $this->nonce = (string) $nonce;
    }

    public function getAuthUrl($callbackUrl = null, $state = null, $scope = null)
    {
        if (null === $scope && null === $this->scope) {
            $scope = 'name email';
        }

        $option = array(
            'client_id'     => $this->appid,
            'redirect_uri'  => null === $callbackUrl ? $this->callbackUrl : $callbackUrl,
            'response_type' => 'code',
            'response_mode' => 'form_post',
            'scope'         => null === $scope ? $this->scope : $scope,
            'state'         => $this->getState($state),
        );

        if ($this->nonce) {
            $option['nonce'] = $this->nonce;
        }

        if (null === $this->loginAgentUrl) {
            return static::AUTH_URL . '?' . $this->http_build_query($option);
        }

        return $this->loginAgentUrl . '?' . $this->http_build_query($option);
    }

    protected function __getAccessToken($storeState, $code = null, $state = null)
    {
        $authCode = isset($code) ? $code : (isset($_POST['code']) ? $_POST['code'] : (isset($_GET['code']) ? $_GET['code'] : ''));

        if (!$authCode) {
            throw new ApiException(__('未获取到 Apple 授权码', 'zib_language'), 0);
        }

        $clientSecret = $this->generateClientSecret();

        $response = wp_remote_post(static::TOKEN_URL, array(
            'timeout' => 20,
            'body'    => array(
                'client_id'     => $this->appid,
                'client_secret' => $clientSecret,
                'code'          => $authCode,
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => $this->getRedirectUri(),
            ),
        ));

        if (is_wp_error($response)) {
            throw new ApiException($response->get_error_message(), 0);
        }

        $this->result = json_decode(wp_remote_retrieve_body($response), true);

        if (!is_array($this->result)) {
            throw new ApiException(__('获取 Apple 授权令牌失败', 'zib_language'), 0);
        }

        if (isset($this->result['error'])) {
            $message = !empty($this->result['error_description']) ? $this->result['error_description'] : $this->result['error'];
            throw new ApiException($message, 0);
        }

        if (empty($this->result['id_token']) && empty($this->result['access_token'])) {
            throw new ApiException(__('未获取到 Apple 授权令牌', 'zib_language'), 0);
        }

        if (!empty($this->result['access_token'])) {
            $this->accessToken = $this->result['access_token'];
        }

        return $this->accessToken;
    }

    public function getUserInfo($accessToken = null, array $firstAuthUser = array())
    {
        $idToken = !empty($this->result['id_token']) ? $this->result['id_token'] : '';

        if (!$idToken && !empty($_POST['id_token'])) {
            $idToken = $_POST['id_token'];
        }

        if (!$idToken) {
            throw new ApiException(__('未获取到 Apple 用户标识', 'zib_language'), 0);
        }

        $claims = $this->decodeIdTokenPayload($idToken);

        if (empty($claims['sub'])) {
            throw new ApiException(__('未获取到 Apple 用户标识', 'zib_language'), 0);
        }

        $this->result         = $claims;
        $this->result['name'] = $this->parseDisplayName($claims, $firstAuthUser);
        $this->openid         = $claims['sub'];

        if (!empty($claims['email'])) {
            $this->result['email'] = $claims['email'];
        } elseif (!empty($firstAuthUser['email'])) {
            $this->result['email'] = $firstAuthUser['email'];
        }

        return $this->result;
    }

    public function parseFirstAuthUser($rawUser)
    {
        if (is_array($rawUser)) {
            return $rawUser;
        }

        if (!is_string($rawUser) || $rawUser === '') {
            return array();
        }

        $user = json_decode($rawUser, true);
        return is_array($user) ? $user : array();
    }

    protected function parseDisplayName(array $claims, array $firstAuthUser = array())
    {
        $name = '';

        if (!empty($firstAuthUser['name'])) {
            $first = !empty($firstAuthUser['name']['firstName']) ? $firstAuthUser['name']['firstName'] : '';
            $last  = !empty($firstAuthUser['name']['lastName']) ? $firstAuthUser['name']['lastName'] : '';
            $name  = trim($first . ' ' . $last);
        }

        if (!$name && !empty($claims['email'])) {
            $name = strstr($claims['email'], '@', true);
        }

        $name = trim($name);

        if ($name && function_exists('zib_new_strlen')) {
            while (zib_new_strlen($name) > 16) {
                $name = function_exists('mb_substr') ? mb_substr($name, 0, -1, 'UTF-8') : substr($name, 0, -1);
            }
        }

        return $name;
    }

    public function generateClientSecret()
    {
        if (!$this->teamId || !$this->keyId || !$this->privateKey || !$this->appid) {
            throw new ApiException(__('Apple 登录配置不完整', 'zib_language'), 0);
        }

        $header = array(
            'alg' => 'ES256',
            'kid' => $this->keyId,
        );

        $now     = time();
        $payload = array(
            'iss' => $this->teamId,
            'iat' => $now,
            'exp' => $now + 86400 * 180,
            'aud' => 'https://appleid.apple.com',
            'sub' => $this->appid,
        );

        $segments      = array($this->base64UrlEncode(wp_json_encode($header)), $this->base64UrlEncode(wp_json_encode($payload)));
        $signingInput  = implode('.', $segments);
        $privateKey    = openssl_pkey_get_private($this->normalizePrivateKey($this->privateKey));

        if (!$privateKey) {
            throw new ApiException(__('Apple 私钥无效', 'zib_language'), 0);
        }

        $derSignature = '';
        if (!openssl_sign($signingInput, $derSignature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new ApiException(__('生成 Apple client_secret 失败', 'zib_language'), 0);
        }

        $segments[] = $this->base64UrlEncode($this->derToJoseSignature($derSignature));

        return implode('.', $segments);
    }

    public function decodeIdTokenPayload($idToken)
    {
        $parts = explode('.', $idToken);
        if (count($parts) < 2) {
            return array();
        }

        $payload = json_decode($this->base64UrlDecode($parts[1]), true);
        return is_array($payload) ? $payload : array();
    }

    protected function normalizePrivateKey($key)
    {
        $key = trim($key);
        if (strpos($key, 'BEGIN PRIVATE KEY') !== false) {
            return $key;
        }

        return "-----BEGIN PRIVATE KEY-----\n" . chunk_split(preg_replace('/\s+/', '', $key), 64, "\n") . "-----END PRIVATE KEY-----";
    }

    protected function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64UrlDecode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'));
    }

    protected function derToJoseSignature($der)
    {
        $pos = 0;
        if (ord($der[$pos++]) !== 0x30) {
            throw new ApiException(__('Apple 签名格式错误', 'zib_language'), 0);
        }

        $this->readDerLength($der, $pos);
        if (ord($der[$pos++]) !== 0x02) {
            throw new ApiException(__('Apple 签名格式错误', 'zib_language'), 0);
        }

        $rLen = $this->readDerLength($der, $pos);
        $r    = substr($der, $pos, $rLen);
        $pos += $rLen;

        if (ord($der[$pos++]) !== 0x02) {
            throw new ApiException(__('Apple 签名格式错误', 'zib_language'), 0);
        }

        $sLen = $this->readDerLength($der, $pos);
        $s    = substr($der, $pos, $sLen);

        return str_pad(ltrim($r, "\x00"), 32, "\x00", STR_PAD_LEFT) . str_pad(ltrim($s, "\x00"), 32, "\x00", STR_PAD_LEFT);
    }

    protected function readDerLength($der, &$pos)
    {
        $length = ord($der[$pos++]);
        if ($length & 0x80) {
            $byteCount = $length & 0x7f;
            $length    = 0;
            for ($i = 0; $i < $byteCount; $i++) {
                $length = ($length << 8) | ord($der[$pos++]);
            }
        }

        return $length;
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

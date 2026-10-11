<?php
/*
 * Project       : Nova主题
 * Description   : 短信服务类（阿里云/腾讯云短信）
 */

if (!defined('ABSPATH')) exit;

class NovaSms
{
    /**
     * 发送短信验证码
     * @param string $phone 手机号
     * @param string $code 验证码
     * @return bool
     */
    public static function send_code($phone, $code)
    {
        $provider = nova_get_setting('sms_provider', 'aliyun');
        if ('aliyun' === $provider) {
            return self::send_aliyun($phone, $code);
        }
        if ('tencent' === $provider) {
            return self::send_tencent($phone, $code);
        }
        return false;
    }

    /**
     * 阿里云短信
     */
    private static function send_aliyun($phone, $code)
    {
        $access_key = nova_get_setting('sms_aliyun_ak');
        $secret     = nova_get_setting('sms_aliyun_sk');
        $sign_name  = nova_get_setting('sms_aliyun_sign');
        $template   = nova_get_setting('sms_aliyun_tpl');
        if (!$access_key || !$secret || !$sign_name || !$template) return false;

        $params = array(
            'AccessKeyId'      => $access_key,
            'Action'           => 'SendSms',
            'Format'           => 'JSON',
            'PhoneNumbers'     => $phone,
            'RegionId'         => 'cn-hangzhou',
            'SignatureMethod'  => 'HMAC-SHA1',
            'SignatureNonce'   => uniqid(mt_rand(0, 0xffff), true),
            'SignatureVersion' => '1.0',
            'SignName'         => $sign_name,
            'TemplateCode'     => $template,
            'TemplateParam'    => json_encode(array('code' => $code)),
            'Timestamp'        => gmdate('Y-m-d\TH:i:s\Z'),
            'Version'          => '2017-05-25',
        );
        ksort($params);
        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $sign  = base64_encode(hash_hmac('sha1', 'GET&%2F&' . rawurlencode($query), $secret . '&', true));
        $url   = 'https://dysmsapi.aliyuncs.com/?' . $query . '&Signature=' . rawurlencode($sign);

        $response = wp_remote_get($url, array('timeout' => 10));
        if (is_wp_error($response)) return false;
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return !empty($body['Code']) && 'OK' === $body['Code'];
    }

    /**
     * 腾讯云短信
     */
    private static function send_tencent($phone, $code)
    {
        $secret_id  = nova_get_setting('sms_tencent_id');
        $secret_key = nova_get_setting('sms_tencent_key');
        $sdk_appid  = nova_get_setting('sms_tencent_appid');
        $sign_name  = nova_get_setting('sms_tencent_sign');
        $template   = nova_get_setting('sms_tencent_tpl');
        if (!$secret_id || !$secret_key || !$sdk_appid) return false;

        $payload = json_encode(array(
            'PhoneNumberSet'   => array('+86' . $phone),
            'SmsSdkAppId'      => $sdk_appid,
            'SignName'         => $sign_name,
            'TemplateId'       => $template,
            'TemplateParamSet' => array($code),
        ));

        $timestamp = time();
        $service   = 'sms';
        $host      = 'sms.tencentcloudapi.com';
        $date      = gmdate('Y-m-d');
        $algorithm = 'TC3-HMAC-SHA256';

        $canonical_request = "POST\n/\n\ncontent-type:application/json\nhost:$host\n\ncontent-type;host\n" . hash('sha256', $payload);
        $string_to_sign    = "$algorithm\n$timestamp\n$date/" . date('Ymd', $timestamp) . "/$service/tc3_request\n" . hash('sha256', $canonical_request);
        $secret_date       = hash_hmac('sha256', $date, 'TC3' . $secret_key, true);
        $secret_service    = hash_hmac('sha256', $service, $secret_date, true);
        $secret_signing    = hash_hmac('sha256', 'tc3_request', $secret_service, true);
        $signature         = hash_hmac('sha256', $string_to_sign, $secret_signing);
        $authorization     = "$algorithm Credential=$secret_id/$date/$service/tc3_request, SignedHeaders=content-type;host, Signature=$signature";

        $response = wp_remote_post('https://' . $host, array(
            'timeout' => 10,
            'headers' => array(
                'Content-Type'      => 'application/json',
                'X-TC-Action'       => 'SendSms',
                'X-TC-Version'      => '2021-01-11',
                'X-TC-Timestamp'    => $timestamp,
                'X-TC-Region'       => 'ap-guangzhou',
                'Authorization'     => $authorization,
            ),
            'body'    => $payload,
        ));
        if (is_wp_error($response)) return false;
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return !empty($body['Response']['SendStatusSet'][0]['Code']) && 'Ok' === $body['Response']['SendStatusSet'][0]['Code'];
    }
}

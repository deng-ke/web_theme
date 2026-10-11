<?php
/**
 * Stripe Checkout Session API
 */

class StripeCheckout
{
    private $secret_key;
    private $webhook_secret;
    private $base_url = 'https://api.stripe.com/v1';

    /**
     * 零小数货币（金额已为最小单位，不乘 100）
     *
     * @var string[]
     */
    private static $zero_decimal_currencies = array(
        'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA',
        'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    );

    public function __construct($config = array())
    {
        $this->secret_key     = isset($config['secret_key']) ? $config['secret_key'] : '';
        $this->webhook_secret = isset($config['webhook_secret']) ? $config['webhook_secret'] : '';
    }

    /**
     * @return array|\WP_Error
     */
    public function createSession($amount, $currency, $order_num, $order_name, $success_url, $cancel_url)
    {
        $minor_amount = self::toMinorAmount($amount, $currency);
        if ($minor_amount <= 0) {
            return new WP_Error('stripe_amount', __('Stripe 支付金额无效', 'zib_language'));
        }

        $body = array(
            'mode'                 => 'payment',
            'client_reference_id'  => (string) $order_num,
            'metadata'             => array(
                'order_num' => (string) $order_num,
            ),
            'success_url'          => $success_url,
            'cancel_url'           => $cancel_url,
            'line_items'           => array(
                array(
                    'quantity'   => 1,
                    'price_data' => array(
                        'currency'     => strtolower((string) $currency),
                        'unit_amount'  => $minor_amount,
                        'product_data' => array(
                            'name' => (string) $order_name,
                        ),
                    ),
                ),
            ),
        );

        $response = $this->request('POST', '/checkout/sessions', $body);
        if (is_wp_error($response)) {
            return $response;
        }

        if (empty($response['url'])) {
            return new WP_Error('stripe_session_url', __('未获取到 Stripe 支付跳转地址', 'zib_language'), $response);
        }

        return $response;
    }

    /**
     * @return array|\WP_Error
     */
    public function retrieveSession($session_id)
    {
        if (!$session_id) {
            return new WP_Error('stripe_session_id', __('Stripe Session ID 无效', 'zib_language'));
        }

        return $this->request('GET', '/checkout/sessions/' . rawurlencode($session_id));
    }

    /**
     * @param string $payload
     * @param string $sig_header
     * @param int $tolerance
     * @return bool|\WP_Error
     */
    public function verifyWebhook($payload, $sig_header, $tolerance = 300)
    {
        if (!$this->webhook_secret) {
            return new WP_Error('stripe_webhook_secret', __('Stripe Webhook Secret 未配置', 'zib_language'));
        }

        if (!$sig_header || !$payload) {
            return new WP_Error('stripe_webhook_header', __('Stripe Webhook 签名头无效', 'zib_language'));
        }

        $timestamp  = null;
        $signatures = array();
        foreach (explode(',', $sig_header) as $part) {
            $part = trim($part);
            if (strpos($part, '=') === false) {
                continue;
            }
            list($key, $value) = explode('=', $part, 2);
            if ($key === 't') {
                $timestamp = $value;
            } elseif ($key === 'v1') {
                $signatures[] = $value;
            }
        }

        if (!$timestamp || !$signatures) {
            return new WP_Error('stripe_webhook_parse', __('Stripe Webhook 签名解析失败', 'zib_language'));
        }

        if (abs(time() - (int) $timestamp) > $tolerance) {
            return new WP_Error('stripe_webhook_time', __('Stripe Webhook 签名已过期', 'zib_language'));
        }

        $signed_payload = $timestamp . '.' . $payload;
        $expected       = hash_hmac('sha256', $signed_payload, $this->webhook_secret);

        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) {
                return true;
            }
        }

        return new WP_Error('stripe_webhook_verify', __('Stripe Webhook 验签失败', 'zib_language'));
    }

    /**
     * @param array $session
     * @return array{order_num:string,pay_num:string}
     */
    public static function parsePaymentData($session)
    {
        $order_num = '';
        $pay_num   = '';

        if (!empty($session['client_reference_id'])) {
            $order_num = $session['client_reference_id'];
        }

        if (!$order_num && !empty($session['metadata']['order_num'])) {
            $order_num = $session['metadata']['order_num'];
        }

        if (!empty($session['payment_intent'])) {
            $pay_num = is_string($session['payment_intent']) ? $session['payment_intent'] : '';
        }

        if (!$pay_num && !empty($session['id'])) {
            $pay_num = $session['id'];
        }

        return array(
            'order_num' => $order_num,
            'pay_num'   => $pay_num,
            'pay_price' => !empty($session['amount_total']) ? ((float) $session['amount_total'] / 100) : 0,
        );
    }

    /**
     * @param string|float $amount
     * @param string $currency
     * @return int
     */
    public static function toMinorAmount($amount, $currency)
    {
        $currency = strtoupper((string) $currency);
        if (in_array($currency, self::$zero_decimal_currencies, true)) {
            return (int) round((float) $amount);
        }

        return (int) round(bcmul((string) $amount, '100', 0));
    }

    /**
     * @param string $method
     * @param string $path
     * @param array|null $body
     * @return array|\WP_Error
     */
    private function request($method, $path, $body = null)
    {
        if (!$this->secret_key) {
            return new WP_Error('stripe_secret_key', __('Stripe Secret Key 未配置', 'zib_language'));
        }

        $args = array(
            'timeout' => 20,
            'method'  => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->secret_key,
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ),
        );

        if ($body !== null) {
            $args['body'] = $this->buildQuery($body);
        }

        $response = wp_remote_request($this->base_url . $path, $args);
        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($data)) {
            $data = array();
        }

        if ($code < 200 || $code >= 300) {
            $message = '';
            if (!empty($data['error']['message'])) {
                $message = $data['error']['message'];
            } else {
                $message = __('Stripe 接口请求失败', 'zib_language');
            }
            return new WP_Error('stripe_http', $message, array('code' => $code, 'data' => $data));
        }

        return $data;
    }

    /**
     * Stripe API 使用 bracket 形式的 form body
     *
     * @param array $params
     * @param string $prefix
     * @return string
     */
    private function buildQuery($params, $prefix = '')
    {
        $parts = array();
        foreach ($params as $key => $value) {
            $name = $prefix === '' ? $key : $prefix . '[' . $key . ']';
            if (is_array($value)) {
                $parts[] = $this->buildQuery($value, $name);
            } else {
                $parts[] = rawurlencode($name) . '=' . rawurlencode((string) $value);
            }
        }

        return implode('&', $parts);
    }
}

/**
 * Stripe 入账
 *
 * @param string $order_num
 * @param string $pay_num
 * @param float|null $pay_price
 * @return array|false
 */
function zibpay_stripe_payment_order($order_num, $pay_num, $pay_price = null)
{
    if (!$order_num || !$pay_num) {
        return false;
    }

    $pay_order_data = array(
        'order_num' => $order_num,
        'pay_type'  => 'stripe',
        'pay_num'   => $pay_num,
    );
    if ($pay_price !== null) {
        $pay_order_data['pay_price'] = $pay_price;
    }

    return ZibPay::payment_order($pay_order_data);
}

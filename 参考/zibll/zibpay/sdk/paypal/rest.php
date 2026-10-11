<?php
/**
 * PayPal Orders v2 REST API
 */

class PayPalRest
{
    private $client_id;
    private $client_secret;
    private $webhook_id;
    private $base_url;
    private $access_token;

    public function __construct($config = array())
    {
        $this->client_id     = isset($config['client_id']) ? $config['client_id'] : '';
        $this->client_secret = isset($config['client_secret']) ? $config['client_secret'] : '';
        $this->webhook_id    = isset($config['webhook_id']) ? $config['webhook_id'] : '';
        $this->base_url      = !empty($config['debug']) ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
    }

    /**
     * @return string|\WP_Error
     */
    public function getAccessToken()
    {
        if ($this->access_token) {
            return $this->access_token;
        }

        $response = wp_remote_post($this->base_url . '/v1/oauth2/token', array(
            'timeout' => 20,
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($this->client_id . ':' . $this->client_secret),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ),
            'body'    => array(
                'grant_type' => 'client_credentials',
            ),
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($data['access_token'])) {
            $message = !empty($data['error_description']) ? $data['error_description'] : __('获取 PayPal 授权令牌失败', 'zib_language');
            return new WP_Error('paypal_rest_token', $message, $data);
        }

        $this->access_token = $data['access_token'];
        return $this->access_token;
    }

    /**
     * @return array|\WP_Error
     */
    public function createOrder($amount, $currency, $order_num, $order_name, $return_url, $cancel_url)
    {
        $token = $this->getAccessToken();
        if (is_wp_error($token)) {
            return $token;
        }

        $body = array(
            'intent'              => 'CAPTURE',
            'purchase_units'      => array(
                array(
                    'reference_id' => (string) $order_num,
                    'custom_id'    => (string) $order_num,
                    'description'  => (string) $order_name,
                    'amount'       => array(
                        'currency_code' => (string) $currency,
                        'value'         => (string) $amount,
                    ),
                ),
            ),
            'application_context' => array(
                'return_url'  => $return_url,
                'cancel_url'  => $cancel_url,
                'user_action' => 'PAY_NOW',
            ),
        );

        $response = $this->request('POST', '/v2/checkout/orders', $body);
        if (is_wp_error($response)) {
            return $response;
        }

        $approve_url = $this->getLink($response, 'approve');
        if (!$approve_url) {
            return new WP_Error('paypal_rest_approve', __('未获取到 PayPal 支付跳转地址', 'zib_language'), $response);
        }

        $response['approve_url'] = $approve_url;
        return $response;
    }

    /**
     * @return array|\WP_Error
     */
    public function captureOrder($paypal_order_id)
    {
        $token = $this->getAccessToken();
        if (is_wp_error($token)) {
            return $token;
        }

        return $this->request('POST', '/v2/checkout/orders/' . rawurlencode($paypal_order_id) . '/capture', new stdClass());
    }

    /**
     * @return array|\WP_Error
     */
    public function getOrder($paypal_order_id)
    {
        $token = $this->getAccessToken();
        if (is_wp_error($token)) {
            return $token;
        }

        return $this->request('GET', '/v2/checkout/orders/' . rawurlencode($paypal_order_id));
    }

    /**
     * @param array $headers
     * @param string $body
     * @return bool|\WP_Error
     */
    public function verifyWebhook($headers, $body)
    {
        if (!$this->webhook_id) {
            return true;
        }

        $token = $this->getAccessToken();
        if (is_wp_error($token)) {
            return $token;
        }

        $event = json_decode($body, true);
        if (!is_array($event)) {
            return new WP_Error('paypal_webhook_body', __('Webhook 数据无效', 'zib_language'));
        }

        $verify_body = array(
            'auth_algo'         => $this->getHeader($headers, 'paypal-auth-algo'),
            'cert_url'          => $this->getHeader($headers, 'paypal-cert-url'),
            'transmission_id'   => $this->getHeader($headers, 'paypal-transmission-id'),
            'transmission_sig'  => $this->getHeader($headers, 'paypal-transmission-sig'),
            'transmission_time' => $this->getHeader($headers, 'paypal-transmission-time'),
            'webhook_id'        => $this->webhook_id,
            'webhook_event'     => $event,
        );

        $response = $this->request('POST', '/v1/notifications/verify-webhook-signature', $verify_body);
        if (is_wp_error($response)) {
            return $response;
        }

        if (empty($response['verification_status']) || $response['verification_status'] !== 'SUCCESS') {
            return new WP_Error('paypal_webhook_verify', __('PayPal Webhook 验签失败', 'zib_language'), $response);
        }

        return true;
    }

    /**
     * 从 capture / order 响应解析本地订单号与 PayPal 流水号
     *
     * @param array $data
     * @return array{order_num:string,pay_num:string}
     */
    public static function parsePaymentData($data)
    {
        $order_num = '';
        $pay_num   = '';
        $pay_price = 0;

        if (!empty($data['custom_id'])) {
            $order_num = $data['custom_id'];
        }

        if (!empty($data['id'])) {
            $pay_num = $data['id'];
        }

        if (!empty($data['amount']['value'])) {
            $pay_price = $data['amount']['value'];
        }

        if (!empty($data['purchase_units']) && is_array($data['purchase_units'])) {
            $unit = $data['purchase_units'][0];
            if (!$order_num && !empty($unit['custom_id'])) {
                $order_num = $unit['custom_id'];
            }
            if (!$order_num && !empty($unit['reference_id'])) {
                $order_num = $unit['reference_id'];
            }
            if (!empty($unit['payments']['captures'][0]['id'])) {
                $pay_num = $unit['payments']['captures'][0]['id'];
            }
            if (!$order_num && !empty($unit['payments']['captures'][0]['custom_id'])) {
                $order_num = $unit['payments']['captures'][0]['custom_id'];
            }
            if (!$pay_price && !empty($unit['amount']['value'])) {
                $pay_price = $unit['amount']['value'];
            }
            if (!$pay_price && !empty($unit['payments']['captures'][0]['amount']['value'])) {
                $pay_price = $unit['payments']['captures'][0]['amount']['value'];
            }
        }

        return array(
            'order_num' => $order_num,
            'pay_num'   => $pay_num,
            'pay_price' => $pay_price,
        );
    }

    /**
     * @param string $method
     * @param string $path
     * @param array|object|null $body
     * @return array|\WP_Error
     */
    private function request($method, $path, $body = null)
    {
        $token = $this->getAccessToken();
        if (is_wp_error($token)) {
            return $token;
        }

        $args = array(
            'timeout' => 20,
            'method'  => $method,
            'headers' => array(
                'Authorization'                 => 'Bearer ' . $token,
                'Content-Type'                  => 'application/json',
                'PayPal-Partner-Attribution-Id' => 'Zibll_SP',
            ),
        );

        if ($body !== null) {
            $args['body'] = wp_json_encode($body);
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
            if (!empty($data['message'])) {
                $message = $data['message'];
            } elseif (!empty($data['details'][0]['description'])) {
                $message = $data['details'][0]['description'];
            } elseif (!empty($data['error_description'])) {
                $message = $data['error_description'];
            } else {
                $message = __('PayPal 接口请求失败', 'zib_language');
            }
            return new WP_Error('paypal_rest_http', $message, array('code' => $code, 'data' => $data));
        }

        return $data;
    }

    private function getLink($response, $rel)
    {
        if (empty($response['links']) || !is_array($response['links'])) {
            return '';
        }
        foreach ($response['links'] as $link) {
            if (!empty($link['rel']) && $link['rel'] === $rel && !empty($link['href'])) {
                return $link['href'];
            }
        }
        return '';
    }

    private function getHeader($headers, $name)
    {
        $name = strtolower($name);
        foreach ((array) $headers as $key => $value) {
            if (strtolower($key) === $name) {
                return is_array($value) ? reset($value) : $value;
            }
        }
        return '';
    }

    /**
     * PayPal Payouts 批量打款（单笔）
     *
     * @return array|\WP_Error
     */
    public function createPayout($email, $amount, $currency, $sender_batch_id, $note = '')
    {
        if (!is_email($email)) {
            return new WP_Error('paypal_payout_email', __('PayPal 收款邮箱无效', 'zib_language'));
        }

        $token = $this->getAccessToken();
        if (is_wp_error($token)) {
            return $token;
        }

        $body = array(
            'sender_batch_header' => array(
                'sender_batch_id' => (string) $sender_batch_id,
                'email_subject'   => get_bloginfo('name'),
            ),
            'items'               => array(
                array(
                    'recipient_type' => 'EMAIL',
                    'amount'         => array(
                        'value'    => (string) $amount,
                        'currency' => (string) strtoupper($currency),
                    ),
                    'receiver'       => (string) $email,
                    'note'           => (string) $note,
                    'sender_item_id' => (string) $sender_batch_id,
                ),
            ),
        );

        return $this->request('POST', '/v1/payments/payouts', $body);
    }
}

/**
 * PayPal REST 入账
 *
     * @param string $order_num
     * @param string $pay_num
     * @param float|null $pay_price
     * @return array|false
 */
function zibpay_paypal_rest_payment_order($order_num, $pay_num, $pay_price = null)
{
    if (!$order_num || !$pay_num) {
        return false;
    }

    $pay_order_data = array(
        'order_num' => $order_num,
        'pay_type'  => 'paypal',
        'pay_num'   => $pay_num,
    );
    if ($pay_price !== null) {
        $pay_order_data['pay_price'] = $pay_price;
    }

    return ZibPay::payment_order($pay_order_data);
}

add_filter('zibpay_payout_paypal', 'zibpay_payout_paypal_request', 10, 2);

/**
 * @param array $error
 * @param array $args
 * @return array
 */
function zibpay_payout_paypal_request($error, $args)
{
    $config = zibpay_get_payconfig('paypal');
    if (empty($config['rest_s'])) {
        $error['msg'] = __('PayPal API 打款需启用 REST 新接口', 'zib_language');
        return $error;
    }

    $user_id     = (int) ($args['user_id'] ?? 0);
    $amount      = isset($args['amount']) ? (float) $args['amount'] : 0;
    $withdraw_id = (int) ($args['withdraw_id'] ?? 0);
    $desc        = !empty($args['desc']) ? $args['desc'] : __('Commission withdrawal', 'zib_language');

    $email = zibpay_get_user_payout_account($user_id, 'paypal');
    if (!$email || !is_email($email)) {
        $error['msg'] = __('用户未绑定 PayPal 收款邮箱', 'zib_language');
        return $error;
    }

    if ($amount <= 0) {
        $error['msg'] = __('打款金额无效', 'zib_language');
        return $error;
    }

    $currency = !empty($config['currency']) ? $config['currency'] : 'USD';
    $out_no   = zibpay_payout_build_out_no('WD', $withdraw_id ?: $user_id);

    $paypal = new PayPalRest($config);
    $result = $paypal->createPayout($email, zib_floatval_round($amount, 2), $currency, $out_no, $desc);

    if (is_wp_error($result)) {
        $error['msg'] = $result->get_error_message();
        $error['raw'] = $result->get_error_data();
        if ($withdraw_id) {
            zibpay_payout_api_log('payout', $withdraw_id, array(
                'success'       => false,
                'channel'       => 'paypal',
                'local_amount'  => $args['local_amount'] ?? 0,
                'settle_amount' => $amount,
                'settle_rate'   => $args['settle_rate'] ?? 1,
                'out_no'        => $out_no,
                'msg'           => $error['msg'],
                'raw'           => $error['raw'],
                'time'          => current_time('Y-m-d H:i:s'),
            ));
        }
        return $error;
    }

    $batch_id = !empty($result['batch_header']['payout_batch_id']) ? $result['batch_header']['payout_batch_id'] : $out_no;
    $success  = array(
        'success' => true,
        'out_no'  => $batch_id,
        'msg'     => __('PayPal 打款已提交', 'zib_language'),
        'raw'     => $result,
    );

    if ($withdraw_id) {
        zibpay_payout_api_log('payout', $withdraw_id, array_merge($success, array(
            'channel'       => 'paypal',
            'local_amount'  => $args['local_amount'] ?? 0,
            'settle_amount' => $amount,
            'settle_rate'   => $args['settle_rate'] ?? 1,
            'time'          => current_time('Y-m-d H:i:s'),
        )));
    }

    return $success;
}

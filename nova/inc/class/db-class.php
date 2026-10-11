<?php
/*
 * Project       : Nova主题
 * Description   : 数据库操作类（订单/消息/积分记录等自建表统一管理）
 */

if (!defined('ABSPATH')) exit;

class NovaDB
{
    /**
     * 统一建表入口
     */
    public static function setup_tables()
    {
        self::create_table_order();
        self::create_table_order_meta();
        self::create_table_message();
        self::create_table_points();
        self::create_table_sign();
        self::create_table_charge();
        self::create_table_withdraw();
        self::create_table_forum_post();
    }

    /**
     * 通用建表方法
     */
    private static function create($table, $sql)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $table;
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }
        return $table_name;
    }

    /**
     * 订单表
     */
    public static function create_table_order()
    {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $sql     = "CREATE TABLE {$wpdb->prefix}nova_order (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            order_num VARCHAR(32) NOT NULL DEFAULT '',
            user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            post_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            order_type VARCHAR(20) NOT NULL DEFAULT 'post', 
            pay_type VARCHAR(20) NOT NULL DEFAULT 'alipay',
            order_price DECIMAL(10,2) NOT NULL DEFAULT 0,
            pay_price DECIMAL(10,2) NOT NULL DEFAULT 0,
            order_status TINYINT(1) NOT NULL DEFAULT 0,
            pay_num VARCHAR(64) NOT NULL DEFAULT '',
            create_time DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            pay_time DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            ip_address VARCHAR(64) NOT NULL DEFAULT '',
            PRIMARY KEY (id),
            UNIQUE KEY order_num (order_num),
            KEY user_id (user_id),
            KEY post_id (post_id)
        ) $charset;";
        self::create('nova_order', $sql);
    }

    /**
     * 订单附加信息表
     */
    public static function create_table_order_meta()
    {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $sql     = "CREATE TABLE {$wpdb->prefix}nova_order_meta (
            meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            meta_key VARCHAR(255) NOT NULL DEFAULT '',
            meta_value LONGTEXT NOT NULL,
            PRIMARY KEY (meta_id),
            KEY order_id (order_id),
            KEY meta_key (meta_key)
        ) $charset;";
        self::create('nova_order_meta', $sql);
    }

    /**
     * 消息表
     */
    public static function create_table_message()
    {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $sql     = "CREATE TABLE {$wpdb->prefix}nova_message (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            from_user BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            msg_type VARCHAR(20) NOT NULL DEFAULT 'system',
            msg_content TEXT NOT NULL,
            msg_meta TEXT NOT NULL,
            msg_read TINYINT(1) NOT NULL DEFAULT 0,
            create_time DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY msg_type (msg_type)
        ) $charset;";
        self::create('nova_message', $sql);
    }

    /**
     * 积分记录表
     */
    public static function create_table_points()
    {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $sql     = "CREATE TABLE {$wpdb->prefix}nova_points (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            points_type VARCHAR(20) NOT NULL DEFAULT 'add',
            points_num INT(11) NOT NULL DEFAULT 0,
            points_desc VARCHAR(255) NOT NULL DEFAULT '',
            create_time DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset;";
        self::create('nova_points', $sql);
    }

    /**
     * 签到表
     */
    public static function create_table_sign()
    {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $sql     = "CREATE TABLE {$wpdb->prefix}nova_sign (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            sign_date DATE NOT NULL DEFAULT '0000-00-00',
            points_num INT(11) NOT NULL DEFAULT 0,
            create_time DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (id),
            UNIQUE KEY user_date (user_id, sign_date)
        ) $charset;";
        self::create('nova_sign', $sql);
    }

    /**
     * 充值卡表
     */
    public static function create_table_charge()
    {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $sql     = "CREATE TABLE {$wpdb->prefix}nova_charge (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            card_num VARCHAR(32) NOT NULL DEFAULT '',
            card_pass VARCHAR(32) NOT NULL DEFAULT '',
            card_price DECIMAL(10,2) NOT NULL DEFAULT 0,
            card_type VARCHAR(20) NOT NULL DEFAULT 'money',
            card_status TINYINT(1) NOT NULL DEFAULT 0,
            user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            use_time DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            create_time DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (id),
            UNIQUE KEY card_num (card_num)
        ) $charset;";
        self::create('nova_charge', $sql);
    }

    /**
     * 提现表
     */
    public static function create_table_withdraw()
    {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $sql     = "CREATE TABLE {$wpdb->prefix}nova_withdraw (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            withdraw_type VARCHAR(20) NOT NULL DEFAULT 'alipay',
            withdraw_account VARCHAR(64) NOT NULL DEFAULT '',
            withdraw_name VARCHAR(64) NOT NULL DEFAULT '',
            withdraw_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
            withdraw_status TINYINT(1) NOT NULL DEFAULT 0,
            create_time DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            admin_remark VARCHAR(255) NOT NULL DEFAULT '',
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset;";
        self::create('nova_withdraw', $sql);
    }

    /**
     * 论坛帖子表
     */
    public static function create_table_forum_post()
    {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $sql     = "CREATE TABLE {$wpdb->prefix}nova_forum_post (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            plate_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            post_title VARCHAR(255) NOT NULL DEFAULT '',
            post_content LONGTEXT NOT NULL,
            post_status TINYINT(1) NOT NULL DEFAULT 0,
            comment_count INT(11) NOT NULL DEFAULT 0,
            view_count INT(11) NOT NULL DEFAULT 0,
            like_count INT(11) NOT NULL DEFAULT 0,
            is_top TINYINT(1) NOT NULL DEFAULT 0,
            is_essence TINYINT(1) NOT NULL DEFAULT 0,
            last_reply_time DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            create_time DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY user_id (user_id),
            KEY plate_id (plate_id)
        ) $charset;";
        self::create('nova_forum_post', $sql);
    }

    /**
     * 获取用户余额
     */
    public static function get_user_balance($user_id)
    {
        return (float) get_user_meta($user_id, 'nova_balance', true);
    }

    /**
     * 变更用户余额
     * @param int $user_id 用户ID
     * @param float $num 正数增加负数减少
     * @param string $desc 变更说明
     */
    public static function update_user_balance($user_id, $num, $desc = '')
    {
        $balance = self::get_user_balance($user_id);
        $new     = round($balance + $num, 2);
        if ($new < 0) return false;
        update_user_meta($user_id, 'nova_balance', $new);
        if ($desc) {
            NovaMsg::add_system($user_id, $desc . '（余额变动 ' . (($num > 0 ? '+' : '') . $num) . '，当前余额 ' . $new . '）');
        }
        return $new;
    }

    /**
     * 获取用户积分
     */
    public static function get_user_points($user_id)
    {
        return (int) get_user_meta($user_id, 'nova_points', true);
    }

    /**
     * 变更用户积分
     */
    public static function update_user_points($user_id, $num, $type = 'add', $desc = '')
    {
        global $wpdb;
        $table  = $wpdb->prefix . 'nova_points';
        $points = self::get_user_points($user_id);
        $new    = max(0, $points + $num);
        update_user_meta($user_id, 'nova_points', $new);
        $wpdb->insert($table, array(
            'user_id'     => $user_id,
            'points_type' => $type,
            'points_num'  => $num,
            'points_desc' => $desc,
            'create_time' => current_time('mysql'),
        ));
        return $new;
    }
}

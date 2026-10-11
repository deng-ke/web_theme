<?php
/*
 * Project       : Nova主题
 * Description   : 类库入口
 */

if (!defined('ABSPATH')) exit;

nova_require(array(
    'file-class',
    'db-class',
    'sms-class',
), true, 'inc/class/');

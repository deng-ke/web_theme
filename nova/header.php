<?php
/*
 * Project       : Nova主题
 * Description   : 页面头部模板
 */

if (!defined('ABSPATH')) exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php nova_page_loader(); ?>

<div id="nova-app" class="nova-app">

<?php nova_header(); ?>

<?php if (nova_is_mobile()) : ?>
    <div class="nova-header-placeholder"></div>
<?php endif; ?>

<div class="nova-main">
<?php
//全站顶部小工具
if (is_active_sidebar('all_top_fluid') && !is_front_page()) {
    echo '<div class="nova-widgets-fluid">';
    dynamic_sidebar('all_top_fluid');
    echo '</div>';
}
?>

<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2021-04-11 21:36:20
 * @LastEditTime : 2026-05-25 00:03:47
 */
defined('ABSPATH') or exit();

global $post, $zib_bbs;
$post_id       = $post->ID;
$comments_open = comments_open($post);
$count_t       = $post->comment_count;
$user_id       = get_current_user_id();
$comment_name  = $zib_bbs->comment_name;

?>

<div id="comments">
    <div class="comment-box">
        <?php echo zib_bbs_get_respond(); ?>
    </div>
    <div class="zib-widget comment-box" id="postcomments">
        <ol class="commentlist list-unstyled bbs-commentlist">
            <?php
            if (!zib_current_user_can('comment_view', $post)) {
                if ($user_id) {
                    echo zib_get_nocan_info($user_id, 'comment_view', sprintf(__('无法查看%s内容', 'zib_language'), $comment_name));
                } else {
                    echo zib_get_null(sprintf(__('请登录后查看%s内容', 'zib_language'), $comment_name), 20, 'null-user.svg', 'comment-null');
                }
            } elseif (have_comments()) {
                echo zib_bbs_get_comment_title();
                wp_list_comments(
                    array(
                        'type'     => 'comment',
                        'callback' => 'zib_comments_list',
                    )
                );
                $loader = '<div style="display:none;" class="post_ajax_loader"><ul class="list-inline flex"><div class="avatar-img placeholder radius"></div><li class="flex1"><div class="placeholder s1 mb6" style="width: 30%;"></div><div class="placeholder k2 mb10"></div><i class="placeholder s1 mb6"></i><i class="placeholder s1 mb6 ml10"></i></li></ul><ul class="list-inline flex"><div class="avatar-img placeholder radius"></div><li class="flex1"><div class="placeholder s1 mb6" style="width: 30%;"></div><div class="placeholder k2 mb10"></div><i class="placeholder s1 mb6"></i><i class="placeholder s1 mb6 ml10"></i></li></ul><ul class="list-inline flex"><div class="avatar-img placeholder radius"></div><li class="flex1"><div class="placeholder s1 mb6" style="width: 30%;"></div><div class="placeholder k2 mb10"></div><i class="placeholder s1 mb6"></i><i class="placeholder s1 mb6 ml10"></i></li></ul><ul class="list-inline flex"><div class="avatar-img placeholder radius"></div><li class="flex1"><div class="placeholder s1 mb6" style="width: 30%;"></div><div class="placeholder k2 mb10"></div><i class="placeholder s1 mb6"></i><i class="placeholder s1 mb6 ml10"></i></li></ul></div>';
                echo $loader;
                zib_bbs_comment_paginate();
            } else {
                echo zib_get_null(sprintf(__('没有%s内容', 'zib_language'), $comment_name), 40, 'null.svg', 'comment comment-null');
                echo '<div class="pagenav hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';
            }
            ?>
        </ol>
    </div>
</div>
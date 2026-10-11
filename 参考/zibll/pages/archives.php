<?php

/**
 * Template name: Zibll-文章归档
 * Description:   A archives page
 */

get_header();
$post_id = get_queried_object_id();
$header_style = zib_get_page_header_style($post_id);
?>
<main class="container">
    <div class="content-wrap">
        <div class="content-layout 55">
        <?php  if($header_style != 1){echo zib_get_page_header($post_id);}?>
            <div class="theme-box radius8">
            <?php  if($header_style == 1){ echo zib_get_page_header($post_id);}?>
                <article class="archive">
                    <div class="ajaxpager">
                        <?php echo zib_get_archives_posts_lists(); ?>
                    </div>
                </article>
            </div>
            <?php comments_template('/template/comments.php', true); ?>
        </div>
    </div>
    <?php get_sidebar(); ?>
</main>

<?php

get_footer();

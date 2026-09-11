<?php
/**
 * Main template file
 *
 * @package CMS_NhomC
 */

get_header();
?>

<main class="site-content">
    <?php if (have_posts()) : ?>
        <div class="cms-post-list">
            <?php
            while (have_posts()) :
                the_post();
                cms_nhomc_render_post_card(get_the_ID());
            endwhile;
            ?>
        </div>

        <div class="cms-pagination">
            <?php
            the_posts_pagination(array(
                'mid_size'  => 2,
                'prev_text' => __('&laquo; Trước', 'cms-nhomc'),
                'next_text' => __('Sau &raquo;', 'cms-nhomc'),
            ));
            ?>
        </div>
    <?php else : ?>
        <div class="content-card">
            <p>Chưa có bài viết nào được đăng tải. Bạn có thể vào trang quản trị để thêm bài viết mới.</p>
        </div>
    <?php endif; ?>
</main>

<?php
get_footer();

<?php
/**
 * Main template file (Content)
 *
 * @package CMS_NhomC
 */

get_header();
?>

<div class="site-content cms-container-layout">
    <div class="cms-layout-grid">
        <!-- Cột nội dung danh sách bài viết -->
        <main class="cms-main-column">
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

        <!-- Sidebar bên phải: Categories & BÀI VIẾT NỔI BẬT trong Content -->
        <aside class="cms-sidebar-column">
            <?php cms_nhomc_render_categories_widget(); ?>
            <?php cms_nhomc_render_featured_posts_widget(5, 'BÀI VIẾT NỔI BẬT'); ?>
            <?php cms_nhomc_render_comments_widget(3, 'Comments'); ?>
            <?php if (function_exists('cms_nhomc_render_last_posts_widget')) { cms_nhomc_render_last_posts_widget(5, 'Latest News'); } ?>
        </aside>
    </div>
</div>

<?php
get_footer();

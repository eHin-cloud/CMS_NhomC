<?php
/**
 * Archive template file (Chuyên mục & Lưu trữ)
 *
 * @package CMS_NhomC
 */

get_header();
?>

<div class="site-content cms-container-layout">
    <!-- Breadcrumbs (Module 19) -->
    <?php cms_nhomc_breadcrumbs(); ?>

    <header class="cms-archive-header">
        <h1 class="cms-archive-title"><?php the_archive_title(); ?></h1>
        <?php the_archive_description('<div class="cms-archive-desc">', '</div>'); ?>
    </header>

    <div class="cms-layout-grid">
        <!-- Cột nội dung chính danh sách bài viết lưu trữ -->
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
                    <p>Chưa có bài viết nào trong mục này.</p>
                </div>
            <?php endif; ?>
        </main>

        <!-- Sidebar bên phải: Hiển thị Categories, Bài viết nổi bật, Bài viết mới & Comments -->
        <aside class="cms-sidebar-column">
            <?php 
            cms_nhomc_render_categories_widget();
            cms_nhomc_render_featured_posts_widget(5, 'BÀI VIẾT NỔI BẬT'); 
            cms_nhomc_render_recent_posts_widget(10, 'BÀI VIẾT MỚI');
            if (function_exists('cms_nhomc_render_newsletter_widget')) {
                cms_nhomc_render_newsletter_widget('NEWSLETTER');
            }
            cms_nhomc_render_comments_widget(3, 'Comments');
            if (function_exists('cms_nhomc_render_pages_widget')) {
                cms_nhomc_render_pages_widget(3, 'Trang mới nhất');
            }
            if (function_exists('cms_nhomc_render_last_posts_widget')) {
                cms_nhomc_render_last_posts_widget(5, 'Latest News');
            }
            ?>
        </aside>
    </div>
</div>

<?php
get_footer();

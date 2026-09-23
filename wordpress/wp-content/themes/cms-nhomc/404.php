<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 * @package CMS_NhomC
 */

get_header();
?>

<div class="site-content cms-container-layout cms-404-page-layout">
    <!-- Breadcrumbs (Module 19) -->
    <?php cms_nhomc_breadcrumbs(); ?>

    <div class="cms-404-wrapper">
        <div class="cms-404-content text-center">
            <div class="cms-404-badge">404</div>
            <h1 class="cms-404-title"><?php esc_html_e('Không tìm thấy trang yêu cầu', 'cms-nhomc'); ?></h1>
            <p class="cms-404-desc">
                <?php esc_html_e('Trang hoặc bài viết bạn đang tìm kiếm không tồn tại, đã bị gỡ bỏ hoặc đường dẫn không chính xác.', 'cms-nhomc'); ?>
            </p>

            <div class="cms-404-search-box">
                <p class="cms-404-search-lead"><?php esc_html_e('Thử tìm kiếm nội dung khác:', 'cms-nhomc'); ?></p>
                <?php get_search_form(); ?>
            </div>

            <div class="cms-404-actions">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="cms-btn-home">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                    <?php esc_html_e('Về trang chủ FIT-TDC', 'cms-nhomc'); ?>
                </a>
            </div>
        </div>

        <!-- Khối gợi ý bài viết xem nhiều / mới nhất -->
        <div class="cms-404-suggestions">
            <h2 class="cms-404-suggestions-title"><?php esc_html_e('BÀI VIẾT NỔI BẬT & ĐỌC NHIỀU', 'cms-nhomc'); ?></h2>
            <?php
            if (function_exists('cms_nhomc_render_recent_posts_widget')) {
                cms_nhomc_render_recent_posts_widget(6, 'BÀI VIẾT MỚI & NỔI BẬT');
            }
            ?>
        </div>
    </div>
</div>

<?php
get_footer();

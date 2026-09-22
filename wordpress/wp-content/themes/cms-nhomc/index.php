<?php
/**
 * Main template file (Content)
 *
 * @package CMS_NhomC
 */

get_header();
?>

<div class="site-content cms-container-layout cms-home-container-layout">
    <!-- Khối Tin Tiêu Điểm / Hero Slider (Module 16 - Anh Quý) -->
    <?php if (function_exists('cms_nhomc_render_featured_slider')) : ?>
        <?php cms_nhomc_render_featured_slider(4, 'TIN TIÊU ĐIỂM'); ?>
    <?php endif; ?>

    <!-- Hàng 3 cột theo sơ đồ Hình 1: Archive (Trái) | Content (Giữa) | Comments (Phải) -->
    <div class="cms-layout-grid cms-home-grid">
        <!-- Cột trái: Archive (Module 11) -->
        <aside class="cms-sidebar-column cms-home-sidebar-left" aria-label="Lưu trữ">
            <?php cms_nhomc_render_archive_widget('Xem nhiều'); ?>
        </aside>

        <!-- Cột giữa: Content (Module 2) - Danh sách bài viết -->
        <main class="cms-main-column cms-home-main-column">
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

        <!-- Cột phải: Comments (Module 12) chuẩn theo Hình 2 -->
        <aside class="cms-sidebar-column cms-home-sidebar-right" aria-label="Bình luận">
            <?php cms_nhomc_render_comments_widget(3, 'Comments'); ?>
        </aside>
    </div>
</div>

<?php
get_footer();

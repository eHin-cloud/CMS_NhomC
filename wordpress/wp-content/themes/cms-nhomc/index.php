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

    <!-- ==============================================================================
         HÀNG 3 CỘT HOMEPAGE THEO SƠ ĐỒ THIẾT KẾ:
         - Cột trái: Archive (Module 11 - Vinh Em)
         - Cột giữa: Content Danh sách bài viết (Module 2 - Vinh Em)
         - Cột phải: Comments (Module 12 - Anh Quý)
         ============================================================================== -->
    <div class="cms-layout-grid cms-home-grid">
        
        <!-- ==========================================================================
             CỘT TRÁI: MODULE 11 - ARCHIVE / XEM NHIỀU (VINH EM - Branch: VinhEm/11-Archive)
             - Vị trí: Aside bên trái giao diện trang chủ (.cms-home-sidebar-left)
             - Chức năng: Hiển thị 8 bài viết xem nhiều nhất (hoặc lưu trữ theo ngày tháng)
               được chia thành 2 cột cân xứng và đánh số thứ tự nổi bật từ 1 đến 8.
             - Logic gọi hàm: cms_nhomc_render_archive_widget('Xem nhiều')
             ========================================================================== -->
        <aside class="cms-sidebar-column cms-home-sidebar-left" aria-label="Lưu trữ">
            <?php cms_nhomc_render_archive_widget('Xem nhiều'); ?>
        </aside>

        <!-- ==========================================================================
             CỘT GIỮA: MODULE 2 - CONTENT DANH SÁCH BÀI VIẾT (VINH EM - Branch: VinhEm/2-content)
             - Vị trí: Khung nội dung chính giữa (.cms-home-main-column)
             - Chức năng: Duyệt qua vòng lặp WordPress Loop (have_posts) và render từng thẻ
               bài viết nằm ngang gồm: Thumbnail ảnh đại diện, Badge ngày/tháng/năm,
               Tiêu đề bài viết H2, nhãn Chuyên mục Categories và Đoạn tóm tắt Excerpt.
             - Logic phân trang: the_posts_pagination() hỗ trợ điều hướng trang 1, 2, 3...
             ========================================================================== -->
        <main class="cms-main-column cms-home-main-column">
            <?php if (have_posts()) : ?>
                <!-- Danh sách thẻ bài viết dạng ngang -->
                <div class="cms-post-list">
                    <?php
                    // Bắt đầu vòng lặp lặp qua từng bài viết bài mới nhất
                    while (have_posts()) :
                        the_post();
                        // Render thẻ bài viết chuẩn thiết kế qua hàm cms_nhomc_render_post_card()
                        cms_nhomc_render_post_card(get_the_ID());
                    endwhile;
                    ?>
                </div>

                <!-- Thanh phân trang tiêu chuẩn WordPress -->
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
                <!-- Thông báo dự phòng khi chưa có bài viết nào trong cơ sở dữ liệu -->
                <div class="content-card">
                    <p>Chưa có bài viết nào được đăng tải. Bạn có thể vào trang quản trị để thêm bài viết mới.</p>
                </div>
            <?php endif; ?>
        </main>

        <!-- Cột phải: Comments (Module 12) chuẩn theo Hình 1 (Anh Quý) -->
        <aside class="cms-sidebar-column cms-home-sidebar-right" aria-label="Bình luận">
            <?php cms_nhomc_render_comments_widget(3, 'Comments'); ?>
        </aside>
    </div>
</div>

<?php
// (20) Newsletter Subscription - Nằm ngay trước Footer trên Homepage (Ponytail Standard)
if (function_exists('cms_nhomc_render_newsletter_section')) {
    cms_nhomc_render_newsletter_section();
}

get_footer();

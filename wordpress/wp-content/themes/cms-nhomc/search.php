<?php
/**
 * Template hiển thị kết quả tìm kiếm (Search Results)
 * Thiết kế chuẩn theo mẫu Bootsnipp 35V6b (Bootstrap 4 Search Bar) & FIT-TDC
 * Cấu trúc 2 cột đồng bộ với toàn bộ hệ thống (index.php, archive.php, single.php)
 *
 * @package CMS_NhomC
 */

get_header();

$raw_query   = get_search_query(false);
$clean_query = is_string($raw_query) ? trim($raw_query) : '';
?>

<div class="site-content cms-container-layout search-results-page">

    <!-- Khối tiêu đề tìm kiếm trải rộng toàn trang bên trên 2 cột -->
    <header class="search-page-header">
        <h1 class="search-main-title">
            <span class="search-title-prefix">Search:</span> &ldquo;<?php echo esc_html(get_search_query()); ?>&rdquo;
        </h1>

        <?php if (empty($clean_query)) : ?>
            <p class="search-notice-text">
                <?php esc_html_e('Vui lòng nhập chủ đề hoặc từ khóa vào ô bên dưới để tìm kiếm.', 'cms-nhomc'); ?>
            </p>
        <?php elseif (!have_posts()) : ?>
            <p class="search-notice-text">
                <?php esc_html_e('We could not find any results for your search. You can give it another try through the search form below.', 'cms-nhomc'); ?>
            </p>
        <?php else : 
            global $wp_query;
            $total = isset($wp_query->found_posts) ? (int) $wp_query->found_posts : 0;
        ?>
            <p class="search-notice-text search-found-text">
                <?php printf(esc_html__('Tìm thấy %s bài viết phù hợp với từ khóa của bạn.', 'cms-nhomc'), '<strong>' . number_format_i18n($total) . '</strong>'); ?>
            </p>
        <?php endif; ?>
    </header>

    <div class="cms-layout-grid">
        <!-- Cột nội dung chính bên trái -->
        <main class="cms-main-column">

            <!-- Khối ô tìm kiếm mẫu Bootsnipp 35V6b với nền màu kem nhạt -->
            <section class="search-box-section" aria-label="<?php esc_attr_e('Khu vực tìm kiếm', 'cms-nhomc'); ?>">
                <div class="search-box-inner">
                    <?php get_search_form(); ?>
                </div>
            </section>

            <?php if (!empty($clean_query)) : 
                $typo_suggestion = null;
                if (class_exists('CMS_NhomC_Vietnamese_Search')) {
                    $typo_suggestion = CMS_NhomC_Vietnamese_Search::get_instance()->detect_typo_and_suggest($clean_query);
                }
                if ($typo_suggestion && !empty($typo_suggestion['suggested'])) :
            ?>
                <!-- Banner gợi ý sửa lỗi chính tả từ từ điển Viet74K (Fuzzy matching) -->
                <div class="search-typo-notice">
                    <div class="typo-box">
                        <span class="typo-lamp" aria-hidden="true">💡</span>
                        <span><?php esc_html_e('Có phải bạn muốn tìm:', 'cms-nhomc'); ?></span>
                        <a href="<?php echo esc_url(home_url('/?s=' . urlencode($typo_suggestion['suggested']))); ?>" class="typo-link">
                            <strong><?php echo esc_html($typo_suggestion['suggested']); ?></strong>
                        </a>?
                    </div>
                </div>
            <?php 
                endif; 
            endif; 
            ?>

            <?php if (have_posts()) : ?>
                <!-- Danh sách bài viết đồng bộ thẻ bài viết cms-post-list -->
                <div class="cms-post-list search-results-list">
                    <?php
                    while (have_posts()) :
                        the_post();
                        cms_nhomc_render_post_card(get_the_ID());
                    endwhile;
                    ?>
                </div>

                <!-- Phân trang chuẩn WordPress đồng bộ toàn theme -->
                <div class="cms-pagination search-pagination-wrapper">
                    <?php
                    the_posts_pagination(array(
                        'mid_size'  => 2,
                        'prev_text' => __('&laquo; Trước', 'cms-nhomc'),
                        'next_text' => __('Sau &raquo;', 'cms-nhomc'),
                    ));
                    ?>
                </div>

            <?php else : ?>
                <!-- Không tìm thấy kết quả: Hiển thị Empty State trang nhã chuẩn card -->
                <div class="content-card search-no-results search-empty-card">
                    <div class="no-results-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="56" height="56" fill="none" stroke="#9ca3af" stroke-width="1.5">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            <line x1="8" y1="11" x2="14" y2="11"/>
                        </svg>
                    </div>
                    <h2 class="no-results-title"><?php esc_html_e('Rất tiếc, không tìm thấy bài viết phù hợp!', 'cms-nhomc'); ?></h2>
                    <p class="no-results-desc">
                        <?php printf(esc_html__('Hãy thử tìm kiếm với các từ khóa phổ biến: %s.', 'cms-nhomc'), '<strong>' . esc_html__('Thể thao, Khoa học, Tin tức, Pickleball, Bóng đá', 'cms-nhomc') . '</strong>'); ?>
                    </p>

                    <div class="search-retry-suggestions">
                        <p class="suggestions-label"><strong><?php esc_html_e('Gợi ý tìm kiếm:', 'cms-nhomc'); ?></strong></p>
                        <ul class="suggestions-list">
                            <li><?php esc_html_e('Kiểm tra lại lỗi chính tả của từ khóa đã nhập.', 'cms-nhomc'); ?></li>
                            <li><?php printf(esc_html__('Sử dụng các từ khóa đơn giản, ngắn gọn hơn (ví dụ: %s).', 'cms-nhomc'), '<em>Pickleball</em>, <em>Bóng đá</em>, <em>Khoa học</em>'); ?></li>
                            <li><?php printf(wp_kses(__('Quay về <a href="%s">Trang chủ</a> để duyệt theo danh mục chuyên mục.', 'cms-nhomc'), array('a' => array('href' => array()))), esc_url(home_url('/'))); ?></li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

        </main>

        <!-- Sidebar bên phải: Đồng bộ chuẩn giao diện hệ thống CMS Nhóm C -->
        <aside class="cms-sidebar-column">
            <?php 
            cms_nhomc_render_categories_widget();
            cms_nhomc_render_featured_posts_widget(5, 'BÀI VIẾT NỔI BẬT'); 
            cms_nhomc_render_comments_widget(3, 'Comments');
            if (function_exists('cms_nhomc_render_last_posts_widget')) {
                cms_nhomc_render_last_posts_widget(5, 'Latest News');
            }
            ?>
        </aside>
    </div>
</div>

<?php
get_footer();

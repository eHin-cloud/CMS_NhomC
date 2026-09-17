<?php
/**
 * Template hiển thị kết quả tìm kiếm (Search Results)
 * Thiết kế chuẩn theo mẫu Bootsnipp 35V6b (Bootstrap 4 Search Bar) & FIT-TDC
 *
 * @package CMS_NhomC
 */

get_header();

$raw_query   = get_search_query(false);
$clean_query = is_string($raw_query) ? trim($raw_query) : '';
?>

<main class="site-content search-results-page">
    <div class="search-page-container">

        <!-- Khối tiêu đề tìm kiếm -->
        <header class="search-page-header text-center">
            <h1 class="search-main-title">
                <span class="search-title-prefix">Search:</span> &ldquo;<?php echo esc_html(get_search_query()); ?>&rdquo;
            </h1>

            <?php if (empty($clean_query)) : ?>
                <p class="search-notice-text">
                    <?php esc_html_e('Vui lòng nhập chủ đề hoặc từ khóa vào ô bên dưới để tìm kiếm.', 'cms-nhomc'); ?>
                </p>
            <?php elseif (!have_posts()) : ?>
                <p class="search-notice-text">
                    We could not find any results for your search. You can give it another try through the search form below.
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

        <!-- Khối ô tìm kiếm mẫu Bootsnipp 35V6b với nền màu kem nhạt -->
        <section class="search-box-section" aria-label="Khu vực tìm kiếm">
            <div class="search-box-inner">
                <?php get_search_form(); ?>
            </div>
        </section>

        <?php if (!empty($clean_query)) : 
            $typo_suggestion = null;
            if (class_exists('CMS_NhomC_Vietnamese_Search')) {
                $typo_suggestion = CMS_NhomC_Vietnamese_Search::get_instance()->detect_typo_and_suggest($clean_query);
            }
        ?>

            <!-- Banner gợi ý sửa lỗi chính tả từ từ điển Viet74K (Fuzzy matching) -->
            <?php if ($typo_suggestion && !empty($typo_suggestion['suggested'])) : ?>
                <div class="search-typo-notice">
                    <div class="typo-box">
                        <span class="typo-lamp">💡</span> <?php esc_html_e('Có phải bạn muốn tìm:', 'cms-nhomc'); ?> 
                        <a href="<?php echo esc_url(home_url('/?s=' . urlencode($typo_suggestion['suggested']))); ?>" class="typo-link">
                            <strong><?php echo esc_html($typo_suggestion['suggested']); ?></strong>
                        </a>?
                    </div>
                </div>
            <?php endif; ?>

            <?php if (have_posts()) : ?>
                <!-- Danh sách bài viết -->
                <div class="search-results-list">
                    <?php
                    while (have_posts()) :
                        the_post();
                        get_template_part('template-parts/content', 'search');
                    endwhile;
                    ?>
                </div>

                <!-- Phân trang chuẩn WordPress theo phong cách FIT-TDC -->
                <?php
                $pagination_links = paginate_links(array(
                    'mid_size'  => 2,
                    'prev_text' => '&laquo; ' . __('Trước', 'cms-nhomc'),
                    'next_text' => __('Sau', 'cms-nhomc') . ' &raquo;',
                    'type'      => 'list',
                ));
                if (!empty($pagination_links)) :
                ?>
                    <nav class="search-pagination-wrapper" aria-label="<?php esc_attr_e('Phân trang kết quả tìm kiếm', 'cms-nhomc'); ?>">
                        <?php echo wp_kses_post($pagination_links); ?>
                    </nav>
                <?php endif; ?>

            <?php else : ?>
                <!-- Không tìm thấy kết quả -->
                <div class="search-no-results">
                    <div class="no-results-icon">
                        <svg viewBox="0 0 24 24" width="56" height="56" fill="none" stroke="#9ca3af" stroke-width="1.5">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            <line x1="8" y1="11" x2="14" y2="11"/>
                        </svg>
                    </div>
                    <h2><?php esc_html_e('Rất tiếc, không tìm thấy bài viết phù hợp!', 'cms-nhomc'); ?></h2>
                    <p><?php printf(esc_html__('Hãy thử tìm kiếm với các từ khóa phổ biến: %s.', 'cms-nhomc'), '<strong>' . esc_html__('Thể thao, Khoa học, Tin tức, Lịch học', 'cms-nhomc') . '</strong>'); ?></p>

                    <div class="search-retry-suggestions">
                        <p><strong><?php esc_html_e('Gợi ý tìm kiếm:', 'cms-nhomc'); ?></strong></p>
                        <ul>
                            <li><?php esc_html_e('Kiểm tra lại lỗi chính tả của từ khóa.', 'cms-nhomc'); ?></li>
                            <li><?php printf(esc_html__('Sử dụng các từ khóa đơn giản hơn (ví dụ: %s).', 'cms-nhomc'), '<em>Pickleball</em>, <em>Bóng đá</em>, <em>AI</em>, <em>Thời khóa biểu</em>'); ?></li>
                            <li><?php printf(wp_kses(__('Quay về <a href="%s">Trang chủ</a> để duyệt theo danh mục.', 'cms-nhomc'), array('a' => array('href' => array()))), esc_url(home_url('/'))); ?></li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</main>

<?php
get_footer();

<?php
/**
 * The template for displaying search results pages
 * Module: (5) Search result (Mẫu tham khảo: http://fit.tdc.edu.vn/tin-tuc)
 * [Ponytail Standard] Tinh gọn, tối đa hóa hàm native WordPress, không over-engineering
 *
 * @package CMS_NhomC
 */

get_header();

$raw_query = get_search_query(false);
$clean_query = is_string($raw_query) ? trim($raw_query) : '';
?>

<main class="site-content search-results-page">
    <div class="search-page-container">

        <?php if ($clean_query === '') : ?>
            <!-- Trường hợp tìm kiếm rỗng / chỉ có khoảng trắng -->
            <div class="search-no-results">
                <div class="no-results-icon">
                    <svg viewBox="0 0 24 24" width="56" height="56" fill="none" stroke="#9ca3af" stroke-width="1.5">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </div>
                <h2><?php esc_html_e('Bạn chưa nhập từ khóa tìm kiếm!', 'cms-nhomc'); ?></h2>
                <p><?php printf(esc_html__('Vui lòng nhập từ khóa về %s vào ô bên dưới.', 'cms-nhomc'), '<strong>' . esc_html__('Thể thao, Khoa học, Tin tức, Lịch học', 'cms-nhomc') . '</strong>'); ?></p>

                <div class="search-retry-box">
                    <form role="search" method="get" class="search-retry-form" action="<?php echo esc_url(home_url('/')); ?>">
                        <input type="search" name="s" class="search-retry-input" placeholder="<?php esc_attr_e('Ví dụ: Bóng đá, AI, Tuyển sinh...', 'cms-nhomc'); ?>" required autofocus />
                        <button type="submit" class="search-retry-btn"><?php esc_html_e('Tìm kiếm', 'cms-nhomc'); ?></button>
                    </form>
                </div>
            </div>

        <?php else : 
            $typo_suggestion = null;
            if (class_exists('CMS_NhomC_Vietnamese_Search')) {
                $typo_suggestion = CMS_NhomC_Vietnamese_Search::get_instance()->detect_typo_and_suggest($clean_query);
            }
        ?>

            <!-- Thanh thông tin kết quả tìm kiếm -->
            <header class="search-header-bar">
                <h1 class="search-title">
                    <?php esc_html_e('Kết quả tìm kiếm cho:', 'cms-nhomc'); ?> <span class="search-keyword">&ldquo;<?php echo esc_html($clean_query); ?>&rdquo;</span>
                </h1>
                <p class="search-count">
                    <?php
                    global $wp_query;
                    $total = isset($wp_query->found_posts) ? (int) $wp_query->found_posts : 0;
                    if ($total > 0) {
                        printf(
                            /* translators: %s: number of results */
                            esc_html(_n('Tìm thấy %s bài viết phù hợp', 'Tìm thấy %s bài viết phù hợp', $total, 'cms-nhomc')),
                            '<strong>' . number_format_i18n($total) . '</strong>'
                        );
                    } else {
                        esc_html_e('Không tìm thấy bài viết nào phù hợp', 'cms-nhomc');
                    }
                    ?>
                </p>
            </header>

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
                        // Sử dụng template part theo chuẩn WordPress VIP & Ponytail DRY
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

                    <div class="search-retry-box">
                        <form role="search" method="get" class="search-retry-form" action="<?php echo esc_url(home_url('/')); ?>">
                            <div class="search-input-wrapper">
                                <span class="search-icon-inside" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    </svg>
                                </span>
                                <input type="search" name="s" class="search-retry-input" placeholder="<?php esc_attr_e('Nhập từ khóa khác...', 'cms-nhomc'); ?>" value="<?php echo esc_attr($clean_query); ?>" required />
                            </div>
                            <button type="submit" class="search-retry-btn"><?php esc_html_e('Tìm kiếm lại', 'cms-nhomc'); ?></button>
                        </form>
                    </div>

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

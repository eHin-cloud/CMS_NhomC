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
if (mb_strlen($clean_query, 'UTF-8') > 100) {
    $clean_query = mb_substr($clean_query, 0, 100, 'UTF-8');
}
?>

<main class="site-content search-results-page">
    <div class="search-page-container">

        <!-- Khối tiêu đề tìm kiếm chuẩn mẫu Bootsnipp 35V6b -->
        <header class="search-page-header text-center">
            <?php if (!empty($clean_query)) : ?>
                <h1 class="search-main-title">
                    <span class="search-title-prefix">Search:</span> &ldquo;<?php echo esc_html($clean_query); ?>&rdquo;
                </h1>

                <?php if (mb_strlen($clean_query, 'UTF-8') < 2) : ?>
                    <p class="search-notice-text search-warning-text">
                        <?php esc_html_e('Từ khóa tìm kiếm quá ngắn (tối thiểu 2 ký tự). Vui lòng nhập từ khóa cụ thể hơn từ 2 đến 100 ký tự.', 'cms-nhomc'); ?>
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
            <?php else : ?>
                <h1 class="search-main-title">
                    <span class="search-title-prefix">Search</span>
                </h1>
                <p class="search-notice-text">
                    Nhập từ khóa vào ô tìm kiếm bên dưới để tìm bài viết bạn quan tâm.
                </p>
            <?php endif; ?>
        </header>

        <!-- Khối ô tìm kiếm mẫu Bootsnipp 35V6b với nền màu kem nhạt -->
        <section class="search-box-section" aria-label="<?php esc_attr_e('Khu vực tìm kiếm', 'cms-nhomc'); ?>">
            <div class="search-box-inner">
                <?php get_search_form(); ?>
            </div>
        </section>

        <!-- Danh sách kết quả nếu có bài viết -->
        <?php if (!empty($clean_query) && have_posts()) : ?>
            <section class="search-results-list" aria-label="<?php esc_attr_e('Danh sách kết quả tìm kiếm', 'cms-nhomc'); ?>">
                <?php
                while (have_posts()) :
                    the_post();
                    get_template_part('template-parts/content', 'search');
                endwhile;
                ?>

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
            </section>
        <?php endif; ?>

    </div>
</main>

<?php
get_footer();

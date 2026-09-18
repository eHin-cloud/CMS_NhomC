<?php
/**
 * Template hiển thị kết quả tìm kiếm (Search Results)
 * Thiết kế chuẩn theo mẫu Bootsnipp 35V6b (Bootstrap 4 Search Bar) & FIT-TDC
 *
 * @package CMS_NhomC
 */

get_header();

$raw_query    = isset($_GET['s']) ? wp_unslash($_GET['s']) : get_search_query(false);
$clean_query  = is_string($raw_query) ? trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags($raw_query))) : '';
$search_error = function_exists('cms_nhomc_get_search_error') ? cms_nhomc_get_search_error($clean_query) : null;
?>

<main class="site-content search-results-page">
    <div class="search-page-container">

        <!-- Khối tiêu đề tìm kiếm chuẩn 100% theo mẫu Bootsnipp 35V6b -->
        <header class="search-page-header text-center">
            <?php if ($search_error) : ?>
                <h1 class="search-main-title">
                    <span class="search-title-prefix">Search:</span> &quot;<?php echo esc_html($clean_query); ?>&quot;
                </h1>
                <div class="search-alert-card search-alert-<?php echo esc_attr($search_error['type']); ?>" role="alert">
                    <div class="search-alert-icon" aria-hidden="true">
                        <?php if ($search_error['type'] === 'error') : ?>
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                        <?php else : ?>
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        <?php endif; ?>
                    </div>
                    <div class="search-alert-body">
                        <strong class="search-alert-title"><?php echo esc_html($search_error['title']); ?>:</strong>
                        <span class="search-alert-message"><?php echo esc_html($search_error['message']); ?></span>
                    </div>
                </div>

            <?php elseif (empty($clean_query)) : ?>
                <!-- Trạng thái ban đầu khi vừa vào trang tìm kiếm, chưa nhập từ khóa nào -->
                <h1 class="search-main-title">
                    <span class="search-title-prefix">Search</span>
                </h1>

            <?php elseif (!have_posts()) : ?>
                <!-- Khi đã tìm kiếm nhưng không có kết quả phù hợp (ví dụ: tìm "abc") -->
                <h1 class="search-main-title">
                    <span class="search-title-prefix">Search:</span> &quot;<?php echo esc_html($clean_query); ?>&quot;
                </h1>
                <p class="search-notice-text">
                    We could not find any results for your search. You can give it another try through the search form below.
                </p>

            <?php else : 
                global $wp_query;
                $total = isset($wp_query->found_posts) ? (int) $wp_query->found_posts : 0;
            ?>
                <!-- Khi tìm thấy kết quả bài viết -->
                <h1 class="search-main-title">
                    <span class="search-title-prefix">Search:</span> &quot;<?php echo esc_html($clean_query); ?>&quot;
                </h1>
                <p class="search-notice-text search-found-text">
                    <?php printf(esc_html__('Tìm thấy %s bài viết phù hợp với từ khóa của bạn.', 'cms-nhomc'), '<strong>' . number_format_i18n($total) . '</strong>'); ?>
                </p>
            <?php endif; ?>
        </header>

        <!-- Khối ô tìm kiếm mẫu Bootsnipp 35V6b với nền màu kem nhạt (Module 4) -->
        <section class="search-box-section" aria-label="<?php esc_attr_e('Khu vực tìm kiếm', 'cms-nhomc'); ?>">
            <div class="search-box-inner">
                <?php get_search_form(); ?>
            </div>
        </section>

        <!-- Bố cục trang tìm kiếm: Kết quả tìm kiếm (5) & Vị trí 14 (Module Comments của Hiền) -->
        <div class="search-layout-3col">
            <!-- Cột giữa: Kết quả tìm kiếm (Module 5 Search result) -->
            <div class="search-main-col-5" id="module-5-search-results">
                <?php if (!$search_error && !empty($clean_query) && have_posts()) : ?>
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
                <?php elseif (!empty($clean_query) && !have_posts()) : ?>
                    <div class="search-empty-state-box text-center" style="padding: 40px 20px; background: #fff; border: 1px solid #e5e7eb; border-radius: 6px;">
                        <p style="color: #6b7280; font-size: 15px; margin: 0;">
                            <?php esc_html_e('Không có bài viết nào phù hợp với kết quả tìm kiếm.', 'cms-nhomc'); ?>
                        </p>
                    </div>
                <?php else : ?>
                    <div class="search-empty-state-box text-center" style="padding: 40px 20px; background: #fff; border: 1px solid #e5e7eb; border-radius: 6px;">
                        <p style="color: #6b7280; font-size: 15px; margin: 0;">
                            <?php esc_html_e('Vui lòng nhập từ khóa vào ô tìm kiếm phía trên để xem kết quả.', 'cms-nhomc'); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Cột 14 (Sidebar phải: Vị trí 14 - Duy nhất Module Comments của Hiền) -->
            <aside class="search-sidebar-col-14" id="module-14-comments" aria-label="<?php esc_attr_e('Module 14 Comments', 'cms-nhomc'); ?>">
                <?php
                if (function_exists('cms_nhomc_render_comments_widget')) {
                    cms_nhomc_render_comments_widget(5, 'COMMENTS');
                }
                ?>
            </aside>
        </div>

        <!-- Module 15: Last Posts - Latest News Timeline (Bootsnipp xrKXW) (Xuân Hòa) -->
        <?php if (function_exists('cms_nhomc_render_last_posts_widget')) : ?>
            <section class="cms-module-15-section" id="module-15-last-posts" aria-label="<?php esc_attr_e('Bài viết mới nhất - Module 15', 'cms-nhomc'); ?>">
                <?php cms_nhomc_render_last_posts_widget(5, 'Latest News'); ?>
            </section>
        <?php endif; ?>

    </div>
</main>

<?php
get_footer();

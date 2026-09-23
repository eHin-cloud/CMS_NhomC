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
        <!-- Breadcrumbs (Module 19) -->
        <?php cms_nhomc_breadcrumbs(); ?>

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

        <!-- Hàng: Module 13 Pages (trái) | Search result 5 (phải) -->
        <div class="cms-search-content-row">

            <!-- Cột trái: Module 13 Pages (DangNguyen/13-pages) -->
            <aside class="cms-module-13-sidebar" id="module-13-pages-search" aria-label="<?php esc_attr_e('Danh sách trang', 'cms-nhomc'); ?>">
                <?php
                $pages_args_m13 = array(
                    'post_type'      => 'page',
                    'post_status'    => 'publish',
                    'posts_per_page' => 3,
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                );
                $pages_query_m13 = new WP_Query($pages_args_m13);
                if ($pages_query_m13->have_posts()) :
                ?>
                    <div class="cms-module-13-pages-col">
                        <?php while ($pages_query_m13->have_posts()) : $pages_query_m13->the_post(); 
                            $m13_pid = get_the_ID();
                            $m13_thumb = '';
                            if (has_post_thumbnail($m13_pid)) {
                                $m13_thumb = get_the_post_thumbnail_url($m13_pid, 'medium');
                            }
                            if (empty($m13_thumb)) {
                                $m13_thumb = get_post_meta($m13_pid, '_thumbnail_ext_url', true);
                            }
                            if (empty($m13_thumb) && function_exists('cms_nhomc_get_post_thumbnail_url')) {
                                $m13_thumb = cms_nhomc_get_post_thumbnail_url($m13_pid);
                            }
                        ?>
                            <article class="cms-page-card cms-page-card--sidebar">
                                <a href="<?php the_permalink(); ?>" class="cms-page-card__link">
                                    <div class="cms-page-card__thumb-wrap">
                                        <?php if (!empty($m13_thumb)) : ?>
                                            <img class="cms-page-card__thumb" src="<?php echo esc_url($m13_thumb); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                                        <?php else : ?>
                                            <div class="cms-page-card__thumb cms-page-card__thumb--placeholder"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="cms-page-card__body">
                                        <h3 class="cms-page-card__title"><?php the_title(); ?></h3>
                                        <p class="cms-page-card__desc"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 15, '...')); ?></p>
                                    </div>
                                </a>
                            </article>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                <?php endif; ?>
            </aside>

            <!-- Cột phải: Kết quả tìm kiếm (code gốc Module 5 - không sửa) -->
            <div class="cms-search-results-main">
        <!-- Danh sách kết quả nếu có bài viết (Module 5 Search result) -->
        <?php if (!$search_error && !empty($clean_query) && have_posts()) : ?>
            <section class="search-results-list" id="module-5-search-results" aria-label="<?php esc_attr_e('Danh sách kết quả tìm kiếm', 'cms-nhomc'); ?>">
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
            <div class="search-empty-state-box text-center" style="padding: 40px 20px; background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; margin: 30px 0;">
                <p style="color: #6b7280; font-size: 15px; margin: 0;">
                    <?php esc_html_e('Không có bài viết nào phù hợp với kết quả tìm kiếm.', 'cms-nhomc'); ?>
                </p>
            </div>
        <?php else : ?>
            <div class="search-empty-state-box text-center" style="padding: 40px 20px; background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; margin: 30px 0;">
                <p style="color: #6b7280; font-size: 15px; margin: 0;">
                    <?php esc_html_e('Vui lòng nhập từ khóa vào ô tìm kiếm phía trên để xem kết quả.', 'cms-nhomc'); ?>
                </p>
            </div>
        <?php endif; ?>
            </div><!-- /.cms-search-results-main -->

            <!-- Cột phải: Module 14 Comments (Hien/14-comments) - logic giữ nguyên -->
            <aside class="cms-module-14-sidebar">
                <section class="cms-module-14-comments-section" id="module-14-comments" aria-label="<?php esc_attr_e('Bình luận - Module 14', 'cms-nhomc'); ?>">
                    <?php if (function_exists('cms_nhomc_render_comments_widget')) : ?>
                        <?php cms_nhomc_render_comments_widget(4, 'COMMENTS'); ?>
                    <?php endif; ?>
                </section>
            </aside>

        </div><!-- /.cms-search-content-row -->

        <!-- Module 15: Latest News - logic giữ nguyên -->
        <div class="search-bottom-row-single">
            <section class="cms-module-15-section" id="module-15-last-posts" aria-label="<?php esc_attr_e('Bài viết mới nhất - Module 15', 'cms-nhomc'); ?>">
                <?php if (function_exists('cms_nhomc_render_last_posts_widget')) : ?>
                    <?php cms_nhomc_render_last_posts_widget(5, 'Latest News'); ?>
                <?php endif; ?>
            </section>
        </div>

    </div>
</main>

<?php
get_footer();

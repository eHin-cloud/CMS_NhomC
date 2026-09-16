<?php
/**
 * Template hiển thị kết quả tìm kiếm (Search Results)
 * Thiết kế chuẩn theo mẫu Bootsnipp 35V6b (Bootstrap 4 search Bar)
 *
 * @package CMS_NhomC
 */

get_header();
?>

<main class="site-content search-results-page">
    <div class="search-page-container">
        <!-- Khối tiêu đề tìm kiếm -->
        <header class="search-page-header text-center">
            <h1 class="search-main-title">
                <span class="search-title-prefix">Search:</span> &ldquo;<?php echo esc_html(get_search_query()); ?>&rdquo;
            </h1>

            <?php if (!have_posts()) : ?>
                <p class="search-notice-text">
                    We could not find any results for your search. You can give it another try through the search form below.
                </p>
            <?php else : ?>
                <p class="search-notice-text search-found-text">
                    Tìm thấy <?php echo number_format_i18n($wp_query->found_posts); ?> bài viết phù hợp với từ khóa của bạn.
                </p>
            <?php endif; ?>
        </header>

        <!-- Khối ô tìm kiếm mẫu Bootsnipp 35V6b với nền màu kem nhạt -->
        <section class="search-box-section" aria-label="Khu vực tìm kiếm">
            <div class="search-box-inner">
                <?php get_search_form(); ?>
            </div>
        </section>

        <!-- Danh sách kết quả nếu có bài viết -->
        <?php if (have_posts()) : ?>
            <section class="search-results-list" aria-label="Danh sách kết quả tìm kiếm">
                <div class="posts-grid">
                    <?php while (have_posts()) : the_post(); ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class('search-post-card'); ?>>
                            <div class="post-thumbnail">
                                <a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
                                    <img src="<?php echo esc_url(cms_nhomc_get_post_thumbnail_url(get_the_ID())); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                                </a>
                            </div>

                            <div class="post-card-body">
                                <h2 class="post-card-title">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h2>

                                <div class="post-card-meta">
                                    <span class="meta-date">
                                        <i class="fa fa-calendar-o" aria-hidden="true"></i> <?php echo get_the_date(); ?>
                                    </span>
                                    <span class="meta-separator">•</span>
                                    <span class="meta-author">
                                        <i class="fa fa-user-o" aria-hidden="true"></i> <?php the_author(); ?>
                                    </span>
                                    <?php if (has_category()) : ?>
                                        <span class="meta-separator">•</span>
                                        <span class="meta-categories">
                                            <i class="fa fa-folder-o" aria-hidden="true"></i> <?php the_category(', '); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="post-card-excerpt">
                                    <?php the_excerpt(); ?>
                                </div>

                                <div class="post-card-action">
                                    <a href="<?php the_permalink(); ?>" class="btn-read-more">
                                        Đọc tiếp &rarr;
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <!-- Phân trang nếu có nhiều bài viết -->
                <div class="search-pagination">
                    <?php
                    the_posts_pagination(array(
                        'prev_text'          => '<i class="fa fa-angle-left"></i> Trang trước',
                        'next_text'          => 'Trang sau <i class="fa fa-angle-right"></i>',
                        'before_page_number' => '<span class="meta-nav screen-reader-text">' . __('Trang', 'cms-nhomc') . ' </span>',
                    ));
                    ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>

<?php
get_footer();

<?php
/**
 * Template hiển thị trang đơn (Single Page)
 * Tương thích 100% bố cục chuẩn 3 cột của Theme CMS Nhóm C
 *
 * @package CMS_NhomC
 */

get_header();
?>

<div class="site-content cms-container-layout cms-single-container-layout cms-page-container-layout">
    <!-- Hàng 3 cột theo chuẩn Theme: Categories (Trái) | Chi tiết Trang & Các trang khác (Giữa) | Bài viết mới (Phải) -->
    <div class="cms-single-grid">

        <!-- Cột trái: Categories (Module 9 - Anh Quý) -->
        <aside class="cms-single-sidebar-left" aria-label="<?php esc_attr_e('Chuyên mục', 'cms-nhomc'); ?>">
            <?php
            if (function_exists('cms_nhomc_render_categories_widget')) {
                cms_nhomc_render_categories_widget();
            }
            ?>
        </aside>

        <!-- Cột giữa: Chi tiết trang + Module 13 Các trang khác (Đặng Nguyên) -->
        <main class="cms-single-main-column">
            <?php while (have_posts()) : the_post(); 
                $page_id = get_the_ID();

                // Lấy ảnh đại diện trang với fallback thông minh
                $page_thumb = '';
                if (has_post_thumbnail($page_id)) {
                    $page_thumb = get_the_post_thumbnail_url($page_id, 'large');
                }
                if (empty($page_thumb)) {
                    $page_thumb = get_post_meta($page_id, '_thumbnail_ext_url', true);
                }
                if (empty($page_thumb) && function_exists('cms_nhomc_get_post_thumbnail_url')) {
                    $page_thumb = cms_nhomc_get_post_thumbnail_url($page_id);
                }
            ?>
                <article id="page-<?php the_ID(); ?>" <?php post_class('cms-single-article cms-single-page-article'); ?>>

                    <!-- Breadcrumbs -->
                    <nav class="cms-breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'cms-nhomc'); ?>">
                        <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Trang Chủ', 'cms-nhomc'); ?></a>
                        <span class="sep">/</span>
                        <span class="current"><?php the_title(); ?></span>
                    </nav>

                    <!-- Tiêu đề trang -->
                    <h1 class="single-post-title cms-single-page__title"><?php the_title(); ?></h1>

                    <!-- Ảnh đại diện trang -->
                    <?php if (!empty($page_thumb)) : ?>
                        <div class="cms-single-page__thumbnail">
                            <img src="<?php echo esc_url($page_thumb); ?>" alt="<?php the_title_attribute(); ?>" class="cms-single-page__thumb-img" loading="lazy" />
                        </div>
                    <?php endif; ?>

                    <!-- Nội dung chi tiết trang -->
                    <div class="single-post-content cms-single-page__content entry-content">
                        <?php the_content(); ?>
                    </div>

                </article>
            <?php endwhile; ?>

            <!-- Module 13: Các trang khác (DangNguyen/13-pages) -->
            <?php
            $current_page_id = get_the_ID();
            $other_pages = get_posts(array(
                'post_type'      => 'page',
                'post_status'    => 'publish',
                'posts_per_page' => 3,
                'exclude'        => array($current_page_id),
                'orderby'        => 'menu_order date',
                'order'          => 'ASC',
            ));

            if (!empty($other_pages)) :
            ?>
                <section class="cms-pages-grid-section" id="module-13-pages" aria-label="<?php esc_attr_e('Các trang khác', 'cms-nhomc'); ?>">
                    <h3 class="widget-cat-title cms-pages-grid-section__heading">
                        <?php esc_html_e('CÁC TRANG KHÁC', 'cms-nhomc'); ?>
                    </h3>
                    <div class="cms-pages-grid">
                        <?php foreach ($other_pages as $op) :
                            $op_thumb = get_post_meta($op->ID, '_thumbnail_ext_url', true);
                            if (empty($op_thumb) && has_post_thumbnail($op->ID)) {
                                $op_thumb = get_the_post_thumbnail_url($op->ID, 'medium');
                            }
                            if (empty($op_thumb) && function_exists('cms_nhomc_get_post_thumbnail_url')) {
                                $op_thumb = cms_nhomc_get_post_thumbnail_url($op->ID);
                            }
                            $op_excerpt = !empty($op->post_excerpt) ? $op->post_excerpt : wp_trim_words(wp_strip_all_tags($op->post_content), 18, '...');
                        ?>
                            <article class="cms-page-card" id="page-card-<?php echo esc_attr($op->ID); ?>">
                                <a href="<?php echo esc_url(get_permalink($op->ID)); ?>" class="cms-page-card__link">
                                    <div class="cms-page-card__thumb-wrap">
                                        <?php if (!empty($op_thumb)) : ?>
                                            <img class="cms-page-card__thumb" src="<?php echo esc_url($op_thumb); ?>" alt="<?php echo esc_attr(get_the_title($op->ID)); ?>" loading="lazy" />
                                        <?php else : ?>
                                            <div class="cms-page-card__thumb cms-page-card__thumb--placeholder"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="cms-page-card__body">
                                        <h4 class="cms-page-card__title"><?php echo esc_html(get_the_title($op->ID)); ?></h4>
                                        <?php if (!empty($op_excerpt)) : ?>
                                            <p class="cms-page-card__desc"><?php echo esc_html($op_excerpt); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

        </main>

        <!-- Cột phải: Bài viết mới (Module 10 - Đặng Nguyên) -->
        <aside class="cms-single-sidebar-right" aria-label="<?php esc_attr_e('Bài viết mới', 'cms-nhomc'); ?>">
            <?php
            if (function_exists('cms_nhomc_render_recent_posts_widget')) {
                cms_nhomc_render_recent_posts_widget(10, 'BÀI VIẾT MỚI');
            }
            ?>
        </aside>

    </div>
</div>

<?php
get_footer();

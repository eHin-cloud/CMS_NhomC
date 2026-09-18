<?php
/**
 * Single post template for CMS Nhóm C
 * Layout: 3 cột theo sơ đồ thiết kế
 *   - Cột trái:  Categories (9)
 *   - Cột giữa: Detail (6) — nội dung bài viết
 *   - Cột phải: Recent Posts (10)
 *   - Bên dưới (full-width): Prev-Next Post (7), Comments (8)
 *
 * @package CMS_NhomC
 */

get_header();
?>

<div class="detail-page-wrapper">

    <!-- ===== GRID 3 CỘT ===== -->
    <div class="detail-3col-grid">

        <!-- CỘT TRÁI: Categories (9) -->
        <aside class="detail-sidebar-left" aria-label="Danh mục chuyên mục">
            <?php cms_nhomc_render_categories_widget('Categories'); ?>
        </aside>

        <!-- CỘT GIỮA: Nội dung bài viết (6) -->
        <main class="detail-main-column">
            <?php while (have_posts()) : the_post();
                $post_id     = get_the_ID();
                $categories  = get_the_category();
                $primary_cat = !empty($categories) ? $categories[0] : null;
            ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('cms-single-article'); ?>>

                    <!-- Breadcrumbs -->
                    <nav class="cms-breadcrumb" aria-label="Breadcrumb">
                        <a href="<?php echo esc_url(home_url('/')); ?>">Trang Chủ</a>
                        <span class="sep">/</span>
                        <?php if ($primary_cat) : ?>
                            <a href="<?php echo esc_url(get_category_link($primary_cat->term_id)); ?>"><?php echo esc_html($primary_cat->name); ?></a>
                            <span class="sep">/</span>
                        <?php else : ?>
                            <a href="<?php echo esc_url(home_url('/category/tin-tuc/')); ?>">Tin Tức</a>
                            <span class="sep">/</span>
                        <?php endif; ?>
                        <span class="current"><?php the_title(); ?></span>
                    </nav>

                    <!-- Tiêu đề bài viết -->
                    <h1 class="single-post-title"><?php the_title(); ?></h1>

                    <!-- Meta: ngày đăng, tác giả, chuyên mục -->
                    <div class="single-post-meta-bar">
                        <span class="meta-date">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <?php echo esc_html(get_the_date('d/m/Y')); ?>
                        </span>
                        <span class="meta-sep">•</span>
                        <span class="meta-author">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <?php the_author(); ?>
                        </span>
                        <?php if ($primary_cat) : ?>
                            <span class="meta-sep">•</span>
                            <span class="meta-cat">
                                <a href="<?php echo esc_url(get_category_link($primary_cat->term_id)); ?>"><?php echo esc_html($primary_cat->name); ?></a>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Ảnh đại diện bài viết -->
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="single-post-thumbnail">
                            <?php the_post_thumbnail('large', array('class' => 'single-thumb-img', 'loading' => 'eager')); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Dẫn đề / Sapo (Excerpt) -->
                    <?php if (has_excerpt()) : ?>
                        <div class="single-post-lead">
                            <?php the_excerpt(); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Nội dung chi tiết bài viết -->
                    <div class="single-post-content entry-content">
                        <?php the_content(); ?>
                    </div>

                    <!-- Tags / Chuyên mục -->
                    <div class="single-post-tags">
                        <span class="tags-label">Tags:</span>
                        <?php
                        $tags = get_the_tags();
                        if (!empty($tags)) {
                            $tag_links = array();
                            foreach ($tags as $tag) {
                                $tag_links[] = '<a href="' . esc_url(get_tag_link($tag->term_id)) . '">' . esc_html($tag->name) . '</a>';
                            }
                            echo implode(', ', $tag_links);
                        } elseif ($primary_cat) {
                            echo '<a href="' . esc_url(get_category_link($primary_cat->term_id)) . '">' . esc_html($primary_cat->name) . '</a>';
                        } else {
                            echo '<a href="#">Tin Tức</a>';
                        }
                        ?>
                    </div>

                </article>
            <?php endwhile; ?>
        </main>

        <!-- CỘT PHẢI: Recent Posts (10) -->
        <aside class="detail-sidebar-right" aria-label="Bài viết mới">
            <?php cms_nhomc_render_recent_posts_widget(10, 'BÀI VIẾT MỚI'); ?>
        </aside>

    </div><!-- /.detail-3col-grid -->

    <!-- ===== KHU VỰC FULL-WIDTH BÊN DƯỚI ===== -->
    <div class="detail-below-grid">

        <!-- Điều hướng bài trước / bài kế tiếp (7) Prev - Next Post chuẩn mẫu TDC -->
        <?php
        // Đặt lại post data để truy xuất prev/next đúng
        if (have_posts()) {
            rewind_posts();
            the_post();
            $prev_post = get_previous_post();
            $next_post = get_next_post();

            if (!empty($next_post) || !empty($prev_post)) :
        ?>
        <nav class="single-post-nav tdc-post-nav" aria-label="Điều hướng bài viết">
            <ul class="tdc-post-nav-list">
                <?php if (!empty($next_post)) :
                    $next_day   = get_the_date('d', $next_post->ID);
                    $next_month = get_the_date('m', $next_post->ID);
                    $next_year  = get_the_date('y', $next_post->ID);
                ?>
                    <li class="tdc-post-nav-item tdc-nav-next">
                        <div class="tdc-nav-date">
                            <span class="tdc-day-month">
                                <span class="tdc-day"><?php echo esc_html($next_day); ?></span>
                                <span class="tdc-divider"></span>
                                <span class="tdc-month"><?php echo esc_html($next_month); ?></span>
                            </span>
                            <span class="tdc-year"><?php echo esc_html($next_year); ?></span>
                        </div>
                        <div class="tdc-nav-title-wrap">
                            <a href="<?php echo esc_url(get_permalink($next_post->ID)); ?>" class="tdc-nav-link">
                                <?php echo esc_html(get_the_title($next_post->ID)); ?>
                            </a>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if (!empty($prev_post)) :
                    $prev_day   = get_the_date('d', $prev_post->ID);
                    $prev_month = get_the_date('m', $prev_post->ID);
                    $prev_year  = get_the_date('y', $prev_post->ID);
                ?>
                    <li class="tdc-post-nav-item tdc-nav-prev">
                        <div class="tdc-nav-date">
                            <span class="tdc-day-month">
                                <span class="tdc-day"><?php echo esc_html($prev_day); ?></span>
                                <span class="tdc-divider"></span>
                                <span class="tdc-month"><?php echo esc_html($prev_month); ?></span>
                            </span>
                            <span class="tdc-year"><?php echo esc_html($prev_year); ?></span>
                        </div>
                        <div class="tdc-nav-title-wrap">
                            <a href="<?php echo esc_url(get_permalink($prev_post->ID)); ?>" class="tdc-nav-link">
                                <?php echo esc_html(get_the_title($prev_post->ID)); ?>
                            </a>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php
            endif;
        }
        ?>

        <!-- Khu vực bình luận (Comments) (8) -->
        <?php
        if (have_posts()) {
            rewind_posts();
            the_post();
            if (comments_open() || get_comments_number()) {
                comments_template();
            }
        }
        ?>

    </div><!-- /.detail-below-grid -->

</div><!-- /.detail-page-wrapper -->

<?php
get_footer();

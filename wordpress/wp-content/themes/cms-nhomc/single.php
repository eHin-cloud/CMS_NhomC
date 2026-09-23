<?php
/**
 * Single post template for CMS Nhóm C
 *
 * @package CMS_NhomC
 */

get_header();
?>

<div class="site-content cms-container-layout cms-single-container-layout">
    <!-- Hàng 3 cột theo sơ đồ: Categories (Trái) | Detail (Giữa) | Recent Post (Phải) -->
    <div class="cms-single-grid">
        
        <!-- Cột trái: CHỈ CÓ MÌNH Categories (Module 9) -->
        <aside class="cms-single-sidebar-left" aria-label="Chuyên mục">
            <?php cms_nhomc_render_categories_widget(); ?>
        </aside>

        <!-- Cột giữa: Detail (Module 6) -->
        <main class="cms-single-main-column">
            <?php while (have_posts()) : the_post(); 
                $post_id     = get_the_ID();
                $categories  = get_the_category();
                $primary_cat = !empty($categories) ? $categories[0] : null;

                // Module 19: Tăng lượt xem bài viết có cơ chế chống spam
                cms_nhomc_track_post_views($post_id);
            ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('cms-single-article'); ?>>
                    
                    <!-- Breadcrumbs (Module 19) -->
                    <?php cms_nhomc_breadcrumbs(); ?>

                    <!-- Tiêu đề bài viết -->
                    <h1 class="single-post-title"><?php the_title(); ?></h1>

                    <!-- Meta bài viết: Tác giả, Ngày đăng, Lượt xem & Thời gian đọc (Module 19) -->
                    <div class="cms-single-post-meta">
                        <span class="cms-meta-item cms-meta-date">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor" aria-hidden="true"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/></svg>
                            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
                        </span>
                        <span class="cms-meta-item cms-meta-author">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor" aria-hidden="true"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            <span><?php the_author(); ?></span>
                        </span>
                        <span class="cms-meta-item cms-meta-views" title="Lượt xem bài viết">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor" aria-hidden="true"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                            <span><?php echo esc_html(cms_nhomc_get_post_views($post_id)); ?></span>
                        </span>
                        <span class="cms-meta-item cms-meta-reading-time" title="Thời gian đọc ước tính">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor" aria-hidden="true"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                            <span><?php echo esc_html(cms_nhomc_calculate_reading_time($post_id)); ?></span>
                        </span>
                    </div>

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

                    <!-- Bài viết liên quan -->
                    <?php
                    $related_args = array(
                        'posts_per_page' => 3,
                        'post__not_in'   => array($post_id),
                        'orderby'        => 'date',
                        'order'          => 'DESC',
                    );

                    if ($primary_cat) {
                        $related_args['cat'] = $primary_cat->term_id;
                    }

                    $related_query = new WP_Query($related_args);

                    if ($related_query->have_posts()) :
                    ?>
                        <section class="single-related-section">
                            <h3 class="related-heading">Bài viết liên quan</h3>
                            <div class="related-grid">
                                <?php while ($related_query->have_posts()) : $related_query->the_post(); 
                                    $rel_thumb = cms_nhomc_get_post_thumbnail_url(get_the_ID());
                                ?>
                                    <div class="related-item">
                                        <div class="related-thumb">
                                            <a href="<?php the_permalink(); ?>">
                                                <img src="<?php echo esc_url($rel_thumb); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                                            </a>
                                        </div>
                                        <h4 class="related-title">
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        </h4>
                                    </div>
                                <?php endwhile; wp_reset_postdata(); ?>
                            </div>
                        </section>
                    <?php endif; ?>

                </article>
            <?php endwhile; ?>
        </main>

        <!-- Cột phải: CHỈ CÓ MÌNH Recent post (Module 10) -->
        <aside class="cms-single-sidebar-right" aria-label="Bài viết mới">
            <?php cms_nhomc_render_recent_posts_widget(10, 'BÀI VIẾT MỚI'); ?>
        </aside>
    </div>

    <!-- Khối hàng bên dưới: Prev - Next Post (Module 7) -->
    <?php
    $prev_post = get_previous_post();
    $next_post = get_next_post();

    if (!empty($next_post) || !empty($prev_post)) :
    ?>
    <section class="cms-single-bottom-nav">
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
    </section>
    <?php endif; ?>

    <!-- Khối hàng bên dưới: Comments (Module 8) -->
    <?php
    if (comments_open() || get_comments_number()) :
    ?>
    <section class="cms-single-bottom-comments">
        <?php comments_template(); ?>
    </section>
    <?php endif; ?>
</div>

<?php
get_footer();

<?php
/**
 * Single post template for CMS Nhóm C
 *
 * @package CMS_NhomC
 */

get_header();
?>

<div class="site-content cms-container-layout">
    <div class="cms-layout-grid">
        <!-- Cột nội dung chính bài viết -->
        <main class="cms-main-column">
            <?php while (have_posts()) : the_post(); 
                $post_id   = get_the_ID();
                $categories = get_the_category();
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

                    <!-- Điều hướng bài trước / bài kế tiếp (7) Prev - Next Post chuẩn mẫu TDC -->
                    <?php
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
                    <?php endif; ?>

                    <!-- Khu vực bình luận (Comments) -->
                    <?php
                    if (comments_open() || get_comments_number()) :
                        comments_template();
                    endif;
                    ?>

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

        <!-- Sidebar bên phải: Categories & BÀI VIẾT MỚI trong trang Detail -->
        <aside class="cms-sidebar-column">
            <?php cms_nhomc_render_categories_widget(); ?>
            <?php cms_nhomc_render_recent_posts_widget(10, 'BÀI VIẾT MỚI'); ?>
            <?php cms_nhomc_render_comments_widget(3, 'Comments'); ?>
            <?php if (function_exists('cms_nhomc_render_last_posts_widget')) { cms_nhomc_render_last_posts_widget(5, 'Latest News'); } ?>
        </aside>
    </div>
</div>

<?php
get_footer();

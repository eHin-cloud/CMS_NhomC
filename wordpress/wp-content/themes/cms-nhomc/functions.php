<?php
/**
 * Functions and definitions cho Theme CMS Nhóm C
 *
 * @package CMS_NhomC
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

function cms_nhomc_setup() {
    // Hỗ trợ thẻ Title tự động của WordPress
    add_theme_support('title-tag');

    // Hỗ trợ ảnh đại diện bài viết
    add_theme_support('post-thumbnails');

    // Hỗ trợ HTML5 cho các thẻ form, comment,...
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ));

    // Đăng ký vị trí Menu điều hướng
    register_nav_menus(array(
        'primary-menu' => __('Menu Chính (Header)', 'cms-nhomc'),
        'mobile-menu'  => __('Menu Di Động (3 Chấm)', 'cms-nhomc'),
    ));
}
add_action('after_setup_theme', 'cms_nhomc_setup');

/**
 * Nạp Style và Script
 */
function cms_nhomc_scripts() {
    // Nạp Font Awesome 4.7.0 cho các icon Footer và điều hướng
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), '4.7.0');

    // Nạp style.css của Theme
    wp_enqueue_style('cms-nhomc-style', get_stylesheet_uri(), array('font-awesome'), '1.2.0');
}
add_action('wp_enqueue_scripts', 'cms_nhomc_scripts');

/**
 * Helper to get post thumbnail URL with smart fallbacks
 */
function cms_nhomc_get_post_thumbnail_url($post_id) {
    $post = get_post($post_id);
    if (!$post) return '';

    if (has_post_thumbnail($post->ID)) {
        return get_the_post_thumbnail_url($post->ID, 'large');
    }

    $slug = $post->post_name;
    $theme_dir = get_template_directory_uri();
    if (strpos($slug, 'tuyen-sinh') !== false) {
        return $theme_dir . '/assets/images/tdc-tuyen-sinh.jpg';
    } elseif (strpos($slug, 'thang-06') !== false || strpos($slug, 'toa-nha') !== false) {
        return $theme_dir . '/assets/images/tdc-toa-nha-xanh.jpg';
    } elseif (strpos($slug, 'kiem-dinh') !== false || strpos($slug, 'thong-tin') !== false) {
        return $theme_dir . '/assets/images/tdc-hoi-nghi-cntt.jpg';
    } elseif (strpos($slug, 'tu-sach') !== false || strpos($slug, 'ho-chi-minh') !== false) {
        return $theme_dir . '/assets/images/tdc-tu-sach-dien-tu.jpg';
    } else {
        $fallbacks = array(
            $theme_dir . '/assets/images/tdc-tuyen-sinh.jpg',
            $theme_dir . '/assets/images/tdc-toa-nha-xanh.jpg',
            $theme_dir . '/assets/images/tdc-hoi-nghi-cntt.jpg',
            $theme_dir . '/assets/images/tdc-tu-sach-dien-tu.jpg',
        );
        return $fallbacks[absint($post->ID) % count($fallbacks)];
    }
}

/**
 * Render single post card matching the design specification
 */
function cms_nhomc_render_post_card($post_id = null) {
    $post = get_post($post_id);
    if (!$post) return;

    $day   = get_the_date('d', $post);
    $month = get_the_date('n', $post);
    $year  = get_the_date('Y', $post);
    $month_label = 'THÁNG ' . $month;

    $permalink = get_permalink($post);
    $title     = get_the_title($post);

    // Categories
    $categories = get_the_category($post->ID);
    $cat_links  = array();
    if (!empty($categories)) {
        foreach ($categories as $cat) {
            $cat_links[] = '<a href="' . esc_url(get_category_link($cat->term_id)) . '">' . esc_html($cat->name) . '</a>';
        }
    }
    $categories_html = !empty($cat_links) ? implode(', ', $cat_links) : '<a href="#">Tin Tức</a>';

    // Thumbnail URL
    $thumb_url = cms_nhomc_get_post_thumbnail_url($post->ID);

    // Excerpt: only display if explicitly set or if post has excerpt
    $excerpt = !empty($post->post_excerpt) ? $post->post_excerpt : '';
    ?>
    <article id="post-<?php echo esc_attr($post->ID); ?>" class="cms-post-card">
        <div class="cms-post-thumb">
            <a href="<?php echo esc_url($permalink); ?>" aria-label="<?php echo esc_attr($title); ?>">
                <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($title); ?>" class="cms-thumb-img" loading="lazy" />
            </a>
        </div>
        <div class="cms-post-body">
            <div class="cms-post-header">
                <div class="cms-date-badge">
                    <span class="cms-date-day"><?php echo esc_html($day); ?></span>
                    <div class="cms-date-meta">
                        <span class="cms-date-month"><?php echo esc_html($month_label); ?></span>
                        <span class="cms-date-year"><?php echo esc_html($year); ?></span>
                    </div>
                </div>
                <div class="cms-title-wrap">
                    <h2 class="cms-post-title">
                        <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
                    </h2>
                    <div class="cms-post-meta">
                        <span class="cms-cat-label">Categories</span>
                        <span class="cms-cat-links"><?php echo wp_kses_post($categories_html); ?></span>
                    </div>
                </div>
            </div>
            <?php if (!empty($excerpt)) : ?>
                <div class="cms-post-excerpt">
                    <?php echo esc_html($excerpt); ?>
                </div>
            <?php endif; ?>
        </div>
    </article>
    <?php
}

/**
 * Shortcode [tin_tuc_ngang] - Renders posts in horizontal list layout
 * Usage: [tin_tuc_ngang so_bai="4" danh_muc="tin-tuc"]
 */
function cms_nhomc_tin_tuc_ngang_shortcode($atts) {
    $atts = shortcode_atts(array(
        'so_bai'   => 4,
        'danh_muc' => '',
        'offset'   => 0,
    ), $atts, 'tin_tuc_ngang');

    $args = array(
        'posts_per_page' => intval($atts['so_bai']),
        'offset'         => intval($atts['offset']),
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    if (!empty($atts['danh_muc'])) {
        $args['category_name'] = sanitize_text_field($atts['danh_muc']);
    }

    $query = new WP_Query($args);

    ob_start();
    if ($query->have_posts()) {
        echo '<div class="cms-post-list">';
        while ($query->have_posts()) {
            $query->the_post();
            cms_nhomc_render_post_card(get_the_ID());
        }
        echo '</div>';
        wp_reset_postdata();
    } else {
        echo '<p class="cms-no-posts">Chưa có bài viết nào.</p>';
    }
    return ob_get_clean();
}
add_shortcode('tin_tuc_ngang', 'cms_nhomc_tin_tuc_ngang_shortcode');


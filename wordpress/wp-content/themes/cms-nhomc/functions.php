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

    // 1. Tuyển sinh
    if (strpos($slug, 'tuyen-sinh') !== false) {
        return $theme_dir . '/assets/images/tdc-tuyen-sinh.jpg';
    }
    // 2. Tòa nhà TDC xanh / Tháng 06
    if (strpos($slug, 'thang-06') !== false || strpos($slug, 'toa-nha') !== false) {
        return $theme_dir . '/assets/images/tdc-toa-nha-xanh.jpg';
    }
    // 3. Hội nghị kiểm định CNTT
    if (strpos($slug, 'kiem-dinh') !== false || strpos($slug, 'nghe-cong-nghe') !== false) {
        return $theme_dir . '/assets/images/tdc-hoi-nghi-cntt.jpg';
    }
    // 4. Tủ sách điện tử Bác Hồ
    if (strpos($slug, 'tu-sach') !== false || strpos($slug, 'ho-chi-minh') !== false) {
        return $theme_dir . '/assets/images/tdc-tu-sach-hcm.jpg';
    }
    // 5. Mở lối tương lai AI
    if (strpos($slug, 'mo-loi') !== false || strpos($slug, 'ky-nguyen-ai') !== false || strpos($slug, '-ai') !== false) {
        return $theme_dir . '/assets/images/tdc-mo-loi-ai.jpg';
    }
    // 6. Ký kết hợp tác chiến lược
    if (strpos($slug, 'ky-ket') !== false || strpos($slug, 'doanh-nghiep') !== false) {
        return $theme_dir . '/assets/images/tdc-ky-ket-hop-tac.jpg';
    }
    // 7. Đoàn - Hội Khoa CNTT hoạt động
    if (strpos($slug, 'doan-hoi-khoa-cntt-to-chuc') !== false || strpos($slug, 'chuoi-hoat-dong') !== false) {
        return $theme_dir . '/assets/images/tdc-doan-hoi-cntt.jpg';
    }
    // 8. Chúc mừng năm mới 2026
    if (strpos($slug, 'chuc-mung-nam-moi') !== false || strpos($slug, 'nam-moi-2026') !== false) {
        return $theme_dir . '/assets/images/tdc-tet-2026.jpg';
    }
    // 9. Công trình thanh niên
    if (strpos($slug, 'tuoi-tre-tdc') !== false || strpos($slug, 'thanh-nien') !== false) {
        return $theme_dir . '/assets/images/tdc-cong-trinh-thanh-nien.jpg';
    }
    // 10. Đại tiệc ngày hội việc làm
    if (strpos($slug, 'dai-tiec') !== false || strpos($slug, '3-trong-1') !== false || strpos($slug, 'viec-lam') !== false) {
        return $theme_dir . '/assets/images/tdc-dai-tiec-viec-lam.jpg';
    }

    $fallbacks = array(
        $theme_dir . '/assets/images/tdc-tuyen-sinh.jpg',
        $theme_dir . '/assets/images/tdc-toa-nha-xanh.jpg',
        $theme_dir . '/assets/images/tdc-hoi-nghi-cntt.jpg',
        $theme_dir . '/assets/images/tdc-tu-sach-hcm.jpg',
        $theme_dir . '/assets/images/tdc-mo-loi-ai.jpg',
        $theme_dir . '/assets/images/tdc-ky-ket-hop-tac.jpg',
        $theme_dir . '/assets/images/tdc-doan-hoi-cntt.jpg',
        $theme_dir . '/assets/images/tdc-tet-2026.jpg',
        $theme_dir . '/assets/images/tdc-cong-trinh-thanh-nien.jpg',
        $theme_dir . '/assets/images/tdc-dai-tiec-viec-lam.jpg',
    );
    return $fallbacks[absint($post->ID) % count($fallbacks)];
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

/**
 * Render Widget: BÀI VIẾT MỚI (Recent Posts)
 */
function cms_nhomc_render_recent_posts_widget($limit = 5, $title = 'BÀI VIẾT MỚI') {
    $args = array(
        'posts_per_page'      => intval($limit),
        'post_status'         => 'publish',
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => 1,
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) :
    ?>
        <div class="cms-sidebar-widget widget-recent-posts">
            <h3 class="widget-title widget-title-recent"><?php echo esc_html($title); ?></h3>
            <ul class="sidebar-post-list">
                <?php while ($query->have_posts()) : $query->the_post(); 
                    $thumb_url = cms_nhomc_get_post_thumbnail_url(get_the_ID());
                ?>
                    <li class="sidebar-post-item">
                        <div class="sidebar-post-thumb">
                            <a href="<?php the_permalink(); ?>">
                                <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                            </a>
                        </div>
                        <div class="sidebar-post-info">
                            <h4 class="sidebar-post-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h4>
                            <span class="sidebar-post-date"><?php echo get_the_date('d/m/Y'); ?></span>
                        </div>
                    </li>
                <?php endwhile; wp_reset_postdata(); ?>
            </ul>
        </div>
    <?php
    endif;
}

/**
 * Render Widget: BÀI VIẾT NỔI BẬT (Featured Posts)
 */
function cms_nhomc_render_featured_posts_widget($limit = 5, $title = 'BÀI VIẾT NỔI BẬT') {
    // 1. Tìm các bài viết thuộc tag hoặc category "Nổi Bật" hoặc có meta _is_featured
    $args = array(
        'posts_per_page'      => intval($limit),
        'post_status'         => 'publish',
        'category_name'       => 'noi-bat',
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => 1,
    );
    $query = new WP_Query($args);

    // Nếu chưa có category nổi bật hoặc ít hơn limit, lấy theo thứ tự khác hoặc các bài còn lại
    if (!$query->have_posts() || $query->post_count < 2) {
        $args_fallback = array(
            'posts_per_page'      => intval($limit),
            'post_status'         => 'publish',
            'orderby'             => 'date',
            'order'               => 'ASC', // Lấy các bài nổi bật đặc thù
            'ignore_sticky_posts' => 1,
        );
        $query = new WP_Query($args_fallback);
    }

    if ($query->have_posts()) :
    ?>
        <div class="cms-sidebar-widget widget-featured-posts">
            <h3 class="widget-title widget-title-featured"><?php echo esc_html($title); ?></h3>
            <ul class="sidebar-post-list">
                <?php while ($query->have_posts()) : $query->the_post(); 
                    $thumb_url = cms_nhomc_get_post_thumbnail_url(get_the_ID());
                ?>
                    <li class="sidebar-post-item">
                        <div class="sidebar-post-thumb">
                            <a href="<?php the_permalink(); ?>">
                                <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                            </a>
                        </div>
                        <div class="sidebar-post-info">
                            <h4 class="sidebar-post-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h4>
                            <span class="sidebar-post-date"><?php echo get_the_date('d/m/Y'); ?></span>
                        </div>
                    </li>
                <?php endwhile; wp_reset_postdata(); ?>
            </ul>
        </div>
    <?php
    endif;
}

/**
 * Render Widget: CATEGORIES (Chuyên mục) - Module 9 (Anh Quý)
 */
function cms_nhomc_render_categories_widget($title = 'Categories') {
    ?>
    <div class="widget-categories-card">
        <h3 class="widget-cat-title"><?php echo esc_html($title); ?></h3>
        <div class="widget-cat-stripe"></div>
        <div class="widget-cat-body">
            <ul class="widget-cat-list">
                <?php
                // Lấy danh sách chuyên mục thực tế từ WordPress
                $categories = get_categories(array(
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                    'hide_empty' => false,
                ));

                // Lọc bỏ danh mục mặc định Chưa phân loại nếu có các chuyên mục khác
                $filtered_cats = array();
                if (!empty($categories)) {
                    foreach ($categories as $cat) {
                        if ($cat->slug !== 'uncategorized' && $cat->slug !== 'chua-phan-loai') {
                            $filtered_cats[] = $cat;
                        }
                    }
                }

                if (!empty($filtered_cats)) {
                    foreach ($filtered_cats as $category) {
                        echo '<li>';
                        echo '<span class="cat-bullet"></span>';
                        echo '<a href="' . esc_url(get_category_link($category->term_id)) . '">' . esc_html($category->name) . '</a>';
                        echo '</li>';
                    }
                } else {
                    // Dữ liệu mẫu hiển thị trực tiếp chuẩn y hệt hình ảnh thiết kế
                    $sample_items = array(
                        array('name' => '.Net Developer', 'link' => home_url('/category/net-developer/')),
                        array('name' => 'Thực Tập Sinh Tester', 'link' => home_url('/category/thuc-tap-sinh-tester/')),
                        array('name' => 'Trợ giảng lập trình - Part time', 'link' => home_url('/category/tro-giang-lap-trinh/')),
                    );

                    foreach ($sample_items as $item) {
                        echo '<li>';
                        echo '<span class="cat-bullet"></span>';
                        echo '<a href="' . esc_url($item['link']) . '">' . esc_html($item['name']) . '</a>';
                        echo '</li>';
                    }
                }
                ?>
            </ul>
        </div>
    </div>
    <?php
}

/**
 * Đăng ký Widget Area (Sidebar)
 */
function cms_nhomc_widgets_init() {
    register_sidebar(array(
        'name'          => __('Sidebar Chính', 'cms-nhomc'),
        'id'            => 'main-sidebar',
        'description'   => __('Khu vực thanh bên cho giao diện', 'cms-nhomc'),
        'before_widget' => '<div id="%1$s" class="widget-categories-card %2$s">',
        'after_widget'  => '</div></div>',
        'before_title'  => '<h3 class="widget-cat-title">',
        'after_title'   => '</h3><div class="widget-cat-stripe"></div><div class="widget-cat-body">',
    ));
}
add_action('widgets_init', 'cms_nhomc_widgets_init');

/**
 * Tự động tạo sẵn 3 chuyên mục mẫu nếu chưa có trong Database
 */
function cms_nhomc_create_default_categories() {
    $default_cats = array(
        '.Net Developer',
        'Thực Tập Sinh Tester',
        'Trợ giảng lập trình - Part time'
    );
    foreach ($default_cats as $cat_name) {
        if (!term_exists($cat_name, 'category')) {
            wp_insert_term($cat_name, 'category');
        }
    }
}
add_action('after_setup_theme', 'cms_nhomc_create_default_categories');


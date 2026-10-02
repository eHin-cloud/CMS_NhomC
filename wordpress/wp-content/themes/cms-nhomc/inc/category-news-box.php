<?php
/**
 * Module Khối Tin Chuyên Mục (Category News Box) Phong Cách Báo Chí & Widget widget_test_4
 * Thiết kế chuẩn theo mẫu báo điện tử:
 * - Vạch chỉ thị màu xanh góc trên
 * - Tiêu đề chuyên mục in hoa đậm + Danh sách chuyên mục con + Nút menu
 * - 1 Tin tiêu điểm: Ảnh đại diện (trái) + Tiêu đề (phải)
 * - 4 Tin phụ: Danh sách tiêu đề + Icon số lượng bình luận
 * - Hỗ trợ Random bài viết theo yêu cầu
 *
 * @package CMS_NhomC
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hàm render Khối Tin Chuyên Mục theo mẫu báo chí (Hỗ trợ Random)
 *
 * @param array $args Cấu hình hiển thị
 * @return string HTML output
 */
function cms_nhomc_render_category_box_html($args = array()) {
    $defaults = array(
        'title'       => 'THỜI SỰ',
        'category'    => '',
        'limit'       => 5,
        'random'      => true,
        'sub_cats'    => array('Dân sinh', 'Giao thông'),
    );
    $params = wp_parse_args($args, $defaults);

    $cat_id = 0;
    $cat_obj = null;
    $title = !empty($params['title']) ? $params['title'] : 'THỜI SỰ';

    if (!empty($params['category'])) {
        if (is_numeric($params['category'])) {
            $cat_obj = get_category(intval($params['category']));
        } else {
            $cat_obj = get_category_by_slug(sanitize_title($params['category']));
        }
        if ($cat_obj && !is_wp_error($cat_obj)) {
            $cat_id = $cat_obj->term_id;
            if (empty($params['title'])) {
                $title = $cat_obj->name;
            }
        }
    }

    $cat_link = $cat_obj ? get_category_link($cat_obj->term_id) : '#';

    // Sub categories
    $sub_nav_links = array();
    if ($cat_id > 0) {
        $real_sub_cats = get_categories(array(
            'parent'     => $cat_id,
            'hide_empty' => false,
            'number'     => 2
        ));
        if (!empty($real_sub_cats)) {
            foreach ($real_sub_cats as $sc) {
                $sub_nav_links[] = array(
                    'name' => $sc->name,
                    'link' => get_category_link($sc->term_id)
                );
            }
        }
    }

    // Nếu không có sub-category thực tế, dùng danh sách mẫu chuẩn "Dân sinh · Giao thông"
    if (empty($sub_nav_links)) {
        if (!empty($params['sub_cats']) && is_array($params['sub_cats'])) {
            foreach ($params['sub_cats'] as $sc_name) {
                $sub_nav_links[] = array(
                    'name' => $sc_name,
                    'link' => $cat_link
                );
            }
        }
    }

    // Query bài viết (Hỗ trợ Random)
    $query_args = array(
        'post_type'           => 'post',
        'posts_per_page'      => max(1, intval($params['limit'])),
        'post_status'         => 'publish',
        'ignore_sticky_posts' => 1,
    );

    if (!empty($params['random'])) {
        $query_args['orderby'] = 'rand';
    } else {
        $query_args['orderby'] = 'date';
        $query_args['order']   = 'DESC';
    }

    if ($cat_id > 0) {
        $query_args['cat'] = $cat_id;
    }

    $query = new WP_Query($query_args);

    // Fallback nếu chuyên mục được chọn không đủ bài
    if (!$query->have_posts()) {
        unset($query_args['cat']);
        $query = new WP_Query($query_args);
    }

    ob_start();
    ?>
    <section class="cms-category-news-block" aria-label="<?php echo esc_attr($title); ?>">
        <!-- 1. Header Chuyên Mục -->
        <header class="cms-cat-block-header">
            <div class="cms-cat-heading-group">
                <span class="cms-cat-top-bar" aria-hidden="true"></span>
                <h3 class="cms-cat-main-title">
                    <a href="<?php echo esc_url($cat_link); ?>" title="<?php echo esc_attr($title); ?>">
                        <?php echo esc_html(mb_strtoupper($title, 'UTF-8')); ?>
                    </a>
                </h3>
            </div>

            <div class="cms-cat-sub-nav">
                <?php if (!empty($sub_nav_links)) : ?>
                    <nav class="cms-cat-sub-links" aria-label="Chuyên mục con">
                        <?php 
                        $rendered_subs = array();
                        foreach ($sub_nav_links as $sub_item) {
                            $rendered_subs[] = sprintf(
                                '<a href="%s" class="cms-sub-link">%s</a>',
                                esc_url($sub_item['link']),
                                esc_html($sub_item['name'])
                            );
                        }
                        echo implode('<span class="cms-sub-separator">·</span>', $rendered_subs);
                        ?>
                    </nav>
                <?php endif; ?>

                <!-- Icon 3 gạch danh mục -->
                <a href="<?php echo esc_url($cat_link); ?>" class="cms-cat-menu-icon" aria-label="Xem thêm bài viết trong chuyên mục" title="Xem thêm">
                    <span></span>
                    <span></span>
                    <span></span>
                </a>
            </div>
        </header>

        <!-- 2. Thân khối bài viết -->
        <div class="cms-cat-block-content">
            <?php if ($query->have_posts()) : 
                $count = 0;
                while ($query->have_posts()) : 
                    $query->the_post();
                    $count++;
                    $post_id   = get_the_ID();
                    $permalink = get_permalink();
                    $p_title   = get_the_title();
                    $comments  = get_comments_number();

                    if ($count === 1) : 
                        // BÀI TIÊU ĐIỂM (Ảnh bên trái + Tiêu đề bên phải)
                        $thumb_url = '';
                        if (function_exists('cms_nhomc_get_post_thumbnail_url')) {
                            $thumb_url = cms_nhomc_get_post_thumbnail_url($post_id);
                        } elseif (has_post_thumbnail()) {
                            $thumb_url = get_the_post_thumbnail_url($post_id, 'medium');
                        }
                        if (empty($thumb_url)) {
                            $thumb_url = 'https://images.unsplash.com/photo-1585829365295-ab7cd400c167?w=600&auto=format&fit=crop&q=80';
                        }
                        ?>
                        <article class="cms-cat-lead-post">
                            <div class="cms-cat-lead-media">
                                <a href="<?php echo esc_url($permalink); ?>" aria-label="<?php echo esc_attr($p_title); ?>">
                                    <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($p_title); ?>" class="cms-cat-lead-img" loading="lazy" />
                                </a>
                            </div>
                            <div class="cms-cat-lead-body">
                                <h4 class="cms-cat-lead-title">
                                    <a href="<?php echo esc_url($permalink); ?>">
                                        <?php echo esc_html($p_title); ?>
                                    </a>
                                </h4>
                            </div>
                        </article>

                        <!-- Mở danh sách các bài viết phụ dạng text list -->
                        <ul class="cms-cat-sub-list">
                    <?php else : 
                        // CÁC BÀI PHỤ DẠNG TEXT LIST
                        ?>
                        <li class="cms-cat-sub-item">
                            <h5 class="cms-cat-sub-title">
                                <a href="<?php echo esc_url($permalink); ?>">
                                    <?php echo esc_html($p_title); ?>
                                </a>
                                <?php if ($comments > 0) : ?>
                                    <span class="cms-cat-comment-badge" title="<?php echo esc_attr($comments); ?> bình luận">
                                        <svg viewBox="0 0 24 24" class="cms-comment-icon" aria-hidden="true" focusable="false">
                                            <path d="M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4V4c0-1.1-.9-2-2-2z"/>
                                        </svg>
                                        <span class="cms-comment-num"><?php echo esc_html($comments); ?></span>
                                    </span>
                                <?php endif; ?>
                            </h5>
                        </li>
                    <?php 
                    endif;
                endwhile; 
                wp_reset_postdata();

                if ($count > 1) {
                    echo '</ul>'; // Đóng cms-cat-sub-list
                }
                ?>
            <?php else : ?>
                <p class="cms-no-posts">Chưa có bài viết.</p>
            <?php endif; ?>
        </div>
        <!-- Vạch kết thúc ngăn cách khối -->
        <div class="cms-cat-bottom-divider" aria-hidden="true"></div>
    </section>
    <?php
    return ob_get_clean();
}

/**
 * Hàm wrapper tương thích ngược
 */
function cms_nhomc_render_category_box($category_input = '', $limit = 5, $custom_title = '') {
    return cms_nhomc_render_category_box_html(array(
        'title'    => $custom_title,
        'category' => $category_input,
        'limit'    => $limit,
        'random'   => true
    ));
}

// =========================================================================
// 1) ĐỊNH NGHĨA WIDGET: widget_test_4
// =========================================================================
class widget_test_4 extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'widget_test_4',
            __('widget_test_4', 'cms-nhomc'),
            array(
                'classname'   => 'widget_test_4',
                'description' => __('Widget hiển thị khối tin tức theo hình mẫu báo điện tử (Random bài viết)', 'cms-nhomc'),
            )
        );
    }

    /**
     * Hiển thị Widget ngoài giao diện (Frontend)
     */
    public function widget($args, $instance) {
        $title    = !empty($instance['title']) ? $instance['title'] : 'THỜI SỰ';
        $category = !empty($instance['category']) ? $instance['category'] : '';
        $limit    = !empty($instance['limit']) ? intval($instance['limit']) : 5;

        echo $args['before_widget'];
        echo cms_nhomc_render_category_box_html(array(
            'title'    => $title,
            'category' => $category,
            'limit'    => $limit,
            'random'   => true, // Random theo yêu cầu đề bài
        ));
        echo $args['after_widget'];
    }

    /**
     * Giao diện cấu hình Widget trong Quản trị (Admin)
     */
    public function form($instance) {
        $title    = !empty($instance['title']) ? $instance['title'] : 'THỜI SỰ';
        $category = !empty($instance['category']) ? $instance['category'] : '';
        $limit    = !empty($instance['limit']) ? intval($instance['limit']) : 5;
        $categories = get_categories(array('hide_empty' => false));
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Tiêu đề khối:', 'cms-nhomc'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('category')); ?>"><?php _e('Chuyên mục (Để trống = Random toàn bộ bài viết):', 'cms-nhomc'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('category')); ?>" name="<?php echo esc_attr($this->get_field_name('category')); ?>">
                <option value=""><?php _e('-- Random từ tất cả chuyên mục --', 'cms-nhomc'); ?></option>
                <?php foreach ($categories as $cat) : ?>
                    <option value="<?php echo esc_attr($cat->slug); ?>" <?php selected($category, $cat->slug); ?>>
                        <?php echo esc_html($cat->name); ?> (<?php echo esc_html($cat->count); ?> bài)
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('limit')); ?>"><?php _e('Số lượng bài viết:', 'cms-nhomc'); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" name="<?php echo esc_attr($this->get_field_name('limit')); ?>" type="number" step="1" min="1" max="20" value="<?php echo esc_attr($limit); ?>" size="3" />
        </p>
        <p>
            <small style="color:#0073aa;"><em>* Chế độ bài viết: Tự động Random bài viết ngẫu nhiên theo hình mẫu thiết kế.</em></small>
        </p>
        <?php
    }

    /**
     * Cập nhật dữ liệu cấu hình
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title']    = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : 'THỜI SỰ';
        $instance['category'] = (!empty($new_instance['category'])) ? sanitize_text_field($new_instance['category']) : '';
        $instance['limit']    = (!empty($new_instance['limit'])) ? intval($new_instance['limit']) : 5;
        return $instance;
    }
}

/**
 * Đăng ký Widget widget_test_4 với WordPress
 */
function cms_nhomc_register_widget_test_4() {
    register_widget('widget_test_4');

    // Đăng ký khu vực Widget phía trên Footer (Before Footer Widget Area)
    register_sidebar(array(
        'name'          => __('Khu vực phía trên Footer (Before Footer)', 'cms-nhomc'),
        'id'            => 'before-footer-sidebar',
        'description'   => __('Khu vực hiển thị widget_test_4 phía trên Footer tại Trang chủ, Trang danh sách và Trang chi tiết.', 'cms-nhomc'),
        'before_widget' => '<div id="%1$s" class="cms-before-footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title" style="display:none;">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'cms_nhomc_register_widget_test_4');

// Tắt hoàn toàn Gutenberg Block Editor trong trang Quản lý Widgets (Khôi phục giao diện Classic Widgets)
add_filter('use_widgets_block_editor', '__return_false');

/**
 * Tự động gán widget_test_4 vào before-footer-sidebar và dọn sạch các block Gutenberg rác
 */
function cms_nhomc_auto_assign_widget_test_4() {
    $sidebars_widgets = get_option('sidebars_widgets', array());
    $needs_update = false;

    // Khởi tạo instance cho widget_test_4 nếu chưa có
    $widget_instances = get_option('widget_widget_test_4', array());
    if (empty($widget_instances) || !isset($widget_instances[1])) {
        $widget_instances = array(
            1 => array(
                'title'    => 'THỜI SỰ',
                'category' => '',
                'limit'    => 5,
            ),
            '_multiwidget' => 1
        );
        update_option('widget_widget_test_4', $widget_instances);
    }

    $current_widgets = isset($sidebars_widgets['before-footer-sidebar']) ? (array)$sidebars_widgets['before-footer-sidebar'] : array();
    $cleaned_widgets = array();
    $has_widget_test_4 = false;

    foreach ($current_widgets as $w_id) {
        if (strpos($w_id, 'widget_test_4') !== false) {
            $has_widget_test_4 = true;
            $cleaned_widgets[] = $w_id;
        } elseif (strpos($w_id, 'block-') === false) {
            $cleaned_widgets[] = $w_id;
        }
    }

    if (!$has_widget_test_4) {
        array_unshift($cleaned_widgets, 'widget_test_4-1');
        $needs_update = true;
    }

    if ($needs_update || count($cleaned_widgets) !== count($current_widgets)) {
        $sidebars_widgets['before-footer-sidebar'] = $cleaned_widgets;
        update_option('sidebars_widgets', $sidebars_widgets);
    }
}
add_action('init', 'cms_nhomc_auto_assign_widget_test_4');

/**
 * Hàm hiển thị widget_test_4 tại khu vực phía trên Footer
 * Tự động kiểm tra: Trang chủ, Trang danh sách (Archive), Trang chi tiết (Single)
 */
function cms_nhomc_render_before_footer_widget() {
    if (is_front_page() || is_home() || is_archive() || is_single()) {
        ?>
        <div class="cms-before-footer-area">
            <div class="cms-container-layout">
                <?php 
                if (is_active_sidebar('before-footer-sidebar')) {
                    dynamic_sidebar('before-footer-sidebar');
                } else {
                    the_widget('widget_test_4', array(
                        'title' => 'THỜI SỰ',
                        'limit' => 5,
                    ), array(
                        'before_widget' => '<div class="cms-before-footer-widget widget_test_4">',
                        'after_widget'  => '</div>',
                    ));
                }
                ?>
            </div>
        </div>
        <?php
    }
}

/**
 * Đăng ký Shortcode [category_box] & [widget_test_4]
 */
function cms_nhomc_category_box_shortcode($atts) {
    $atts = shortcode_atts(array(
        'slug'     => '',
        'cat'      => '',
        'limit'    => 5,
        'title'    => 'THỜI SỰ',
        'random'   => 1,
    ), $atts, 'category_box');

    $category = !empty($atts['slug']) ? $atts['slug'] : $atts['cat'];
    return cms_nhomc_render_category_box_html(array(
        'title'    => $atts['title'],
        'category' => $category,
        'limit'    => intval($atts['limit']),
        'random'   => (bool)$atts['random']
    ));
}
add_shortcode('category_box', 'cms_nhomc_category_box_shortcode');
add_shortcode('widget_test_4', 'cms_nhomc_category_box_shortcode');

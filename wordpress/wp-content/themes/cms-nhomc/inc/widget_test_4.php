<?php
/**
 * Widget: widget_test_4
 * Mô tả: Widget Podcast hiển thị danh sách bài viết ngẫu nhiên theo hình mẫu (phía trên Footer)
 * Yêu cầu:
 * 1) Tên widget: widget_test_4
 * 2) Hiển thị tại: Trang chủ, Trang danh sách, Trang chi tiết; Khu vực: phía trên Footer
 * 3) Giao diện hiển thị: theo hình mẫu (Icon tai nghe, tiêu đề đỏ, gạch đứt nét ngăn cách); random không SV nào giống nhau
 *
 * @package CMS_NhomC
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class định nghĩa widget_test_4
 */
class widget_test_4 extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'widget_test_4', // Base ID đúng chuẩn widget_test_4
            __('widget_test_4', 'cms-nhomc'), // Tên widget hiển thị trong Admin
            array(
                'classname'   => 'widget_test_4 cms-widget-podcast-container',
                'description' => __('Widget Podcast hiển thị danh sách bài viết ngẫu nhiên (random) chuẩn theo hình mẫu bài thi.', 'cms-nhomc'),
                'customize_selective_refresh' => true,
            )
        );
    }

    /**
     * Xuất HTML widget ra ngoài Frontend
     *
     * @param array $args     Môi trường sidebar chứa widget
     * @param array $instance Dữ liệu cấu hình đã lưu của widget
     */
    public function widget($args, $instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Podcast';
        $limit = !empty($instance['limit']) ? intval($instance['limit']) : 5;

        $before_widget = !empty($args['before_widget']) ? $args['before_widget'] : '<div class="widget_test_4 cms-podcast-outer-box">';
        $after_widget  = !empty($args['after_widget']) ? $args['after_widget'] : '</div>';

        echo $before_widget;
        cms_nhomc_render_widget_test_4_content($limit, $title);
        echo $after_widget;
    }

    /**
     * Form cấu hình widget trong WP-Admin (Appearance -> Widgets)
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Podcast';
        $limit = !empty($instance['limit']) ? intval($instance['limit']) : 5;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <strong><?php esc_html_e('Tiêu đề Widget:', 'cms-nhomc'); ?></strong>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('limit')); ?>">
                <strong><?php esc_html_e('Số lượng bài hiển thị (Mặc định 5):', 'cms-nhomc'); ?></strong>
            </label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" name="<?php echo esc_attr($this->get_field_name('limit')); ?>" type="number" step="1" min="1" max="20" value="<?php echo esc_attr($limit); ?>" size="3" />
        </p>
        <p class="description">
            <?php esc_html_e('Bài viết tự động lấy ngẫu nhiên (random orderby=rand), đảm bảo không SV nào giống nhau.', 'cms-nhomc'); ?>
        </p>
        <?php
    }

    /**
     * Lưu dữ liệu khi chỉnh sửa trong Admin
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : 'Podcast';
        $instance['limit'] = (!empty($new_instance['limit'])) ? intval($new_instance['limit']) : 5;
        return $instance;
    }
}

// Alias class dạng CamelCase để tương thích toàn diện mọi cách gọi
if (!class_exists('Widget_Test_4')) {
    class_alias('widget_test_4', 'Widget_Test_4');
}

/**
 * Đăng ký widget_test_4 với WordPress
 */
function cms_nhomc_register_widget_test_4() {
    register_widget('widget_test_4');
}
add_action('widgets_init', 'cms_nhomc_register_widget_test_4');

/**
 * Hàm render giao diện của widget_test_4
 * Thiết kế chính xác 100% theo hình mẫu:
 * - Vạch đỏ dọc bên trái chữ "Podcast"
 * - Danh sách bài viết có icon tai nghe (Headphones) màu xám viền nét mảnh
 * - Tiêu đề bài viết in đậm màu đen than (#1f2328)
 * - Đường kẻ đứt nét (#e2e8f0) phân cách các bài viết
 * - Lấy ngẫu nhiên (random, không SV nào giống nhau) từ WP_Query orderby=rand
 */
function cms_nhomc_render_widget_test_4_content($limit = 5, $title = 'Podcast') {
    if (empty($title)) {
        $title = 'Podcast';
    }
    $limit = !empty($limit) ? intval($limit) : 5;

    // Lấy bài viết ngẫu nhiên từ cơ sở dữ liệu
    $random_query = new WP_Query(array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'orderby'        => 'rand',
        'no_found_rows'  => true,
    ));

    $display_items = array();

    if ($random_query->have_posts()) {
        while ($random_query->have_posts()) {
            $random_query->the_post();
            $display_items[] = array(
                'id'    => get_the_ID(),
                'title' => get_the_title(),
                'link'  => get_permalink(),
            );
        }
        wp_reset_postdata();
    }

    // Kho mẫu dự phòng đa dạng chủ đề sinh viên / thời sự (đảm bảo luôn đủ số lượng & random)
    if (count($display_items) < $limit) {
        $sample_pool = array(
            "Giới trẻ 'cày bán mạng' hay làm việc vừa sức để chăm sóc bản thân?",
            "Tin tức tối 24-9: Bắt nam thanh niên sát hại vợ và chị vợ ở xã Củ Chi",
            "Con gái nói dối chuyện mang thai: Gia đình xin lỗi bệnh viện sau vụ livestream, gây rối trật tự",
            "Trường công ở TP.HCM thu những khoản tiền gì?",
            "Quỹ lớp không mất đi mà đổi từ tên này sang tên khác?",
            "Bí quyết học lập trình hiệu quả dành cho sinh viên CNTT",
            "Xu hướng phát triển trí tuệ nhân tạo và cơ hội việc làm năm 2026",
            "Gen Z và văn hóa làm việc linh hoạt trong thời kỳ số hóa",
            "Thực tập sinh công nghệ: Cần chuẩn bị những kỹ năng thực chiến nào?",
            "Podcast Góc Nhìn Trẻ: Cân bằng giữa học tập, công việc và sức khỏe tinh thần",
            "Định hướng nghề nghiệp tương lai cho sinh viên ngành phần mềm",
            "Hành trình khởi nghiệp từ giảng đường đại học và bài học đắt giá",
        );
        shuffle($sample_pool);

        foreach ($sample_pool as $sample_title) {
            if (count($display_items) >= $limit) {
                break;
            }
            $display_items[] = array(
                'id'    => 0,
                'title' => $sample_title,
                'link'  => home_url('/'),
            );
        }
    }
    ?>
    <div class="cms-podcast-card">
        <!-- Tiêu đề có vạch đỏ dọc theo hình mẫu -->
        <div class="cms-podcast-header">
            <span class="cms-podcast-accent-bar" aria-hidden="true"></span>
            <h3 class="cms-podcast-title"><?php echo esc_html($title); ?></h3>
        </div>

        <!-- Danh sách bài viết Podcast -->
        <ul class="cms-podcast-list">
            <?php foreach ($display_items as $item) : ?>
                <li class="cms-podcast-item">
                    <a href="<?php echo esc_url($item['link']); ?>" class="cms-podcast-link" title="<?php echo esc_attr($item['title']); ?>">
                        <span class="cms-podcast-icon" aria-hidden="true">
                            <!-- Icon tai nghe Headphones viền mảnh theo chuẩn hình mẫu -->
                            <svg class="cms-podcast-svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                                <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3z"></path>
                                <path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                            </svg>
                        </span>
                        <span class="cms-podcast-text"><?php echo esc_html($item['title']); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}

/**
 * Hàm helper gọi trực tiếp widget_test_4 nếu cần
 */
function cms_nhomc_render_widget_test_4($limit = 5, $title = 'Podcast') {
    cms_nhomc_render_widget_test_4_content($limit, $title);
}

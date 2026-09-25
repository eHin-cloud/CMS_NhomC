<?php
/**
 * Chức năng: (20) Newsletter Subscription (Đăng ký nhận bản tin)
 * Kiến trúc: WordPress Native + Ponytail Standard
 *
 * @package CMS_NhomC
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * 1. KHỞI TẠO BẢNG CƠ SỞ DỮ LIỆU
 * Bảng: {$wpdb->prefix}newsletter_subscribers
 * Cột: id, email (UNIQUE), status, created_at, updated_at, ip_address, user_agent
 */
function cms_nhomc_create_newsletter_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'newsletter_subscribers';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        email varchar(191) NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'active',
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        ip_address varchar(45) DEFAULT '',
        user_agent text NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY email (email),
        KEY status (status),
        KEY created_at (created_at)
    ) {$charset_collate};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
// Chạy khi kích hoạt theme và kiểm tra đảm bảo bảng luôn sẵn sàng
add_action('after_switch_theme', 'cms_nhomc_create_newsletter_table');
add_action('admin_init', 'cms_nhomc_check_newsletter_table');

function cms_nhomc_check_newsletter_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'newsletter_subscribers';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
        cms_nhomc_create_newsletter_table();
    }
}

/**
 * Lấy IP an toàn của client
 */
function cms_nhomc_get_client_ip() {
    $ip = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return sanitize_text_field(substr($ip, 0, 45));
}

/**
 * 2. XỬ LÝ AJAX ĐĂNG KÝ BẢN TIN (FRONTEND)
 * Hỗ trợ cả khách vãng lai (nopriv) và thành viên đăng nhập
 */
function cms_nhomc_ajax_newsletter_subscribe() {
    // 1. Kiểm tra Nonce bảo mật chống CSRF
    if (!check_ajax_referer('cms_nhomc_newsletter_nonce', 'nonce', false)) {
        wp_send_json_error(array(
            'message' => __('Yêu cầu không hợp lệ hoặc phiên làm việc đã hết hạn. Vui lòng tải lại trang.', 'cms-nhomc'),
            'code'    => 'invalid_nonce',
        ), 403);
        return;
    }

    // 2. Nhận và làm sạch dữ liệu email
    $raw_email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if (empty($raw_email)) {
        wp_send_json_error(array(
            'message' => __('Vui lòng nhập địa chỉ email.', 'cms-nhomc'),
            'code'    => 'empty_email',
        ), 400);
        return;
    }

    $email = sanitize_email($raw_email);

    // 3. Kiểm tra tính hợp lệ của định dạng email
    if (!is_email($email)) {
        wp_send_json_error(array(
            'message' => __('Vui lòng nhập địa chỉ email hợp lệ.', 'cms-nhomc'),
            'code'    => 'invalid_email',
        ), 400);
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'newsletter_subscribers';

    // Đảm bảo bảng tồn tại trước khi thao tác
    cms_nhomc_check_newsletter_table();

    // 4. Kiểm tra email đã đăng ký chưa (Duplicate Check)
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT id, status FROM {$table_name} WHERE email = %s LIMIT 1",
        $email
    ));

    $current_time = current_time('mysql');

    if ($existing) {
        // Nếu đã từng hủy đăng ký thì cho phép kích hoạt lại
        if ($existing->status === 'unsubscribed') {
            $updated = $wpdb->update(
                $table_name,
                array(
                    'status'     => 'active',
                    'updated_at' => $current_time,
                ),
                array('id' => $existing->id),
                array('%s', '%s'),
                array('%d')
            );
            if ($updated !== false) {
                wp_send_json_success(array(
                    'message' => __('Đăng ký nhận bản tin thành công! Chào mừng bạn quay trở lại.', 'cms-nhomc'),
                    'email'   => $email,
                ));
                return;
            }
        }

        // Email đã tồn tại và đang active
        wp_send_json_error(array(
            'message' => __('Email này đã được đăng ký trước đó.', 'cms-nhomc'),
            'code'    => 'duplicate_email',
        ), 409);
        return;
    }

    // 5. Lưu vào Cơ sở dữ liệu thật với $wpdb->insert
    $ip_address = cms_nhomc_get_client_ip();
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(substr($_SERVER['HTTP_USER_AGENT'], 0, 500)) : '';

    $inserted = $wpdb->insert(
        $table_name,
        array(
            'email'      => $email,
            'status'     => 'active',
            'created_at' => $current_time,
            'updated_at' => $current_time,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
        ),
        array('%s', '%s', '%s', '%s', '%s', '%s')
    );

    if ($inserted === false) {
        // Trường hợp race-condition bị chặn bởi UNIQUE key của MySQL
        if (strpos($wpdb->last_error, 'Duplicate') !== false) {
            wp_send_json_error(array(
                'message' => __('Email này đã được đăng ký trước đó.', 'cms-nhomc'),
                'code'    => 'duplicate_email',
            ), 409);
            return;
        }

        wp_send_json_error(array(
            'message' => __('Không thể hoàn tất đăng ký. Vui lòng thử lại sau.', 'cms-nhomc'),
            'code'    => 'db_error',
        ), 500);
        return;
    }

    // 6. Trả về phản hồi thành công JSON chuẩn
    wp_send_json_success(array(
        'message' => __('Đăng ký nhận bản tin thành công!', 'cms-nhomc'),
        'email'   => $email,
    ));
    return;
}
add_action('wp_ajax_cms_nhomc_newsletter_subscribe', 'cms_nhomc_ajax_newsletter_subscribe');
add_action('wp_ajax_nopriv_cms_nhomc_newsletter_subscribe', 'cms_nhomc_ajax_newsletter_subscribe');

/**
 * 3. COMPONENT RENDER FRONTEND
 * Chuẩn Ponytail: Tách component độc lập, tái sử dụng cao, không trùng lặp code
 */

/**
 * Render Form Newsletter (dùng chung cho cả Section và Sidebar)
 */
function cms_nhomc_render_newsletter_form($args = array()) {
    $defaults = array(
        'id'          => 'newsletter-form-' . wp_unique_id(),
        'layout'      => 'full', // 'full' (homepage/section) hoặc 'sidebar'
        'title'       => __('NEWSLETTER', 'cms-nhomc'),
        'description' => __('Đăng ký nhận các bài viết và thông tin mới nhất từ website.', 'cms-nhomc'),
        'button_text' => __('Đăng ký', 'cms-nhomc'),
        'placeholder' => __('Nhập địa chỉ email...', 'cms-nhomc'),
    );
    $args = wp_parse_args($args, $defaults);

    $is_sidebar = ($args['layout'] === 'sidebar');
    $card_class = 'cms-newsletter-card' . ($is_sidebar ? ' cms-newsletter-card--sidebar' : ' cms-newsletter-card--full');
    $form_id    = esc_attr($args['id']);
    $input_id   = $form_id . '-email';
    $nonce      = wp_create_nonce('cms_nhomc_newsletter_nonce');
    ?>
    <section class="<?php echo esc_attr($card_class); ?>" aria-label="<?php echo esc_attr($args['title']); ?>">
        <div class="cms-newsletter-inner">
            <header class="cms-newsletter-header">
                <div class="cms-newsletter-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                </div>
                <div class="cms-newsletter-header-text">
                    <h3 class="cms-newsletter-title"><?php echo esc_html($args['title']); ?></h3>
                    <?php if (!empty($args['description'])) : ?>
                        <p class="cms-newsletter-desc"><?php echo esc_html($args['description']); ?></p>
                    <?php endif; ?>
                </div>
            </header>

            <form class="cms-newsletter-form" id="<?php echo $form_id; ?>" method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" novalidate>
                <input type="hidden" name="action" value="cms_nhomc_newsletter_subscribe" />
                <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>" />

                <div class="cms-newsletter-input-group">
                    <label for="<?php echo esc_attr($input_id); ?>" class="cms-newsletter-label screen-reader-text">
                        <?php esc_html_e('Địa chỉ email của bạn', 'cms-nhomc'); ?>
                    </label>
                    <div class="cms-newsletter-input-wrap">
                        <span class="cms-newsletter-input-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="4"></circle>
                                <path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94"></path>
                            </svg>
                        </span>
                        <input
                            type="email"
                            id="<?php echo esc_attr($input_id); ?>"
                            name="email"
                            class="cms-newsletter-input"
                            placeholder="<?php echo esc_attr($args['placeholder']); ?>"
                            autocomplete="email"
                            required
                            maxlength="191"
                            aria-required="true"
                        />
                    </div>
                    <button type="submit" class="cms-newsletter-btn" aria-label="<?php echo esc_attr($args['button_text']); ?>">
                        <span class="cms-newsletter-btn-text"><?php echo esc_html($args['button_text']); ?></span>
                        <span class="cms-newsletter-btn-spinner" aria-hidden="true" style="display: none;">
                            <svg class="spinner-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5">
                                <circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-linecap="round"></circle>
                            </svg>
                        </span>
                    </button>
                </div>

                <div class="cms-newsletter-message" role="status" aria-live="polite" style="display: none;"></div>
            </form>
        </div>
    </section>
    <?php
}

/**
 * Render Newsletter Section trên Trang chủ (nằm trước Footer)
 */
function cms_nhomc_render_newsletter_section() {
    ?>
    <div class="cms-newsletter-homepage-wrapper" id="section-newsletter">
        <div class="cms-container-layout">
            <?php
            cms_nhomc_render_newsletter_form(array(
                'layout'      => 'full',
                'title'       => __('NEWSLETTER', 'cms-nhomc'),
                'description' => __('Đăng ký nhận các bài viết và thông tin mới nhất từ website.', 'cms-nhomc'),
                'button_text' => __('Đăng ký', 'cms-nhomc'),
                'placeholder' => __('Nhập địa chỉ email của bạn...', 'cms-nhomc'),
            ));
            ?>
        </div>
    </div>
    <?php
}

/**
 * Render Newsletter Widget trong Sidebar
 */
function cms_nhomc_render_newsletter_widget($title = 'NEWSLETTER') {
    ?>
    <div class="widget-categories-card widget-newsletter-card">
        <div class="widget-cat-body" style="padding: 0;">
            <?php
            cms_nhomc_render_newsletter_form(array(
                'layout'      => 'sidebar',
                'title'       => !empty($title) ? $title : __('NEWSLETTER', 'cms-nhomc'),
                'description' => __('Đăng ký nhận bài viết mới nhất.', 'cms-nhomc'),
                'button_text' => __('Đăng ký', 'cms-nhomc'),
                'placeholder' => __('Nhập email...', 'cms-nhomc'),
            ));
            ?>
        </div>
    </div>
    <?php
}

/**
 * Shortcode [cms_nhomc_newsletter layout="full|sidebar" title="..." description="..."]
 */
function cms_nhomc_newsletter_shortcode($atts) {
    $atts = shortcode_atts(array(
        'layout'      => 'full',
        'title'       => __('NEWSLETTER', 'cms-nhomc'),
        'description' => __('Đăng ký nhận các bài viết và thông tin mới nhất từ website.', 'cms-nhomc'),
        'button_text' => __('Đăng ký', 'cms-nhomc'),
        'placeholder' => __('Nhập địa chỉ email...', 'cms-nhomc'),
    ), $atts, 'cms_nhomc_newsletter');

    ob_start();
    cms_nhomc_render_newsletter_form($atts);
    return ob_get_clean();
}
add_shortcode('cms_nhomc_newsletter', 'cms_nhomc_newsletter_shortcode');

/**
 * 4. ĐĂNG KÝ WIDGET WORDPRESS CHUẨN
 */
class CMS_NhomC_Newsletter_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'cms_nhomc_newsletter_widget',
            __('CMS Nhóm C: Newsletter (Đăng ký nhận tin)', 'cms-nhomc'),
            array(
                'description' => __('Form đăng ký nhận bản tin qua email chuẩn Ponytail cho Sidebar', 'cms-nhomc'),
                'customize_selective_refresh' => true,
            )
        );
    }

    public function widget($args, $instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('NEWSLETTER', 'cms-nhomc');
        $desc  = !empty($instance['desc']) ? $instance['desc'] : __('Đăng ký nhận bài viết mới nhất.', 'cms-nhomc');

        echo $args['before_widget'];
        cms_nhomc_render_newsletter_form(array(
            'layout'      => 'sidebar',
            'title'       => $title,
            'description' => $desc,
        ));
        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('NEWSLETTER', 'cms-nhomc');
        $desc  = !empty($instance['desc']) ? $instance['desc'] : __('Đăng ký nhận bài viết mới nhất.', 'cms-nhomc');
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Tiêu đề:', 'cms-nhomc'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('desc')); ?>"><?php esc_html_e('Mô tả ngắn:', 'cms-nhomc'); ?></label>
            <textarea class="widefat" id="<?php echo esc_attr($this->get_field_id('desc')); ?>" name="<?php echo esc_attr($this->get_field_name('desc')); ?>" rows="3"><?php echo esc_textarea($desc); ?></textarea>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['desc']  = (!empty($new_instance['desc'])) ? sanitize_text_field($new_instance['desc']) : '';
        return $instance;
    }
}

function cms_nhomc_register_newsletter_widget() {
    register_widget('CMS_NhomC_Newsletter_Widget');
}
add_action('widgets_init', 'cms_nhomc_register_newsletter_widget');

/**
 * 5. WORDPRESS ADMIN MANAGEMENT (Dashboard -> Newsletter -> Subscribers)
 * Tính năng: Danh sách thật, Thống kê thật, Tìm kiếm, Lọc trạng thái, Phân trang, Xóa, Đổi trạng thái, Xuất CSV
 */
function cms_nhomc_newsletter_admin_menu() {
    add_menu_page(
        __('Newsletter', 'cms-nhomc'),
        __('Newsletter', 'cms-nhomc'),
        'manage_options',
        'cms-nhomc-newsletter',
        'cms_nhomc_render_newsletter_admin_page',
        'dashicons-email-alt',
        26
    );

    add_submenu_page(
        'cms-nhomc-newsletter',
        __('Danh sách người đăng ký', 'cms-nhomc'),
        __('Subscribers', 'cms-nhomc'),
        'manage_options',
        'cms-nhomc-newsletter',
        'cms_nhomc_render_newsletter_admin_page'
    );
}
add_action('admin_menu', 'cms_nhomc_newsletter_admin_menu');

/**
 * Xử lý Xuất file CSV (Admin Export)
 */
function cms_nhomc_handle_newsletter_export_csv() {
    if (!isset($_GET['action']) || $_GET['action'] !== 'cms_nhomc_export_subscribers') {
        return;
    }

    if (!current_user_can('manage_options')) {
        wp_die(__('Bạn không có quyền thực hiện thao tác này.', 'cms-nhomc'), 403);
    }

    check_admin_referer('cms_nhomc_export_csv');

    global $wpdb;
    $table_name = $wpdb->prefix . 'newsletter_subscribers';
    cms_nhomc_check_newsletter_table();

    $subscribers = $wpdb->get_results("SELECT id, email, status, created_at, updated_at, ip_address FROM {$table_name} ORDER BY id DESC", ARRAY_A);

    $filename = 'subscribers-' . date('Y-m-d_H-i-s') . '.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Thêm BOM UTF-8 để Excel đọc tiếng Việt không bị lỗi font
    echo "\xEF\xBB\xBF";

    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Email', 'Trạng thái', 'Ngày đăng ký', 'Cập nhật lần cuối', 'Địa chỉ IP'));

    if (!empty($subscribers)) {
        foreach ($subscribers as $row) {
            fputcsv($output, array(
                $row['id'],
                $row['email'],
                ($row['status'] === 'active') ? 'Đang hoạt động' : 'Đã hủy',
                $row['created_at'],
                $row['updated_at'],
                $row['ip_address'],
            ));
        }
    }

    fclose($output);
    exit;
}
add_action('admin_init', 'cms_nhomc_handle_newsletter_export_csv');

/**
 * Trang quản trị Newsletter Admin Page
 */
function cms_nhomc_render_newsletter_admin_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Bạn không có quyền truy cập trang này.', 'cms-nhomc'));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'newsletter_subscribers';
    cms_nhomc_check_newsletter_table();

    $notice_message = '';
    $notice_type    = 'success';

    // Xử lý hành động XÓA hoặc ĐỔI TRẠNG THÁI
    if (isset($_GET['do_action']) && isset($_GET['subscriber_id'])) {
        $action_name   = sanitize_text_field($_GET['do_action']);
        $subscriber_id = intval($_GET['subscriber_id']);

        if (check_admin_referer('cms_nhomc_subscriber_action_' . $subscriber_id)) {
            if ($action_name === 'delete') {
                $deleted = $wpdb->delete($table_name, array('id' => $subscriber_id), array('%d'));
                if ($deleted) {
                    $notice_message = __('Đã xóa người đăng ký thành công.', 'cms-nhomc');
                } else {
                    $notice_message = __('Không thể xóa người đăng ký.', 'cms-nhomc');
                    $notice_type    = 'error';
                }
            } elseif ($action_name === 'toggle_status') {
                $current_status = sanitize_text_field($_GET['current_status']);
                $new_status = ($current_status === 'active') ? 'unsubscribed' : 'active';
                $updated = $wpdb->update(
                    $table_name,
                    array('status' => $new_status, 'updated_at' => current_time('mysql')),
                    array('id' => $subscriber_id),
                    array('%s', '%s'),
                    array('%d')
                );
                if ($updated !== false) {
                    $notice_message = __('Đã cập nhật trạng thái người đăng ký thành công.', 'cms-nhomc');
                } else {
                    $notice_message = __('Không thể cập nhật trạng thái.', 'cms-nhomc');
                    $notice_type    = 'error';
                }
            }
        }
    }

    // Tham số tìm kiếm và lọc
    $search_query   = isset($_GET['s']) ? sanitize_text_field(trim($_GET['s'])) : '';
    $status_filter  = isset($_GET['status_filter']) ? sanitize_text_field($_GET['status_filter']) : '';

    // Thống kê số liệu thật từ Database
    $total_all          = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
    $total_active       = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE status = 'active'");
    $total_unsubscribed = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE status = 'unsubscribed'");

    // Xây dựng câu truy vấn có điều kiện lọc & tìm kiếm
    $where_clauses = array('1=1');
    $query_params  = array();

    if (!empty($search_query)) {
        $where_clauses[] = 'email LIKE %s';
        $query_params[]  = '%' . $wpdb->esc_like($search_query) . '%';
    }

    if (!empty($status_filter) && in_array($status_filter, array('active', 'unsubscribed'), true)) {
        $where_clauses[] = 'status = %s';
        $query_params[]  = $status_filter;
    }

    $where_sql = implode(' AND ', $where_clauses);

    // Phân trang chuẩn WordPress Admin
    $per_page     = 20;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset       = ($current_page - 1) * $per_page;

    if (!empty($query_params)) {
        $count_sql = $wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE {$where_sql}", $query_params);
        $total_filtered = (int) $wpdb->get_var($count_sql);

        $data_sql = $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE {$where_sql} ORDER BY id DESC LIMIT %d OFFSET %d",
            array_merge($query_params, array($per_page, $offset))
        );
        $subscribers = $wpdb->get_results($data_sql);
    } else {
        $total_filtered = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE {$where_sql}");
        $subscribers = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE {$where_sql} ORDER BY id DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ));
    }

    $total_pages = ceil($total_filtered / $per_page);
    $export_url  = wp_nonce_url(admin_url('admin.php?action=cms_nhomc_export_subscribers'), 'cms_nhomc_export_csv');
    ?>
    <div class="wrap cms-newsletter-admin-wrap">
        <h1 class="wp-heading-inline">
            <span class="dashicons dashicons-email-alt" style="font-size: 30px; width: 30px; height: 30px; vertical-align: middle; margin-right: 6px;"></span>
            <?php esc_html_e('Quản lý Người đăng ký nhận bản tin (Newsletter Subscribers)', 'cms-nhomc'); ?>
        </h1>

        <a href="<?php echo esc_url($export_url); ?>" class="page-title-action button-primary" style="margin-left: 12px;">
            <span class="dashicons dashicons-download" style="vertical-align: middle; margin-top: -2px;"></span>
            <?php esc_html_e('Xuất danh sách (CSV)', 'cms-nhomc'); ?>
        </a>

        <hr class="wp-header-end">

        <?php if (!empty($notice_message)) : ?>
            <div class="notice notice-<?php echo esc_attr($notice_type); ?> is-dismissible">
                <p><?php echo esc_html($notice_message); ?></p>
            </div>
        <?php endif; ?>

        <!-- Thẻ thống kê nhanh -->
        <div style="display: flex; gap: 16px; margin: 20px 0 24px;">
            <div style="background: #fff; border: 1px solid #ccd0d4; border-left: 4px solid #2271b1; padding: 14px 20px; border-radius: 4px; flex: 1;">
                <div style="font-size: 13px; color: #646970; text-transform: uppercase; font-weight: 600;"><?php esc_html_e('Tổng người đăng ký', 'cms-nhomc'); ?></div>
                <div style="font-size: 26px; font-weight: 700; color: #1d2327; margin-top: 4px;"><?php echo number_format_i18n($total_all); ?></div>
            </div>
            <div style="background: #fff; border: 1px solid #ccd0d4; border-left: 4px solid #00a32a; padding: 14px 20px; border-radius: 4px; flex: 1;">
                <div style="font-size: 13px; color: #646970; text-transform: uppercase; font-weight: 600;"><?php esc_html_e('Đang hoạt động', 'cms-nhomc'); ?></div>
                <div style="font-size: 26px; font-weight: 700; color: #00a32a; margin-top: 4px;"><?php echo number_format_i18n($total_active); ?></div>
            </div>
            <div style="background: #fff; border: 1px solid #ccd0d4; border-left: 4px solid #d63638; padding: 14px 20px; border-radius: 4px; flex: 1;">
                <div style="font-size: 13px; color: #646970; text-transform: uppercase; font-weight: 600;"><?php esc_html_e('Đã hủy đăng ký', 'cms-nhomc'); ?></div>
                <div style="font-size: 26px; font-weight: 700; color: #d63638; margin-top: 4px;"><?php echo number_format_i18n($total_unsubscribed); ?></div>
            </div>
        </div>

        <!-- Bộ lọc và tìm kiếm -->
        <div class="tablenav top" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 12px;">
            <ul class="subsubsub" style="margin: 0;">
                <li>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=cms-nhomc-newsletter')); ?>" class="<?php echo empty($status_filter) ? 'current' : ''; ?>">
                        <?php esc_html_e('Tất cả', 'cms-nhomc'); ?> <span class="count">(<?php echo $total_all; ?>)</span>
                    </a> |
                </li>
                <li>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=cms-nhomc-newsletter&status_filter=active')); ?>" class="<?php echo ($status_filter === 'active') ? 'current' : ''; ?>">
                        <?php esc_html_e('Đang hoạt động', 'cms-nhomc'); ?> <span class="count">(<?php echo $total_active; ?>)</span>
                    </a> |
                </li>
                <li>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=cms-nhomc-newsletter&status_filter=unsubscribed')); ?>" class="<?php echo ($status_filter === 'unsubscribed') ? 'current' : ''; ?>">
                        <?php esc_html_e('Đã hủy', 'cms-nhomc'); ?> <span class="count">(<?php echo $total_unsubscribed; ?>)</span>
                    </a>
                </li>
            </ul>

            <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="display: flex; gap: 6px;">
                <input type="hidden" name="page" value="cms-nhomc-newsletter" />
                <?php if (!empty($status_filter)) : ?>
                    <input type="hidden" name="status_filter" value="<?php echo esc_attr($status_filter); ?>" />
                <?php endif; ?>
                <input type="search" name="s" value="<?php echo esc_attr($search_query); ?>" placeholder="<?php esc_attr_e('Tìm theo email...', 'cms-nhomc'); ?>" />
                <button type="submit" class="button"><?php esc_html_e('Tìm kiếm', 'cms-nhomc'); ?></button>
                <?php if (!empty($search_query)) : ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=cms-nhomc-newsletter')); ?>" class="button"><?php esc_html_e('Xóa tìm kiếm', 'cms-nhomc'); ?></a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Bảng hiển thị danh sách người đăng ký thật -->
        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr>
                    <th scope="col" style="width: 60px;"><strong><?php esc_html_e('ID', 'cms-nhomc'); ?></strong></th>
                    <th scope="col" style="width: 280px;"><strong><?php esc_html_e('Email', 'cms-nhomc'); ?></strong></th>
                    <th scope="col" style="width: 140px;"><strong><?php esc_html_e('Trạng thái', 'cms-nhomc'); ?></strong></th>
                    <th scope="col" style="width: 170px;"><strong><?php esc_html_e('Ngày đăng ký', 'cms-nhomc'); ?></strong></th>
                    <th scope="col" style="width: 140px;"><strong><?php esc_html_e('Địa chỉ IP', 'cms-nhomc'); ?></strong></th>
                    <th scope="col" style="width: 180px;"><strong><?php esc_html_e('Hành động', 'cms-nhomc'); ?></strong></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($subscribers)) : ?>
                    <?php foreach ($subscribers as $row) : 
                        $toggle_url = wp_nonce_url(
                            admin_url('admin.php?page=cms-nhomc-newsletter&do_action=toggle_status&subscriber_id=' . $row->id . '&current_status=' . $row->status . (!empty($status_filter) ? '&status_filter=' . $status_filter : '') . (!empty($search_query) ? '&s=' . urlencode($search_query) : '')),
                            'cms_nhomc_subscriber_action_' . $row->id
                        );
                        $delete_url = wp_nonce_url(
                            admin_url('admin.php?page=cms-nhomc-newsletter&do_action=delete&subscriber_id=' . $row->id . (!empty($status_filter) ? '&status_filter=' . $status_filter : '') . (!empty($search_query) ? '&s=' . urlencode($search_query) : '')),
                            'cms_nhomc_subscriber_action_' . $row->id
                        );
                    ?>
                        <tr>
                            <td><?php echo esc_html($row->id); ?></td>
                            <td>
                                <strong><a href="mailto:<?php echo esc_attr($row->email); ?>"><?php echo esc_html($row->email); ?></a></strong>
                            </td>
                            <td>
                                <?php if ($row->status === 'active') : ?>
                                    <span style="background: #e7f7ed; color: #008a20; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; display: inline-block;">
                                        &#9679; <?php esc_html_e('Hoạt động', 'cms-nhomc'); ?>
                                    </span>
                                <?php else : ?>
                                    <span style="background: #fcf0f1; color: #d63638; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; display: inline-block;">
                                        &#9679; <?php esc_html_e('Đã hủy', 'cms-nhomc'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html(mysql2date('d/m/Y H:i', $row->created_at)); ?></td>
                            <td><code><?php echo esc_html(!empty($row->ip_address) ? $row->ip_address : '—'); ?></code></td>
                            <td>
                                <a href="<?php echo esc_url($toggle_url); ?>" class="button button-small" style="margin-right: 4px;">
                                    <?php echo ($row->status === 'active') ? esc_html__('Hủy kích hoạt', 'cms-nhomc') : esc_html__('Kích hoạt lại', 'cms-nhomc'); ?>
                                </a>
                                <a href="<?php echo esc_url($delete_url); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php echo esc_js(__('Bạn có chắc chắn muốn xóa người đăng ký này?', 'cms-nhomc')); ?>');">
                                    <?php esc_html_e('Xóa', 'cms-nhomc'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 24px; color: #646970;">
                            <?php if (!empty($search_query)) : ?>
                                <?php printf(esc_html__('Không tìm thấy người đăng ký nào với từ khóa "%s".', 'cms-nhomc'), esc_html($search_query)); ?>
                            <?php else : ?>
                                <?php esc_html_e('Chưa có người đăng ký nào trong danh sách.', 'cms-nhomc'); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Phân trang -->
        <?php if ($total_pages > 1) : ?>
            <div class="tablenav bottom" style="margin-top: 16px;">
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php printf(esc_html__('%s người đăng ký', 'cms-nhomc'), number_format_i18n($total_filtered)); ?></span>
                    <?php
                    echo paginate_links(array(
                        'base'      => add_query_arg('paged', '%#%'),
                        'format'    => '',
                        'prev_text' => __('&laquo; Trước', 'cms-nhomc'),
                        'next_text' => __('Sau &raquo;', 'cms-nhomc'),
                        'total'     => $total_pages,
                        'current'   => $current_page,
                    ));
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

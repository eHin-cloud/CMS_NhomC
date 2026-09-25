<?php
/**
 * Widget Test 4: VietNamNet Pre-Footer Info & Contact Widget
 * 
 * Yêu cầu:
 * 1) Widget có tên: widget_test_4
 * 2) Hiển thị widget_test_4 tại trang chủ, trang danh sách, trang chi tiết; Khu vực: phía trên Footer
 * 3) Cấu trúc HTML & CSS chuẩn chỉnh 100% y hệt báo điện tử VietNamNet (https://vietnamnet.vn/)
 *
 * @package CMS_NhomC
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Lớp Widget widget_test_4 mở rộng từ WP_Widget chuẩn của WordPress
 */
class widget_test_4 extends WP_Widget {

    /**
     * Khởi tạo widget với Base ID và Tên chuẩn xác: widget_test_4
     */
    public function __construct() {
        parent::__construct(
            'widget_test_4',
            'widget_test_4',
            array(
                'description'                 => __('Widget hiển thị thông tin toà soạn và liên hệ VietNamNet (widget_test_4)', 'cms-nhomc'),
                'customize_selective_refresh' => true,
            )
        );
    }

    /**
     * Dữ liệu mặc định chuẩn chỉnh 100% khớp từng chữ theo HTML VietNamNet
     */
    public static function get_default_data() {
        return array(
            'agency'       => 'Bộ Dân tộc và Tôn giáo',
            'license'      => '146/GP-BVHTTDL',
            'license_date' => '17/10/2025',
            'editor'       => 'Nguyễn Văn Bá',
            'address'      => 'Tầng 18, Toà nhà Cục Viễn thông (VNTA), 68 Dương Đình Nghệ, phường Cầu Giấy, TP. Hà Nội.',
            'phone'        => '02439369898',
            'hotline'      => '0923457788',
            'ads_hn'       => '0919405885',
            'ads_hcm'      => '0919435885',
            'email'        => 'vietnamnet@vietnamnet.vn',
            'ads_email'    => 'contact@vietnamnet.vn',
            'tech_email'   => 'support@tech.vietnamnet.vn',
            'price_url'    => 'http://vads.vn',
            'contact_url'  => 'https://vietnamnet.vn/thong-tin-toa-soan',
            'ads_url'      => 'https://vads.vn/#vnn_source=trangchu&vnn_medium=menu-bottom',
        );
    }

    /**
     * Bộ dữ liệu ngẫu nhiên hỗ trợ nếu quản trị viên muốn kích hoạt chế độ random giữa các sinh viên
     */
    public static function get_random_presets() {
        return array(
            'agencies' => array(
                'Bộ Dân tộc và Tôn giáo',
                'Bộ Thông tin và Truyền thông',
                'Bộ Văn hóa, Thể thao và Du lịch',
                'Bộ Khoa học và Công nghệ',
                'Viện Báo chí & Truyền thông Việt Nam',
                'Hội Nhà báo Việt Nam'
            ),
            'licenses' => array(
                '146/GP-BVHTTDL',
                '258/GP-BTTTT',
                '312/GP-BTTTT',
                '405/GP-BVHTTDL',
                '189/GP-BTTTT',
                '521/GP-BVHTTDL'
            ),
            'dates' => array(
                '17/10/2025',
                '08/04/2024',
                '15/09/2023',
                '22/11/2024',
                '05/01/2025',
                '19/08/2024'
            ),
            'editors' => array(
                'Nguyễn Văn Bá',
                'Trần Minh Quân',
                'Võ Xuân Hòa',
                'Lê Hải Đăng',
                'Phạm Quốc Tuấn',
                'Đặng Hoàng Nam'
            ),
            'addresses' => array(
                'Tầng 18, Toà nhà Cục Viễn thông (VNTA), 68 Dương Đình Nghệ, phường Cầu Giấy, TP. Hà Nội.',
                'Tòa nhà Báo điện tử VietNamNet, số 47 Phạm Văn Đồng, phường Cầu Giấy, TP. Hà Nội.',
                'Số 115 Trần Duy Hưng, phường Trung Hòa, quận Cầu Giấy, TP. Hà Nội.'
            ),
            'phones' => array(
                '02439369898',
                '02437739999',
                '02438259988'
            ),
            'hotlines' => array(
                '0923457788',
                '0908889966',
                '0912345678'
            ),
            'ads_hn' => array(
                '0919405885',
                '0918882233',
                '0915667788'
            ),
            'ads_hcm' => array(
                '0919435885',
                '0917778899',
                '0916334455'
            ),
        );
    }

    /**
     * Phân giải dữ liệu: Mặc định hiển thị chuẩn chỉnh 100% theo hình ảnh mẫu
     */
    public static function resolve_data($instance) {
        $defaults = self::get_default_data();

        // Kiểm tra xem có cấu hình random không
        $enable_random = !empty($instance['enable_random']);

        if ($enable_random) {
            $presets = self::get_random_presets();
            $seed_string = isset($_COOKIE['vnn_sv_seed']) ? $_COOKIE['vnn_sv_seed'] : '';
            if (empty($seed_string)) {
                $client_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
                $seed_string = md5($client_ip . get_current_user_id() . wp_salt('nonce'));
                if (!headers_sent()) {
                    @setcookie('vnn_sv_seed', $seed_string, time() + 86400, COOKIEPATH, COOKIE_DOMAIN);
                }
            }
            $seed_num = crc32($seed_string);
            mt_srand($seed_num);

            $defaults['agency']       = $presets['agencies'][$seed_num % count($presets['agencies'])];
            $defaults['license']      = $presets['licenses'][$seed_num % count($presets['licenses'])];
            $defaults['license_date'] = $presets['dates'][$seed_num % count($presets['dates'])];
            $defaults['editor']       = $presets['editors'][$seed_num % count($presets['editors'])];
            $defaults['address']      = $presets['addresses'][$seed_num % count($presets['addresses'])];
            $defaults['phone']        = $presets['phones'][$seed_num % count($presets['phones'])];
            $defaults['hotline']      = $presets['hotlines'][$seed_num % count($presets['hotlines'])];
            $defaults['ads_hn']       = $presets['ads_hn'][$seed_num % count($presets['ads_hn'])];
            $defaults['ads_hcm']      = $presets['ads_hcm'][$seed_num % count($presets['ads_hcm'])];

            mt_srand();
        }

        $agency       = !empty($instance['agency']) ? $instance['agency'] : $defaults['agency'];
        $license      = !empty($instance['license']) ? $instance['license'] : $defaults['license'];
        $license_date = !empty($instance['license_date']) ? $instance['license_date'] : $defaults['license_date'];
        $editor       = !empty($instance['editor']) ? $instance['editor'] : $defaults['editor'];
        $address      = !empty($instance['address']) ? $instance['address'] : $defaults['address'];
        $phone        = !empty($instance['phone']) ? $instance['phone'] : $defaults['phone'];
        $hotline      = !empty($instance['hotline']) ? $instance['hotline'] : $defaults['hotline'];
        $ads_hn       = !empty($instance['ads_hn']) ? $instance['ads_hn'] : $defaults['ads_hn'];
        $ads_hcm      = !empty($instance['ads_hcm']) ? $instance['ads_hcm'] : $defaults['ads_hcm'];
        $email        = !empty($instance['email']) ? $instance['email'] : $defaults['email'];
        $ads_email    = !empty($instance['ads_email']) ? $instance['ads_email'] : $defaults['ads_email'];
        $tech_email   = !empty($instance['tech_email']) ? $instance['tech_email'] : $defaults['tech_email'];
        $price_url    = !empty($instance['price_url']) ? $instance['price_url'] : $defaults['price_url'];
        $contact_url  = !empty($instance['contact_url']) ? $instance['contact_url'] : $defaults['contact_url'];
        $ads_url      = !empty($instance['ads_url']) ? $instance['ads_url'] : $defaults['ads_url'];

        return array(
            'agency'       => $agency,
            'license'      => $license,
            'license_date' => $license_date,
            'editor'       => $editor,
            'address'      => $address,
            'phone'        => $phone,
            'hotline'      => $hotline,
            'ads_hn'       => $ads_hn,
            'ads_hcm'      => $ads_hcm,
            'email'        => $email,
            'ads_email'    => $ads_email,
            'tech_email'   => $tech_email,
            'price_url'    => $price_url,
            'contact_url'  => $contact_url,
            'ads_url'      => $ads_url,
        );
    }

    /**
     * Render giao diện Widget ra Frontend (CẤU TRÚC HTML Y HỆT 100% CỦA VIETNAMNET.VN)
     */
    public function widget($args, $instance) {
        $data = self::resolve_data($instance);
        $theme_img_dir = get_template_directory_uri() . '/assets/images';

        echo isset($args['before_widget']) ? $args['before_widget'] : '';
        ?>
        <div class="footer__bottom" id="widget-test-4-root">
            <div class="footer__bottom-logo">
                <a href="<?php echo esc_url(home_url('/')); ?>" data-utm-source="#vnn_source=trangchu&amp;vnn_medium=logo_bottom">
                    <img src="<?php echo esc_url($theme_img_dir . '/logoVietnamNet.svg'); ?>" onerror="this.src='https://static.vnncdn.net/v1/logo/logoVietnamNet.svg'" alt="Tin tức VietNamNet" width="180" height="45">
                </a>
            </div>
            <div class="footer__bottom-address">
                <ul class="footer__bottom-list">
                    <li class="footer__bottom-item"> 
                        Cơ quan chủ quản: <?php echo esc_html($data['agency']); ?>
                    </li>
                    <li class="footer__bottom-item"> 
                        Số giấy phép: <?php echo esc_html($data['license']); ?>, cấp ngày <?php echo esc_html($data['license_date']); ?>
                    </li>
                    <li class="footer__bottom-item text-special"> 
                        Tổng biên tập: <?php echo esc_html($data['editor']); ?>
                    </li>
                    <li class="footer__bottom-item text-title"> 
                        <a class="footer__bottom-title" title="Liên hệ tòa soạn" href="<?php echo esc_url($data['contact_url']); ?>">Liên hệ tòa soạn</a>
                    </li>
                    <li class="footer__bottom-item"> 
                        Địa chỉ: <?php echo esc_html($data['address']); ?>
                    </li>
                    <li class="footer__bottom-item"> 
                        Điện thoại:  <b class="phone"><?php echo esc_html($data['phone']); ?></b>
                        - Hotline:  <b class="phone"><?php echo esc_html($data['hotline']); ?></b>
                    </li>
                    <li class="footer__bottom-item"> 
                        Email: <?php echo esc_html($data['email']); ?>
                    </li>
                    <li class="footer__bottom-item"> 
                        © 1997 Báo VietNamNet. All rights reserved.
                        Chỉ được phát hành lại thông tin từ website này khi có sự
                        đồng ý bằng văn bản của báo VietNamNet.
                    </li>
                </ul>
            </div>
            <div class="footer__gom">
                <div class="footer__bottom-contact">
                    <ul class="footer__bottom-list">
                        <li class="footer__bottom-item text-title"> 
                            <a class="footer__bottom-title" title="Liên hệ quảng cáo" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url($data['ads_url']); ?>">Liên hệ quảng cáo</a>
                        </li>
                        <li class="footer__bottom-item"> 
                            Công ty Cổ phần Truyền thông VietNamNet
                        </li>
                        <li class="footer__bottom-item">
                            Hotline: 
                            <span class="footer__bottom-item-phone"><?php echo esc_html($data['ads_hn']); ?> (Hà Nội)</span>
                            - <span class="footer__bottom-item-phone"><?php echo esc_html($data['ads_hcm']); ?> (Tp.HCM)</span>
                        </li>
                        <li class="footer__bottom-item">
                            Email: <?php echo esc_html($data['ads_email']); ?>
                        </li>
                        <li class="footer__bottom-item"> 
                            Báo giá: <a title="http://vads.vn" href="<?php echo esc_url($data['price_url']); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($data['price_url']); ?></a>
                        </li>
                        <li class="footer__bottom-item">
                            Hỗ trợ kỹ thuật: <?php echo esc_html($data['tech_email']); ?>
                        </li>
                    </ul>
                </div>
            
                <div class="footer__bottom-follow">
                    <ul class="footer__bottom-list footer__bottom-text">
                        <li class="footer__bottom-item text-center">
                            Theo dõi VietNamNet trên
                        </li>
                    </ul>
                    <ul class="footer__bottom-list footer__bottom-social">
                        <li class="footer__bottom-item">
                            <a title="VietNamNet Facebook" target="_blank" rel="noopener noreferrer" href="https://www.facebook.com/vietnamnet.vn">
                                <img alt="VietNamNet Facebook" src="<?php echo esc_url($theme_img_dir . '/facebook-black.svg'); ?>" onerror="this.src='https://static.vnncdn.net/v1/icon/facebook-black.svg'">
                            </a>
                        </li>
                        <li class="footer__bottom-item">
                            <a title="VietNamNet Youtube" target="_blank" rel="noopener noreferrer" href="https://www.youtube.com/c/B%C3%A1oVietNamNetTV">
                                <img alt="VietNamNet Youtube" src="<?php echo esc_url($theme_img_dir . '/youtube-black.svg'); ?>" onerror="this.src='https://static.vnncdn.net/v1/icon/youtube-black.svg'">
                            </a>
                        </li>
                        <li class="footer__bottom-item">
                            <a title="VietNamNet Tiktok" target="_blank" rel="noopener noreferrer" href="https://www.tiktok.com/@vietnamnet.vn">
                                <img alt="VietNamNet Tiktok" src="<?php echo esc_url($theme_img_dir . '/tiktok-black.svg'); ?>" onerror="this.src='https://static.vnncdn.net/v1/icon/tiktok-black.svg'">
                            </a>
                        </li>
                        <li class="footer__bottom-item">
                            <a title="VietNamNet Zalo" target="_blank" rel="noopener noreferrer" href="http://zalo.me/660139855964186242?src=qr">
                                <img alt="VietNamNet Zalo" src="<?php echo esc_url($theme_img_dir . '/zalo-black.svg'); ?>" onerror="this.src='https://static.vnncdn.net/v1/icon/zalo-black.svg'">
                            </a>
                        </li>
                    </ul>

                    <div class="footer__bottom-submit">
                        <a href="https://vietnamnet.vn/download-app" target="_blank" rel="noopener noreferrer">Tải ứng dụng</a>
                        <a href="https://vietnamnet.vn/doc-gia-gui-bai" target="_blank" rel="noopener noreferrer">Độc giả gửi bài</a>
                        <a href="https://vietnamnet.vn/tuyen-dung" target="_blank" rel="noopener noreferrer">Tuyển dụng</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
        echo isset($args['after_widget']) ? $args['after_widget'] : '';
    }

    /**
     * Form cấu hình Widget trong Admin Dashboard (Appearance > Widgets)
     */
    public function form($instance) {
        $defaults = self::get_default_data();
        $defaults['enable_random'] = 0;
        $instance = wp_parse_args((array) $instance, $defaults);
        ?>
        <div class="vnn-admin-widget-form">
            <p>
                <input class="checkbox" type="checkbox" <?php checked(!empty($instance['enable_random']), 1); ?> id="<?php echo esc_attr($this->get_field_id('enable_random')); ?>" name="<?php echo esc_attr($this->get_field_name('enable_random')); ?>" value="1" />
                <label for="<?php echo esc_attr($this->get_field_id('enable_random')); ?>">
                    <strong><?php esc_html_e('Kích hoạt Random thông tin (Mỗi SV hiển thị khác nhau)', 'cms-nhomc'); ?></strong>
                </label>
            </p>
            <p style="font-size: 12px; color: #64748b; margin-top: -5px;">
                <?php esc_html_e('Mặc định bỏ chọn sẽ hiển thị chuẩn chỉnh 100% theo đúng ảnh mẫu. Nếu tích chọn sẽ tự động sinh dữ liệu ngẫu nhiên cho từng sinh viên.', 'cms-nhomc'); ?>
            </p>

            <hr style="margin: 12px 0; border: none; border-top: 1px solid #e2e8f0;" />

            <p>
                <label for="<?php echo esc_attr($this->get_field_id('agency')); ?>"><?php esc_html_e('Cơ quan chủ quản:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('agency')); ?>" name="<?php echo esc_attr($this->get_field_name('agency')); ?>" type="text" value="<?php echo esc_attr($instance['agency']); ?>" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('license')); ?>"><?php esc_html_e('Số giấy phép:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('license')); ?>" name="<?php echo esc_attr($this->get_field_name('license')); ?>" type="text" value="<?php echo esc_attr($instance['license']); ?>" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('license_date')); ?>"><?php esc_html_e('Ngày cấp:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('license_date')); ?>" name="<?php echo esc_attr($this->get_field_name('license_date')); ?>" type="text" value="<?php echo esc_attr($instance['license_date']); ?>" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('editor')); ?>"><?php esc_html_e('Tổng biên tập:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('editor')); ?>" name="<?php echo esc_attr($this->get_field_name('editor')); ?>" type="text" value="<?php echo esc_attr($instance['editor']); ?>" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('address')); ?>"><?php esc_html_e('Địa chỉ tòa soạn:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('address')); ?>" name="<?php echo esc_attr($this->get_field_name('address')); ?>" type="text" value="<?php echo esc_attr($instance['address']); ?>" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('phone')); ?>"><?php esc_html_e('Điện thoại:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('phone')); ?>" name="<?php echo esc_attr($this->get_field_name('phone')); ?>" type="text" value="<?php echo esc_attr($instance['phone']); ?>" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('hotline')); ?>"><?php esc_html_e('Hotline toà soạn:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('hotline')); ?>" name="<?php echo esc_attr($this->get_field_name('hotline')); ?>" type="text" value="<?php echo esc_attr($instance['hotline']); ?>" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('ads_hn')); ?>"><?php esc_html_e('Hotline QC (Hà Nội):', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('ads_hn')); ?>" name="<?php echo esc_attr($this->get_field_name('ads_hn')); ?>" type="text" value="<?php echo esc_attr($instance['ads_hn']); ?>" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('ads_hcm')); ?>"><?php esc_html_e('Hotline QC (Tp.HCM):', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('ads_hcm')); ?>" name="<?php echo esc_attr($this->get_field_name('ads_hcm')); ?>" type="text" value="<?php echo esc_attr($instance['ads_hcm']); ?>" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('email')); ?>"><?php esc_html_e('Email toà soạn:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('email')); ?>" name="<?php echo esc_attr($this->get_field_name('email')); ?>" type="email" value="<?php echo esc_attr($instance['email']); ?>" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('ads_email')); ?>"><?php esc_html_e('Email quảng cáo:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('ads_email')); ?>" name="<?php echo esc_attr($this->get_field_name('ads_email')); ?>" type="email" value="<?php echo esc_attr($instance['ads_email']); ?>" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('tech_email')); ?>"><?php esc_html_e('Email kỹ thuật:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('tech_email')); ?>" name="<?php echo esc_attr($this->get_field_name('tech_email')); ?>" type="email" value="<?php echo esc_attr($instance['tech_email']); ?>" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('price_url')); ?>"><?php esc_html_e('Đường dẫn Báo giá:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('price_url')); ?>" name="<?php echo esc_attr($this->get_field_name('price_url')); ?>" type="text" value="<?php echo esc_attr($instance['price_url']); ?>" />
            </p>
        </div>
        <?php
    }

    /**
     * Lưu cấu hình khi người dùng sửa trong Admin
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['enable_random'] = !empty($new_instance['enable_random']) ? 1 : 0;
        $instance['agency']        = sanitize_text_field($new_instance['agency']);
        $instance['license']       = sanitize_text_field($new_instance['license']);
        $instance['license_date']  = sanitize_text_field($new_instance['license_date']);
        $instance['editor']        = sanitize_text_field($new_instance['editor']);
        $instance['address']       = sanitize_text_field($new_instance['address']);
        $instance['phone']         = sanitize_text_field($new_instance['phone']);
        $instance['hotline']       = sanitize_text_field($new_instance['hotline']);
        $instance['ads_hn']        = sanitize_text_field($new_instance['ads_hn']);
        $instance['ads_hcm']       = sanitize_text_field($new_instance['ads_hcm']);
        $instance['email']         = sanitize_email($new_instance['email']);
        $instance['ads_email']     = sanitize_email($new_instance['ads_email']);
        $instance['tech_email']    = sanitize_email($new_instance['tech_email']);
        $instance['price_url']     = esc_url_raw($new_instance['price_url']);
        return $instance;
    }
}

/**
 * Đăng ký Widget widget_test_4 với WordPress qua hook widgets_init
 */
function cms_nhomc_register_widget_test_4() {
    register_widget('widget_test_4');

    // Đăng ký Sidebar / Widget Area khu vực phía trên Footer
    register_sidebar(array(
        'name'          => __('Pre Footer Widget Area (widget_test_4)', 'cms-nhomc'),
        'id'            => 'pre_footer_widget_area',
        'description'   => __('Khu vực hiển thị widget phía trên Footer (Trang chủ, danh sách, chi tiết)', 'cms-nhomc'),
        'before_widget' => '<div id="%1$s" class="cms-pre-footer-widget-item %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="cms-pre-footer-widget-title" style="display:none;">',
        'after_title'   => '</h4>',
    ));
}
add_action('widgets_init', 'cms_nhomc_register_widget_test_4');

/**
 * Hàm chuyên dụng render widget_test_4 tại khu vực phía trên Footer
 */
function cms_nhomc_render_widget_test_4() {
    ?>
    <!-- KHU VỰC HIỂN THỊ WIDGET PHÍA TRÊN FOOTER (widget_test_4) -->
    <section class="cms-pre-footer-section" id="cms-pre-footer-widget-test-4" aria-label="Thông tin liên hệ toà soạn">
        <div class="cms-pre-footer-wrapper">
            <?php
            if (is_active_sidebar('pre_footer_widget_area')) {
                dynamic_sidebar('pre_footer_widget_area');
            } else {
                the_widget('widget_test_4');
            }
            ?>
        </div>
    </section>
    <?php
}

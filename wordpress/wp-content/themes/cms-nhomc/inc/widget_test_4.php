<?php
/**
 * Widget Test 4: VietNamNet Pre-Footer Info & Contact Widget
 * 
 * Yêu cầu:
 * 1) Widget có tên: widget_test_4
 * 2) Hiển thị widget_test_4 tại trang chủ, trang danh sách, trang chi tiết; Khu vực: phía trên Footer
 * 3) Giao diện hiển thị: giống chuẩn chỉnh 100% theo hình ảnh mẫu, giữ nguyên cấu trúc
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
     * Dữ liệu mặc định chuẩn chỉnh 100% khớp từng chữ theo hình ảnh mẫu
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
        );
    }

    /**
     * Render giao diện Widget ra Frontend (CẤU TRÚC CHUẨN CHỈNH 100% THEO ẢNH MẪU)
     */
    public function widget($args, $instance) {
        $data = self::resolve_data($instance);
        $logo_url = get_template_directory_uri() . '/assets/images/vietnamnet-logo.png';

        echo isset($args['before_widget']) ? $args['before_widget'] : '';
        ?>
        <div class="vnn-widget-container" id="vnn-widget-test-4">
            <div class="vnn-widget-inner">
                
                <!-- CỘT 1 (BÊN TRÁI): LOGO, TOÀ SOÀN & PHÁP LÝ -->
                <div class="vnn-col vnn-col-editorial">
                    <!-- Khối trên: Logo nằm bên trái, 3 dòng Cơ quan / Giấy phép / TBT nằm bên phải logo -->
                    <div class="vnn-editorial-top-block">
                        <div class="vnn-logo-column">
                            <a href="<?php echo esc_url(home_url('/')); ?>" class="vnn-logo-link" title="Báo VietNamNet">
                                <img src="<?php echo esc_url($logo_url); ?>" alt="VietNamNet" class="vnn-logo-img" />
                            </a>
                            <div class="vnn-logo-subtext">VIETNAMNET.VN</div>
                        </div>

                        <div class="vnn-editorial-summary">
                            <div class="vnn-text-row">
                                <span class="vnn-lbl">Cơ quan chủ quản:</span> 
                                <span class="vnn-val"><?php echo esc_html($data['agency']); ?></span>
                            </div>
                            <div class="vnn-text-row">
                                <span class="vnn-lbl">Số giấy phép:</span> 
                                <span class="vnn-val"><?php echo esc_html($data['license']); ?></span>, 
                                <span class="vnn-sub-lbl">cấp ngày</span> 
                                <span class="vnn-val"><?php echo esc_html($data['license_date']); ?></span>
                            </div>
                            <div class="vnn-text-row vnn-row-editor">
                                <span class="vnn-lbl-editor">Tổng biên tập:</span> 
                                <strong class="vnn-val-editor"><?php echo esc_html($data['editor']); ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Nút Liên hệ toà soạn (nằm bên dưới khối trên) -->
                    <div class="vnn-action-wrap">
                        <a href="mailto:<?php echo esc_attr($data['email']); ?>" class="vnn-btn-card">Liên hệ tòa soạn</a>
                    </div>

                    <!-- Khối thông tin địa chỉ, liên hệ và bản quyền bên dưới -->
                    <div class="vnn-editorial-bottom-block">
                        <p class="vnn-text-row vnn-address-row">
                            <span class="vnn-lbl">Địa chỉ:</span> 
                            <span class="vnn-val"><?php echo esc_html($data['address']); ?></span>
                        </p>
                        <p class="vnn-text-row">
                            <span class="vnn-lbl">Điện thoại:</span> 
                            <strong><?php echo esc_html($data['phone']); ?></strong> - 
                            <span class="vnn-lbl">Hotline:</span> 
                            <strong><?php echo esc_html($data['hotline']); ?></strong>
                        </p>
                        <p class="vnn-text-row">
                            <span class="vnn-lbl">Email:</span> 
                            <a href="mailto:<?php echo esc_attr($data['email']); ?>" class="vnn-email-link"><?php echo esc_html($data['email']); ?></a>
                        </p>
                        <p class="vnn-text-row vnn-copyright-text">
                            &copy; 1997 Báo VietNamNet. All rights reserved. Chỉ được phát hành lại thông tin từ website này khi có sự đồng ý bằng văn bản của báo VietNamNet.
                        </p>
                    </div>
                </div>

                <!-- CỘT 2 (Ở GIỮA): QUẢNG CÁO & TRUYỀN THÔNG -->
                <div class="vnn-col vnn-col-commercial">
                    <!-- Nút Liên hệ quảng cáo (nằm trên cùng của cột 2) -->
                    <div class="vnn-action-wrap vnn-action-ads">
                        <a href="mailto:<?php echo esc_attr($data['ads_email']); ?>" class="vnn-btn-card">Liên hệ quảng cáo</a>
                    </div>

                    <div class="vnn-commercial-details">
                        <p class="vnn-text-row vnn-company-title">
                            Công ty Cổ phần Truyền thông VietNamNet
                        </p>
                        <p class="vnn-text-row">
                            <span class="vnn-lbl">Hotline:</span> 
                            <strong><?php echo esc_html($data['ads_hn']); ?></strong> (Hà Nội) - 
                            <strong><?php echo esc_html($data['ads_hcm']); ?></strong> (Tp.HCM)
                        </p>
                        <p class="vnn-text-row">
                            <span class="vnn-lbl">Email:</span> 
                            <a href="mailto:<?php echo esc_attr($data['ads_email']); ?>" class="vnn-email-link"><?php echo esc_html($data['ads_email']); ?></a>
                        </p>
                        <p class="vnn-text-row">
                            <span class="vnn-lbl">Báo giá:</span> 
                            <a href="<?php echo esc_url($data['price_url']); ?>" target="_blank" rel="noopener noreferrer" class="vnn-price-link">
                                <strong><?php echo esc_html($data['price_url']); ?></strong>
                            </a>
                        </p>
                        <p class="vnn-text-row">
                            <span class="vnn-lbl">Hỗ trợ kỹ thuật:</span> 
                            <a href="mailto:<?php echo esc_attr($data['tech_email']); ?>" class="vnn-email-link"><?php echo esc_html($data['tech_email']); ?></a>
                        </p>
                    </div>
                </div>

                <!-- CỘT 3 (BÊN PHẢI): THEO DÕI MẠNG XÃ HỘI & TIỆN ÍCH -->
                <div class="vnn-col vnn-col-social">
                    <div class="vnn-social-header-text">Theo dõi VietNamNet trên</div>

                    <!-- 4 Icon mạng xã hội tròn đen chuẩn xác theo ảnh -->
                    <div class="vnn-social-circle-group">
                        <!-- Facebook -->
                        <a href="https://facebook.com/vietnamnet" target="_blank" rel="noopener noreferrer" class="vnn-circle-btn vnn-fb" title="Facebook">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>

                        <!-- YouTube -->
                        <a href="https://youtube.com/vietnamnet" target="_blank" rel="noopener noreferrer" class="vnn-circle-btn vnn-yt" title="YouTube">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                            </svg>
                        </a>

                        <!-- TikTok -->
                        <a href="https://tiktok.com/@vietnamnet" target="_blank" rel="noopener noreferrer" class="vnn-circle-btn vnn-tiktok" title="TikTok">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                                <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-1.01-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                            </svg>
                        </a>

                        <!-- Zalo -->
                        <a href="https://zalo.me/vietnamnet" target="_blank" rel="noopener noreferrer" class="vnn-circle-btn vnn-zalo" title="Zalo">
                            <span class="vnn-zalo-caption">Zalo</span>
                        </a>
                    </div>

                    <!-- 2 Dòng tiện ích màu xanh đậm -->
                    <div class="vnn-links-vertical">
                        <div class="vnn-links-row">
                            <a href="#" class="vnn-action-link">Tải ứng dụng</a> 
                            <span class="vnn-sep-bar">|</span> 
                            <a href="#" class="vnn-action-link">Độc giả gửi bài</a>
                        </div>
                        <div class="vnn-links-row vnn-row-career">
                            <a href="#" class="vnn-action-link">Tuyển dụng</a>
                        </div>
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

<?php
/**
 * Widget Test 4: VietNamNet Pre-Footer Info & Contact Widget
 * 
 * Yêu cầu:
 * 1) Widget có tên: widget_test_4
 * 2) Hiển thị widget_test_4 tại trang chủ, trang danh sách, trang chi tiết; Khu vực: phía trên Footer
 * 3) Giao diện hiển thị: theo như hình mẫu; random, không SV nào giống nhau
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
     * Dữ liệu mẫu phong phú dùng để random tự động đảm bảo không sinh viên nào giống nhau
     */
    public static function get_random_presets() {
        return array(
            'agencies' => array(
                'Bộ Dân tộc và Tôn giáo',
                'Bộ Thông tin và Truyền thông',
                'Bộ Văn hóa, Thể thao và Du lịch',
                'Bộ Khoa học và Công nghệ',
                'Viện Hàn lâm Khoa học và Công nghệ Việt Nam',
                'Hiệp hội Truyền thông & Báo chí Việt Nam',
                'Cục Phát thanh, Truyền hình và Thông tin điện tử',
                'Hội Nhà báo Việt Nam'
            ),
            'licenses' => array(
                '146/GP-BVHTTDL',
                '258/GP-BTTTT',
                '312/GP-BTTTT',
                '405/GP-BVHTTDL',
                '189/GP-BTTTT',
                '521/GP-BVHTTDL',
                '098/GP-BTTTT',
                '437/GP-BVHTTDL'
            ),
            'dates' => array(
                '17/10/2025',
                '08/04/2024',
                '15/09/2023',
                '22/11/2024',
                '05/01/2025',
                '19/08/2024',
                '12/03/2025',
                '26/06/2024'
            ),
            'editors' => array(
                'Nguyễn Văn Bá',
                'Trần Minh Quân',
                'Võ Xuân Hòa',
                'Lê Hải Đăng',
                'Phạm Quốc Tuấn',
                'Đặng Hoàng Nam',
                'Vũ Đình Trọng',
                'Bùi Quang Huy'
            ),
            'addresses' => array(
                'Tầng 18, Toà nhà Cục Viễn thông (VNTA), 68 Dương Đình Nghệ, phường Cầu Giấy, TP. Hà Nội.',
                'Tòa nhà Báo điện tử VietNamNet, số 47 Phạm Văn Đồng, phường Cầu Giấy, TP. Hà Nội.',
                'Số 115 Trần Duy Hưng, phường Trung Hòa, quận Cầu Giấy, TP. Hà Nội.',
                'Tầng 8, Tòa nhà Thông tấn xã Việt Nam, số 216 Nguyễn Đình Chiểu, Quận 3, TP. Hồ Chí Minh.',
                'Số 32 Huỳnh Thúc Kháng, phường Láng Hạ, quận Đống Đa, TP. Hà Nội.'
            ),
            'phones' => array(
                '02439369898',
                '02437739999',
                '02438259988',
                '02439876543',
                '02437654321'
            ),
            'hotlines' => array(
                '0923457788',
                '0908889966',
                '0912345678',
                '0934567890',
                '0987654321',
                '0966554433'
            ),
            'ads_hn' => array(
                '0919405885',
                '0918882233',
                '0915667788',
                '0914556677',
                '0912112233'
            ),
            'ads_hcm' => array(
                '0919435885',
                '0917778899',
                '0916334455',
                '0913998877',
                '0918332211'
            ),
            'emails' => array(
                'vietnamnet@vietnamnet.vn',
                'toasoan@vietnamnet.vn',
                'contact@vietnamnet.vn'
            ),
            'ads_emails' => array(
                'contact@vietnamnet.vn',
                'quangcao@vietnamnet.vn',
                'ads@vietnamnet.vn'
            ),
            'tech_emails' => array(
                'support@tech.vietnamnet.vn',
                'kythuat@vietnamnet.vn',
                'tech@vietnamnet.vn'
            )
        );
    }

    /**
     * Sinh dữ liệu ngẫu nhiên cho một sinh viên / phiên duyệt web
     */
    public static function resolve_data($instance) {
        $presets = self::get_random_presets();

        // Sử dụng một seed cố định theo IP hoặc Cookie để trong 1 phiên của sinh viên không bị nhảy lung tung khi chuyển trang,
        // nhưng giữa các máy SV khác nhau (hoặc khi bật random mỗi lần) sẽ sinh ngẫu nhiên khác nhau.
        $seed_string = isset($_COOKIE['vnn_sv_seed']) ? $_COOKIE['vnn_sv_seed'] : '';
        if (empty($seed_string)) {
            $client_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
            $seed_string = md5($client_ip . get_current_user_id() . wp_salt('nonce'));
            if (!headers_sent()) {
                @setcookie('vnn_sv_seed', $seed_string, time() + 86400, COOKIEPATH, COOKIE_DOMAIN);
            }
        }

        // Chuyển seed thành số nguyên
        $seed_num = crc32($seed_string);
        mt_srand($seed_num);

        $auto_random = !isset($instance['auto_random']) || !empty($instance['auto_random']);

        // Cơ quan chủ quản
        $agency = !empty($instance['agency']) ? $instance['agency'] : '';
        if (empty($agency) || $auto_random) {
            $agency = $presets['agencies'][$seed_num % count($presets['agencies'])];
        }

        // Số giấy phép
        $license = !empty($instance['license']) ? $instance['license'] : '';
        if (empty($license) || $auto_random) {
            $license = $presets['licenses'][$seed_num % count($presets['licenses'])];
        }

        // Ngày cấp
        $license_date = !empty($instance['license_date']) ? $instance['license_date'] : '';
        if (empty($license_date) || $auto_random) {
            $license_date = $presets['dates'][$seed_num % count($presets['dates'])];
        }

        // Tổng biên tập
        $editor = !empty($instance['editor']) ? $instance['editor'] : '';
        if (empty($editor) || $auto_random) {
            $editor = $presets['editors'][$seed_num % count($presets['editors'])];
        }

        // Địa chỉ
        $address = !empty($instance['address']) ? $instance['address'] : '';
        if (empty($address) || $auto_random) {
            $address = $presets['addresses'][$seed_num % count($presets['addresses'])];
        }

        // Điện thoại & Hotline
        $phone = !empty($instance['phone']) ? $instance['phone'] : '';
        if (empty($phone) || $auto_random) {
            $phone = $presets['phones'][$seed_num % count($presets['phones'])];
        }

        $hotline = !empty($instance['hotline']) ? $instance['hotline'] : '';
        if (empty($hotline) || $auto_random) {
            $hotline = $presets['hotlines'][$seed_num % count($presets['hotlines'])];
        }

        // Hotline Quảng cáo HN & HCM
        $ads_hn = !empty($instance['ads_hn']) ? $instance['ads_hn'] : '';
        if (empty($ads_hn) || $auto_random) {
            $ads_hn = $presets['ads_hn'][$seed_num % count($presets['ads_hn'])];
        }

        $ads_hcm = !empty($instance['ads_hcm']) ? $instance['ads_hcm'] : '';
        if (empty($ads_hcm) || $auto_random) {
            $ads_hcm = $presets['ads_hcm'][$seed_num % count($presets['ads_hcm'])];
        }

        // Email toà soạn
        $email = !empty($instance['email']) ? $instance['email'] : '';
        if (empty($email) || $auto_random) {
            $email = $presets['emails'][$seed_num % count($presets['emails'])];
        }

        // Email quảng cáo
        $ads_email = !empty($instance['ads_email']) ? $instance['ads_email'] : '';
        if (empty($ads_email) || $auto_random) {
            $ads_email = $presets['ads_emails'][$seed_num % count($presets['ads_emails'])];
        }

        // Email kỹ thuật
        $tech_email = !empty($instance['tech_email']) ? $instance['tech_email'] : '';
        if (empty($tech_email) || $auto_random) {
            $tech_email = $presets['tech_emails'][$seed_num % count($presets['tech_emails'])];
        }

        // Phục hồi lại seed ngẫu nhiên mặc định
        mt_srand();

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
            'price_url'    => !empty($instance['price_url']) ? $instance['price_url'] : 'http://vads.vn',
        );
    }

    /**
     * Render giao diện Widget ra Frontend (theo đúng 100% hình mẫu đề bài)
     */
    public function widget($args, $instance) {
        $data = self::resolve_data($instance);

        echo isset($args['before_widget']) ? $args['before_widget'] : '';
        ?>
        <div class="vnn-widget-container" id="vnn-widget-test-4">
            <div class="vnn-widget-inner">
                <!-- CỘT 1: THÔNG TIN TOÀ SOÀN & PHÁP LÝ -->
                <div class="vnn-col vnn-col-editorial">
                    <!-- Logo VietNamNet -->
                    <div class="vnn-brand-header">
                        <div class="vnn-logo-box">
                            <!-- SVG Ngọn lửa biểu tượng VietNamNet chuẩn xác -->
                            <svg class="vnn-flame-icon" viewBox="0 0 38 46" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="VietNamNet">
                                <path d="M19.5 2C19.5 2 24.5 10.5 24.5 18C24.5 22.8 21.6 26.5 18.5 29.5C15.2 26.5 11 23.2 11 16.5C11 12.8 13.5 9 13.5 9C13.5 9 8 14.5 8 22C8 31.5 15 38 23 38C31 38 36.5 31.5 36.5 22C36.5 12 19.5 2 19.5 2Z" fill="#DC2626"/>
                                <path d="M16 23C16 23 20 28 20 31.5C20 33.8 18.8 35.5 17 37C15.5 35.5 13.5 33.8 13.5 31C13.5 29 14.8 27 14.8 27C14.8 27 12 29.5 12 32.5C12 36.2 15 39 18 39C21 39 23 36.2 23 32.5C23 28.5 16 23 16 23Z" fill="#EF4444"/>
                            </svg>
                            <div class="vnn-brand-typography">
                                <span class="vnn-brand-title">vietnamnet</span>
                                <span class="vnn-brand-subtitle">VIETNAMNET.VN</span>
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách thông tin toà soạn -->
                    <div class="vnn-editorial-info">
                        <p class="vnn-info-line">
                            <span class="vnn-label">Cơ quan chủ quản:</span> 
                            <span class="vnn-value vnn-val-agency"><?php echo esc_html($data['agency']); ?></span>
                        </p>
                        <p class="vnn-info-line">
                            <span class="vnn-label">Số giấy phép:</span> 
                            <span class="vnn-value vnn-val-license"><?php echo esc_html($data['license']); ?></span>, 
                            <span class="vnn-sub-label">cấp ngày</span> 
                            <span class="vnn-value vnn-val-date"><?php echo esc_html($data['license_date']); ?></span>
                        </p>
                        <p class="vnn-info-line vnn-editor-line">
                            <span class="vnn-label">Tổng biên tập:</span> 
                            <strong class="vnn-value vnn-val-editor"><?php echo esc_html($data['editor']); ?></strong>
                        </p>

                        <!-- Nút Liên hệ toà soạn -->
                        <div class="vnn-btn-wrap">
                            <a href="mailto:<?php echo esc_attr($data['email']); ?>" class="vnn-btn vnn-btn-secondary">Liên hệ tòa soạn</a>
                        </div>

                        <p class="vnn-info-line vnn-address-line">
                            <span class="vnn-label">Địa chỉ:</span> 
                            <span class="vnn-value vnn-val-address"><?php echo esc_html($data['address']); ?></span>
                        </p>
                        <p class="vnn-info-line">
                            <span class="vnn-label">Điện thoại:</span> 
                            <strong><?php echo esc_html($data['phone']); ?></strong> - 
                            <span class="vnn-label">Hotline:</span> 
                            <strong><?php echo esc_html($data['hotline']); ?></strong>
                        </p>
                        <p class="vnn-info-line">
                            <span class="vnn-label">Email:</span> 
                            <a href="mailto:<?php echo esc_attr($data['email']); ?>" class="vnn-link"><?php echo esc_html($data['email']); ?></a>
                        </p>
                        <p class="vnn-info-line vnn-copyright-line">
                            &copy; 1997 Báo VietNamNet. All rights reserved. Chỉ được phát hành lại thông tin từ website này khi có sự đồng ý bằng văn bản của báo VietNamNet.
                        </p>
                    </div>
                </div>

                <!-- CỘT 2: THÔNG TIN QUẢNG CÁO & TRUYỀN THÔNG -->
                <div class="vnn-col vnn-col-commercial">
                    <!-- Nút Liên hệ quảng cáo -->
                    <div class="vnn-btn-wrap vnn-btn-wrap-ads">
                        <a href="mailto:<?php echo esc_attr($data['ads_email']); ?>" class="vnn-btn vnn-btn-secondary">Liên hệ quảng cáo</a>
                    </div>

                    <div class="vnn-commercial-info">
                        <p class="vnn-info-line vnn-company-name">
                            Công ty Cổ phần Truyền thông VietNamNet
                        </p>
                        <p class="vnn-info-line">
                            <span class="vnn-label">Hotline:</span> 
                            <strong><?php echo esc_html($data['ads_hn']); ?></strong> (Hà Nội) - 
                            <strong><?php echo esc_html($data['ads_hcm']); ?></strong> (Tp.HCM)
                        </p>
                        <p class="vnn-info-line">
                            <span class="vnn-label">Email:</span> 
                            <a href="mailto:<?php echo esc_attr($data['ads_email']); ?>" class="vnn-link"><?php echo esc_html($data['ads_email']); ?></a>
                        </p>
                        <p class="vnn-info-line">
                            <span class="vnn-label">Báo giá:</span> 
                            <a href="<?php echo esc_url($data['price_url']); ?>" target="_blank" rel="noopener noreferrer" class="vnn-link vnn-link-highlight">
                                <strong><?php echo esc_html($data['price_url']); ?></strong>
                            </a>
                        </p>
                        <p class="vnn-info-line">
                            <span class="vnn-label">Hỗ trợ kỹ thuật:</span> 
                            <a href="mailto:<?php echo esc_attr($data['tech_email']); ?>" class="vnn-link"><?php echo esc_html($data['tech_email']); ?></a>
                        </p>
                    </div>
                </div>

                <!-- CỘT 3: THEO DÕI MẠNG XÃ HỘI & TIỆN ÍCH -->
                <div class="vnn-col vnn-col-social">
                    <div class="vnn-social-heading">Theo dõi VietNamNet trên</div>

                    <!-- 4 Icon mạng xã hội tròn đen chuẩn xác -->
                    <div class="vnn-social-icons">
                        <!-- Facebook -->
                        <a href="https://facebook.com/vietnamnet" target="_blank" rel="noopener noreferrer" class="vnn-social-item vnn-social-fb" title="Facebook">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>

                        <!-- YouTube -->
                        <a href="https://youtube.com/vietnamnet" target="_blank" rel="noopener noreferrer" class="vnn-social-item vnn-social-yt" title="YouTube">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                            </svg>
                        </a>

                        <!-- TikTok -->
                        <a href="https://tiktok.com/@vietnamnet" target="_blank" rel="noopener noreferrer" class="vnn-social-item vnn-social-tiktok" title="TikTok">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                                <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-1.01-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                            </svg>
                        </a>

                        <!-- Zalo -->
                        <a href="https://zalo.me/vietnamnet" target="_blank" rel="noopener noreferrer" class="vnn-social-item vnn-social-zalo" title="Zalo">
                            <span class="vnn-zalo-text">Zalo</span>
                        </a>
                    </div>

                    <!-- Các liên kết tiện ích -->
                    <div class="vnn-utility-links">
                        <p class="vnn-utility-line">
                            <a href="#" class="vnn-utility-link">Tải ứng dụng</a> 
                            <span class="vnn-utility-divider">|</span> 
                            <a href="#" class="vnn-utility-link">Độc giả gửi bài</a>
                        </p>
                        <p class="vnn-utility-line">
                            <a href="#" class="vnn-utility-link">Tuyển dụng</a>
                        </p>
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
        $defaults = array(
            'auto_random'  => 1,
            'agency'       => '',
            'license'      => '',
            'license_date' => '',
            'editor'       => '',
            'address'      => '',
            'phone'        => '',
            'hotline'      => '',
            'ads_hn'       => '',
            'ads_hcm'      => '',
            'email'        => '',
            'ads_email'    => '',
            'tech_email'   => '',
            'price_url'    => 'http://vads.vn',
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        ?>
        <div class="vnn-admin-widget-form">
            <p>
                <input class="checkbox" type="checkbox" <?php checked($instance['auto_random'], 1); ?> id="<?php echo esc_attr($this->get_field_id('auto_random')); ?>" name="<?php echo esc_attr($this->get_field_name('auto_random')); ?>" value="1" />
                <label for="<?php echo esc_attr($this->get_field_id('auto_random')); ?>">
                    <strong><?php esc_html_e('Tự động Random thông tin (Mỗi sinh viên hiển thị khác nhau)', 'cms-nhomc'); ?></strong>
                </label>
            </p>
            <p style="font-size: 12px; color: #64748b; margin-top: -5px;">
                <?php esc_html_e('Khi bật tuỳ chọn này, các thông tin (cơ quan, giấy phép, tổng biên tập, SĐT...) sẽ được sinh ngẫu nhiên tự động theo yêu cầu bài thi. Nếu bỏ chọn, các giá trị bên dưới sẽ được sử dụng.', 'cms-nhomc'); ?>
            </p>

            <hr style="margin: 12px 0; border: none; border-top: 1px solid #e2e8f0;" />

            <p>
                <label for="<?php echo esc_attr($this->get_field_id('agency')); ?>"><?php esc_html_e('Cơ quan chủ quản:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('agency')); ?>" name="<?php echo esc_attr($this->get_field_name('agency')); ?>" type="text" value="<?php echo esc_attr($instance['agency']); ?>" placeholder="VD: Bộ Dân tộc và Tôn giáo" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('license')); ?>"><?php esc_html_e('Số giấy phép:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('license')); ?>" name="<?php echo esc_attr($this->get_field_name('license')); ?>" type="text" value="<?php echo esc_attr($instance['license']); ?>" placeholder="VD: 146/GP-BVHTTDL" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('license_date')); ?>"><?php esc_html_e('Ngày cấp:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('license_date')); ?>" name="<?php echo esc_attr($this->get_field_name('license_date')); ?>" type="text" value="<?php echo esc_attr($instance['license_date']); ?>" placeholder="VD: 17/10/2025" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('editor')); ?>"><?php esc_html_e('Tổng biên tập:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('editor')); ?>" name="<?php echo esc_attr($this->get_field_name('editor')); ?>" type="text" value="<?php echo esc_attr($instance['editor']); ?>" placeholder="VD: Nguyễn Văn Bá" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('address')); ?>"><?php esc_html_e('Địa chỉ tòa soạn:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('address')); ?>" name="<?php echo esc_attr($this->get_field_name('address')); ?>" type="text" value="<?php echo esc_attr($instance['address']); ?>" placeholder="VD: Tầng 18, Toà nhà Cục Viễn thông (VNTA)..." />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('phone')); ?>"><?php esc_html_e('Điện thoại:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('phone')); ?>" name="<?php echo esc_attr($this->get_field_name('phone')); ?>" type="text" value="<?php echo esc_attr($instance['phone']); ?>" placeholder="02439369898" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('hotline')); ?>"><?php esc_html_e('Hotline toà soạn:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('hotline')); ?>" name="<?php echo esc_attr($this->get_field_name('hotline')); ?>" type="text" value="<?php echo esc_attr($instance['hotline']); ?>" placeholder="0923457788" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('ads_hn')); ?>"><?php esc_html_e('Hotline Quảng cáo (Hà Nội):', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('ads_hn')); ?>" name="<?php echo esc_attr($this->get_field_name('ads_hn')); ?>" type="text" value="<?php echo esc_attr($instance['ads_hn']); ?>" placeholder="0919405885" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('ads_hcm')); ?>"><?php esc_html_e('Hotline Quảng cáo (Tp.HCM):', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('ads_hcm')); ?>" name="<?php echo esc_attr($this->get_field_name('ads_hcm')); ?>" type="text" value="<?php echo esc_attr($instance['ads_hcm']); ?>" placeholder="0919435885" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('email')); ?>"><?php esc_html_e('Email toà soạn:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('email')); ?>" name="<?php echo esc_attr($this->get_field_name('email')); ?>" type="email" value="<?php echo esc_attr($instance['email']); ?>" placeholder="vietnamnet@vietnamnet.vn" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('ads_email')); ?>"><?php esc_html_e('Email liên hệ quảng cáo:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('ads_email')); ?>" name="<?php echo esc_attr($this->get_field_name('ads_email')); ?>" type="email" value="<?php echo esc_attr($instance['ads_email']); ?>" placeholder="contact@vietnamnet.vn" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('tech_email')); ?>"><?php esc_html_e('Email hỗ trợ kỹ thuật:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('tech_email')); ?>" name="<?php echo esc_attr($this->get_field_name('tech_email')); ?>" type="email" value="<?php echo esc_attr($instance['tech_email']); ?>" placeholder="support@tech.vietnamnet.vn" />
            </p>
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('price_url')); ?>"><?php esc_html_e('Đường dẫn Báo giá:', 'cms-nhomc'); ?></label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('price_url')); ?>" name="<?php echo esc_attr($this->get_field_name('price_url')); ?>" type="text" value="<?php echo esc_attr($instance['price_url']); ?>" placeholder="http://vads.vn" />
            </p>
        </div>
        <?php
    }

    /**
     * Lưu cấu hình khi người dùng sửa trong Admin
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['auto_random']  = !empty($new_instance['auto_random']) ? 1 : 0;
        $instance['agency']       = sanitize_text_field($new_instance['agency']);
        $instance['license']      = sanitize_text_field($new_instance['license']);
        $instance['license_date'] = sanitize_text_field($new_instance['license_date']);
        $instance['editor']       = sanitize_text_field($new_instance['editor']);
        $instance['address']      = sanitize_text_field($new_instance['address']);
        $instance['phone']        = sanitize_text_field($new_instance['phone']);
        $instance['hotline']      = sanitize_text_field($new_instance['hotline']);
        $instance['ads_hn']       = sanitize_text_field($new_instance['ads_hn']);
        $instance['ads_hcm']      = sanitize_text_field($new_instance['ads_hcm']);
        $instance['email']        = sanitize_email($new_instance['email']);
        $instance['ads_email']    = sanitize_email($new_instance['ads_email']);
        $instance['tech_email']   = sanitize_email($new_instance['tech_email']);
        $instance['price_url']    = esc_url_raw($new_instance['price_url']);
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

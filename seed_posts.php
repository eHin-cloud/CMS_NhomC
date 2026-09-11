<?php
/**
 * Seeder script for sample posts matching screenshot
 */
define('WP_USE_THEMES', false);
require_once __DIR__ . '/wordpress/wp-load.php';

$theme_uri = get_template_directory_uri();

$posts_data = array(
    array(
        'title'   => 'Tin Hot: Trường CĐ Công Nghệ Thủ Đức Tiếp Nhận Hồ Sơ Tuyển Sinh Tất Cả Các Ngày Trong Tuần, Kể Cả Thứ 7 Và Chủ Nhật',
        'slug'    => 'tin-hot-truong-cd-cong-nghe-thu-duc-tiep-nhan-ho-so-tuyen-sinh',
        'date'    => '2026-07-27 08:30:00',
        'excerpt' => 'TIN VUI TUYỂN SINH: Hiện tại, Khoa CNTT vẫn đang tiếp tục nhận hồ sơ tuyển sinh cho 3 ngành mũi nhọn thuộc Khối Công nghệ và Sáng tạo, mở ra lộ trình học tập thực tế, giúp bạn tự tin làm chủ công nghệ và đón đầu mọi cơ hội nghề nghiệp trong kỷ nguyên số: Công nghệ thông tin; Thiết kế đồ họa; Truyền thông và Mạng máy tính',
        'content' => '<p>Nhằm tạo điều kiện thuận lợi tối đa cho các bạn học sinh ở xa cũng như các bậc phụ huynh bận rộn công việc hành chính, Trường sẽ tổ chức tiếp nhận hồ sơ xét tuyển và nhập học xuyên suốt tất cả các ngày trong tuần, KỂ CẢ THỨ 7 VÀ CHỦ NHẬT.</p>
<p class="text-center"><img src="' . $theme_uri . '/assets/images/tdc-tuyen-sinh.jpg" alt="Tuyển sinh TDC" class="aligncenter size-full" /></p>
<p><strong>DANH SÁCH CÁC GIẤY TỜ CẦN CHUẨN BỊ BẮT BUỘC:</strong></p>
<p>Để thủ tục diễn ra nhanh chóng, các bạn vui lòng chuẩn bị đầy đủ các loại giấy tờ (bản sao có công chứng) sau đây:</p>
<p>01 Bản sao Bằng tốt nghiệp THPT HOẶC Giấy chứng nhận tốt nghiệp tạm thời năm 2026 / Giấy xác nhận điểm thi tốt nghiệp THPT (có công chứng).</p>
<p>01 Bản sao Học bạ THPT (có công chứng).</p>
<p>01 Bản sao Căn cước công dân (CCCD) (có công chứng).</p>
<blockquote>
<p><em>“Đừng để ước mơ chờ đợi thêm một phút nào nữa! Hãy thực hiện theo các bước sau:<br>
Đăng ký trực tuyến tại: <a href="https://dktuyensinh.tdc.edu.vn/#/ChucNang" target="_blank" rel="noopener">https://dktuyensinh.tdc.edu.vn/#/ChucNang</a><br>
Nộp hồ sơ trực tiếp: Phòng Tuyển sinh - Đào tạo (D08-02) – Trường Cao đẳng Công nghệ Thủ Đức.<br>
Địa chỉ: 53 Võ Văn Ngân, Phường Thủ Đức, TP.HCM.<br>
Hotline: 028.38966825 - 028.38970023<br>
TDC – Nơi khởi đầu cho sự nghiệp vững chắc trong kỷ nguyên số!”</em></p>
</blockquote>',
        'image'   => 'tdc-tuyen-sinh.jpg',
    ),
    array(
        'title'   => 'TDC THÁNG 06/2026: NÂNG TẦM CHẤT LƯỢNG - KẾT NỐI VÀ LAN TỎA TINH THẦN TÌNH NGUYỆN',
        'slug'    => 'tdc-thang-06-2026-nang-tam-chat-luong-ket-noi-va-lan-toa-tinh-than',
        'date'    => '2026-07-14 09:15:00',
        'excerpt' => 'Tháng 06/2026 đánh dấu những bước chuyển mình quan trọng của Trường Cao đẳng Công nghệ Thủ Đức trong việc nâng tầm chất lượng giảng dạy, kết nối hợp tác doanh nghiệp và lan tỏa tinh thần tình nguyện vì cộng đồng.',
        'content' => '<p>Tháng 06/2026 đánh dấu những bước chuyển mình quan trọng của Trường Cao đẳng Công nghệ Thủ Đức trong việc nâng tầm chất lượng giảng dạy, kết nối hợp tác doanh nghiệp và lan tỏa tinh thần tình nguyện vì cộng đồng.</p>
<p class="text-center"><img src="' . $theme_uri . '/assets/images/tdc-toa-nha-xanh.jpg" alt="Tòa nhà TDC" class="aligncenter size-full" /></p>
<p>Nhà trường đã tổ chức nhiều hội thảo chuyên đề, ký kết biên bản ghi nhớ hợp tác với các doanh nghiệp công nghệ hàng đầu, đồng thời triển khai các chiến dịch mùa hè xanh và chương trình đào tạo kỹ năng số cho sinh viên.</p>',
        'image'   => 'tdc-toa-nha-xanh.jpg',
    ),
    array(
        'title'   => 'Nghề Công nghệ thông tin trình độ cao đẳng được công nhận đạt chuẩn kiểm định chất lượng chương trình đào tạo',
        'slug'    => 'nghe-cong-nghe-thong-tin-duoc-cong-nhan-dat-chuan-kiem-dinh',
        'date'    => '2026-07-13 14:00:00',
        'excerpt' => 'Sáng ngày 13/07/2026, Trường Cao đẳng Công nghệ Thủ Đức đã long trọng tổ chức lễ công bố và đón nhận Giấy chứng nhận đạt chuẩn kiểm định chất lượng giáo dục nghề nghiệp cho ngành Công nghệ thông tin trình độ cao đẳng.',
        'content' => '<p>Sáng ngày 13/07/2026, Trường Cao đẳng Công nghệ Thủ Đức đã long trọng tổ chức lễ công bố và đón nhận Giấy chứng nhận đạt chuẩn kiểm định chất lượng giáo dục nghề nghiệp cho ngành Công nghệ thông tin trình độ cao đẳng.</p>
<p class="text-center"><img src="' . $theme_uri . '/assets/images/tdc-hoi-nghi-cntt.jpg" alt="Kiểm định chất lượng CNTT" class="aligncenter size-full" /></p>
<p>Đây là kết quả của nhiều năm nỗ lực không ngừng trong việc cải tiến chương trình đào tạo, nâng cao trình độ đội ngũ giảng viên và đầu tư cơ sở vật chất hiện đại, đáp ứng tiêu chuẩn quốc gia và hội nhập khu vực.</p>',
        'image'   => 'tdc-hoi-nghi-cntt.jpg',
    ),
    array(
        'title'   => 'Giới Thiệu Tủ Sách Điện Tử Chủ Tịch Hồ Chí Minh: Nguồn Tư Liệu Số Quý Giá Cho Cán Bộ, Giảng Viên Và Sinh Viên',
        'slug'    => 'gioi-thieu-tu-sach-dien-tu-chu-tich-ho-chi-minh',
        'date'    => '2026-07-07 10:20:00',
        'excerpt' => 'Nhằm lan tỏa phong trào học tập và làm theo tư tưởng, đạo đức, phong cách Hồ Chí Minh, thư viện nhà trường trân trọng giới thiệu Tủ sách điện tử Chủ tịch Hồ Chí Minh - nguồn tư liệu số phong phú và quý giá dành cho cán bộ, giảng viên và sinh viên.',
        'content' => '<p>Nhằm lan tỏa phong trào học tập và làm theo tư tưởng, đạo đức, phong cách Hồ Chí Minh, thư viện nhà trường trân trọng giới thiệu Tủ sách điện tử Chủ tịch Hồ Chí Minh - nguồn tư liệu số phong phú và quý giá dành cho cán bộ, giảng viên và sinh viên.</p>
<p class="text-center"><img src="' . $theme_uri . '/assets/images/tdc-tu-sach-dien-tu.jpg" alt="Tủ sách điện tử TDC" class="aligncenter size-full" /></p>
<p>Tủ sách gồm hơn 200 đầu sách, bài viết, tài liệu về cuộc đời, sự nghiệp và tư tưởng của Chủ tịch Hồ Chí Minh, có thể truy cập mọi lúc, mọi nơi qua thiết bị di động.</p>',
        'image'   => 'tdc-tu-sach-dien-tu.jpg',
    ),
);

// Ensure Category "Tin Tức" exists
$cat = get_term_by('name', 'Tin Tức', 'category');
if (!$cat) {
    $cat_insert = wp_insert_term('Tin Tức', 'category', array('slug' => 'tin-tuc'));
    $cat_id = is_array($cat_insert) ? $cat_insert['term_id'] : 1;
} else {
    $cat_id = $cat->term_id;
}

foreach ($posts_data as $data) {
    $existing = get_page_by_path($data['slug'], OBJECT, 'post');
    if ($existing) {
        wp_update_post(array(
            'ID'           => $existing->ID,
            'post_title'   => $data['title'],
            'post_content' => $data['content'],
            'post_excerpt' => $data['excerpt'],
            'post_date'    => $data['date'],
            'post_date_gmt'=> $data['date'],
            'post_status'  => 'publish',
            'tags_input'   => array('Tin Tức'),
        ));
        wp_set_post_categories($existing->ID, array($cat_id));
        wp_set_post_tags($existing->ID, 'Tin Tức', false);
        echo "Updated post: " . $data['title'] . "\n";
    } else {
        $new_id = wp_insert_post(array(
            'post_title'   => $data['title'],
            'post_name'    => $data['slug'],
            'post_content' => $data['content'],
            'post_excerpt' => $data['excerpt'],
            'post_date'    => $data['date'],
            'post_date_gmt'=> $data['date'],
            'post_status'  => 'publish',
            'post_type'    => 'post',
            'post_category'=> array($cat_id),
            'tags_input'   => array('Tin Tức'),
        ));
        wp_set_post_tags($new_id, 'Tin Tức', false);
        echo "Created post ID {$new_id}: " . $data['title'] . "\n";
    }
}
echo "Seeding completed successfully!\n";

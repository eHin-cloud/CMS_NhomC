<?php
/**
 * Seeder script for sample posts matching screenshot 1 and screenshot 2
 */
define('WP_USE_THEMES', false);
require_once __DIR__ . '/wordpress/wp-load.php';

$theme_uri = get_template_directory_uri();

// Ensure Categories exist
$cat_tintuc = get_term_by('slug', 'tin-tuc', 'category');
if (!$cat_tintuc) {
    $ins = wp_insert_term('Tin Tức', 'category', array('slug' => 'tin-tuc'));
    $cat_tintuc_id = is_array($ins) ? $ins['term_id'] : 1;
} else {
    $cat_tintuc_id = $cat_tintuc->term_id;
}

$cat_noibat = get_term_by('slug', 'noi-bat', 'category');
if (!$cat_noibat) {
    $ins = wp_insert_term('Nổi Bật', 'category', array('slug' => 'noi-bat'));
    $cat_noibat_id = is_array($ins) ? $ins['term_id'] : 1;
} else {
    $cat_noibat_id = $cat_noibat->term_id;
}

$posts_data = array(
    // ====== 5 BÀI VIẾT MỚI (Hình 1 - Bài viết mới trong Detail) ======
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
        'category_id' => $cat_tintuc_id,
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
        'category_id' => $cat_tintuc_id,
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
        'category_id' => $cat_tintuc_id,
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
        'category_id' => $cat_tintuc_id,
        'image'   => 'tdc-tu-sach-dien-tu.jpg',
    ),
    array(
        'title'   => 'Mở Lối Tương Lai - Đón Đầu Kỷ Nguyên AI Cùng Khoa Công Nghệ Thông Tin Trường CĐ Công Nghệ Thủ Đức',
        'slug'    => 'mo-loi-tuong-lai-don-dau-ky-nguyen-ai-cung-khoa-cntt',
        'date'    => '2026-07-02 08:00:00',
        'excerpt' => 'Khoa Công nghệ thông tin Trường Cao đẳng Công nghệ Thủ Đức chính thức đưa các học phần Trí tuệ nhân tạo (AI), Khoa học dữ liệu vào chương trình đào tạo chính khóa nhằm chuẩn bị hành trang vững chắc cho sinh viên.',
        'content' => '<p>Khoa Công nghệ thông tin Trường Cao đẳng Công nghệ Thủ Đức chính thức đưa các học phần Trí tuệ nhân tạo (AI), Khoa học dữ liệu vào chương trình đào tạo chính khóa nhằm chuẩn bị hành trang vững chắc cho sinh viên.</p>',
        'category_id' => $cat_tintuc_id,
        'image'   => 'tdc-tuyen-sinh.jpg',
    ),

    // ====== 5 BÀI VIẾT NỔI BẬT (Hình 2 - Bài viết nổi bật ở Content) ======
    array(
        'title'   => 'TRƯỜNG CAO ĐẲNG CÔNG NGHỆ THỦ ĐỨC (TDC) KÝ KẾT HỢP TÁC CHIẾN LƯỢC TOÀN DIỆN VỚI CÁC DOANH NGHIỆP',
        'slug'    => 'truong-cao-dang-cong-nghe-thu-duc-tdc-ky-ket-hop-tac-chien-luoc',
        'date'    => '2026-04-17 10:00:00',
        'excerpt' => 'Lễ ký kết thỏa thuận hợp tác chiến lược giữa TDC và hơn 20 doanh nghiệp công nghệ hàng đầu tại TP.HCM nhằm nâng cao cơ hội thực tập, việc làm cho sinh viên.',
        'content' => '<p>Lễ ký kết thỏa thuận hợp tác chiến lược giữa TDC và hơn 20 doanh nghiệp công nghệ hàng đầu tại TP.HCM nhằm nâng cao cơ hội thực tập, việc làm cho sinh viên.</p>',
        'category_id' => $cat_noibat_id,
        'image'   => 'tdc-hoi-nghi-cntt.jpg',
    ),
    array(
        'title'   => 'Đoàn - Hội Khoa Công Nghệ Thông Tin: Tổ chức thành công chuỗi hoạt động chào mừng ngày truyền thống HSSV',
        'slug'    => 'doan-hoi-khoa-cntt-to-chuc-thanh-cong-chuoi-hoat-dong',
        'date'    => '2026-03-30 15:30:00',
        'excerpt' => 'Chuỗi hoạt động phong trào sôi nổi với nhiều hội thao, cuộc thi học thuật lập trình và các hoạt động tình nguyện ý nghĩa của sinh viên khoa CNTT TDC.',
        'content' => '<p>Chuỗi hoạt động phong trào sôi nổi với nhiều hội thao, cuộc thi học thuật lập trình và các hoạt động tình nguyện ý nghĩa của sinh viên khoa CNTT TDC.</p>',
        'category_id' => $cat_noibat_id,
        'image'   => 'tdc-toa-nha-xanh.jpg',
    ),
    array(
        'title'   => 'Khoa Công nghệ thông tin: Chúc mừng năm mới 2026 và định hướng phát triển đào tạo nguồn nhân lực số',
        'slug'    => 'khoa-cong-nghe-thong-tin-chuc-mung-nam-moi-2026',
        'date'    => '2026-02-17 08:30:00',
        'excerpt' => 'Nhân dịp đầu xuân năm mới 2026, tập thể Ban chủ nhiệm khoa CNTT gửi lời chúc an khang thịnh vượng đến toàn thể quý thầy cô, phụ huynh và sinh viên.',
        'content' => '<p>Nhân dịp đầu xuân năm mới 2026, tập thể Ban chủ nhiệm khoa CNTT gửi lời chúc an khang thịnh vượng đến toàn thể quý thầy cô, phụ huynh và sinh viên.</p>',
        'category_id' => $cat_noibat_id,
        'image'   => 'tdc-tu-sach-dien-tu.jpg',
    ),
    array(
        'title'   => 'TUỔI TRẺ TDC TRAO TẶNG CÔNG TRÌNH THANH NIÊN CHÀO MỪNG XUÂN ẤT TỴ 2026',
        'slug'    => 'tuoi-tre-tdc-trao-tang-cong-trinh-thanh-nien-2026',
        'date'    => '2026-02-04 14:00:00',
        'excerpt' => 'Đoàn trường trao tặng công trình thanh niên số hóa không gian học tập và các phần quà ý nghĩa cho học sinh, sinh viên vượt khó hiếu học.',
        'content' => '<p>Đoàn trường trao tặng công trình thanh niên số hóa không gian học tập và các phần quà ý nghĩa cho học sinh, sinh viên vượt khó hiếu học.</p>',
        'category_id' => $cat_noibat_id,
        'image'   => 'tdc-toa-nha-xanh.jpg',
    ),
    array(
        'title'   => 'Đoàn - Hội Khoa CNTT mang "Đại Tiệc" 3 Trong 1 Đến Ngày Hội Việc Làm TDC 2024',
        'slug'    => 'doan-hoi-khoa-cntt-mang-dai-tiec-3-trong-1-den-ngay-hoi-viec-lam',
        'date'    => '2024-04-22 09:00:00',
        'excerpt' => 'Không gian trưng bày các sản phẩm công nghệ sáng tạo, tư vấn tuyển dụng và phỏng vấn thử của Khoa CNTT thu hút đông đảo sinh viên và doanh nghiệp.',
        'content' => '<p>Không gian trưng bày các sản phẩm công nghệ sáng tạo, tư vấn tuyển dụng và phỏng vấn thử của Khoa CNTT thu hút đông đảo sinh viên và doanh nghiệp.</p>',
        'category_id' => $cat_noibat_id,
        'image'   => 'tdc-hoi-nghi-cntt.jpg',
    ),
);

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
        ));
        wp_set_post_categories($existing->ID, array($data['category_id']));
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
            'post_category'=> array($data['category_id']),
        ));
        wp_set_post_tags($new_id, 'Tin Tức', false);
        echo "Created post ID {$new_id}: " . $data['title'] . "\n";
    }
}
echo "Seeding completed successfully!\n";

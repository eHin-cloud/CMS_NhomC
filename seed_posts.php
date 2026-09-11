<?php
/**
 * Seeder script for sample posts matching screenshot
 */
define('WP_USE_THEMES', false);
require_once __DIR__ . '/wordpress/wp-load.php';

$posts_data = array(
    array(
        'title'   => 'Tin hot: Trường CĐ Công nghệ Thủ Đức tiếp nhận hồ sơ tuyển sinh tất cả các ngày trong tuần',
        'slug'    => 'tin-hot-truong-cd-cong-nghe-thu-duc-tiep-nhan-ho-so-tuyen-sinh',
        'date'    => '2026-07-27 08:30:00',
        'excerpt' => 'TIN VUI TUYỂN SINH: Hiện tại, Khoa CNTT vẫn đang tiếp tục nhận hồ sơ tuyển sinh cho 3 ngành mũi nhọn thuộc Khối Công nghệ và Sáng tạo, mở ra lộ trình học tập thực tế, giúp bạn tự tin làm chủ công nghệ và đón đầu mọi cơ hội nghề nghiệp trong kỷ nguyên số: Công nghệ...',
        'content' => 'TIN VUI TUYỂN SINH: Hiện tại, Khoa CNTT vẫn đang tiếp tục nhận hồ sơ tuyển sinh cho 3 ngành mũi nhọn thuộc Khối Công nghệ và Sáng tạo, mở ra lộ trình học tập thực tế, giúp bạn tự tin làm chủ công nghệ và đón đầu mọi cơ hội nghề nghiệp trong kỷ nguyên số: Công nghệ thông tin, Truyền thông đa phương tiện và An toàn thông tin.',
        'image'   => 'tdc-tuyen-sinh.jpg',
    ),
    array(
        'title'   => 'TDC THÁNG 06/2026: NÂNG TẦM CHẤT LƯỢNG - KẾT NỐI VÀ LAN TỎA TINH THẦN TRÁCH NHIỆM',
        'slug'    => 'tdc-thang-06-2026-nang-tam-chat-luong-ket-noi-va-lan-toa-tinh-than',
        'date'    => '2026-07-14 09:15:00',
        'excerpt' => '',
        'content' => 'Tháng 06/2026 đánh dấu những bước chuyển mình quan trọng của Trường Cao đẳng Công nghệ Thủ Đức trong việc nâng tầm chất lượng giảng dạy, kết nối hợp tác doanh nghiệp và lan tỏa tinh thần trách nhiệm cộng đồng.',
        'image'   => 'tdc-toa-nha-xanh.jpg',
    ),
    array(
        'title'   => 'Nghề Công nghệ thông tin trình độ cao đẳng được công nhận đạt chuẩn kiểm định chất lượng giáo dục',
        'slug'    => 'nghe-cong-nghe-thong-tin-duoc-cong-nhan-dat-chuan-kiem-dinh',
        'date'    => '2026-07-13 14:00:00',
        'excerpt' => '',
        'content' => 'Sáng ngày 13/07/2026, Trường Cao đẳng Công nghệ Thủ Đức đã long trọng tổ chức lễ công bố và đón nhận Giấy chứng nhận đạt chuẩn kiểm định chất lượng giáo dục nghề nghiệp cho ngành Công nghệ thông tin trình độ cao đẳng.',
        'image'   => 'tdc-hoi-nghi-cntt.jpg',
    ),
    array(
        'title'   => 'Giới Thiệu Tủ Sách Điện Tử Chủ Tịch Hồ Chí Minh: Nguồn Tư Liệu Số Quý Giá Cho Cán Bộ, Giảng Viên Và Sinh Viên',
        'slug'    => 'gioi-thieu-tu-sach-dien-tu-chu-tich-ho-chi-minh',
        'date'    => '2026-07-07 10:20:00',
        'excerpt' => '',
        'content' => 'Nhằm lan tỏa phong trào học tập và làm theo tư tưởng, đạo đức, phong cách Hồ Chí Minh, thư viện nhà trường trân trọng giới thiệu Tủ sách điện tử Chủ tịch Hồ Chí Minh - nguồn tư liệu số phong phú và quý giá.',
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
        ));
        wp_set_post_categories($existing->ID, array($cat_id));
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
        ));
        echo "Created post ID {$new_id}: " . $data['title'] . "\n";
    }
}
echo "Seeding completed successfully!\n";

<?php
/**
 * Setup script for FIT-TDC Nhóm C
 * - Sets site title & description with Nhóm C identification
 * - Creates 5 member accounts + 1 admin account with role Administrator
 * - Sets up FIT-TDC categories
 * - Populates realistic FIT-TDC news posts with matching local assets
 */

define('WP_USE_THEMES', false);
require_once __DIR__ . '/wp-load.php';

echo "=== 1. CẬP NHẬT TÊN VÀ KHẨU HIỆU WEBSITE (NHÓM C) ===\n";
update_option('blogname', 'FIT-TDC - Khoa Công nghệ thông tin - Nhóm C');
update_option('blogdescription', 'Khoa Công nghệ thông tin - Cao đẳng Công nghệ Thủ Đức - Nhóm C');
echo "Blog Name: " . get_option('blogname') . "\n";
echo "Blog Description: " . get_option('blogdescription') . "\n\n";

echo "=== 2. TẠO TÀI KHOẢN CHO TỪNG THÀNH VIÊN NHÓM C ===\n";
$members = array(
    array(
        'username'     => 'xuanhoa',
        'email'        => 'vhoa1682006@gmail.com',
        'display_name' => 'Văn Nguyễn Xuân Hòa (Nhóm C)',
        'first_name'   => 'Xuân Hòa',
        'last_name'    => 'Văn Nguyễn',
        'role'         => 'administrator',
        'password'     => 'Password@123',
    ),
    array(
        'username'     => 'thanhhien',
        'email'        => 'thenghien2006@gmail.com',
        'display_name' => 'Nguyễn Thanh Hiền (Nhóm C)',
        'first_name'   => 'Thanh Hiền',
        'last_name'    => 'Nguyễn',
        'role'         => 'administrator',
        'password'     => 'Password@123',
    ),
    array(
        'username'     => 'vinhem',
        'email'        => 'trumvinh85@gmail.com',
        'display_name' => 'Huỳnh Văn Vinh Em (Nhóm C)',
        'first_name'   => 'Vinh Em',
        'last_name'    => 'Huỳnh Văn',
        'role'         => 'administrator',
        'password'     => 'Password@123',
    ),
    array(
        'username'     => 'anhquy',
        'email'        => 'nguyquy67@gmail.com',
        'display_name' => 'Nguyễn Anh Quý (Nhóm C)',
        'first_name'   => 'Anh Quý',
        'last_name'    => 'Nguyễn',
        'role'         => 'administrator',
        'password'     => 'Password@123',
    ),
    array(
        'username'     => 'dangnguyen',
        'email'        => 'dn1275102@gmail.com',
        'display_name' => 'Đặng Đăng Nguyên (Nhóm C)',
        'first_name'   => 'Đăng Nguyên',
        'last_name'    => 'Đặng',
        'role'         => 'administrator',
        'password'     => 'Password@123',
    ),
    array(
        'username'     => 'admin_nhomc',
        'email'        => 'admin_nhomc@fit.tdc.edu.vn',
        'display_name' => 'Quản trị viên Nhóm C',
        'first_name'   => 'Admin',
        'last_name'    => 'Nhóm C',
        'role'         => 'administrator',
        'password'     => 'Password@123',
    ),
);

$member_ids = array();

foreach ($members as $m) {
    $existing = get_user_by('login', $m['username']);
    if (!$existing) {
        $existing = get_user_by('email', $m['email']);
    }

    if ($existing) {
        wp_update_user(array(
            'ID'           => $existing->ID,
            'user_login'   => $m['username'],
            'user_email'   => $m['email'],
            'display_name' => $m['display_name'],
            'first_name'   => $m['first_name'],
            'last_name'    => $m['last_name'],
            'user_pass'    => $m['password'],
            'role'         => $m['role'],
        ));
        $uid = $existing->ID;
        echo "[UPDATED] Thành viên: {$m['display_name']} (Username: {$m['username']} | Role: {$m['role']})\n";
    } else {
        $uid = wp_insert_user(array(
            'user_login'   => $m['username'],
            'user_email'   => $m['email'],
            'display_name' => $m['display_name'],
            'first_name'   => $m['first_name'],
            'last_name'    => $m['last_name'],
            'user_pass'    => $m['password'],
            'role'         => $m['role'],
        ));
        echo "[CREATED] Thành viên: {$m['display_name']} (Username: {$m['username']} | Role: {$m['role']})\n";
    }
    $member_ids[$m['username']] = $uid;
}

echo "\n=== 3. CHUẨN HÓA DANH MỤC FIT-TDC ===\n";
$categories_def = array(
    'tin-tuc'    => 'Tin Tức',
    'tuyen-sinh' => 'Tuyển Sinh',
    'dao-tao'    => 'Đào Tạo',
    'sinh-vien'  => 'Sinh Viên',
    'hoat-dong'  => 'Hoạt Động',
    'noi-bat'    => 'Nổi Bật',
);

$cat_map = array();
foreach ($categories_def as $slug => $name) {
    $term = get_term_by('slug', $slug, 'category');
    if (!$term) {
        $ins = wp_insert_term($name, 'category', array('slug' => $slug));
        $term_id = is_array($ins) ? $ins['term_id'] : 1;
    } else {
        $term_id = $term->term_id;
        wp_update_term($term_id, 'category', array('name' => $name));
    }
    $cat_map[$slug] = $term_id;
    echo "Category: {$name} (ID: {$term_id}, Slug: {$slug})\n";
}

echo "\n=== 4. CẬP NHẬT DỮ LIỆU BÀI VIẾT THỰC TẾ TỪ HTTP://FIT.TDC.EDU.VN/ ===\n";
$theme_uri = get_template_directory_uri();

$posts_data = array(
    array(
        'title'       => 'Tin hot: Trường CĐ Công nghệ Thủ Đức tiếp nhận hồ sơ tuyển sinh tất cả các ngày trong tuần, kể cả Thứ 7 và Chủ Nhật',
        'slug'        => 'tin-hot-truong-cd-cong-nghe-thu-duc-tiep-nhan-ho-so-tuyen-sinh-tat-ca-cac-ngay-trong-tuan-ke-ca-thu-7-va-chu-nhat',
        'date'        => '2026-07-27 08:30:00',
        'excerpt'     => 'TIN VUI TUYỂN SINH: Hiện tại, Khoa CNTT vẫn đang tiếp tục nhận hồ sơ tuyển sinh cho 3 ngành mũi nhọn thuộc Khối Công nghệ và Sáng tạo, mở ra lộ trình học tập thực tế, giúp bạn tự tin làm chủ công nghệ và đón đầu mọi cơ hội nghề nghiệp trong kỷ nguyên số: Công nghệ thông tin; Thiết kế đồ họa; Truyền thông và Mạng máy tính',
        'content'     => '<p>Nhằm tạo điều kiện thuận lợi tối đa cho các bạn học sinh ở xa cũng như các bậc phụ huynh bận rộn công việc hành chính, Trường Cao đẳng Công nghệ Thủ Đức (TDC) tổ chức tiếp nhận hồ sơ xét tuyển và nhập học xuyên suốt tất cả các ngày trong tuần, <strong>KỂ CẢ THỨ 7 VÀ CHỦ NHẬT</strong>.</p>
<p class="text-center"><img src="' . $theme_uri . '/assets/images/tdc-tuyen-sinh.jpg" alt="Tuyển sinh FIT-TDC" class="aligncenter size-full" /></p>
<h3>DANH SÁCH CÁC GIẤY TỜ CẦN CHUẨN BỊ BẮT BUỘC:</h3>
<p>Để thủ tục diễn ra nhanh chóng, các bạn vui lòng chuẩn bị đầy đủ các loại giấy tờ (bản sao có công chứng) sau đây:</p>
<ul>
<li>01 Bản sao Bằng tốt nghiệp THPT HOẶC Giấy chứng nhận tốt nghiệp tạm thời năm 2026 / Giấy xác nhận điểm thi tốt nghiệp THPT (có công chứng).</li>
<li>01 Bản sao Học bạ THPT (có công chứng).</li>
<li>01 Bản sao Căn cước công dân (CCCD) (có công chứng).</li>
</ul>
<blockquote>
<p><em>“Đừng để ước mơ chờ đợi thêm một phút nào nữa! Hãy nhanh chóng nộp hồ sơ tại Phòng Tuyển sinh - Đào tạo (D08-02) – Trường Cao đẳng Công nghệ Thủ Đức. Địa chỉ: 53 Võ Văn Ngân, Phường Linh Chiểu, TP. Thủ Đức, TP.HCM.”</em></p>
</blockquote>',
        'categories'  => array($cat_map['tin-tuc'], $cat_map['tuyen-sinh'], $cat_map['noi-bat']),
        'author'      => 'xuanhoa',
        'image'       => 'tdc-tuyen-sinh.jpg',
    ),
    array(
        'title'       => 'TDC THÁNG 06/2026: NÂNG TẦM CHẤT LƯỢNG - KẾT NỐI VÀ LAN TỎA TINH THẦN TÌNH NGUYỆN',
        'slug'        => 'tdc-thang-062026-nang-tam-chat-luong-ket-noi-va-lan-toa-tinh-than-tinh-nguyen',
        'date'        => '2026-07-14 09:15:00',
        'excerpt'     => 'Tháng 06/2026 đánh dấu những bước chuyển mình quan trọng của Trường Cao đẳng Công nghệ Thủ Đức trong việc nâng tầm chất lượng giảng dạy, kết nối hợp tác doanh nghiệp và lan tỏa tinh thần tình nguyện vì cộng đồng.',
        'content'     => '<p>Tháng 06/2026 đánh dấu những bước chuyển mình quan trọng của Trường Cao đẳng Công nghệ Thủ Đức trong việc nâng tầm chất lượng giảng dạy, kết nối hợp tác doanh nghiệp và lan tỏa tinh thần tình nguyện vì cộng đồng.</p>
<p class="text-center"><img src="' . $theme_uri . '/assets/images/tdc-toa-nha-xanh.jpg" alt="Tòa nhà TDC" class="aligncenter size-full" /></p>
<p>Nhà trường đã tổ chức nhiều hội thảo chuyên đề, ký kết biên bản ghi nhớ hợp tác với các doanh nghiệp công nghệ hàng đầu, đồng thời triển khai chiến dịch tình nguyện Mùa hè xanh 2026 với sự tham gia nhiệt huyết của đoàn viên sinh viên Khoa CNTT.</p>',
        'categories'  => array($cat_map['tin-tuc'], $cat_map['hoat-dong']),
        'author'      => 'thanhhien',
        'image'       => 'tdc-toa-nha-xanh.jpg',
    ),
    array(
        'title'       => 'Nghề Công nghệ thông tin trình độ cao đẳng được công nhận đạt chuẩn kiểm định chất lượng chương trình đào tạo',
        'slug'        => 'nghe-cong-nghe-thong-tin-trinh-do-cao-dang-duoc-cong-nhan-dat-chuan-kiem-dinh-chat-luong-chuong-trinh-dao-tao',
        'date'        => '2026-07-13 14:00:00',
        'excerpt'     => 'Sáng ngày 13/07/2026, Trường Cao đẳng Công nghệ Thủ Đức đã long trọng tổ chức lễ công bố và đón nhận Giấy chứng nhận đạt chuẩn kiểm định chất lượng giáo dục nghề nghiệp cho ngành Công nghệ thông tin trình độ cao đẳng.',
        'content'     => '<p>Sáng ngày 13/07/2026, Trường Cao đẳng Công nghệ Thủ Đức đã long trọng tổ chức lễ công bố và đón nhận Giấy chứng nhận đạt chuẩn kiểm định chất lượng giáo dục nghề nghiệp cho ngành Công nghệ thông tin trình độ cao đẳng.</p>
<p class="text-center"><img src="' . $theme_uri . '/assets/images/tdc-hoi-nghi-cntt.jpg" alt="Kiểm định chất lượng CNTT" class="aligncenter size-full" /></p>
<p>Đây là minh chứng rõ nét cho chất lượng đào tạo vượt trội, bám sát nhu cầu thực tế của các doanh nghiệp phần mềm và giải pháp chuyển đổi số tại TP.HCM và toàn quốc.</p>',
        'categories'  => array($cat_map['tin-tuc'], $cat_map['dao-tao'], $cat_map['noi-bat']),
        'author'      => 'vinhem',
        'image'       => 'tdc-hoi-nghi-cntt.jpg',
    ),
    array(
        'title'       => 'Giới Thiệu Tủ Sách Điện Tử Chủ Tịch Hồ Chí Minh: Nguồn Tư Liệu Số Quý Giá Cho CBGVNV Và Sinh Viên TDC',
        'slug'        => 'gioi-thieu-tu-sach-dien-tu-chu-tich-ho-chi-minh-nguon-tu-lieu-so-quy-gia-cho-cbgvnv-va-sinh-vien-tdc',
        'date'        => '2026-07-07 10:20:00',
        'excerpt'     => 'Nhằm lan tỏa phong trào học tập và làm theo tư tưởng, đạo đức, phong cách Hồ Chí Minh, thư viện nhà trường trân trọng giới thiệu Tủ sách điện tử Chủ tịch Hồ Chí Minh - nguồn tư liệu số phong phú và quý giá dành cho cán bộ, giảng viên và sinh viên TDC.',
        'content'     => '<p>Nhằm lan tỏa phong trào học tập và làm theo tư tưởng, đạo đức, phong cách Hồ Chí Minh, thư viện nhà trường trân trọng giới thiệu Tủ sách điện tử Chủ tịch Hồ Chí Minh - nguồn tư liệu số phong phú và quý giá dành cho cán bộ, giảng viên và sinh viên TDC.</p>
<p class="text-center"><img src="' . $theme_uri . '/assets/images/tdc-tu-sach-dien-tu.jpg" alt="Tủ sách điện tử TDC" class="aligncenter size-full" /></p>
<p>Kho tư liệu số bao gồm các tài liệu quý về di sản Hồ Chí Minh, hình ảnh tư liệu lịch sử và các công trình nghiên cứu khoa học xã hội nhân văn có giá trị.</p>',
        'categories'  => array($cat_map['tin-tuc'], $cat_map['sinh-vien']),
        'author'      => 'anhquy',
        'image'       => 'tdc-tu-sach-dien-tu.jpg',
    ),
    array(
        'title'       => 'Mở Lối Tương Lai - Đón Đầu Kỷ Nguyên AI Cùng Khoa Công Nghệ Thông Tin (FIT-TDC)',
        'slug'        => 'mo-loi-tuong-lai-don-dau-ky-nguyen-ai-cung-khoa-cong-nghe-thong-tin-fit-tdc',
        'date'        => '2026-07-02 08:00:00',
        'excerpt'     => 'Khoa Công nghệ thông tin Trường Cao đẳng Công nghệ Thủ Đức chính thức đưa các học phần Trí tuệ nhân tạo (AI), Khoa học dữ liệu và Điện toán đám mây vào chương trình đào tạo chính khóa nhằm chuẩn bị hành trang vững chắc cho sinh viên.',
        'content'     => '<p>Khoa Công nghệ thông tin Trường Cao đẳng Công nghệ Thủ Đức chính thức đưa các học phần Trí tuệ nhân tạo (AI), Khoa học dữ liệu và Điện toán đám mây vào chương trình đào tạo chính khóa.</p>
<p class="text-center"><img src="' . $theme_uri . '/assets/images/tdc-mo-loi-ai.jpg" alt="Kỷ nguyên AI FIT-TDC" class="aligncenter size-full" /></p>
<p>Chương trình giúp sinh viên nắm vững các kỹ thuật lập trình AI, học máy (Machine Learning) và ứng dụng các mô hình ngôn ngữ lớn (LLM) giải quyết các bài toán thực tiễn của doanh nghiệp.</p>',
        'categories'  => array($cat_map['tin-tuc'], $cat_map['dao-tao'], $cat_map['noi-bat']),
        'author'      => 'dangnguyen',
        'image'       => 'tdc-mo-loi-ai.jpg',
    ),
    array(
        'title'       => 'TRƯỜNG CAO ĐẲNG CÔNG NGHỆ THỦ ĐỨC (TDC) KHÁNH THÀNH PHÒNG THỰC HÀNH TRIỂN KHAI BẢO TRÌ DO FPT TELECOM TÀI TRỢ',
        'slug'        => 'truong-cao-dang-cong-nghe-thu-duc-tdc-khanh-thanh-phong-thuc-hanh-trien-khai-bao-tri-do-fpt-telecom-tai-tro',
        'date'        => '2026-04-17 10:36:00',
        'excerpt'     => 'Lễ khánh thành phòng thực hành mạng và triển khai bảo trì hệ thống do FPT Telecom tài trợ trang thiết bị chuẩn quốc tế phục vụ công tác giảng dạy chuyên sâu cho ngành Truyền thông và Mạng máy tính.',
        'content'     => '<p>Lễ khánh thành phòng thực hành mạng và triển khai bảo trì hệ thống do FPT Telecom tài trợ trang thiết bị chuẩn quốc tế phục vụ công tác giảng dạy chuyên sâu cho ngành Truyền thông và Mạng máy tính.</p>
<p class="text-center"><img src="' . $theme_uri . '/assets/images/tdc-ky-ket-hop-tac.jpg" alt="Khánh thành phòng thực hành" class="aligncenter size-full" /></p>
<p>Sinh viên được trực tiếp thao tác trên các thiết bị định tuyến Router, Switch quang và hệ thống cáp mạng viễn thông thực tế của nhà mạng lớn nhất Việt Nam.</p>',
        'categories'  => array($cat_map['tin-tuc'], $cat_map['dao-tao'], $cat_map['noi-bat']),
        'author'      => 'xuanhoa',
        'image'       => 'tdc-ky-ket-hop-tac.jpg',
    ),
    array(
        'title'       => 'Đoàn – Hội Khoa Công Nghệ Thông Tin: Tổ chức thành công Giải đấu Liên quân Mobile toàn trường',
        'slug'        => 'doan-hoi-khoa-cong-nghe-thong-tin-to-chuc-thanh-cong-giai-dau-lien-quan-moblie-toan-truong',
        'date'        => '2026-03-30 15:30:00',
        'excerpt'     => 'Chuỗi hoạt động thể thao điện tử (E-Sports) sôi nổi quy tụ hơn 32 đội tuyển sinh viên tham gia tranh tài gay cấn, thể hiện tinh thần đồng đội và bản lĩnh công nghệ.',
        'content'     => '<p>Đoàn – Hội Khoa Công Nghệ Thông Tin đã tổ chức thành công Giải đấu Thể thao điện tử Liên quân Mobile toàn trường thu hút đông đảo sinh viên các khóa tham gia và cổ vũ nhiệt tình.</p>
<p class="text-center"><img src="' . $theme_uri . '/assets/images/tdc-doan-hoi-cntt.jpg" alt="Giải đấu Liên quân Mobile" class="aligncenter size-full" /></p>
<p>Sự kiện đã tạo sân chơi lành mạnh, giải trí sau những giờ học tập căng thẳng và thắt chặt tình đoàn kết giữa các chi đoàn trong khoa.</p>',
        'categories'  => array($cat_map['tin-tuc'], $cat_map['hoat-dong'], $cat_map['sinh-vien'], $cat_map['noi-bat']),
        'author'      => 'thanhhien',
        'image'       => 'tdc-doan-hoi-cntt.jpg',
    ),
    array(
        'title'       => 'Khoa Công nghệ thông tin: Chúc mừng năm mới 2026 và định hướng phát triển đào tạo nguồn nhân lực số',
        'slug'        => 'khoa-cong-nghe-thong-tin-chuc-mung-nam-moi-2026',
        'date'        => '2026-02-17 08:30:00',
        'excerpt'     => 'Nhân dịp xuân mới 2026, Ban chủ nhiệm Khoa Công nghệ thông tin gửi lời chúc mừng năm mới đến toàn thể giảng viên, sinh viên và các đối tác doanh nghiệp đồng hành cùng nhà trường.',
        'content'     => '<p>Nhân dịp xuân mới 2026, Ban chủ nhiệm Khoa Công nghệ thông tin gửi lời chúc mừng năm mới an khang thịnh vượng đến toàn thể cán bộ giảng viên, sinh viên và các đối tác doanh nghiệp.</p>
<p class="text-center"><img src="' . $theme_uri . '/assets/images/tdc-tet-2026.jpg" alt="Chúc mừng năm mới FIT-TDC" class="aligncenter size-full" /></p>
<p>Năm 2026, Khoa tiếp tục đổi mới chương trình đào tạo, mở rộng mạng lưới liên kết việc làm tại các tập đoàn công nghệ đa quốc gia.</p>',
        'categories'  => array($cat_map['tin-tuc'], $cat_map['noi-bat']),
        'author'      => 'vinhem',
        'image'       => 'tdc-tet-2026.jpg',
    ),
    array(
        'title'       => 'Tuổi trẻ TDC trao tặng công trình thanh niên: Website hỗ trợ du lịch thông minh tại xã Phước Hải',
        'slug'        => 'tuoi-tre-tdc-trao-tang-cong-trinh-thanh-nien-website-ho-tro-du-lich-thong-minh-tai-xa-phuoc-hai',
        'date'        => '2026-02-04 14:00:00',
        'excerpt'     => 'Đoàn trường và sinh viên Khoa CNTT đã chuyển giao thành công sản phẩm phần mềm du lịch thông minh góp phần thúc đẩy chuyển đổi số kinh tế địa phương.',
        'content'     => '<p>Đoàn trường Cao đẳng Công nghệ Thủ Đức và đội ngũ sinh viên tình nguyện Khoa CNTT đã chuyển giao thành công website quảng bá và bản đồ số du lịch thông minh cho đại diện chính quyền địa phương.</p>
<p class="text-center"><img src="' . $theme_uri . '/assets/images/tdc-cong-trinh-thanh-nien.jpg" alt="Công trình thanh niên số" class="aligncenter size-full" /></p>
<p>Dự án khẳng định năng lực thực chiến và trách nhiệm cống hiến vì cộng đồng của tuổi trẻ FIT-TDC.</p>',
        'categories'  => array($cat_map['tin-tuc'], $cat_map['hoat-dong'], $cat_map['sinh-vien']),
        'author'      => 'anhquy',
        'image'       => 'tdc-cong-trinh-thanh-nien.jpg',
    ),
    array(
        'title'       => 'Đoàn - Hội Khoa CNTT mang "Đại Tiệc" 3 Trong 1 Đến Ngày Hội Chào Tân Sinh Viên K25',
        'slug'        => 'doan-hoi-khoa-cntt-mang-dai-tiec-3-trong-1-den-ngay-hoi-chao-tan-sinh-vien-k25',
        'date'        => '2024-10-15 09:00:00',
        'excerpt'     => 'Không gian trải nghiệm công nghệ thực tế ảo VR, gian hàng tư vấn câu lạc bộ học thuật và âm nhạc sôi động đã chào đón các bạn tân sinh viên bước vào ngôi nhà chung FIT-TDC.',
        'content'     => '<p>Không gian trải nghiệm công nghệ thực tế ảo VR, gian hàng tư vấn câu lạc bộ học thuật và âm nhạc sôi động đã chào đón các bạn tân sinh viên bước vào ngôi nhà chung FIT-TDC.</p>
<p class="text-center"><img src="' . $theme_uri . '/assets/images/tdc-dai-tiec-viec-lam.jpg" alt="Ngày hội chào tân sinh viên" class="aligncenter size-full" /></p>
<p>Các câu lạc bộ Lập trình Web, Thiết kế Đồ họa và Mạng máy tính đã giới thiệu nhiều dự án công nghệ ấn tượng thu hút đông đảo sinh viên tham gia.</p>',
        'categories'  => array($cat_map['tin-tuc'], $cat_map['sinh-vien'], $cat_map['hoat-dong']),
        'author'      => 'dangnguyen',
        'image'       => 'tdc-dai-tiec-viec-lam.jpg',
    ),
    array(
        'title'       => 'Tuyển sinh 2026: Trở thành "người giữ thế giới số" cùng ngành Truyền thông & Mạng máy tính tại TDC',
        'slug'        => 'tuyen-sinh-2026-tro-thanh-nguoi-giu-the-gioi-so-cung-nganh-truyen-thong-mang-may-tinh-tai-tdc',
        'date'        => '2026-06-20 10:00:00',
        'excerpt'     => 'Khám phá ngành Truyền thông & Mạng máy tính - ngành học then chốt trong kỷ nguyên bảo mật dữ liệu, điện toán đám mây và kết nối IoT toàn cầu với cơ hội việc làm rộng mở.',
        'content'     => '<p>Khám phá ngành Truyền thông & Mạng máy tính - ngành học then chốt trong kỷ nguyên bảo mật dữ liệu, điện toán đám mây và kết nối IoT toàn cầu.</p>
<p class="text-center"><img src="' . $theme_uri . '/assets/images/tdc-tuyen-sinh.jpg" alt="Tuyển sinh ngành Mạng" class="aligncenter size-full" /></p>
<p>Sinh viên được trang bị kiến thức quản trị mạng doanh nghiệp, an toàn thông tin mạng và vận hành hệ thống máy chủ hiện đại.</p>',
        'categories'  => array($cat_map['tuyen-sinh'], $cat_map['dao-tao']),
        'author'      => 'xuanhoa',
        'image'       => 'tdc-tuyen-sinh.jpg',
    ),
    array(
        'title'       => 'Nghị lực phi thường của tân kỹ sư đồ họa: Khi đam mê xóa nhòa mọi khiếm khuyết thể chất',
        'slug'        => 'nghi-luc-phi-thuong-cua-tan-ky-su-do-hoa-khi-dam-me-xoa-nhoa-moi-khiem-khuyet-vat-ly',
        'date'        => '2026-06-15 11:00:00',
        'excerpt'     => 'Câu chuyện truyền cảm hứng xúc động về ý chí kiên định và tinh thần vượt khó của sinh viên ngành Thiết kế đồ họa Khoa CNTT đã đạt danh hiệu Thủ khoa tốt nghiệp loại Giỏi.',
        'content'     => '<p>Câu chuyện truyền cảm hứng xúc động về ý chí kiên định và tinh thần vượt khó của sinh viên ngành Thiết kế đồ họa Khoa CNTT đã đạt danh hiệu Thủ khoa tốt nghiệp loại Giỏi.</p>
<p class="text-center"><img src="' . $theme_uri . '/assets/images/tdc-tu-sach-hcm.jpg" alt="Sinh viên tiêu biểu FIT-TDC" class="aligncenter size-full" /></p>
<p>Thầy cô và bạn bè luôn đồng hành hỗ trợ, minh chứng cho môi trường giáo dục nhân văn và trách nhiệm tại TDC.</p>',
        'categories'  => array($cat_map['sinh-vien'], $cat_map['tin-tuc']),
        'author'      => 'thanhhien',
        'image'       => 'tdc-tu-sach-hcm.jpg',
    ),
);

foreach ($posts_data as $p) {
    $author_id = isset($member_ids[$p['author']]) ? $member_ids[$p['author']] : 1;
    $existing = get_page_by_path($p['slug'], OBJECT, 'post');

    if ($existing) {
        wp_update_post(array(
            'ID'            => $existing->ID,
            'post_title'    => $p['title'],
            'post_content'  => $p['content'],
            'post_excerpt'  => $p['excerpt'],
            'post_date'     => $p['date'],
            'post_date_gmt' => $p['date'],
            'post_author'   => $author_id,
            'post_status'   => 'publish',
        ));
        wp_set_post_categories($existing->ID, $p['categories']);
        update_post_meta($existing->ID, '_thumbnail_ext_url', $theme_uri . '/assets/images/' . $p['image']);
        echo "[UPDATED POST] {$p['title']} (Tác giả: {$p['author']})\n";
    } else {
        $new_id = wp_insert_post(array(
            'post_title'    => $p['title'],
            'post_name'     => $p['slug'],
            'post_content'  => $p['content'],
            'post_excerpt'  => $p['excerpt'],
            'post_date'     => $p['date'],
            'post_date_gmt' => $p['date'],
            'post_author'   => $author_id,
            'post_status'   => 'publish',
            'post_type'     => 'post',
            'post_category' => $p['categories'],
        ));
        update_post_meta($new_id, '_thumbnail_ext_url', $theme_uri . '/assets/images/' . $p['image']);
        echo "[CREATED POST #{$new_id}] {$p['title']} (Tác giả: {$p['author']})\n";
    }
}

echo "\n=== HOÀN TẤT CẤU HÌNH DATABASE FIT-TDC & TÀI KHOẢN NHÓM C ===\n";

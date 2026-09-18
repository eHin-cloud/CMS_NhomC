<?php
/**
 * Seeder script: Tạo dữ liệu mẫu bình luận (Comments) từ các tài khoản thành viên Nhóm C đã đăng nhập
 * Phục vụ kiểm thử giao diện bình luận lồng nhau (Bootsnipp gNVj0) và form đăng bài (Bootsnipp rNEdR)
 */

define('WP_USE_THEMES', false);
require_once __DIR__ . '/wp-load.php';

echo "=== BẮT ĐẦU SEED BÌNH LUẬN CHO CÁC TÀI KHOẢN ĐÃ LOGIN (NHÓM C) ===\n\n";

// 1. Lấy danh sách thành viên Nhóm C
$usernames = array('thanhhien', 'xuanhoa', 'vinhem', 'anhquy', 'dangnguyen', 'admin_nhomc');
$users = array();

foreach ($usernames as $uname) {
    $u = get_user_by('login', $uname);
    if ($u) {
        $users[$uname] = $u;
        echo "[TÀI KHOẢN HỢP LỆ] ID: {$u->ID} | {$u->display_name} ({$u->user_email})\n";
    }
}

if (empty($users)) {
    echo "[LỖI] Không tìm thấy tài khoản thành viên Nhóm C nào trong CSDL!\n";
    exit(1);
}

// 2. Lấy danh sách các bài viết đã xuất bản
$posts = get_posts(array(
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => 10,
    'orderby'        => 'ID',
    'order'          => 'DESC',
));

if (empty($posts)) {
    echo "[LỖI] Không có bài viết nào để thêm bình luận!\n";
    exit(1);
}

echo "\nTìm thấy " . count($posts) . " bài viết mới nhất để thêm bình luận.\n\n";

// Danh sách các bình luận mẫu tự nhiên và chuyên nghiệp
$sample_threads = array(
    array(
        'parent' => array(
            'user'    => 'xuanhoa',
            'content' => 'Thông tin bài viết rất chi tiết và kịp thời cho sinh viên Khoa Công nghệ Thông tin. Cảm ơn Ban biên tập đã đăng tải bài viết chất lượng này!',
        ),
        'reply' => array(
            'user'    => 'thanhhien',
            'content' => 'Cảm ơn bạn Xuân Hòa đã luôn theo dõi và đồng hành cùng các hoạt động phong trào học tập của Khoa CNTT TDC!',
        ),
    ),
    array(
        'parent' => array(
            'user'    => 'vinhem',
            'content' => 'Nội dung rất ý nghĩa và truyền cảm hứng mạnh mẽ đến toàn thể sinh viên. Rất mong Khoa sẽ có thêm nhiều chuyên đề và bài viết chuyên sâu như thế này trong thời gian tới.',
        ),
        'reply' => array(
            'user'    => 'dangnguyen',
            'content' => 'Chuẩn luôn anh Vinh Em ơi! Sinh viên khóa mới chúng em đang rất hào hứng và mong chờ các hoạt động sắp tới của nhóm.',
        ),
    ),
    array(
        'parent' => array(
            'user'    => 'anhquy',
            'content' => 'Đã chia sẻ bài viết này cho các bạn trong lớp cùng nắm thông tin. Rất hữu ích cho kỳ học đồ án sắp tới!',
        ),
        'reply' => array(
            'user'    => 'admin_nhomc',
            'content' => 'Cảm ơn Anh Quý và các bạn sinh viên đã tích cực chia sẻ và lan tỏa thông tin tích cực đến cộng đồng TDC.',
        ),
    ),
);

$total_seeded = 0;

foreach ($posts as $index => $post) {
    echo "--- Đang xử lý bài viết [ID: {$post->ID}] \"{$post->post_title}\" ---\n";
    
    // Chọn luồng hội thoại mẫu theo chỉ số
    $thread = $sample_threads[$index % count($sample_threads)];
    
    $parent_user = $users[$thread['parent']['user']] ?? reset($users);
    $reply_user  = $users[$thread['reply']['user']] ?? end($users);
    
    // Kiểm tra xem đã có bình luận nội dung tương tự chưa để tránh trùng lặp
    $existing_comments = get_comments(array(
        'post_id' => $post->ID,
        'search'  => substr($thread['parent']['content'], 0, 30),
    ));
    
    if (!empty($existing_comments)) {
        echo "   -> Đã có bình luận mẫu trên bài viết này, bỏ qua để tránh trùng.\n";
        continue;
    }
    
    // Thêm bình luận cha (Parent comment) từ tài khoản đã đăng nhập
    $parent_comment_data = array(
        'comment_post_ID'      => $post->ID,
        'comment_author'       => $parent_user->display_name,
        'comment_author_email' => $parent_user->user_email,
        'comment_author_url'   => $parent_user->user_url,
        'comment_content'      => $thread['parent']['content'],
        'comment_type'         => 'comment',
        'comment_parent'       => 0,
        'user_id'              => $parent_user->ID,
        'comment_date'         => current_time('mysql', 0),
        'comment_date_gmt'     => current_time('mysql', 1),
        'comment_approved'     => 1,
    );
    
    $parent_id = wp_insert_comment($parent_comment_data);
    
    if ($parent_id) {
        echo "   [+] Thêm bình luận cha #{$parent_id} bởi: {$parent_user->display_name}\n";
        $total_seeded++;
        
        // Thêm bình luận con (Reply / Khung trả lời) từ tài khoản đã đăng nhập khác
        $reply_comment_data = array(
            'comment_post_ID'      => $post->ID,
            'comment_author'       => $reply_user->display_name,
            'comment_author_email' => $reply_user->user_email,
            'comment_author_url'   => $reply_user->user_url,
            'comment_content'      => $thread['reply']['content'],
            'comment_type'         => 'comment',
            'comment_parent'       => $parent_id, // Lồng vào bình luận cha
            'user_id'              => $reply_user->ID,
            'comment_date'         => current_time('mysql', 0),
            'comment_date_gmt'     => current_time('mysql', 1),
            'comment_approved'     => 1,
        );
        
        $reply_id = wp_insert_comment($reply_comment_data);
        if ($reply_id) {
            echo "   [+] Thêm câu trả lời #{$reply_id} (Reply cha #{$parent_id}) bởi: {$reply_user->display_name}\n";
            $total_seeded++;
        }
    }
}

// Cập nhật lại số lượng comment cho toàn bộ bài viết
wp_update_comment_count_now(0);

echo "\n=== HOÀN TẤT SEEDER! ĐÃ TẠO TỔNG CỘNG {$total_seeded} BÌNH LUẬN TỪ CÁC TÀI KHOẢN ĐÃ LOGIN ===\n";

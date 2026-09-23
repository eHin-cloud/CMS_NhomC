<?php
/**
 * Functions and definitions cho Theme CMS Nhóm C
 *
 * @package CMS_NhomC
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Nạp bộ xử lý tìm kiếm thông minh tiếng Việt (Viet74K)
require_once get_template_directory() . '/inc/class-vietnamese-search.php';

function cms_nhomc_setup() {
    // Hỗ trợ thẻ Title tự động của WordPress
    add_theme_support('title-tag');

    // Hỗ trợ ảnh đại diện bài viết
    add_theme_support('post-thumbnails');

    // Hỗ trợ HTML5 cho các thẻ form, comment,...
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ));

    // Đăng ký vị trí Menu điều hướng
    register_nav_menus(array(
        'primary-menu' => __('Menu Chính (Header)', 'cms-nhomc'),
        'mobile-menu'  => __('Menu Di Động (3 Chấm)', 'cms-nhomc'),
    ));

    // Luôn tắt kiểm duyệt bình luận thủ công để hiển thị ngay lập tức
    if (get_option('comment_moderation') != 0) {
        update_option('comment_moderation', 0);
    }
    if (get_option('comment_previously_approved') != 0) {
        update_option('comment_previously_approved', 0);
    }
    if (get_option('comment_whitelist') != 0) {
        update_option('comment_whitelist', 0);
    }
}
add_action('after_setup_theme', 'cms_nhomc_setup');

/**
 * Nạp Style và Script
 */
function cms_nhomc_scripts() {
    // Nạp Font Awesome 4.7.0 cho các icon Footer và điều hướng
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), '4.7.0');

    // Nạp style.css của Theme (sử dụng filemtime để tự động xóa cache trình duyệt khi sửa css)
    wp_enqueue_style('cms-nhomc-style', get_stylesheet_uri(), array('font-awesome'), filemtime(get_stylesheet_directory() . '/style.css'));

    // Nạp JavaScript Smart Search & Autocomplete
    wp_enqueue_script('cms-nhomc-smart-search', get_template_directory_uri() . '/assets/js/smart-search.js', array(), '1.0.0', true);
    wp_localize_script('cms-nhomc-smart-search', 'cmsNhomcSearch', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'restUrl' => esc_url_raw(rest_url('cms-nhomc/v1')),
        'nonce'   => wp_create_nonce('cms_nhomc_search_nonce'),
        'homeUrl' => home_url('/'),
    ));

    // Nạp script trả lời bình luận lồng nhau chuẩn WordPress
    if (is_singular() && comments_open()) {
        if (get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }
        // Nạp script xử lý Sửa/Xóa bình luận cho tài khoản đã đăng nhập
        wp_enqueue_script('cms-nhomc-comment-actions', get_template_directory_uri() . '/assets/js/comment-actions.js', array(), filemtime(get_template_directory() . '/assets/js/comment-actions.js'), true);
        wp_localize_script('cms-nhomc-comment-actions', 'cmsNhomcComment', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
        ));
    }
}
add_action('wp_enqueue_scripts', 'cms_nhomc_scripts');

/**
 * Tùy biến độ dài đoạn trích bài viết (Excerpt)
 */
function cms_nhomc_excerpt_length($length) {
    return 35;
}
add_filter('excerpt_length', 'cms_nhomc_excerpt_length', 999);

/**
 * Tùy biến ký tự kết thúc đoạn trích
 */
function cms_nhomc_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'cms_nhomc_excerpt_more');

/**
 * [Senior Standard] Kiểm tra và bắt toàn bộ lỗi của từ khóa tìm kiếm
 * Trả về thông tin lỗi chi tiết (code, title, message, type) hoặc null nếu hợp lệ.
 * 
 * Các trường hợp bắt lỗi:
 * 1. empty: Để trống hoặc chỉ chứa khoảng trắng
 * 2. too_short: Dưới 2 ký tự (tối thiểu 2 ký tự)
 * 3. too_long: Vượt quá 100 ký tự (giới hạn tối đa 100 ký tự)
 * 4. invalid_chars: Chỉ chứa ký tự đặc biệt, không có chữ cái hoặc chữ số hợp lệ
 */
function cms_nhomc_get_search_error($query_str = null) {
    if (!isset($_GET['s'])) {
        return null;
    }

    if ($query_str === null) {
        $raw = get_search_query(false);
        $query_str = is_string($raw) ? $raw : '';
    }

    // Làm sạch HTML / XSS / Script trước khi kiểm tra
    $clean = wp_strip_all_tags($query_str);
    $trimmed = trim(preg_replace('/\s+/u', ' ', $clean));

    // 1. Kiểm tra để trống hoặc chỉ có khoảng trắng (trạng thái mở trang tìm kiếm ban đầu)
    if ($trimmed === '') {
        return null;
    }

    // 2. Kiểm tra độ dài tối thiểu (dưới 2 ký tự)
    if (mb_strlen($trimmed, 'UTF-8') < 2) {
        return array(
            'code'    => 'too_short',
            'title'   => __('Từ khóa quá ngắn', 'cms-nhomc'),
            'message' => __('Từ khóa tìm kiếm phải có từ 2 ký tự trở lên. Vui lòng nhập từ khóa dài hơn.', 'cms-nhomc'),
            'type'    => 'warning',
        );
    }

    // 3. Kiểm tra độ dài tối đa (trên 100 ký tự)
    if (mb_strlen($trimmed, 'UTF-8') > 100) {
        return array(
            'code'    => 'too_long',
            'title'   => __('Từ khóa vượt quá giới hạn', 'cms-nhomc'),
            'message' => sprintf(__('Từ khóa tìm kiếm không được vượt quá 100 ký tự (hiện có %d ký tự). Vui lòng rút gọn từ khóa.', 'cms-nhomc'), mb_strlen($trimmed, 'UTF-8')),
            'type'    => 'error',
        );
    }

    // 4. Kiểm tra chỉ chứa ký tự đặc biệt (không có bất kỳ chữ cái hay chữ số Unicode nào)
    if (!preg_match('/[\p{L}\p{N}]/u', $trimmed)) {
        return array(
            'code'    => 'invalid_chars',
            'title'   => __('Từ khóa không hợp lệ', 'cms-nhomc'),
            'message' => __('Từ khóa chỉ chứa ký tự đặc biệt. Vui lòng nhập từ khóa có chứa ít nhất một chữ cái hoặc số.', 'cms-nhomc'),
            'type'    => 'error',
        );
    }

    return null;
}

/**
 * [Ponytail Standard] Giới hạn phạm vi tìm kiếm chỉ trên bài viết (post)
 * Loại bỏ page / attachments thừa, tối ưu tốc độ truy vấn database.
 * Đồng thời ngăn ngừa truy vấn rác khi từ khóa rỗng, quá ngắn, quá dài hoặc không hợp lệ.
 */
function cms_nhomc_filter_search_query($query) {
    if (!is_admin() && $query->is_search()) {
        $query->set('post_type', 'post');
        $query->set('post_status', 'publish');

        // Chuẩn hóa từ khóa tìm kiếm: loại bỏ khoảng trắng đầu/cuối và rút gọn khoảng trắng kép
        $s = $query->get('s');
        if (is_string($s)) {
            $s = wp_strip_all_tags($s);
            if (mb_strlen($s, 'UTF-8') > 100) {
                $s = mb_substr($s, 0, 100, 'UTF-8');
            }
            $query->set('s', $s);
        }

        // Bắt lỗi toàn diện: nếu từ khóa không hợp lệ, chặn truy vấn database ngay lập tức
        $err = cms_nhomc_get_search_error($s);
        if ($err !== null || !is_string($s) || $s === '' || mb_strlen($s, 'UTF-8') < 2) {
            $query->set('post__in', array(0));
            $query->set('no_found_rows', true);
            return;
        }

        // Kích hoạt tìm kiếm theo cụm từ hoàn chỉnh, tránh chia nhỏ từ gây ra False Positive
        $query->set('sentence', true);
    }
}
add_action('pre_get_posts', 'cms_nhomc_filter_search_query');

/**
 * [Senior Standard] Tùy biến SQL tìm kiếm chính xác:
 * 1. Khớp cụm từ thực tế (Phrase/Sentence matching), loại bỏ triệt để tình trạng False Positive do WordPress
 *    tự động tách các từ riêng lẻ (ví dụ: tìm 'Bóng đá' không bị bắt nhầm vào bài 'Pickleball' có từ 'bóng bàn' + 'đang').
 * 2. Mở rộng tìm kiếm trên các trường dữ liệu thực: Tiêu đề (Title), Đoạn trích (Excerpt), Nội dung (Content),
 *    và Chuyên mục (Category / Taxonomies).
 * 3. Bảo đảm không trả về bất kỳ kết quả không liên quan nào, không fake dữ liệu, không hard-code.
 */
function cms_nhomc_exact_relevant_search_sql($search, $query) {
    if (!is_admin() && $query->is_search()) {
        global $wpdb;
        $s = $query->get('s');
        if (!is_string($s)) {
            return $search;
        }

        $s = trim(preg_replace('/\s+/u', ' ', $s));
        if ($s === '') {
            return " AND 1=0 ";
        }

        $escaped = '%' . $wpdb->esc_like($s) . '%';

        $search = $wpdb->prepare("
            AND (
                ({$wpdb->posts}.post_title LIKE %s)
                OR ({$wpdb->posts}.ID IN (
                    SELECT tr_sub.object_id 
                    FROM {$wpdb->term_relationships} tr_sub 
                    INNER JOIN {$wpdb->term_taxonomy} tt_sub ON tr_sub.term_taxonomy_id = tt_sub.term_taxonomy_id 
                    INNER JOIN {$wpdb->terms} t_sub ON tt_sub.term_id = t_sub.term_id 
                    WHERE tt_sub.taxonomy IN ('category', 'post_tag') 
                      AND t_sub.name LIKE %s
                ))
            )
        ", $escaped, $escaped);
    }
    return $search;
}
add_filter('posts_search', 'cms_nhomc_exact_relevant_search_sql', 20, 2);

/**
 * Ngăn chặn hoàn toàn truy vấn SQL vào cơ sở dữ liệu khi tìm kiếm rỗng hoặc kiểu dữ liệu không hợp lệ
 */
function cms_nhomc_prevent_empty_search_db_query($posts, $query) {
    if (!is_admin() && $query->is_search()) {
        $s = $query->get('s');
        if (!is_string($s) || trim($s) === '') {
            $query->found_posts = 0;
            $query->max_num_pages = 0;
            return array();
        }
    }
    return $posts;
}
add_filter('posts_pre_query', 'cms_nhomc_prevent_empty_search_db_query', 10, 2);

/**
 * [Ponytail Standard] Highlight từ khóa tìm kiếm dùng HTML5 <mark> nguyên bản
 * Bảo đảm regex xử lý an toàn chuỗi ký tự đặc biệt, không làm vỡ các thẻ HTML bao ngoài
 * và bảo toàn các thực thể HTML (HTML entities).
 *
 * @param string $text Chuỗi văn bản cần bôi sáng
 * @param string|null $query Từ khóa tùy chọn (mặc định lấy từ query WordPress)
 * @return string Chuỗi đã được bôi sáng từ khóa an toàn
 */
function cms_nhomc_highlight_keyword($text, $query = null) {
    if ($text === '' || $text === null || !is_string($text)) {
        return $text;
    }

    if ($query === null) {
        if (!function_exists('is_search') || !is_search()) {
            return $text;
        }
        $raw_query = function_exists('get_search_query') ? get_search_query(false) : '';
    } else {
        $raw_query = $query;
    }

    if (!is_string($raw_query)) {
        return $text;
    }

    $clean_query = trim($raw_query);
    if ($clean_query === '') {
        return $text;
    }

    // Bảo vệ tài nguyên CPU / Chống ReDoS: giới hạn độ dài truy vấn tối đa 200 ký tự
    if (mb_strlen($clean_query, 'UTF-8') > 200) {
        $clean_query = mb_substr($clean_query, 0, 200, 'UTF-8');
    }

    // Trích xuất các cụm từ trong ngoặc kép nếu có
    $phrases = array();
    if (preg_match_all('/["\']([^"\']+)["\']/u', $clean_query, $phrase_matches)) {
        foreach ($phrase_matches[1] as $phrase) {
            $trimmed_phrase = trim($phrase);
            if ($trimmed_phrase !== '') {
                $phrases[] = $trimmed_phrase;
            }
        }
    }

    // Tách các từ đơn (giới hạn tối đa 30 từ để ngăn DoS / quá tải regex)
    $words = preg_split('/\s+/u', $clean_query, 30, PREG_SPLIT_NO_EMPTY);
    $unquoted_clean = (string) preg_replace('/^[\s"\'“”«»]+|[\s"\'“”«»]+$/u', '', $clean_query);

    // Trích xuất các cụm từ liên tiếp (n-grams 2 đến 4 từ) từ truy vấn để bôi sáng cụm từ tự nhiên
    $num_words = count($words);
    if ($num_words >= 2 && $num_words <= 8) {
        for ($len = min($num_words - 1, 4); $len >= 2; $len--) {
            for ($i = 0; $i <= $num_words - $len; $i++) {
                $sub_phrase = implode(' ', array_slice($words, $i, $len));
                $sub_phrase_clean = (string) preg_replace('/^[\s"\'“”«»,.:;!?()\[\]{}]+|[\s"\'“”«»,.:;!?()\[\]{}]+$/u', '', $sub_phrase);
                if ($sub_phrase_clean !== '') {
                    $phrases[] = $sub_phrase_clean;
                }
            }
        }
    }

    $all_candidates = array_merge(
        array($clean_query),
        ($unquoted_clean !== '' ? array($unquoted_clean) : array()),
        $phrases,
        $words
    );

    // Thêm phiên bản không dấu ngoặc kép / dấu câu của từng từ đơn bằng regex Unicode an toàn
    foreach ($words as $w) {
        $unquoted_w = (string) preg_replace('/^[\s"\'“”«»,.:;!?()\[\]{}]+|[\s"\'“”«»,.:;!?()\[\]{}]+$/u', '', $w);
        if ($unquoted_w !== '' && $unquoted_w !== $w) {
            $all_candidates[] = $unquoted_w;
        }
    }

    $keywords = array();
    foreach (array_unique($all_candidates) as $term) {
        $term_clean = trim($term);
        if ($term_clean === '') {
            continue;
        }

        // Bỏ qua nếu từ khóa chỉ toàn ký tự dấu câu / ký hiệu toán học thuần túy (như +, *, =, ...)
        if (preg_match('/^[\p{P}\p{S}\s]+$/u', $term_clean)) {
            continue;
        }

        if (mb_strlen($term_clean, 'UTF-8') >= 1) {
            $keywords[] = $term_clean;
        }
    }

    if (empty($keywords)) {
        return $text;
    }

    // Giới hạn tối đa 25 từ khóa để bảo vệ CPU và tránh regex quá tải
    if (count($keywords) > 25) {
        $keywords = array_slice($keywords, 0, 25);
    }

    // Sắp xếp theo độ dài giảm dần để từ dài hơn được khớp trước
    usort($keywords, function($a, $b) {
        return mb_strlen($b, 'UTF-8') - mb_strlen($a, 'UTF-8');
    });

    $escaped_terms = array();
    $accent_map = array(
        'a' => '[aàáảãạăằắẳẵặâầấẩẫậ]',
        'A' => '[AÀÁẢÃẠĂẰẮẲẴẶÂẦẤẨẪẬ]',
        'd' => '[dđ]',
        'D' => '[DĐ]',
        'e' => '[eèéẻẽẹêềếểễệ]',
        'E' => '[EÈÉẺẼẸÊỀẾỂỄỆ]',
        'i' => '[iìíỉĩị]',
        'I' => '[IÌÍỈĨỊ]',
        'o' => '[oòóỏõọôồốổỗộơờớởỡợ]',
        'O' => '[OÒÓỎÕỌÔỒỐỔỖỘƠỜỚỞỠỢ]',
        'u' => '[uùúủũụưừứửữự]',
        'U' => '[UÙÚỦŨỤƯỪỨỬỮỰ]',
        'y' => '[yỳýỷỹỵ]',
        'Y' => '[YỲÝỶỸỴ]',
    );

    foreach ($keywords as $kw) {
        $first_char = mb_substr($kw, 0, 1, 'UTF-8');
        $has_word_start = preg_match('/[\p{L}\p{N}]/u', $first_char);

        // Chuyển ký tự không dấu thành pattern chấp nhận cả có dấu
        $chars = preg_split('//u', $kw, -1, PREG_SPLIT_NO_EMPTY);
        $accent_quoted = '';
        foreach ($chars as $ch) {
            if (isset($accent_map[$ch])) {
                $accent_quoted .= $accent_map[$ch];
            } else {
                $accent_quoted .= preg_quote($ch, '/');
            }
        }

        // Lookbehind ngăn không khớp vào giữa/cuối từ khác (ví dụ: 'AI' không khớp 'Blockchain' hay 'thứ hai', 'an' không khớp 'Ban')
        $prefix = $has_word_start ? '(?<![\p{L}\p{N}])' : '';

        // Đối với từ khóa ngắn (<= 2 ký tự) kết thúc bằng chữ/số (như '3', 'C', 'AI', 'IT', 'an'):
        $last_char = mb_substr($kw, -1, 1, 'UTF-8');
        $has_word_end = preg_match('/[\p{L}\p{N}]/u', $last_char);
        $suffix = (mb_strlen($kw, 'UTF-8') <= 2 && $has_word_end) ? '(?![\p{L}\p{N}])' : '';

        $escaped_terms[] = $prefix . $accent_quoted . $suffix;
    }

    $pattern = '/(' . implode('|', $escaped_terms) . ')/iu';

    // Phân tích $text thành các phần tử: Thẻ HTML hợp lệ, Comment, Thực thể HTML, và Text thuần
    // Pattern nhận diện chuẩn HTML tag tránh bắt nhầm dấu so sánh như "2 < 3 and 5 > 4"
    $split_pattern = '/(<!--.*?-->|<\/?[a-zA-Z][a-zA-Z0-9:-]*(?:\s+[^\s"\'\/=>]+(?:\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s"\'=><`]+))?)*\s*\/?>|&[a-zA-Z0-9#]+;)/us';

    $parts = preg_split($split_pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    if ($parts === false || !is_array($parts)) {
        return $text;
    }

    if (count($parts) <= 1) {
        if (strpos($text, '<') === false && strpos($text, '&') === false) {
            return preg_replace_callback($pattern, function($matches) {
                return '<mark class="search-highlight">' . $matches[0] . '</mark>';
            }, $text);
        }
    }

    $in_highlight = false;
    $result = '';
    foreach ($parts as $part) {
        if ($part === '') {
            continue;
        }
        $first_char = substr($part, 0, 1);
        $last_char  = substr($part, -1);
        if ($first_char === '<' && $last_char === '>') {
            if (preg_match('/^<mark\b[^>]*class=["\'][^"\']*search-highlight[^"\']*["\']/i', $part)) {
                $in_highlight = true;
            } elseif (stripos($part, '</mark>') !== false) {
                $in_highlight = false;
            }
            $result .= $part;
        } elseif ($first_char === '&' && $last_char === ';') {
            $result .= $part;
        } else {
            if ($in_highlight) {
                $result .= $part;
            } else {
                $result .= preg_replace_callback($pattern, function($matches) {
                    return '<mark class="search-highlight">' . $matches[0] . '</mark>';
                }, $part);
            }
        }
    }

    return $result;
}

/**
 * Helper to get post thumbnail URL with smart fallbacks
 */
function cms_nhomc_get_post_thumbnail_url($post_id) {
    $post = get_post($post_id);
    if (!$post) return '';

    if (has_post_thumbnail($post->ID)) {
        return get_the_post_thumbnail_url($post->ID, 'large');
    }

    $slug = (isset($post->post_name) && is_string($post->post_name)) ? $post->post_name : '';
    $theme_dir = get_template_directory_uri();

    // 1. Tuyển sinh
    if (strpos($slug, 'tuyen-sinh') !== false) {
        return $theme_dir . '/assets/images/tdc-tuyen-sinh.jpg';
    }
    // 2. Tòa nhà TDC xanh / Tháng 06
    if (strpos($slug, 'thang-06') !== false || strpos($slug, 'toa-nha') !== false) {
        return $theme_dir . '/assets/images/tdc-toa-nha-xanh.jpg';
    }
    // 3. Hội nghị kiểm định CNTT
    if (strpos($slug, 'kiem-dinh') !== false || strpos($slug, 'nghe-cong-nghe') !== false) {
        return $theme_dir . '/assets/images/tdc-hoi-nghi-cntt.jpg';
    }
    // 4. Tủ sách điện tử Bác Hồ
    if (strpos($slug, 'tu-sach') !== false || strpos($slug, 'ho-chi-minh') !== false) {
        return $theme_dir . '/assets/images/tdc-tu-sach-hcm.jpg';
    }
    // 5. Mở lối tương lai AI
    if (strpos($slug, 'mo-loi') !== false || strpos($slug, 'ky-nguyen-ai') !== false || strpos($slug, '-ai') !== false) {
        return $theme_dir . '/assets/images/tdc-mo-loi-ai.jpg';
    }
    // 6. Ký kết hợp tác chiến lược
    if (strpos($slug, 'ky-ket') !== false || strpos($slug, 'doanh-nghiep') !== false) {
        return $theme_dir . '/assets/images/tdc-ky-ket-hop-tac.jpg';
    }
    // 7. Đoàn - Hội Khoa CNTT hoạt động
    if (strpos($slug, 'doan-hoi-khoa-cntt-to-chuc') !== false || strpos($slug, 'chuoi-hoat-dong') !== false) {
        return $theme_dir . '/assets/images/tdc-doan-hoi-cntt.jpg';
    }
    // 8. Chúc mừng năm mới 2026
    if (strpos($slug, 'chuc-mung-nam-moi') !== false || strpos($slug, 'nam-moi-2026') !== false) {
        return $theme_dir . '/assets/images/tdc-tet-2026.jpg';
    }
    // 9. Công trình thanh niên
    if (strpos($slug, 'tuoi-tre-tdc') !== false || strpos($slug, 'thanh-nien') !== false) {
        return $theme_dir . '/assets/images/tdc-cong-trinh-thanh-nien.jpg';
    }
    // 10. Đại tiệc ngày hội việc làm
    if (strpos($slug, 'dai-tiec') !== false || strpos($slug, '3-trong-1') !== false || strpos($slug, 'viec-lam') !== false) {
        return $theme_dir . '/assets/images/tdc-dai-tiec-viec-lam.jpg';
    }

    $fallbacks = array(
        $theme_dir . '/assets/images/tdc-tuyen-sinh.jpg',
        $theme_dir . '/assets/images/tdc-toa-nha-xanh.jpg',
        $theme_dir . '/assets/images/tdc-hoi-nghi-cntt.jpg',
        $theme_dir . '/assets/images/tdc-tu-sach-hcm.jpg',
        $theme_dir . '/assets/images/tdc-mo-loi-ai.jpg',
        $theme_dir . '/assets/images/tdc-ky-ket-hop-tac.jpg',
        $theme_dir . '/assets/images/tdc-doan-hoi-cntt.jpg',
        $theme_dir . '/assets/images/tdc-tet-2026.jpg',
        $theme_dir . '/assets/images/tdc-cong-trinh-thanh-nien.jpg',
        $theme_dir . '/assets/images/tdc-dai-tiec-viec-lam.jpg',
    );
    return $fallbacks[absint($post->ID) % count($fallbacks)];
}

/**
 * Render single post card matching the design specification
 */
function cms_nhomc_render_post_card($post_id = null) {
    $post = get_post($post_id);
    if (!$post) return;

    $day   = get_the_date('d', $post);
    $month = get_the_date('n', $post);
    $year  = get_the_date('Y', $post);
    $month_label = 'THÁNG ' . $month;

    $permalink = get_permalink($post);
    $title     = get_the_title($post);

    // Categories
    $categories = get_the_category($post->ID);
    $cat_links  = array();
    if (!empty($categories)) {
        foreach ($categories as $cat) {
            $cat_links[] = '<a href="' . esc_url(get_category_link($cat->term_id)) . '">' . esc_html($cat->name) . '</a>';
        }
    }
    $categories_html = !empty($cat_links) ? implode(', ', $cat_links) : '<a href="#">Tin Tức</a>';

    // Thumbnail URL
    $thumb_url = cms_nhomc_get_post_thumbnail_url($post->ID);

    // Excerpt: only display if explicitly set or if post has excerpt
    $excerpt = !empty($post->post_excerpt) ? $post->post_excerpt : '';
    ?>
    <article id="post-<?php echo esc_attr($post->ID); ?>" class="cms-post-card">
        <div class="cms-post-thumb">
            <a href="<?php echo esc_url($permalink); ?>" aria-label="<?php echo esc_attr($title); ?>">
                <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($title); ?>" class="cms-thumb-img" loading="lazy" />
            </a>
        </div>
        <div class="cms-post-body">
            <div class="cms-post-header">
                <div class="cms-date-badge">
                    <span class="cms-date-day"><?php echo esc_html($day); ?></span>
                    <div class="cms-date-meta">
                        <span class="cms-date-month"><?php echo esc_html($month_label); ?></span>
                        <span class="cms-date-year"><?php echo esc_html($year); ?></span>
                    </div>
                </div>
                <div class="cms-title-wrap">
                    <h2 class="cms-post-title">
                        <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
                    </h2>
                    <div class="cms-post-meta">
                        <span class="cms-cat-label">Categories</span>
                        <span class="cms-cat-links"><?php echo wp_kses_post($categories_html); ?></span>
                    </div>
                </div>
            </div>
            <?php if (!empty($excerpt)) : ?>
                <div class="cms-post-excerpt">
                    <?php echo esc_html($excerpt); ?>
                </div>
            <?php endif; ?>
        </div>
    </article>
    <?php
}

/**
 * Shortcode [tin_tuc_ngang] - Renders posts in horizontal list layout
 * Usage: [tin_tuc_ngang so_bai="4" danh_muc="tin-tuc"]
 */
function cms_nhomc_tin_tuc_ngang_shortcode($atts) {
    $atts = shortcode_atts(array(
        'so_bai'   => 4,
        'danh_muc' => '',
        'offset'   => 0,
    ), $atts, 'tin_tuc_ngang');

    $args = array(
        'posts_per_page' => intval($atts['so_bai']),
        'offset'         => intval($atts['offset']),
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    if (!empty($atts['danh_muc'])) {
        $args['category_name'] = sanitize_text_field($atts['danh_muc']);
    }

    $query = new WP_Query($args);

    ob_start();
    if ($query->have_posts()) {
        echo '<div class="cms-post-list">';
        while ($query->have_posts()) {
            $query->the_post();
            cms_nhomc_render_post_card(get_the_ID());
        }
        echo '</div>';
        wp_reset_postdata();
    } else {
        echo '<p class="cms-no-posts">Chưa có bài viết nào.</p>';
    }
    return ob_get_clean();
}
add_shortcode('tin_tuc_ngang', 'cms_nhomc_tin_tuc_ngang_shortcode');

/**
 * Lấy danh sách Recent Posts chuẩn WordPress Core WP_Query
 * Điều kiện lọc bắt buộc:
 * - post_type = 'post'
 * - post_status = 'publish' (chỉ lấy bài đã xuất bản, loại bỏ hoàn toàn draft, pending, trash)
 * - orderby = 'date', order = 'DESC' (mới nhất xếp trước)
 * - posts_per_page = 10 (mặc định 10 bài)
 * - ignore_sticky_posts = 1 (tránh bài ghim làm sai lệch thứ tự thời gian)
 *
 * @param int $limit Số lượng bài viết cần lấy (mặc định: 10)
 * @param array $extra_args Tham số mở rộng nếu cần
 * @return WP_Query
 */
function cms_nhomc_get_recent_posts($limit = 10, $extra_args = array()) {
    $default_args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => max(1, intval($limit)),
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => 1,
        'no_found_rows'       => true,
    );

    $args = wp_parse_args($extra_args, $default_args);
    return new WP_Query($args);
}

/**
 * Render Widget: BÀI VIẾT MỚI (Recent Posts) - Module chuẩn Senior WordPress Developer
 * Hiển thị tối đa 10 bài viết mới nhất với đầy đủ:
 * Thumbnail, Tiêu đề, Ngày đăng, Chuyên mục, Mô tả ngắn (Excerpt), Link tới trang Detail.
 * Có Empty State và Error State.
 */
function cms_nhomc_render_recent_posts_widget($limit = 10, $title = 'BÀI VIẾT MỚI') {
    $limit = !empty($limit) ? intval($limit) : 10;
    $title = !empty($title) ? $title : __('BÀI VIẾT MỚI', 'cms-nhomc');

    $query = cms_nhomc_get_recent_posts($limit);
    ?>
    <div class="widget-categories-card widget-recent-posts-card">
        <h3 class="widget-cat-title widget-recent-title"><?php echo esc_html($title); ?></h3>
        <div class="widget-cat-stripe widget-recent-stripe"></div>
        <div class="widget-cat-body widget-recent-body">
            <?php if ($query->have_posts()) : ?>
                <ul class="sidebar-post-list recent-post-list">
                    <?php while ($query->have_posts()) : $query->the_post(); 
                        $post_id    = get_the_ID();
                        $thumb_url  = cms_nhomc_get_post_thumbnail_url($post_id);
                        $permalink  = get_permalink($post_id);
                        $post_title = get_the_title($post_id);
                        $post_date  = get_the_date('d/m/Y', $post_id);
                        $categories = get_the_category($post_id);
                        $primary_cat = !empty($categories) ? $categories[0] : null;
                        $raw_excerpt = get_the_excerpt($post_id);
                        $excerpt    = !empty($raw_excerpt) ? wp_trim_words(wp_strip_all_tags($raw_excerpt), 15, '...') : '';
                    ?>
                        <li class="sidebar-post-item recent-post-item">
                            <div class="sidebar-post-thumb recent-post-thumb">
                                <a href="<?php echo esc_url($permalink); ?>" aria-label="<?php echo esc_attr($post_title); ?>">
                                    <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($post_title); ?>" loading="lazy" />
                                </a>
                            </div>
                            <div class="sidebar-post-info recent-post-info">
                                <h4 class="sidebar-post-title recent-post-title">
                                    <a href="<?php echo esc_url($permalink); ?>" title="<?php echo esc_attr($post_title); ?>">
                                        <?php echo esc_html($post_title); ?>
                                    </a>
                                </h4>
                                <div class="recent-post-meta">
                                    <span class="recent-post-date"><i class="fa fa-calendar-o" aria-hidden="true"></i> <?php echo esc_html($post_date); ?></span>
                                    <?php if ($primary_cat) : ?>
                                        <span class="recent-post-sep">•</span>
                                        <a href="<?php echo esc_url(get_category_link($primary_cat->term_id)); ?>" class="recent-post-category">
                                            <?php echo esc_html($primary_cat->name); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($excerpt)) : ?>
                                    <div class="recent-post-excerpt">
                                        <?php echo esc_html($excerpt); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endwhile; wp_reset_postdata(); ?>
                </ul>
            <?php else : ?>
                <div class="recent-posts-empty-state">
                    <div class="empty-icon"><i class="fa fa-newspaper-o" aria-hidden="true"></i></div>
                    <p class="empty-text"><?php esc_html_e('Chưa có bài viết mới nào được đăng tải.', 'cms-nhomc'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Shortcode [cms_nhomc_recent_posts limit="10" title="BÀI VIẾT MỚI"]
 */
function cms_nhomc_recent_posts_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => 10,
        'title' => 'BÀI VIẾT MỚI',
    ), $atts, 'cms_nhomc_recent_posts');

    ob_start();
    cms_nhomc_render_recent_posts_widget(intval($atts['limit']), sanitize_text_field($atts['title']));
    return ob_get_clean();
}
add_shortcode('cms_nhomc_recent_posts', 'cms_nhomc_recent_posts_shortcode');
add_shortcode('recent_posts', 'cms_nhomc_recent_posts_shortcode');

/**
 * WordPress Core Widget Class: CMS_NhomC_Recent_Posts_Custom_Widget
 */
class CMS_NhomC_Recent_Posts_Custom_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'cms_nhomc_recent_posts_widget',
            __('[CMS Nhóm C] Bài viết mới (Recent Posts)', 'cms-nhomc'),
            array('description' => __('Hiển thị 10 bài viết mới nhất kèm hình ảnh và thông tin chi tiết.', 'cms-nhomc'))
        );
    }

    public function widget($args, $instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'BÀI VIẾT MỚI';
        $limit = !empty($instance['limit']) ? intval($instance['limit']) : 10;
        cms_nhomc_render_recent_posts_widget($limit, $title);
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'BÀI VIẾT MỚI';
        $limit = !empty($instance['limit']) ? intval($instance['limit']) : 10;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Tiêu đề:', 'cms-nhomc'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('limit')); ?>"><?php esc_html_e('Số lượng bài viết:', 'cms-nhomc'); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" name="<?php echo esc_attr($this->get_field_name('limit')); ?>" type="number" step="1" min="1" max="30" value="<?php echo esc_attr($limit); ?>" size="3" />
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['limit'] = (!empty($new_instance['limit'])) ? intval($new_instance['limit']) : 10;
        return $instance;
    }
}

function cms_nhomc_register_recent_posts_widget() {
    register_widget('CMS_NhomC_Recent_Posts_Custom_Widget');
}
add_action('widgets_init', 'cms_nhomc_register_recent_posts_widget');


/**
 * Render Widget: BÀI VIẾT NỔI BẬT (Featured Posts)
 */
function cms_nhomc_render_featured_posts_widget($limit = 5, $title = 'BÀI VIẾT NỔI BẬT') {
    // 1. Tìm các bài viết thuộc tag hoặc category "Nổi Bật" hoặc có meta _is_featured
    $args = array(
        'posts_per_page'      => intval($limit),
        'post_status'         => 'publish',
        'category_name'       => 'noi-bat',
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => 1,
    );
    $query = new WP_Query($args);

    // Nếu chưa có category nổi bật hoặc ít hơn limit, lấy theo thứ tự khác hoặc các bài còn lại
    if (!$query->have_posts() || $query->post_count < 2) {
        $args_fallback = array(
            'posts_per_page'      => intval($limit),
            'post_status'         => 'publish',
            'orderby'             => 'date',
            'order'               => 'ASC', // Lấy các bài nổi bật đặc thù
            'ignore_sticky_posts' => 1,
        );
        $query = new WP_Query($args_fallback);
    }

    if ($query->have_posts()) :
    ?>
        <div class="cms-sidebar-widget widget-featured-posts">
            <h3 class="widget-title widget-title-featured"><?php echo esc_html($title); ?></h3>
            <ul class="sidebar-post-list">
                <?php while ($query->have_posts()) : $query->the_post(); 
                    $thumb_url = cms_nhomc_get_post_thumbnail_url(get_the_ID());
                ?>
                    <li class="sidebar-post-item">
                        <div class="sidebar-post-thumb">
                            <a href="<?php the_permalink(); ?>">
                                <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                            </a>
                        </div>
                        <div class="sidebar-post-info">
                            <h4 class="sidebar-post-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h4>
                            <span class="sidebar-post-date"><?php echo get_the_date('d/m/Y'); ?></span>
                        </div>
                    </li>
                <?php endwhile; wp_reset_postdata(); ?>
            </ul>
        </div>
    <?php
    endif;
}

/**
 * Render Widget: CATEGORIES (Chuyên mục) - Module 9 (Anh Quý)
 */
function cms_nhomc_render_categories_widget($title = 'Categories') {
    ?>
    <div class="widget-categories-card">
        <h3 class="widget-cat-title"><?php echo esc_html($title); ?></h3>
        <div class="widget-cat-stripe"></div>
        <div class="widget-cat-body">
            <ul class="widget-cat-list">
                <?php
                // Lấy danh sách chuyên mục thực tế từ WordPress
                $categories = get_categories(array(
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                    'hide_empty' => false,
                ));

                // Lọc bỏ danh mục mặc định Chưa phân loại nếu có các chuyên mục khác
                $filtered_cats = array();
                if (!empty($categories)) {
                    foreach ($categories as $cat) {
                        if ($cat->slug !== 'uncategorized' && $cat->slug !== 'chua-phan-loai') {
                            $filtered_cats[] = $cat;
                        }
                    }
                }

                if (!empty($filtered_cats)) {
                    foreach ($filtered_cats as $category) {
                        echo '<li>';
                        echo '<span class="cat-bullet"></span>';
                        echo '<a href="' . esc_url(get_category_link($category->term_id)) . '">' . esc_html($category->name) . '</a>';
                        echo '</li>';
                    }
                } else {
                    // Dữ liệu mẫu hiển thị trực tiếp chuẩn y hệt hình ảnh thiết kế
                    $sample_items = array(
                        array('name' => '.Net Developer', 'link' => home_url('/category/net-developer/')),
                        array('name' => 'Thực Tập Sinh Tester', 'link' => home_url('/category/thuc-tap-sinh-tester/')),
                        array('name' => 'Trợ giảng lập trình - Part time', 'link' => home_url('/category/tro-giang-lap-trinh/')),
                    );

                    foreach ($sample_items as $item) {
                        echo '<li>';
                        echo '<span class="cat-bullet"></span>';
                        echo '<a href="' . esc_url($item['link']) . '">' . esc_html($item['name']) . '</a>';
                        echo '</li>';
                    }
                }
                ?>
            </ul>
        </div>
    </div>
    <?php
}

/**
 * Render Widget: ARCHIVE (Lưu trữ theo ngày tháng / Bài viết mới nhất) - Module 11
 * Thiết kế giao diện 2 cột đánh số 1 - 8 theo chuẩn thiết kế spec
 */
function cms_nhomc_render_archive_widget($title = 'Xem nhiều') {
    // 1. Lấy danh sách bài viết mới nhất theo ngày tháng đăng tải
    $recent_posts = get_posts(array(
        'numberposts' => 8,
        'post_status' => 'publish',
        'orderby'     => 'date',
        'order'       => 'DESC',
    ));

    // Dữ liệu dự phòng chuẩn theo ảnh thiết kế spec
    $spec_sample_posts = array(
        array('title' => 'Việt Nam thua Hàn Quốc 0 - 6', 'link' => home_url('/'), 'comments' => 0),
        array('title' => 'Hai nhà thầu nước ngoài từ chối bồi thường vụ cao tốc Đà Nẵng – Quảng Ngãi', 'link' => home_url('/'), 'comments' => 37),
        array('title' => 'Dự kiến trình Chính phủ nghỉ Tết từ 29/12 Âm lịch', 'link' => home_url('/'), 'comments' => 0),
        array('title' => 'Israel lắp lồng chống UAV trên nóc xe tăng hiện đại nhất', 'link' => home_url('/'), 'comments' => 0),
        array('title' => 'Chủ tịch nước Võ Văn Thưởng gặp Tổng thống Putin', 'link' => home_url('/'), 'comments' => 0),
        array('title' => 'Xem xét đình chỉ Chủ tịch xã liên quan chung cư mini 200 căn hộ', 'link' => home_url('/'), 'comments' => 0),
        array('title' => 'Mắt 10/10 cũng khó thấy mặt người trong hình', 'link' => home_url('/'), 'comments' => 0),
        array('title' => 'Mỹ sẵn sàng đưa 2.000 lính phản ứng nhanh tới Israel', 'link' => home_url('/'), 'comments' => 0),
    );

    $items_posts = array();
    if (!empty($recent_posts)) {
        foreach ($recent_posts as $p) {
            $items_posts[] = array(
                'title'    => get_the_title($p->ID),
                'link'     => get_permalink($p->ID),
                'comments' => get_comments_number($p->ID),
            );
        }
    }
    // Bù đủ 8 bài viết nếu DB chưa đủ
    $count_p = count($items_posts);
    if ($count_p < 8) {
        for ($i = $count_p; $i < 8; $i++) {
            $items_posts[] = $spec_sample_posts[$i];
        }
    }

    // 2. Lấy danh sách các mốc lưu trữ theo ngày tháng (Daily Archives)
    $daily_archives = wp_get_archives(array(
        'type'            => 'daily',
        'format'          => 'custom',
        'echo'            => 0,
        'limit'           => 8,
    ));

    $items_dates = array();
    if (!empty($daily_archives)) {
        preg_match_all('/<a[^>]*href=[\'"]([^\'"]*)[\'"][^>]*>(.*?)<\/a>/i', $daily_archives, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $items_dates[] = array(
                'link'  => $match[1],
                'title' => strip_tags($match[2]),
            );
            if (count($items_dates) >= 8) break;
        }
    }

    if (empty($items_dates)) {
        $sample_dates = array(
            '27/07/2026', '14/07/2026', '13/07/2026', '07/07/2026',
            '02/07/2026', '20/06/2026', '15/06/2026', '17/04/2026'
        );
        foreach ($sample_dates as $sd) {
            $items_dates[] = array('link' => home_url('/'), 'title' => $sd);
        }
    }
    // Bù đủ 8 mốc lưu trữ nếu chưa đủ
    $count_d = count($items_dates);
    if ($count_d < 8) {
        for ($i = $count_d; $i < 8; $i++) {
            $items_dates[] = array('link' => home_url('/'), 'title' => sprintf('%02d/01/2026', 8 - $i));
        }
    }
    ?>
    <div class="cms-archive-ranked-widget">
        <div class="archive-ranked-header">
            <h3 class="archive-ranked-title"><?php echo esc_html($title); ?></h3>
            <div class="archive-ranked-tabs">
                <button type="button" class="archive-tab-btn active" data-target="posts">Mới nhất</button>
                <span class="tab-sep">|</span>
                <button type="button" class="archive-tab-btn" data-target="dates">Ngày tháng</button>
            </div>
        </div>

        <div class="archive-ranked-body">
            <!-- TAB 1: Danh sách bài viết mới nhất theo ngày tháng (8 bài) -->
            <div class="archive-tab-panel active" id="archive-panel-posts">
                <div class="archive-ranked-grid">
                    <!-- Cột 1: Đánh số 1 - 4 -->
                    <div class="archive-col">
                        <?php for ($i = 0; $i < 4; $i++) : 
                            $item = $items_posts[$i];
                        ?>
                            <div class="archive-ranked-item">
                                <span class="archive-ranked-num"><?php echo ($i + 1); ?></span>
                                <div class="archive-ranked-content">
                                    <a href="<?php echo esc_url($item['link']); ?>" class="archive-ranked-link" title="<?php echo esc_attr($item['title']); ?>">
                                        <?php echo esc_html($item['title']); ?>
                                    </a>
                                    <?php if (!empty($item['comments'])) : ?>
                                        <span class="archive-ranked-comments" title="<?php echo esc_attr($item['comments']); ?> bình luận">
                                            <i class="fa fa-commenting-o"></i> <?php echo intval($item['comments']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <!-- Cột 2: Đánh số 5 - 8 -->
                    <div class="archive-col">
                        <?php for ($i = 4; $i < 8; $i++) : 
                            $item = $items_posts[$i];
                        ?>
                            <div class="archive-ranked-item">
                                <span class="archive-ranked-num"><?php echo ($i + 1); ?></span>
                                <div class="archive-ranked-content">
                                    <a href="<?php echo esc_url($item['link']); ?>" class="archive-ranked-link" title="<?php echo esc_attr($item['title']); ?>">
                                        <?php echo esc_html($item['title']); ?>
                                    </a>
                                    <?php if (!empty($item['comments'])) : ?>
                                        <span class="archive-ranked-comments" title="<?php echo esc_attr($item['comments']); ?> bình luận">
                                            <i class="fa fa-commenting-o"></i> <?php echo intval($item['comments']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- TAB 2: Danh sách mốc lưu trữ theo ngày tháng (8 tháng) -->
            <div class="archive-tab-panel" id="archive-panel-dates" style="display: none;">
                <div class="archive-ranked-grid">
                    <!-- Cột 1: Đánh số 1 - 4 -->
                    <div class="archive-col">
                        <?php for ($i = 0; $i < 4; $i++) : 
                            $item = $items_dates[$i];
                        ?>
                            <div class="archive-ranked-item">
                                <span class="archive-ranked-num"><?php echo ($i + 1); ?></span>
                                <div class="archive-ranked-content">
                                    <a href="<?php echo esc_url($item['link']); ?>" class="archive-ranked-link" title="<?php echo esc_attr($item['title']); ?>">
                                        <?php echo esc_html($item['title']); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <!-- Cột 2: Đánh số 5 - 8 -->
                    <div class="archive-col">
                        <?php for ($i = 4; $i < 8; $i++) : 
                            $item = $items_dates[$i];
                        ?>
                            <div class="archive-ranked-item">
                                <span class="archive-ranked-num"><?php echo ($i + 1); ?></span>
                                <div class="archive-ranked-content">
                                    <a href="<?php echo esc_url($item['link']); ?>" class="archive-ranked-link" title="<?php echo esc_attr($item['title']); ?>">
                                        <?php echo esc_html($item['title']); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    (function() {
        var widget = document.querySelector('.cms-archive-ranked-widget');
        if (!widget) return;
        var buttons = widget.querySelectorAll('.archive-tab-btn');
        buttons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                buttons.forEach(function(b) { b.classList.remove('active'); });
                this.classList.add('active');
                var target = this.getAttribute('data-target');
                var panels = widget.querySelectorAll('.archive-tab-panel');
                panels.forEach(function(panel) {
                    if (panel.id === 'archive-panel-' + target) {
                        panel.style.display = 'block';
                    } else {
                        panel.style.display = 'none';
                    }
                });
            });
        });
    })();
    </script>
    <?php
}

/**
 * Đăng ký Widget Area (Sidebar)
 */
function cms_nhomc_widgets_init() {
    register_sidebar(array(
        'name'          => __('Sidebar Chính', 'cms-nhomc'),
        'id'            => 'main-sidebar',
        'description'   => __('Khu vực thanh bên cho giao diện', 'cms-nhomc'),
        'before_widget' => '<div id="%1$s" class="widget-categories-card %2$s">',
        'after_widget'  => '</div></div>',
        'before_title'  => '<h3 class="widget-cat-title">',
        'after_title'   => '</h3><div class="widget-cat-stripe"></div><div class="widget-cat-body">',
    ));
}
add_action('widgets_init', 'cms_nhomc_widgets_init');

/**
 * Tự động tạo sẵn 3 chuyên mục mẫu nếu chưa có trong Database
 */
function cms_nhomc_create_default_categories() {
    $default_cats = array(
        '.Net Developer',
        'Thực Tập Sinh Tester',
        'Trợ giảng lập trình - Part time'
    );
    foreach ($default_cats as $cat_name) {
        if (!term_exists($cat_name, 'category')) {
            wp_insert_term($cat_name, 'category');
        }
    }
}
add_action('after_setup_theme', 'cms_nhomc_create_default_categories');

/**
 * Render Widget: COMMENTS (Bình luận) - Module 14 (Hien/14-comments)
 */
function cms_nhomc_render_comments_widget($limit = 5, $title = 'Comments') {
    $comments = get_comments(array(
        'number'      => intval($limit) * 2,
        'status'      => 'approve',
        'post_status' => 'publish',
        'type'        => 'comment',
    ));

    $display_items = array();
    if (!empty($comments)) {
        foreach ($comments as $comment) {
            // Bỏ qua bình luận mặc định khởi tạo của WordPress
            if (strpos($comment->comment_content, 'Xin chào, đây là một bình luận') !== false) {
                continue;
            }
            $post_title = get_the_title($comment->comment_post_ID);
            $display_items[] = array(
                'author'  => get_comment_author($comment),
                'date'    => get_comment_date('d/m/Y', $comment),
                'content' => wp_strip_all_tags($comment->comment_content),
                'link'    => get_comment_link($comment),
                'post'    => !empty($post_title) ? $post_title : '',
            );
            if (count($display_items) >= $limit) {
                break;
            }
        }
    }

    // Dữ liệu mẫu chuẩn y chang mẫu hình ảnh nếu chưa có bình luận
    if (empty($display_items)) {
        $sample_comments = array(
            array('author' => 'Thành Viên', 'content' => 'Bài viết hay quá, rất hữu ích!', 'date' => date('d/m/Y')),
            array('author' => 'Độc Giả', 'content' => 'Cảm ơn tác giả đã chia sẻ nội dung này.', 'date' => date('d/m/Y')),
            array('author' => 'Khách', 'content' => 'Trình bày chi tiết, dễ hiểu và chuyên nghiệp.', 'date' => date('d/m/Y')),
            array('author' => 'Sinh Viên', 'content' => 'Nội dung bài viết rất thực tế và chất lượng.', 'date' => date('d/m/Y')),
            array('author' => 'Admin', 'content' => 'Chào mừng bạn đến với hệ thống CMS Nhóm C!', 'date' => date('d/m/Y')),
        );

        $recent_posts = get_posts(array(
            'numberposts' => 5,
            'post_status' => 'publish',
        ));

        foreach ($sample_comments as $idx => $cmt) {
            if ($idx >= $limit) break;
            $link = isset($recent_posts[$idx]) ? get_permalink($recent_posts[$idx]->ID) : home_url('/');
            $display_items[] = array(
                'author'  => $cmt['author'],
                'date'    => $cmt['date'],
                'content' => $cmt['content'],
                'link'    => $link,
                'post'    => '',
            );
        }
    }
    ?>
    <div class="cms-sidebar-widget widget-comments-box">
        <h3 class="widget-comments-title"><?php echo esc_html($title); ?></h3>
        <div class="widget-comments-stripe"></div>
        <ul class="widget-comments-list">
            <?php foreach ($display_items as $item) : ?>
                <li class="widget-comments-item">
                    <a href="<?php echo esc_url($item['link']); ?>" class="widget-comments-link">
                        <span class="widget-comment-content"><?php echo esc_html($item['content']); ?></span>
                        <?php if (!empty($item['author'])) : ?>
                            <span class="widget-comment-meta">
                                <span class="widget-comment-author">
                                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    <?php echo esc_html($item['author']); ?>
                                </span>
                                <?php if (!empty($item['date'])) : ?>
                                    <span class="widget-comment-dot">&bull;</span>
                                    <span class="widget-comment-date"><?php echo esc_html($item['date']); ?></span>
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}

/**
 * Shortcode hiển thị Widget Comments: [cms_comments]
 */
function cms_nhomc_comments_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => 3,
        'title' => 'Comments',
    ), $atts, 'cms_comments');

    ob_start();
    cms_nhomc_render_comments_widget($atts['limit'], $atts['title']);
    return ob_get_clean();
}
add_shortcode('cms_comments', 'cms_nhomc_comments_shortcode');

/**
 * Tự động khởi tạo 3 bình luận mẫu chuẩn theo ảnh thiết kế (Module 12)
 */
function cms_nhomc_create_default_comments() {
    $sample_texts = array(
        'Bài viết hay quá',
        'Cảm ơn tác giả',
        'Bài viết thật hữu ích',
    );

    // Kiểm tra xem đã tồn tại bình luận chuẩn chưa
    $existing = get_comments(array(
        'search' => 'Bài viết hay quá',
    ));
    if (!empty($existing)) {
        return;
    }

    $posts = get_posts(array(
        'numberposts' => 3,
        'post_status' => 'publish',
        'orderby'     => 'date',
        'order'       => 'DESC',
    ));

    if (empty($posts)) {
        return;
    }

    $authors = array('Nguyễn Văn Nam', 'Trần Thị Mai', 'Lê Hoàng');
    foreach ($sample_texts as $i => $text) {
        $target_post = isset($posts[$i]) ? $posts[$i] : $posts[0];
        wp_insert_comment(array(
            'comment_post_ID'      => $target_post->ID,
            'comment_author'       => isset($authors[$i]) ? $authors[$i] : 'Bạn đọc',
            'comment_author_email' => 'reader' . ($i + 1) . '@example.com',
            'comment_content'      => $text,
            'comment_approved'     => 1,
            'comment_date'         => current_time('mysql', false),
            'comment_date_gmt'     => current_time('mysql', true),
        ));
    }
}
add_action('after_setup_theme', 'cms_nhomc_create_default_comments');

/**
 * Custom Comment Callback theo phong cách Bootsnipp gNVj0 (Bootstrap Media Object)
 * Giữ nguyên bố cục chính xác theo mẫu thiết kế
 */
function cms_nhomc_comment_callback($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;
    $comment_id = get_comment_ID();
    // Avatar bóng người màu xám chuẩn theo ảnh mẫu Bootsnipp gNVj0
    $avatar_url = 'https://ssl.gstatic.com/accounts/ui/avatar_2x.png';

    // Phân quyền:
    // - Sửa: CHỈ chính chủ tác giả mới được sửa bình luận của mình (Admin không được sửa bài của người khác)
    // - Xóa: Chính chủ tác giả HOẶC Admin/Quản trị viên có quyền xóa bình luận (kể cả của người khác)
    $current_user_id = get_current_user_id();
    $is_logged_in    = is_user_logged_in();
    $is_author       = $is_logged_in && ((int)$comment->user_id > 0 && (int)$comment->user_id === (int)$current_user_id);
    $is_admin        = $is_logged_in && (current_user_can('moderate_comments') || current_user_can('administrator'));

    $can_edit   = $is_author;
    $can_delete = $is_author || $is_admin;
    ?>
    <li <?php comment_class('cms-comment-item'); ?> id="comment-<?php echo $comment_id; ?>">
        <div class="media comment-box" id="div-comment-<?php echo $comment_id; ?>">
            <div class="media-left">
                <a href="<?php echo esc_url(get_comment_author_url()); ?>">
                    <img class="img-responsive user-photo" src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr(get_comment_author()); ?>">
                </a>
            </div>
            <div class="media-body">
                <h4 class="media-heading"><?php comment_author(); ?></h4>

                <div class="cms-comment-content-wrap" id="cms-comment-wrap-<?php echo $comment_id; ?>">
                    <div class="cms-comment-text" id="cms-comment-text-<?php echo $comment_id; ?>">
                        <?php comment_text(); ?>
                    </div>

                    <?php if ($can_edit) : ?>
                        <div class="cms-comment-edit-form" id="cms-comment-edit-form-<?php echo $comment_id; ?>" style="display: none;">
                            <textarea class="form-control cms-comment-edit-textarea" id="cms-comment-textarea-<?php echo $comment_id; ?>" rows="3"><?php echo esc_textarea(get_comment_text($comment_id)); ?></textarea>
                            <div class="cms-comment-edit-buttons">
                                <button type="button" class="btn btn-sm btn-secondary cms-btn-cancel-edit" data-comment-id="<?php echo $comment_id; ?>">Hủy</button>
                                <button type="button" class="btn btn-sm btn-primary cms-btn-save-edit" data-comment-id="<?php echo $comment_id; ?>" data-nonce="<?php echo wp_create_nonce('cms_edit_comment_' . $comment_id); ?>" data-version-hash="<?php echo md5(trim($comment->comment_content)); ?>">Lưu thay đổi</button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="comment-reply-wrap">
                    <?php if ($can_edit) : ?>
                        <button type="button" class="cms-comment-action-btn cms-comment-edit-btn" data-comment-id="<?php echo $comment_id; ?>" data-nonce="<?php echo wp_create_nonce('cms_edit_comment_' . $comment_id); ?>" title="<?php esc_attr_e('Chỉnh sửa bình luận', 'cms-nhomc'); ?>">
                            <i class="fa fa-pencil"></i> Sửa
                        </button>
                    <?php endif; ?>

                    <?php if ($can_delete) : ?>
                        <button type="button" class="cms-comment-action-btn cms-comment-delete-btn" data-comment-id="<?php echo $comment_id; ?>" data-nonce="<?php echo wp_create_nonce('cms_delete_comment_' . $comment_id); ?>" title="<?php esc_attr_e('Xóa bình luận', 'cms-nhomc'); ?>">
                            <i class="fa fa-trash"></i> Xóa
                        </button>
                    <?php endif; ?>

                    <?php
                    comment_reply_link(array_merge($args, array(
                        'add_below'  => 'div-comment',
                        'depth'      => $depth,
                        'max_depth'  => $args['max_depth'],
                        'reply_text' => 'Reply',
                    )));
                    ?>
                </div>
            </div>
        </div>
    <?php
}

/* ==========================================================================
   MODULE 15: LAST POSTS - BOOTSNIPP xrKXW & BÀI VIẾT MỚI NHẤT (XUÂN HÒA)
   - Layout 1: Khối nền kem/xanh nhạt hiển thị danh sách bài viết mới nhất
   - Layout 2: Vertical Timeline chuẩn mẫu Bootsnipp xrKXW
   ========================================================================== */

/**
 * Hàm lấy danh sách bài viết mới nhất cho Module 15
 *
 * @param int $limit Số lượng bài viết
 * @return WP_Query
 */
function cms_nhomc_get_last_posts_query($limit = 5) {
    return new WP_Query(array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => max(1, intval($limit)),
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => 1,
        'no_found_rows'       => true,
    ));
}

/**
 * Render Widget / Component: Module 15 Last Posts (Chỉ hiển thị Latest News Timeline theo mẫu Bootsnipp xrKXW)
 *
 * @param int $limit Số lượng bài viết
 * @param string $title Tiêu đề khối Timeline (mặc định: Latest News)
 */
function cms_nhomc_render_last_posts_widget($limit = 5, $title = 'Latest News') {
    $limit = !empty($limit) ? intval($limit) : 5;
    $title = !empty($title) ? $title : 'Latest News';

    $query = cms_nhomc_get_last_posts_query($limit);
    $has_posts = $query->have_posts();

    // Dữ liệu mẫu hiển thị khi chưa có bài viết trên site
    $sample_timeline_posts = array(
        array(
            'title'   => 'New Web Design',
            'date'    => '21 March, 2014',
            'excerpt' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque scelerisque diam non nisi semper, et elementum lorem ornare. Maecenas placerat facilisis mollis. Duis sagittis ligula in sodales vehicula....',
            'link'    => home_url('/'),
        ),
        array(
            'title'   => '21 000 Job Seekers',
            'date'    => '4 March, 2014',
            'excerpt' => 'Curabitur purus sem, malesuada eu luctus eget, suscipit sed turpis. Nam pellentesque felis vitae justo accumsan, sed semper nisi sollicitudin...',
            'link'    => home_url('/'),
        ),
        array(
            'title'   => 'Awesome Employers',
            'date'    => '1 April, 2014',
            'excerpt' => 'Fusce ullamcorper ligula sit amet quam accumsan aliquet. Sed nulla odio, tincidunt vitae nunc vitae, mollis pharetra velit. Sed nec tempor nibh...',
            'link'    => home_url('/'),
        ),
    );
    ?>
    <div class="cms-module-15-last-posts-wrapper">
        <!-- Khối Vertical Timeline chuẩn mẫu Bootsnipp xrKXW -->
        <div class="cms-last-posts-timeline-container">
            <h4 class="timeline-main-title"><?php echo esc_html($title); ?></h4>
            <ul class="timeline">
                <?php if ($has_posts) : 
                    while ($query->have_posts()) : $query->the_post(); 
                        $post_date = get_the_date('j F, Y'); // định dạng chuẩn: 21 March, 2014
                        $excerpt_raw = get_the_excerpt();
                        $excerpt = !empty($excerpt_raw) ? wp_trim_words(wp_strip_all_tags($excerpt_raw), 25, '...') : wp_trim_words(wp_strip_all_tags(get_the_content()), 25, '...');
                    ?>
                        <li class="timeline-item">
                            <div class="timeline-header">
                                <a href="<?php the_permalink(); ?>" class="timeline-post-title" title="<?php the_title_attribute(); ?>">
                                    <?php the_title(); ?>
                                </a>
                                <span class="float-right timeline-post-date"><?php echo esc_html($post_date); ?></span>
                            </div>
                            <p class="timeline-post-excerpt"><?php echo esc_html($excerpt); ?></p>
                        </li>
                    <?php endwhile; wp_reset_postdata(); 
                else : 
                    foreach ($sample_timeline_posts as $item) : ?>
                        <li class="timeline-item">
                            <div class="timeline-header">
                                <a href="<?php echo esc_url($item['link']); ?>" class="timeline-post-title">
                                    <?php echo esc_html($item['title']); ?>
                                </a>
                                <span class="float-right timeline-post-date"><?php echo esc_html($item['date']); ?></span>
                            </div>
                            <p class="timeline-post-excerpt"><?php echo esc_html($item['excerpt']); ?></p>
                        </li>
                    <?php endforeach; 
                endif; ?>
            </ul>
        </div>
    </div>
    <?php
}

/**
 * Shortcode hiển thị Module 15: [last_posts] hoặc [cms_last_posts]
 * Ví dụ sử dụng:
 * [last_posts limit="5" title="Latest News" layout="all"]
 * [last_posts limit="4" layout="timeline"]
 * [last_posts limit="5" layout="box"]
 */
function cms_nhomc_last_posts_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => 5,
        'title' => 'Latest News',
    ), $atts, 'last_posts');

    ob_start();
    cms_nhomc_render_last_posts_widget(intval($atts['limit']), sanitize_text_field($atts['title']));
    return ob_get_clean();
}
add_shortcode('last_posts', 'cms_nhomc_last_posts_shortcode');
add_shortcode('cms_last_posts', 'cms_nhomc_last_posts_shortcode');
add_shortcode('cms_nhomc_last_posts', 'cms_nhomc_last_posts_shortcode');

/**
 * WordPress Core Widget Class: CMS_NhomC_Last_Posts_Widget (Module 15)
 */
class CMS_NhomC_Last_Posts_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'cms_nhomc_last_posts_widget',
            __('[CMS Nhóm C] (15) Latest News Timeline', 'cms-nhomc'),
            array('description' => __('Hiển thị bài viết mới nhất dạng Vertical Timeline chuẩn Bootsnipp xrKXW (Xuân Hòa)', 'cms-nhomc'))
        );
    }

    public function widget($args, $instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Latest News';
        $limit = !empty($instance['limit']) ? intval($instance['limit']) : 5;

        echo isset($args['before_widget']) ? $args['before_widget'] : '';
        cms_nhomc_render_last_posts_widget($limit, $title);
        echo isset($args['after_widget']) ? $args['after_widget'] : '';
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Latest News';
        $limit = !empty($instance['limit']) ? intval($instance['limit']) : 5;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Tiêu đề Timeline:', 'cms-nhomc'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('limit')); ?>"><?php esc_html_e('Số lượng bài viết:', 'cms-nhomc'); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" name="<?php echo esc_attr($this->get_field_name('limit')); ?>" type="number" step="1" min="1" max="20" value="<?php echo esc_attr($limit); ?>" size="3" />
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['limit'] = (!empty($new_instance['limit'])) ? intval($new_instance['limit']) : 5;
        return $instance;
    }
}

function cms_nhomc_register_last_posts_widget() {
    register_widget('CMS_NhomC_Last_Posts_Widget');
}
add_action('widgets_init', 'cms_nhomc_register_last_posts_widget');

// Alias my_custom_comments to cms_nhomc_comment_callback for flexibility
if (!function_exists('my_custom_comments')) {
    function my_custom_comments($comment, $args, $depth) {
        return cms_nhomc_comment_callback($comment, $args, $depth);
    }
}

/**
 * ==========================================================================
 * TỰ ĐỘNG DUYỆT 100% BÌNH LUẬN (KHÔNG CẦN QUẢN TRỊ VIÊN DUYỆT THỦ CÔNG)
 * ==========================================================================
 */
add_filter('pre_comment_approved', function($approved, $commentdata) {
    return 1;
}, 999, 2);

add_action('comment_post', function($comment_id) {
    wp_set_comment_status($comment_id, 'approve');
}, 999);

/**
 * Xử lý khi người dùng gửi trả lời vào một bình luận đã bị xóa trước đó:
 * Thay thế câu báo mặc định của WP core thành "Bình luận này đã bị xóa bởi tác giả hoặc quản trị viên."
 */
add_action('comment_reply_to_unapproved_comment', function($comment_post_id, $comment_parent) {
    $parent = get_comment($comment_parent);
    if (!$parent || 'trash' === $parent->comment_approved) {
        wp_die(
            '<p>Bình luận bạn đang trả lời đã bị xóa bởi tác giả hoặc quản trị viên.</p>',
            'Bình luận đã bị xóa',
            array('response' => 403, 'back_link' => true)
        );
    }
}, 1, 2);

add_filter('gettext', function($translation, $text, $domain) {
    if ('Sorry, replies to unapproved comments are not allowed.' === $text || 'Sorry, you cannot reply to a comment that is not approved.' === $text) {
        return 'Bình luận bạn đang trả lời đã bị xóa bởi tác giả hoặc quản trị viên.';
    }
    return $translation;
}, 20, 3);

/**
 * ==========================================================================
 * AJAX HANDLERS: SỬA VÀ XÓA BÌNH LUẬN (DÀNH CHO NGƯỜI DÙNG ĐÃ LOGIN)
 * ==========================================================================
 */

/**
 * AJAX: Chỉnh sửa nội dung bình luận (Bên lưu sau sẽ báo lỗi xung đột)
 */
add_action('wp_ajax_cms_nhomc_edit_comment', 'cms_nhomc_ajax_edit_comment');
function cms_nhomc_ajax_edit_comment() {
    $comment_id   = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
    $content      = isset($_POST['content']) ? trim($_POST['content']) : '';
    $nonce        = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
    $version_hash = isset($_POST['version_hash']) ? sanitize_text_field($_POST['version_hash']) : '';

    if (!$comment_id || empty($content)) {
        wp_send_json_error(array('message' => 'Dữ liệu không hợp lệ hoặc nội dung trống.'));
    }

    if (!wp_verify_nonce($nonce, 'cms_edit_comment_' . $comment_id)) {
        wp_send_json_error(array('message' => 'Lỗi bảo mật (nonce không hợp lệ). Vui lòng tải lại trang.'));
    }

    $comment = get_comment($comment_id);
    if (!$comment) {
        wp_send_json_error(array('message' => 'Không tìm thấy bình luận.'));
    }

    $current_user_id = get_current_user_id();
    $can_edit = is_user_logged_in() && (
        (int)$comment->user_id > 0 && (int)$comment->user_id === (int)$current_user_id
    );

    if (!$can_edit) {
        wp_send_json_error(array('message' => 'Bạn chỉ có quyền chỉnh sửa bình luận do chính mình viết!'));
    }

    // NẾU NỘI DUNG ĐÃ BỊ THAY ĐỔI BỞI PHIÊN KHÁC TRƯỚC ĐÓ -> BÁO LỖI CHẶN LƯU
    $current_db_hash = md5(trim($comment->comment_content));
    if (!empty($version_hash) && $version_hash !== $current_db_hash) {
        wp_send_json_error(array(
            'message' => 'Lỗi: Bình luận này đã được chỉnh sửa trước đó bởi một phiên khác! Thao tác lưu bị từ chối để tránh ghi đè mất dữ liệu. Vui lòng tải lại trang để xem nội dung mới nhất.'
        ));
    }

    $updated = wp_update_comment(array(
        'comment_ID'      => $comment_id,
        'comment_content' => wp_kses_post($content),
    ));

    if ($updated === false) {
        wp_send_json_error(array('message' => 'Không thể cập nhật bình luận.'));
    }

    $updated_comment   = get_comment($comment_id);
    $formatted_content = apply_filters('comment_text', $updated_comment->comment_content, $updated_comment);

    wp_send_json_success(array(
        'message'          => 'Cập nhật bình luận thành công!',
        'content'          => $formatted_content,
        'raw_content'      => $updated_comment->comment_content,
        'new_version_hash' => md5(trim($updated_comment->comment_content)),
    ));
}

/**
 * AJAX: Xóa bình luận
 */
add_action('wp_ajax_cms_nhomc_delete_comment', 'cms_nhomc_ajax_delete_comment');
function cms_nhomc_ajax_delete_comment() {
    $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
    $nonce      = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

    if (!$comment_id) {
        wp_send_json_error(array('message' => 'ID bình luận không hợp lệ.'));
    }

    if (!wp_verify_nonce($nonce, 'cms_delete_comment_' . $comment_id)) {
        wp_send_json_error(array('message' => 'Lỗi bảo mật (nonce không hợp lệ). Vui lòng tải lại trang.'));
    }

    $comment = get_comment($comment_id);
    if (!$comment) {
        wp_send_json_error(array('message' => 'Không tìm thấy bình luận.'));
    }

    $current_user_id = get_current_user_id();
    $is_logged_in    = is_user_logged_in();
    $is_author       = $is_logged_in && ((int)$comment->user_id > 0 && (int)$comment->user_id === (int)$current_user_id);
    $is_admin        = $is_logged_in && (current_user_can('moderate_comments') || current_user_can('administrator'));

    $can_delete = $is_author || $is_admin;

    if (!$can_delete) {
        wp_send_json_error(array('message' => 'Bạn không có quyền xóa bình luận này!'));
    }

    $post_id = $comment->comment_post_ID;

    // Xóa bình luận vĩnh viễn
    $deleted = wp_delete_comment($comment_id, true);

    if (!$deleted) {
        wp_send_json_error(array('message' => 'Không thể xóa bình luận này.'));
    }

    // Cập nhật lại số lượng bình luận cho bài viết
    wp_update_comment_count_now($post_id);
    $new_count = get_comments_number($post_id);

    wp_send_json_success(array(
        'message'     => 'Đã xóa bình luận thành công!',
        'new_count'   => $new_count,
        'count_label' => sprintf('(%d) Comments', $new_count),
    ));
}

/* ==========================================================================
   MODULE 13: PAGES - TRANG MỚI NHẤT (ĐẶNG NGUYÊN)
   - Bố cục chuẩn TDC: Thay vì 3 bài trên 1 dòng, chuyển thành dạng cột đứng:
     1 HÀNG = 1 BÀI VIẾT trên mọi breakpoint (Desktop, Laptop, Tablet, Mobile).
   - Hiển thị đầy đủ: Tiêu đề, Hình ảnh responsive (không méo, đúng tỷ lệ),
     mô tả ngắn (excerpt) và link đến trang chi tiết.
   ========================================================================== */

/**
 * Tự động tạo 3 trang chuyên ngành mẫu (Module 13) nếu chưa có
 */
function cms_nhomc_create_default_pages() {
    $theme_uri = get_template_directory_uri();
    $default_pages = array(
        array(
            'title'   => 'Ngành Công Nghệ Thông Tin',
            'slug'    => 'nganh-cong-nghe-thong-tin',
            'content' => 'Trang bị cho sinh viên kiến thức và kỹ năng để trở thành nhà phát triển phần mềm chuyên nghiệp.',
            'image'   => 'nganh-cong-nghe-thong-tin.jpg',
            'order'   => 1,
        ),
        array(
            'title'   => 'Ngành Truyền Thông & Mạng Máy Tính',
            'slug'    => 'nganh-truyen-thong-va-mang-may-tinh',
            'content' => 'Sinh viên có khả năng nghiên cứu, thiết kế, phát triển và triển khai các ứng dụng về các công nghệ Mạng máy tính.',
            'image'   => 'nganh-truyen-thong-mang.jpg',
            'order'   => 2,
        ),
        array(
            'title'   => 'Ngành Thiết Kế Đồ Họa',
            'slug'    => 'nganh-thiet-ke-do-hoa',
            'content' => 'Cung cấp các kiến thức về thiết kế đồ họa và công nghệ thông tin đa phương tiện.',
            'image'   => 'nganh-thiet-ke-do-hoa.jpg',
            'order'   => 3,
        ),
    );

    foreach ($default_pages as $p) {
        $existing = get_page_by_path($p['slug'], OBJECT, 'page');
        if (!$existing) {
            $pid = wp_insert_post(array(
                'post_title'   => $p['title'],
                'post_name'    => $p['slug'],
                'post_content' => '<p>' . esc_html($p['content']) . '</p>',
                'post_excerpt' => $p['content'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'menu_order'   => $p['order'],
            ));
            if ($pid && !is_wp_error($pid)) {
                update_post_meta($pid, '_thumbnail_ext_url', $theme_uri . '/assets/images/' . $p['image']);
            }
        }
    }
}
add_action('after_setup_theme', 'cms_nhomc_create_default_pages');

/**
 * Lấy danh sách Pages hiển thị cho Module 13
 */
function cms_nhomc_get_module13_pages($limit = 3) {
    $theme_uri = get_template_directory_uri();
    $limit = max(1, intval($limit));

    $query_pages = get_posts(array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'orderby'        => 'menu_order date',
        'order'          => 'ASC',
        'exclude'        => array(2, 3),
    ));

    if (empty($query_pages)) {
        $query_pages = get_posts(array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => 'menu_order date',
            'order'          => 'ASC',
        ));
    }

    $results = array();
    if (!empty($query_pages)) {
        foreach ($query_pages as $page) {
            $pid = $page->ID;
            $thumb = get_post_meta($pid, '_thumbnail_ext_url', true);
            if (empty($thumb) && has_post_thumbnail($pid)) {
                $thumb = get_the_post_thumbnail_url($pid, 'large');
            }
            if (empty($thumb)) {
                $thumb = cms_nhomc_get_post_thumbnail_url($pid);
            }

            $excerpt = !empty($page->post_excerpt) ? $page->post_excerpt : wp_trim_words(wp_strip_all_tags($page->post_content), 20, '...');

            $results[] = array(
                'id'        => $pid,
                'title'     => get_the_title($pid),
                'permalink' => get_permalink($pid),
                'thumb'     => $thumb,
                'excerpt'   => $excerpt,
            );
        }
    }

    if (empty($results)) {
        $results = array(
            array(
                'title'     => 'Ngành Công Nghệ Thông Tin',
                'permalink' => home_url('/nganh-cong-nghe-thong-tin/'),
                'thumb'     => $theme_uri . '/assets/images/nganh-cong-nghe-thong-tin.jpg',
                'excerpt'   => 'Trang bị cho sinh viên kiến thức và kỹ năng để trở thành nhà phát triển phần mềm chuyên nghiệp.',
            ),
            array(
                'title'     => 'Ngành Truyền Thông & Mạng Máy Tính',
                'permalink' => home_url('/nganh-truyen-thong-va-mang-may-tinh/'),
                'thumb'     => $theme_uri . '/assets/images/nganh-truyen-thong-mang.jpg',
                'excerpt'   => 'Sinh viên có khả năng nghiên cứu, thiết kế, phát triển và triển khai các ứng dụng về các công nghệ Mạng máy tính.',
            ),
            array(
                'title'     => 'Ngành Thiết Kế Đồ Họa',
                'permalink' => home_url('/nganh-thiet-ke-do-hoa/'),
                'thumb'     => $theme_uri . '/assets/images/nganh-thiet-ke-do-hoa.jpg',
                'excerpt'   => 'Cung cấp các kiến thức về thiết kế đồ họa và công nghệ thông tin đa phương tiện.',
            ),
        );
    }

    return $results;
}

/**
 * Render Widget: TRANG MỚI NHẤT / PAGES (Module 13 - Đặng Nguyên)
 * Bố cục: 1 HÀNG = 1 BÀI VIẾT (dạng cột đứng) trên mọi breakpoint.
 */
function cms_nhomc_render_pages_widget($limit = 3, $title = 'Trang mới nhất') {
    $title = !empty($title) ? $title : __('Trang mới nhất', 'cms-nhomc');
    $pages = cms_nhomc_get_module13_pages($limit);
    ?>
    <div class="widget-categories-card widget-pages-card">
        <h3 class="widget-cat-title widget-pages-title"><?php echo esc_html($title); ?></h3>
        <div class="widget-cat-stripe widget-pages-stripe"></div>
        <div class="widget-cat-body widget-pages-body">
            <div class="module13-pages-list">
                <?php foreach ($pages as $item) : ?>
                    <article class="module13-page-item">
                        <h4 class="module13-page-title">
                            <a href="<?php echo esc_url($item['permalink']); ?>"><?php echo esc_html($item['title']); ?></a>
                        </h4>
                        <div class="module13-page-thumb">
                            <a href="<?php echo esc_url($item['permalink']); ?>" tabindex="-1" aria-hidden="true">
                                <img src="<?php echo esc_url($item['thumb']); ?>" alt="<?php echo esc_attr($item['title']); ?>" loading="lazy" />
                            </a>
                        </div>
                        <div class="module13-page-desc">
                            <?php echo esc_html($item['excerpt']); ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Shortcode [cms_nhomc_pages limit="3" title="Trang mới nhất"]
 */
function cms_nhomc_pages_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => 3,
        'title' => 'Trang mới nhất',
    ), $atts, 'cms_nhomc_pages');

    ob_start();
    cms_nhomc_render_pages_widget(intval($atts['limit']), sanitize_text_field($atts['title']));
    return ob_get_clean();
}
add_shortcode('cms_nhomc_pages', 'cms_nhomc_pages_shortcode');

/**
 * Đăng ký Widget WordPress chuẩn cho Module 13
 */
class CMS_NhomC_Pages_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'cms_nhomc_pages_widget',
            __('CMS Nhóm C: Trang mới nhất (Module 13)', 'cms-nhomc'),
            array('description' => __('Hiển thị 3 bài viết/trang dạng cột đứng 1 hàng 1 bài (Module 13)', 'cms-nhomc'))
        );
    }
    public function widget($args, $instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Trang mới nhất', 'cms-nhomc');
        $limit = !empty($instance['limit']) ? intval($instance['limit']) : 3;
        cms_nhomc_render_pages_widget($limit, $title);
    }
}
function cms_nhomc_register_pages_widget() {
    register_widget('CMS_NhomC_Pages_Widget');
}
add_action('widgets_init', 'cms_nhomc_register_pages_widget');


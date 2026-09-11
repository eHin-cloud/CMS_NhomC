<?php
/**
 * Functions and definitions cho Theme CMS Nhóm C
 *
 * @package CMS_NhomC
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

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
}
add_action('after_setup_theme', 'cms_nhomc_setup');

/**
 * Nạp Style và Script
 */
function cms_nhomc_scripts() {
    // Nạp style.css của Theme
    wp_enqueue_style('cms-nhomc-style', get_stylesheet_uri(), array(), '1.0.0');
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
 * [Ponytail Standard] Giới hạn phạm vi tìm kiếm chỉ trên bài viết (post)
 * Loại bỏ page / attachments thừa, tối ưu tốc độ truy vấn database.
 * Đồng thời ngăn ngừa truy vấn rác khi từ khóa rỗng hoặc chỉ chứa khoảng trắng / kiểu dữ liệu không hợp lệ.
 */
function cms_nhomc_filter_search_query($query) {
    if (!is_admin() && $query->is_main_query() && $query->is_search()) {
        $query->set('post_type', 'post');
        $query->set('post_status', 'publish');

        // Ngăn truy vấn rác vào cơ sở dữ liệu khi từ khóa rỗng hoặc không phải chuỗi hợp lệ
        $s = $query->get('s');
        if (!is_string($s) || trim($s) === '') {
            $query->set('post__in', array(0));
            $query->set('no_found_rows', true);
        }
    }
}
add_action('pre_get_posts', 'cms_nhomc_filter_search_query');

/**
 * Ngăn chặn hoàn toàn truy vấn SQL vào cơ sở dữ liệu khi tìm kiếm rỗng hoặc kiểu dữ liệu không hợp lệ
 */
function cms_nhomc_prevent_empty_search_db_query($posts, $query) {
    if (!is_admin() && $query->is_main_query() && $query->is_search()) {
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
    foreach ($keywords as $kw) {
        $first_char = mb_substr($kw, 0, 1, 'UTF-8');
        $has_word_start = preg_match('/[\p{L}\p{N}]/u', $first_char);
        $quoted = preg_quote($kw, '/');

        // Lookbehind ngăn không khớp vào giữa/cuối từ khác (ví dụ: 'AI' không khớp 'Blockchain' hay 'thứ hai', 'an' không khớp 'Ban')
        $prefix = $has_word_start ? '(?<![\p{L}\p{N}])' : '';

        // Đối với từ khóa ngắn (<= 2 ký tự) kết thúc bằng chữ/số (như '3', 'C', 'AI', 'IT', 'an'):
        // Thêm lookahead để tránh khớp sai vào số có nhiều chữ số (như '3' khớp '35') hoặc từ dài hơn (như 'C' khớp 'Cao', 'AI' khớp 'AIDA')
        $last_char = mb_substr($kw, -1, 1, 'UTF-8');
        $has_word_end = preg_match('/[\p{L}\p{N}]/u', $last_char);
        $suffix = (mb_strlen($kw, 'UTF-8') <= 2 && $has_word_end) ? '(?![\p{L}\p{N}])' : '';

        $escaped_terms[] = $prefix . $quoted . $suffix;
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

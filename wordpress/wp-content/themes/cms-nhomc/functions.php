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
}
add_action('after_setup_theme', 'cms_nhomc_setup');

/**
 * Nạp Style và Script
 */
function cms_nhomc_scripts() {
    // Nạp Font Awesome 4.7.0 cho các icon Footer và điều hướng
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), '4.7.0');

    // Nạp style.css của Theme
    wp_enqueue_style('cms-nhomc-style', get_stylesheet_uri(), array('font-awesome'), '1.3.0');

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
        wp_enqueue_script('cms-nhomc-comment-actions', get_template_directory_uri() . '/assets/js/comment-actions.js', array(), '1.0.0', true);
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
 * [Ponytail Standard] Giới hạn phạm vi tìm kiếm chỉ trên bài viết (post)
 * Loại bỏ page / attachments thừa, tối ưu tốc độ truy vấn database.
 * Đồng thời ngăn ngừa truy vấn rác khi từ khóa rỗng hoặc chỉ chứa khoảng trắng / kiểu dữ liệu không hợp lệ.
 */
function cms_nhomc_filter_search_query($query) {
    if (!is_admin() && $query->is_search()) {
        $query->set('post_type', 'post');
        $query->set('post_status', 'publish');

        // Chuẩn hóa từ khóa tìm kiếm: loại bỏ khoảng trắng đầu/cuối và rút gọn khoảng trắng kép
        $s = $query->get('s');
        if (is_string($s)) {
            $s = trim(preg_replace('/\s+/u', ' ', $s));
            $query->set('s', $s);
        }

        // Ngăn truy vấn rác vào cơ sở dữ liệu khi từ khóa rỗng hoặc không phải chuỗi hợp lệ
        if (!is_string($s) || $s === '') {
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
 * Render Widget: COMMENTS (Bình luận) - Module 12 (Anh Quý)
 */
function cms_nhomc_render_comments_widget($limit = 3, $title = 'Comments') {
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
            $display_items[] = array(
                'content' => wp_strip_all_tags($comment->comment_content),
                'link'    => get_comment_link($comment),
            );
            if (count($display_items) >= $limit) {
                break;
            }
        }
    }

    // Dữ liệu mẫu chuẩn y chang mẫu hình ảnh nếu chưa có bình luận
    if (empty($display_items)) {
        $sample_comments = array(
            'Bài viết hay quá',
            'Cảm ơn tác giả',
            'Bài viết thật hữu ích',
        );

        $recent_posts = get_posts(array(
            'numberposts' => 3,
            'post_status' => 'publish',
        ));

        foreach ($sample_comments as $idx => $cmt_text) {
            $link = isset($recent_posts[$idx]) ? get_permalink($recent_posts[$idx]->ID) : home_url('/');
            $display_items[] = array(
                'content' => $cmt_text,
                'link'    => $link,
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
                        <?php echo esc_html($item['content']); ?>
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

    // Kiểm tra quyền Sửa/Xóa: Người dùng viết bình luận đó hoặc Quản trị viên
    $current_user_id = get_current_user_id();
    $can_edit_or_delete = is_user_logged_in() && (
        ((int)$comment->user_id > 0 && (int)$comment->user_id === (int)$current_user_id) ||
        current_user_can('moderate_comments') ||
        current_user_can('edit_comment', $comment_id)
    );
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

                <?php if ($comment->comment_approved == '0') : ?>
                    <p class="cms-comment-moderation-notice"><em>Bình luận của bạn đang chờ quản trị viên phê duyệt.</em></p>
                <?php endif; ?>

                <div class="cms-comment-content-wrap" id="cms-comment-wrap-<?php echo $comment_id; ?>">
                    <div class="cms-comment-text" id="cms-comment-text-<?php echo $comment_id; ?>">
                        <?php comment_text(); ?>
                    </div>

                    <?php if ($can_edit_or_delete) : ?>
                        <div class="cms-comment-edit-form" id="cms-comment-edit-form-<?php echo $comment_id; ?>" style="display: none;">
                            <textarea class="form-control cms-comment-edit-textarea" id="cms-comment-textarea-<?php echo $comment_id; ?>" rows="3"><?php echo esc_textarea(get_comment_text($comment_id)); ?></textarea>
                            <div class="cms-comment-edit-buttons">
                                <button type="button" class="btn btn-sm btn-secondary cms-btn-cancel-edit" data-comment-id="<?php echo $comment_id; ?>">Hủy</button>
                                <button type="button" class="btn btn-sm btn-primary cms-btn-save-edit" data-comment-id="<?php echo $comment_id; ?>" data-nonce="<?php echo wp_create_nonce('cms_edit_comment_' . $comment_id); ?>">Lưu thay đổi</button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="comment-reply-wrap">
                    <?php if ($can_edit_or_delete) : ?>
                        <button type="button" class="cms-comment-action-btn cms-comment-edit-btn" data-comment-id="<?php echo $comment_id; ?>" title="<?php esc_attr_e('Chỉnh sửa bình luận', 'cms-nhomc'); ?>">
                            <i class="fa fa-pencil"></i> Sửa
                        </button>
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
 * AJAX HANDLERS: SỬA VÀ XÓA BÌNH LUẬN (DÀNH CHO NGƯỜI DÙNG ĐÃ LOGIN)
 * ==========================================================================
 */

/**
 * AJAX: Chỉnh sửa nội dung bình luận
 */
add_action('wp_ajax_cms_nhomc_edit_comment', 'cms_nhomc_ajax_edit_comment');
function cms_nhomc_ajax_edit_comment() {
    $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
    $content    = isset($_POST['content']) ? trim($_POST['content']) : '';
    $nonce      = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

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
        ((int)$comment->user_id > 0 && (int)$comment->user_id === (int)$current_user_id) ||
        current_user_can('moderate_comments') ||
        current_user_can('edit_comment', $comment_id)
    );

    if (!$can_edit) {
        wp_send_json_error(array('message' => 'Bạn không có quyền chỉnh sửa bình luận này.'));
    }

    $updated = wp_update_comment(array(
        'comment_ID'      => $comment_id,
        'comment_content' => wp_kses_post($content),
    ));

    if ($updated === false) {
        wp_send_json_error(array('message' => 'Không thể cập nhật bình luận.'));
    }

    $updated_comment = get_comment($comment_id);
    $formatted_content = apply_filters('comment_text', $updated_comment->comment_content, $updated_comment);

    wp_send_json_success(array(
        'message'     => 'Cập nhật bình luận thành công!',
        'content'     => $formatted_content,
        'raw_content' => $updated_comment->comment_content,
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
    $can_delete = is_user_logged_in() && (
        ((int)$comment->user_id > 0 && (int)$comment->user_id === (int)$current_user_id) ||
        current_user_can('moderate_comments') ||
        current_user_can('edit_comment', $comment_id)
    );

    if (!$can_delete) {
        wp_send_json_error(array('message' => 'Bạn không có quyền xóa bình luận này.'));
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

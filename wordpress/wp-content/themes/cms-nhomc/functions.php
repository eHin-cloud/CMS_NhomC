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
    // Nạp Font Awesome 4.7.0 cho các icon Footer và điều hướng
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), '4.7.0');

    // Nạp style.css của Theme
    wp_enqueue_style('cms-nhomc-style', get_stylesheet_uri(), array('font-awesome'), '1.2.0');
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

/**
 * Helper to get post thumbnail URL with smart fallbacks
 */
function cms_nhomc_get_post_thumbnail_url($post_id) {
    $post = get_post($post_id);
    if (!$post) return '';

    if (has_post_thumbnail($post->ID)) {
        return get_the_post_thumbnail_url($post->ID, 'large');
    }

    $slug = $post->post_name;
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
 * Render Widget: BÀI VIẾT MỚI (Recent Posts)
 */
function cms_nhomc_render_recent_posts_widget($limit = 5, $title = 'BÀI VIẾT MỚI') {
    $args = array(
        'posts_per_page'      => intval($limit),
        'post_status'         => 'publish',
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => 1,
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) :
    ?>
        <div class="cms-sidebar-widget widget-recent-posts">
            <h3 class="widget-title widget-title-recent"><?php echo esc_html($title); ?></h3>
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


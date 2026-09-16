<?php
/**
 * CMS_NhomC_Vietnamese_Search
 * Bộ xử lý tìm kiếm thông minh tiếng Việt dựa trên từ điển Viet74K (duyet/vietnamese-wordlist).
 *
 * Tính năng:
 * 1. Chuẩn hóa tiếng Việt (Normalization Pipeline: Unicode NFC, trim, lowercase, accent-removal, tokenization).
 * 2. Từ điển Viet74K & Chỉ mục nạp sẵn (Caching & fast dictionary lookup).
 * 3. Nhận diện lỗi chính tả (Typo detection), thiếu/thừa ký tự, sai dấu (Fuzzy search với Confidence score).
 * 4. Gợi ý từ khóa ("Có phải bạn muốn tìm: ...").
 * 5. Xếp hạng kết quả đa tầng (Exact -> Phrase -> Prefix -> Normalized -> Fuzzy).
 * 6. API Autocomplete & Realtime Search.
 *
 * @package CMS_NhomC
 */

if (!defined('ABSPATH')) {
    exit;
}

class CMS_NhomC_Vietnamese_Search {

    /**
     * Singleton instance
     * @var CMS_NhomC_Vietnamese_Search|null
     */
    private static $instance = null;

    /**
     * Đường dẫn file Viet74K.txt
     * @var string
     */
    private $wordlist_path;

    /**
     * Đường dẫn file cache PHP của từ điển
     * @var string
     */
    private $cache_path;

    /**
     * Mảng từ điển đã được lập chỉ mục:
     * - 'unaccent_to_accent': ['khu nghi duong' => 'khu nghỉ dưỡng', ...]
     * - 'prefix_buckets': ['kh' => [...], ...]
     * @var array|null
     */
    private $dictionary = null;

    /**
     * Khởi tạo Singleton
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->wordlist_path = get_template_directory() . '/data/Viet74K.txt';
        $this->cache_path    = get_template_directory() . '/data/viet74k_index.cache';

        if (function_exists('add_action')) {
            // Đăng ký AJAX hooks
            add_action('wp_ajax_cms_nhomc_smart_search', array($this, 'ajax_search'));
            add_action('wp_ajax_nopriv_cms_nhomc_smart_search', array($this, 'ajax_search'));

            add_action('wp_ajax_cms_nhomc_autocomplete', array($this, 'ajax_autocomplete'));
            add_action('wp_ajax_nopriv_cms_nhomc_autocomplete', array($this, 'ajax_autocomplete'));

            // Đăng ký REST API routes
            add_action('rest_api_init', array($this, 'register_rest_routes'));
        }

        if (function_exists('add_filter')) {
            // Hook vào query WordPress cho trang search.php
            add_filter('posts_search', array($this, 'filter_posts_search_sql'), 25, 2);
        }
    }

    /**
     * Đăng ký REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('cms-nhomc/v1', '/search', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'rest_search'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route('cms-nhomc/v1', '/autocomplete', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'rest_autocomplete'),
            'permission_callback' => '__return_true',
        ));
    }

    /**
     * Nạp từ điển Viet74K có cache tối ưu tốc độ
     * @return array
     */
    public function get_dictionary() {
        if ($this->dictionary !== null) {
            return $this->dictionary;
        }

        // Kiểm tra file cache nhị phân/serialize
        if (file_exists($this->cache_path) && filemtime($this->cache_path) >= filemtime($this->wordlist_path)) {
            $data = @unserialize(file_get_contents($this->cache_path));
            if (is_array($data) && isset($data['unaccent_to_accent'])) {
                $this->dictionary = $data;
                return $this->dictionary;
            }
        }

        // Nếu chưa có cache, xây dựng từ Viet74K.txt
        $this->dictionary = $this->build_dictionary_index();
        @file_put_contents($this->cache_path, serialize($this->dictionary));

        return $this->dictionary;
    }

    /**
     * Phân tích và đánh chỉ mục file Viet74K.txt
     * @return array
     */
    private function build_dictionary_index() {
        $unaccent_to_accent = array();
        $prefix_buckets     = array();

        // Danh sách các cụm từ ngữ cảnh quan trọng theo đặc tả và thực tế
        $domain_phrases = array(
            'khu nghỉ dưỡng',
            'bãi đỗ xe',
            'bãi đậu xe',
            'tiện ích',
            'tiện ích thể thao',
            'thể dục thể thao',
            'thể thao',
            'khoa học',
            'tin tức',
            'tuyển sinh',
            'công nghệ thông tin',
            'trí tuệ nhân tạo',
            'thời khóa biểu',
            'học phí',
            'bóng đá',
            'bóng bàn',
            'cầu lông',
            'hồ bơi',
            'công viên',
            'nhà hàng',
            'khách sạn',
        );
        foreach ($domain_phrases as $dp) {
            $un = mb_strtolower($this->remove_accents($dp), 'UTF-8');
            $unaccent_to_accent[$un] = $dp;
            $p2 = mb_substr($un, 0, 2, 'UTF-8');
            $p3 = mb_substr($un, 0, 3, 'UTF-8');
            if ($p2 !== '') $prefix_buckets[$p2][] = $dp;
            if ($p3 !== '') $prefix_buckets[$p3][] = $dp;
        }

        if (file_exists($this->wordlist_path)) {
            $file = fopen($this->wordlist_path, 'r');
            if ($file) {
                while (($line = fgets($file)) !== false) {
                    $word = trim($line);
                    if ($word === '') continue;

                    // Chuẩn hóa Unicode NFC
                    if (class_exists('Normalizer')) {
                        $word = Normalizer::normalize($word, Normalizer::FORM_C);
                    }

                    $unaccent = $this->remove_accents($word);
                    $unaccent_lower = mb_strtolower($unaccent, 'UTF-8');

                    // Lưu ánh xạ không dấu -> có dấu
                    if (!isset($unaccent_to_accent[$unaccent_lower])) {
                        $unaccent_to_accent[$unaccent_lower] = $word;
                    }

                    // Bucket theo 2 và 3 ký tự đầu
                    $p2 = mb_substr($unaccent_lower, 0, 2, 'UTF-8');
                    $p3 = mb_substr($unaccent_lower, 0, 3, 'UTF-8');

                    if ($p2 !== '') {
                        if (!isset($prefix_buckets[$p2])) $prefix_buckets[$p2] = array();
                        if (count($prefix_buckets[$p2]) < 500) $prefix_buckets[$p2][] = $word;
                    }
                    if ($p3 !== '') {
                        if (!isset($prefix_buckets[$p3])) $prefix_buckets[$p3] = array();
                        if (count($prefix_buckets[$p3]) < 500) $prefix_buckets[$p3][] = $word;
                    }
                }
                fclose($file);
            }
        }

        return array(
            'unaccent_to_accent' => $unaccent_to_accent,
            'prefix_buckets'     => $prefix_buckets,
        );
    }

    /**
     * Xóa dấu tiếng Việt (Accent Normalization)
     * Chuyển: 'khu nghỉ dưỡng' -> 'khu nghi duong', 'tiện ích' -> 'tien ich'
     *
     * @param string $str
     * @return string
     */
    public function remove_accents($str) {
        if (!is_string($str) || $str === '') return '';

        $accents = array(
            'a' => array('à','á','ả','ã','ạ','ă','ằ','ắ','ẳ','ẵ','ặ','â','ầ','ấ','ẩ','ẫ','ậ'),
            'A' => array('À','Á','Ả','Ã','Ạ','Ă','Ằ','Ắ','Ẳ','Ẵ','Ặ','Â','Ầ','Ấ','Ẩ','Ẫ','Ậ'),
            'd' => array('đ'),
            'D' => array('Đ'),
            'e' => array('è','é','ẻ','ẽ','ẹ','ê','ề','ế','ể','ễ','ệ'),
            'E' => array('È','É','Ẻ','Ẽ','Ẹ','Ê','Ề','Ế','Ể','Ễ','Ệ'),
            'i' => array('ì','í','ỉ','ĩ','ị'),
            'I' => array('Ì','Í','Ỉ','Ĩ','Ị'),
            'o' => array('ò','ó','ỏ','õ','ọ','ô','ồ','ố','ổ','ỗ','ộ','ơ','ờ','ớ','ở','ỡ','ợ'),
            'O' => array('Ò','Ó','Ỏ','Õ','Ọ','Ô','Ồ','Ố','Ổ','Ỗ','Ộ','Ơ','Ờ','Ớ','Ở','Ỡ','Ợ'),
            'u' => array('ù','ú','ủ','ũ','ụ','ư','ừ','ứ','ử','ữ','ự'),
            'U' => array('Ù','Ú','Ủ','Ũ','Ụ','Ư','Ừ','Ứ','Ử','Ữ','Ự'),
            'y' => array('ỳ','ý','ỷ','ỹ','ỵ'),
            'Y' => array('Ỳ','Ý','Ỷ','Ỹ','Ỵ'),
        );

        foreach ($accents as $non_accent => $accent_list) {
            $str = str_replace($accent_list, $non_accent, $str);
        }

        return $str;
    }

    /**
     * Search Normalization Pipeline:
     * Raw Query -> trim -> lowercase -> Unicode normalization -> whitespace normalization -> accent normalization -> tokenization
     *
     * @param string $raw_query
     * @return array
     */
    public function normalize_query($raw_query) {
        if (!is_string($raw_query)) {
            return array(
                'raw'             => '',
                'normalized'      => '',
                'unaccent'        => '',
                'tokens'          => array(),
                'unaccent_tokens' => array(),
            );
        }

        $query = trim($raw_query);
        if (mb_strlen($query, 'UTF-8') > 200) {
            $query = mb_substr($query, 0, 200, 'UTF-8');
        }

        if (class_exists('Normalizer')) {
            $query = Normalizer::normalize($query, Normalizer::FORM_C);
        }

        $query = preg_replace('/\s+/u', ' ', $query);
        $query = trim($query);

        $lower = mb_strtolower($query, 'UTF-8');
        $unaccent = $this->remove_accents($lower);

        $tokens = preg_split('/\s+/u', $lower, -1, PREG_SPLIT_NO_EMPTY);
        $unaccent_tokens = preg_split('/\s+/u', $unaccent, -1, PREG_SPLIT_NO_EMPTY);

        return array(
            'raw'             => $raw_query,
            'normalized'      => $lower,
            'unaccent'        => $unaccent,
            'tokens'          => $tokens,
            'unaccent_tokens' => $unaccent_tokens,
        );
    }

    /**
     * Nhận diện lỗi chính tả và đề xuất từ khóa gần đúng nhất (Fuzzy Matching & Typo Suggestion)
     *
     * @param string $query_str
     * @return array|null ['suggested' => string, 'confidence' => float]
     */
    public function detect_typo_and_suggest($query_str) {
        $norm = $this->normalize_query($query_str);
        $unaccent = $norm['unaccent'];
        if (mb_strlen($unaccent, 'UTF-8') < 3) {
            return null;
        }

        $dict = $this->get_dictionary();
        $unaccent_map = $dict['unaccent_to_accent'];

        // 1. Nếu cụm từ không dấu khớp trực tiếp với 1 từ/cụm từ trong Viet74K:
        if (isset($unaccent_map[$unaccent])) {
            $accented = $unaccent_map[$unaccent];
            if (mb_strtolower($accented, 'UTF-8') !== $norm['normalized']) {
                return array(
                    'suggested'  => $accented,
                    'confidence' => 1.0,
                    'reason'     => 'accent_restoration',
                );
            }
        }

        // 2. Tìm kiếm trong cơ sở dữ liệu WordPress trước: nếu tiêu đề bài viết khớp gần đúng
        $wp_suggestion = $this->suggest_from_wordpress_content($norm);
        if ($wp_suggestion !== null && $wp_suggestion['confidence'] >= 0.75) {
            return $wp_suggestion;
        }

        // 3. Fuzzy search qua bucket tiền tố của Viet74K
        $query_len = mb_strlen($unaccent, 'UTF-8');
        $prefix3 = mb_substr($unaccent, 0, 3, 'UTF-8');
        $prefix2 = mb_substr($unaccent, 0, 2, 'UTF-8');

        $candidates = array();
        if (isset($dict['prefix_buckets'][$prefix3])) {
            $candidates = array_merge($candidates, $dict['prefix_buckets'][$prefix3]);
        }
        if (isset($dict['prefix_buckets'][$prefix2])) {
            $candidates = array_merge($candidates, $dict['prefix_buckets'][$prefix2]);
        }
        $candidates = array_unique($candidates);

        $best_candidate = null;
        $best_distance  = 999;
        $best_confidence = 0;

        foreach ($candidates as $cand) {
            $cand_unaccent = mb_strtolower($this->remove_accents($cand), 'UTF-8');
            $cand_len = mb_strlen($cand_unaccent, 'UTF-8');

            if (abs($cand_len - $query_len) > 2) {
                continue;
            }

            $dist = levenshtein($unaccent, $cand_unaccent);
            if ($dist <= 2 && $dist < $best_distance) {
                $max_len = max($query_len, $cand_len);
                $confidence = 1 - ($dist / $max_len);

                if ($confidence >= 0.75) {
                    $best_distance   = $dist;
                    $best_confidence = $confidence;
                    $best_candidate  = $cand;
                }
            }
        }

        if ($best_candidate !== null && mb_strtolower($best_candidate, 'UTF-8') !== $norm['normalized']) {
            return array(
                'suggested'  => $best_candidate,
                'confidence' => round($best_confidence, 2),
                'reason'     => 'fuzzy_wordlist',
            );
        }

        // 4. Token-by-token fuzzy correction nếu là cụm nhiều từ (ví dụ: 'khu nghi duon')
        if (count($norm['tokens']) >= 2) {
            $corrected_tokens = array();
            $has_correction = false;

            foreach ($norm['tokens'] as $tok) {
                $tok_un = $this->remove_accents($tok);
                if (isset($unaccent_map[$tok_un])) {
                    $corrected_tokens[] = $unaccent_map[$tok_un];
                    if ($unaccent_map[$tok_un] !== $tok) {
                        $has_correction = true;
                    }
                } else {
                    $tok_p2 = mb_substr($tok_un, 0, 2, 'UTF-8');
                    $tok_cand = isset($dict['prefix_buckets'][$tok_p2]) ? $dict['prefix_buckets'][$tok_p2] : array();
                    $best_t = $tok;
                    $best_td = 999;
                    $tok_len = mb_strlen($tok_un, 'UTF-8');

                    foreach ($tok_cand as $tc) {
                        $tc_un = mb_strtolower($this->remove_accents($tc), 'UTF-8');
                        if (abs(mb_strlen($tc_un, 'UTF-8') - $tok_len) > 1) continue;
                        $td = levenshtein($tok_un, $tc_un);
                        if ($td <= 1 && $td < $best_td) {
                            $best_td = $td;
                            $best_t = $tc;
                        }
                    }

                    if ($best_t !== $tok) {
                        $corrected_tokens[] = $best_t;
                        $has_correction = true;
                    } else {
                        $corrected_tokens[] = $tok;
                    }
                }
            }

            if ($has_correction) {
                $assembled = implode(' ', $corrected_tokens);
                $assembled_un = mb_strtolower($this->remove_accents($assembled), 'UTF-8');
                if (isset($unaccent_map[$assembled_un])) {
                    $assembled = $unaccent_map[$assembled_un];
                }

                if (mb_strtolower($assembled, 'UTF-8') !== $norm['normalized']) {
                    return array(
                        'suggested'  => $assembled,
                        'confidence' => 0.88,
                        'reason'     => 'token_fuzzy_assembly',
                    );
                }
            }
        }

        return null;
    }

    /**
     * Gợi ý từ tiêu đề bài viết và chuyên mục thực tế trong WordPress
     */
    private function suggest_from_wordpress_content($norm) {
        global $wpdb;
        $unaccent_query = $norm['unaccent'];

        $items = array();
        if ($wpdb && is_object($wpdb) && method_exists($wpdb, 'get_col')) {
            $db_items = $wpdb->get_col("
                SELECT post_title FROM {$wpdb->posts} 
                WHERE post_type = 'post' AND post_status = 'publish' 
                LIMIT 50
            ");
            if (is_array($db_items)) {
                $items = $db_items;
            }
        } elseif (function_exists('get_posts')) {
            $sample_posts = get_posts(array('posts_per_page' => 50));
            if (is_array($sample_posts)) {
                foreach ($sample_posts as $sp) {
                    if (is_object($sp) && isset($sp->post_title)) {
                        $items[] = $sp->post_title;
                    }
                }
            }
        }

        if (function_exists('get_categories')) {
            $categories = get_categories(array('hide_empty' => false));
            if (is_array($categories)) {
                foreach ($categories as $cat) {
                    if (is_object($cat) && isset($cat->name)) {
                        $items[] = $cat->name;
                    }
                }
            }
        }

        $best_match = null;
        $highest_confidence = 0;
        $query_len = mb_strlen($unaccent_query, 'UTF-8');

        foreach ($items as $item) {
            $item_unaccent = mb_strtolower($this->remove_accents($item), 'UTF-8');

            // Kiểm tra xem item có chứa cụm từ không
            if (strpos($item_unaccent, $unaccent_query) !== false) {
                return null;
            }

            // Tách các n-gram từ $item để so sánh
            $words = explode(' ', $item);
            $words_count = count($words);
            $token_count = max(1, count($norm['tokens']));

            for ($i = 0; $i <= $words_count - $token_count; $i++) {
                $sub_phrase = implode(' ', array_slice($words, $i, $token_count));
                $sub_unaccent = mb_strtolower($this->remove_accents($sub_phrase), 'UTF-8');

                $dist = levenshtein($unaccent_query, $sub_unaccent);
                $max_len = max($query_len, mb_strlen($sub_unaccent, 'UTF-8'));
                if ($max_len > 0) {
                    $conf = 1 - ($dist / $max_len);
                    if ($conf > $highest_confidence && $conf >= 0.75) {
                        $highest_confidence = $conf;
                        $best_match = $sub_phrase;
                    }
                }
            }
        }

        if ($best_match !== null) {
            return array(
                'suggested'  => $best_match,
                'confidence' => round($highest_confidence, 2),
                'reason'     => 'wp_content_match',
            );
        }

        return null;
    }

    /**
     * Tìm kiếm và tính điểm xếp hạng (Ranking Engine)
     *
     * @param string $query_str Từ khóa tìm kiếm
     * @param int $limit Số lượng bài viết trả về
     * @param int $offset Phân trang offset
     * @return array
     */
    public function search_posts($query_str, $limit = 10, $offset = 0) {
        $norm = $this->normalize_query($query_str);
        if ($norm['normalized'] === '') {
            return array(
                'posts'      => array(),
                'total'      => 0,
                'suggestion' => null,
                'categories' => array(),
            );
        }

        $suggestion = $this->detect_typo_and_suggest($query_str);

        // Lấy tất cả bài viết publish để tính điểm và xếp hạng
        $args = array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 100,
        );
        $all_posts = get_posts($args);

        $scored_posts = array();
        $query_tokens = $norm['tokens'];
        $unaccent_tokens = $norm['unaccent_tokens'];
        $clean_query = $norm['normalized'];
        $unaccent_query = $norm['unaccent'];

        foreach ($all_posts as $post) {
            $score = 0;
            $title = $post->post_title;
            $content = wp_strip_all_tags($post->post_content);
            $excerpt = !empty($post->post_excerpt) ? $post->post_excerpt : wp_trim_words($content, 35, '...');

            $title_lower = mb_strtolower($title, 'UTF-8');
            $title_unaccent = mb_strtolower($this->remove_accents($title), 'UTF-8');

            $content_lower = mb_strtolower($content, 'UTF-8');
            $content_unaccent = mb_strtolower($this->remove_accents($content), 'UTF-8');

            // 1. Exact Match trong Title
            if ($title_lower === $clean_query) {
                $score += 150;
            } elseif (strpos($title_lower, $clean_query) !== false) {
                $score += 90;
            }

            // 2. Unaccented Match trong Title (tiếng Việt không dấu)
            if ($title_unaccent === $unaccent_query) {
                $score += 120;
            } elseif (strpos($title_unaccent, $unaccent_query) !== false) {
                $score += 70;
            }

            // 3. Prefix Match trong Title
            if (strpos($title_lower, $clean_query) === 0 || strpos($title_unaccent, $unaccent_query) === 0) {
                $score += 40;
            }

            // 4. Token Matching trong Title
            foreach ($unaccent_tokens as $t) {
                if ($t !== '' && strpos($title_unaccent, $t) !== false) {
                    $score += 20;
                }
            }

            // 5. Category matching
            $post_cats = get_the_category($post->ID);
            if (is_array($post_cats)) {
                foreach ($post_cats as $cat) {
                    $cat_name_lower = mb_strtolower($cat->name, 'UTF-8');
                    $cat_name_unaccent = mb_strtolower($this->remove_accents($cat->name), 'UTF-8');
                    if (strpos($cat_name_lower, $clean_query) !== false || strpos($cat_name_unaccent, $unaccent_query) !== false) {
                        $score += 35;
                    }
                }
            }

            // 7. Fuzzy matching từ gợi ý typo nếu có
            if ($suggestion && isset($suggestion['suggested'])) {
                $sug_norm = $this->normalize_query($suggestion['suggested']);
                if (strpos($title_unaccent, $sug_norm['unaccent']) !== false) {
                    $score += 25;
                }
            }

            if ($score > 0) {
                $scored_posts[] = array(
                    'post'  => $post,
                    'score' => $score,
                );
            }
        }

        // Sắp xếp theo điểm số giảm dần
        usort($scored_posts, function($a, $b) {
            return $b['score'] - $a['score'];
        });

        $total_found = count($scored_posts);
        $paginated_posts = array_slice($scored_posts, $offset, $limit);

        // Format dữ liệu trả về cho UI
        $results = array();
        foreach ($paginated_posts as $item) {
            $p = $item['post'];

            $thumb_url = '';
            if (has_post_thumbnail($p->ID)) {
                $thumb_url = get_the_post_thumbnail_url($p->ID, 'medium_large');
            } elseif (function_exists('cms_nhomc_get_post_thumbnail_url')) {
                $thumb_url = cms_nhomc_get_post_thumbnail_url($p->ID);
            }

            $categories = array();
            $cats = get_the_category($p->ID);
            if (!empty($cats) && is_array($cats)) {
                foreach ($cats as $cat) {
                    $categories[] = array(
                        'id'   => $cat->term_id,
                        'name' => $cat->name,
                        'link' => get_category_link($cat->term_id),
                    );
                }
            }

            $excerpt_raw = !empty($p->post_excerpt) ? $p->post_excerpt : wp_trim_words(wp_strip_all_tags($p->post_content), 30, '...');

            $results[] = array(
                'id'                  => $p->ID,
                'title'               => get_the_title($p),
                'highlighted_title'   => cms_nhomc_highlight_keyword(get_the_title($p), $query_str),
                'permalink'           => get_permalink($p),
                'date'                => array(
                    'day'   => get_the_date('d', $p),
                    'month' => get_the_date('m', $p),
                    'year'  => get_the_date('Y', $p),
                ),
                'thumbnail_url'       => $thumb_url,
                'categories'          => $categories,
                'excerpt'             => $excerpt_raw,
                'highlighted_excerpt' => cms_nhomc_highlight_keyword($excerpt_raw, $query_str),
                'score'               => $item['score'],
            );
        }

        // Tìm các category khớp từ khóa
        $matching_categories = array();
        $all_categories = get_categories(array('hide_empty' => false));
        if (is_array($all_categories)) {
            foreach ($all_categories as $c) {
                $c_unaccent = mb_strtolower($this->remove_accents($c->name), 'UTF-8');
                if (strpos($c_unaccent, $unaccent_query) !== false) {
                    $matching_categories[] = array(
                        'id'    => $c->term_id,
                        'name'  => $c->name,
                        'link'  => get_category_link($c->term_id),
                        'count' => $c->count,
                    );
                }
            }
        }

        return array(
            'posts'      => $results,
            'total'      => $total_found,
            'suggestion' => $suggestion,
            'categories' => $matching_categories,
        );
    }

    /**
     * AJAX Autocomplete Endpoint
     */
    public function ajax_autocomplete() {
        check_ajax_referer('cms_nhomc_search_nonce', 'nonce', false);

        $q = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
        $data = $this->search_posts($q, 6, 0);

        wp_send_json_success($data);
    }

    /**
     * AJAX Smart Search Endpoint (kết quả đầy đủ realtime)
     */
    public function ajax_search() {
        check_ajax_referer('cms_nhomc_search_nonce', 'nonce', false);

        $q      = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
        $page   = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit  = 10;
        $offset = ($page - 1) * $limit;

        $data = $this->search_posts($q, $limit, $offset);
        $data['page']       = $page;
        $data['total_page'] = ceil($data['total'] / $limit);

        wp_send_json_success($data);
    }

    /**
     * REST API Autocomplete
     */
    public function rest_autocomplete($request) {
        $q = $request->get_param('q');
        $q = is_string($q) ? sanitize_text_field($q) : '';
        return rest_ensure_response($this->search_posts($q, 6, 0));
    }

    /**
     * REST API Search
     */
    public function rest_search($request) {
        $q      = $request->get_param('q');
        $q      = is_string($q) ? sanitize_text_field($q) : '';
        $page   = max(1, intval($request->get_param('page')));
        $limit  = 10;
        $offset = ($page - 1) * $limit;

        $data = $this->search_posts($q, $limit, $offset);
        $data['page']       = $page;
        $data['total_page'] = ceil($data['total'] / $limit);

        return rest_ensure_response($data);
    }

    /**
     * Tùy biến SQL tìm kiếm WordPress để hỗ trợ tiếng Việt không dấu và có dấu
     */
    public function filter_posts_search_sql($search, $query) {
        if (!is_admin() && $query->is_main_query() && $query->is_search()) {
            global $wpdb;
            $s = $query->get('s');
            if (!is_string($s) || trim($s) === '') {
                return $search;
            }

            $norm = $this->normalize_query($s);
            $clean_query = $norm['normalized'];
            $unaccent_query = $norm['unaccent'];

            $like_raw      = '%' . $wpdb->esc_like($s) . '%';
            $like_clean    = '%' . $wpdb->esc_like($clean_query) . '%';
            $like_unaccent = '%' . $wpdb->esc_like($unaccent_query) . '%';

            $search = $wpdb->prepare("
                AND (
                    ({$wpdb->posts}.post_title LIKE %s)
                    OR ({$wpdb->posts}.post_title LIKE %s)
                    OR ({$wpdb->posts}.post_title LIKE %s)
                    OR ({$wpdb->posts}.ID IN (
                        SELECT tr_sub.object_id 
                        FROM {$wpdb->term_relationships} tr_sub 
                        INNER JOIN {$wpdb->term_taxonomy} tt_sub ON tr_sub.term_taxonomy_id = tt_sub.term_taxonomy_id 
                        INNER JOIN {$wpdb->terms} t_sub ON tt_sub.term_id = t_sub.term_id 
                        WHERE tt_sub.taxonomy IN ('category', 'post_tag') 
                          AND (t_sub.name LIKE %s OR t_sub.slug LIKE %s)
                    ))
                )
            ", $like_raw, $like_clean, $like_unaccent, $like_clean, $like_clean);
        }
        return $search;
    }
}

// Khởi tạo instance
CMS_NhomC_Vietnamese_Search::get_instance();

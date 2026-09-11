<?php
/**
 * Template part for displaying results in search pages
 * [Ponytail Standard] Tách component độc lập, tái sử dụng cao, không trùng lặp code (DRY)
 *
 * @package CMS_NhomC
 */

$post_id    = get_the_ID();
$post_date  = get_the_date('d', $post_id);
$post_month = get_the_date('m', $post_id);
$post_year  = get_the_date('Y', $post_id);

$raw_title = get_the_title();
$title_to_display = (is_string($raw_title) && trim($raw_title) !== '') ? $raw_title : __('(Không có tiêu đề)', 'cms-nhomc');
$highlighted_title = cms_nhomc_highlight_keyword($title_to_display);

$raw_excerpt = get_the_excerpt();
$highlighted_excerpt = is_string($raw_excerpt) ? cms_nhomc_highlight_keyword($raw_excerpt) : '';
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('search-post-card'); ?>>
    
    <!-- Cột Trái: Ảnh Thumbnail bài viết -->
    <div class="search-post-thumb">
        <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
            <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('medium_large', array(
                    'class'   => 'search-thumb-img',
                    'alt'     => the_title_attribute(array('echo' => false)),
                    'loading' => 'lazy',
                )); ?>
            <?php else : ?>
                <div class="search-thumb-placeholder">
                    <div class="placeholder-icon">
                        <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                            <circle cx="9" cy="9" r="2"/>
                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                        </svg>
                    </div>
                    <span><?php bloginfo('name'); ?></span>
                </div>
            <?php endif; ?>
        </a>
    </div>

    <!-- Cột Phải: Thông tin bài viết -->
    <div class="search-post-body">
        
        <!-- Hàng Ngày tháng + Tiêu đề & Danh mục -->
        <div class="search-post-header">
            <!-- Khối Date Badge phong cách FIT TDC -->
            <div class="search-date-badge">
                <div class="date-day"><?php echo esc_html($post_date); ?></div>
                <div class="date-month-year">
                    <span class="date-month"><?php printf(esc_html__('Tháng %s', 'cms-nhomc'), esc_html($post_month)); ?></span>
                    <span class="date-year"><?php echo esc_html($post_year); ?></span>
                </div>
            </div>

            <!-- Cụm Tiêu đề và Categories -->
            <div class="search-title-section">
                <h2 class="search-post-title">
                    <a href="<?php the_permalink(); ?>">
                        <?php echo wp_kses_post($highlighted_title); ?>
                    </a>
                </h2>

                <div class="search-post-categories">
                    <span class="cat-label"><?php esc_html_e('Chuyên mục', 'cms-nhomc'); ?></span>
                    <div class="cat-links">
                        <?php
                        $categories = get_the_category();
                        if (is_array($categories) && !empty($categories)) {
                            foreach ($categories as $index => $category) {
                                if ($index > 0) echo ', ';
                                echo '<a href="' . esc_url(get_category_link($category->term_id)) . '">' . esc_html($category->name) . '</a>';
                            }
                        } else {
                            echo '<span>' . esc_html__('Chưa phân loại', 'cms-nhomc') . '</span>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Đoạn tóm tắt Excerpt có highlight từ khóa -->
        <div class="search-post-excerpt">
            <p><?php echo wp_kses_post($highlighted_excerpt); ?></p>
        </div>

        <!-- Nút Xem chi tiết -->
        <div class="search-post-footer">
            <a href="<?php the_permalink(); ?>" class="search-readmore-link">
                <?php esc_html_e('Xem chi tiết', 'cms-nhomc'); ?>
                <svg viewBox="0 0 20 20" width="16" height="16" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>

    </div>

</article>


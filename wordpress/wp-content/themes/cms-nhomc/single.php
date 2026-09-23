<?php
/**
 * Single post template for CMS Nhóm C
 *
 * @package CMS_NhomC
 */

get_header();
?>

<div class="site-content cms-container-layout cms-single-container-layout">
    <!-- Hàng 3 cột theo sơ đồ: Categories (Trái) | Detail (Giữa) | Recent Post (Phải) -->
    <div class="cms-single-grid">
        
        <!-- Cột trái: CHỈ CÓ MÌNH Categories (Module 9) -->
        <aside class="cms-single-sidebar-left" aria-label="Chuyên mục">
            <?php cms_nhomc_render_categories_widget(); ?>
        </aside>

        <!-- Cột giữa: Detail (Module 6) -->
        <main class="cms-single-main-column">
            <?php while (have_posts()) : the_post(); 
                $post_id   = get_the_ID();
                $categories = get_the_category();
                $primary_cat = !empty($categories) ? $categories[0] : null;
            ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('cms-single-article'); ?>>
                    
                    <!-- Breadcrumbs -->
                    <nav class="cms-breadcrumb" aria-label="Breadcrumb">
                        <a href="<?php echo esc_url(home_url('/')); ?>">Trang Chủ</a>
                        <span class="sep">/</span>
                        <?php if ($primary_cat) : ?>
                            <a href="<?php echo esc_url(get_category_link($primary_cat->term_id)); ?>"><?php echo esc_html($primary_cat->name); ?></a>
                            <span class="sep">/</span>
                        <?php else : ?>
                            <a href="<?php echo esc_url(home_url('/category/tin-tuc/')); ?>">Tin Tức</a>
                            <span class="sep">/</span>
                        <?php endif; ?>
                        <span class="current"><?php the_title(); ?></span>
                    </nav>

                    <!-- Tiêu đề bài viết -->
                    <h1 class="single-post-title"><?php the_title(); ?></h1>

                    <!-- Dẫn đề / Sapo (Excerpt) -->
                    <?php if (has_excerpt()) : ?>
                        <div class="single-post-lead">
                            <?php the_excerpt(); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Nội dung chi tiết bài viết -->
                    <div class="single-post-content entry-content">
                        <?php the_content(); ?>
                    </div>

                    <!-- Tags / Chuyên mục -->
                    <div class="single-post-tags">
                        <span class="tags-label">Tags:</span>
                        <?php
                        $tags = get_the_tags();
                        if (!empty($tags)) {
                            $tag_links = array();
                            foreach ($tags as $tag) {
                                $tag_links[] = '<a href="' . esc_url(get_tag_link($tag->term_id)) . '">' . esc_html($tag->name) . '</a>';
                            }
                            echo implode(', ', $tag_links);
                        } elseif ($primary_cat) {
                            echo '<a href="' . esc_url(get_category_link($primary_cat->term_id)) . '">' . esc_html($primary_cat->name) . '</a>';
                        } else {
                            echo '<a href="#">Tin Tức</a>';
                        }
                        ?>
                    </div>
                    <!-- ================= MODULE 18: CHIA SẺ & SAO CHÉP LIÊN KẾT ================= -->
                    <div class="cms-social-share-box">
                        <div class="cms-share-wrapper">
                            <div class="cms-share-title">
                                <svg class="cms-share-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="18" cy="5" r="3"></circle>
                                    <circle cx="6" cy="12" r="3"></circle>
                                    <circle cx="18" cy="19" r="3"></circle>
                                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                                </svg>
                                <span>Chia sẻ bài viết:</span>
                            </div>

                            <div class="cms-share-buttons">
                                <!-- Nút Facebook -->
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" 
                                   target="_blank" 
                                   rel="noopener noreferrer" 
                                   class="cms-share-btn cms-share-fb"
                                   title="Chia sẻ lên Facebook">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                    <span>Facebook</span>
                                </a>

                                <!-- Nút Zalo -->
                                <a href="https://zalo.me/share?url=<?php echo urlencode(get_permalink()); ?>" 
                                   target="_blank" 
                                   rel="noopener noreferrer" 
                                   class="cms-share-btn cms-share-zalo"
                                   title="Chia sẻ qua Zalo">
                                    <svg class="cms-zalo-icon" width="18" height="18" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M22.782 0.166H27.199C33.265 0.166 36.81 1.057 39.957 2.744C43.104 4.431 45.588 6.896 47.256 10.043C48.943 13.19 49.834 16.735 49.834 22.801V27.199C49.834 33.265 48.943 36.81 47.256 39.957C45.569 43.104 43.104 45.588 39.957 47.256C36.81 48.943 33.265 49.834 27.199 49.834H22.801C16.735 49.834 13.19 48.943 10.043 47.256C6.896 45.569 4.412 43.104 2.744 39.957C1.057 36.81 0.166 33.265 0.166 27.199V22.801C0.166 16.735 1.057 13.19 2.744 10.043C4.431 6.896 6.896 4.412 10.043 2.744C13.171 1.057 16.735 0.166 22.782 0.166Z" fill="#0068FF"/>
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.779 43.589C10.102 43.846 13.006 43.184 15.068 42.183C24.023 47.132 38.02 46.895 46.492 41.473C46.821 40.98 47.128 40.468 47.413 39.936C49.106 36.778 50 33.22 50 27.132V22.718C50 16.629 49.106 13.071 47.413 9.913C45.739 6.754 43.246 4.281 40.088 2.588C36.929 0.894 33.371 0 27.283 0H22.85C17.664 0 14.298 0.653 11.47 1.899C11.315 2.037 11.164 2.178 11.015 2.321C2.717 10.32 2.087 27.659 9.123 37.078C9.131 37.092 9.139 37.106 9.149 37.12C10.233 38.719 9.187 41.515 7.551 43.152C7.284 43.399 7.379 43.551 7.779 43.589Z" fill="#ffffff"/>
                                        <path d="M20.563 17H10.838V19.085H17.587L10.933 27.332C10.724 27.635 10.573 27.919 10.573 28.564V29.095H19.748C20.203 29.095 20.582 28.716 20.582 28.261V27.142H13.492L19.748 19.294C19.843 19.18 20.013 18.972 20.089 18.877L20.127 18.82C20.487 18.289 20.563 17.834 20.563 17.284V17Z" fill="#0068FF"/>
                                        <path d="M32.942 29.095H34.326V17H32.24V28.393C32.24 28.773 32.544 29.095 32.942 29.095Z" fill="#0068FF"/>
                                        <path d="M25.814 19.692C23.198 19.692 21.075 21.816 21.075 24.432C21.075 27.048 23.198 29.171 25.814 29.171C28.43 29.171 30.553 27.048 30.553 24.432C30.572 21.816 28.449 19.692 25.814 19.692ZM25.814 27.218C24.279 27.218 23.027 25.967 23.027 24.432C23.027 22.896 24.279 21.645 25.814 21.645C27.35 21.645 28.601 22.896 28.601 24.432C28.601 25.967 27.369 27.218 25.814 27.218Z" fill="#0068FF"/>
                                        <path d="M40.487 19.616C37.852 19.616 35.71 21.758 35.71 24.393C35.71 27.029 37.852 29.171 40.487 29.171C43.122 29.171 45.264 27.029 45.264 24.393C45.264 21.758 43.122 19.616 40.487 19.616ZM40.487 27.218C38.932 27.218 37.681 25.967 37.681 24.412C37.681 22.858 38.932 21.607 40.487 21.607C42.041 21.607 43.292 22.858 43.292 24.412C43.292 25.967 42.041 27.218 40.487 27.218Z" fill="#0068FF"/>
                                        <path d="M29.456 29.094H30.575V19.957H28.622V28.279C28.622 28.715 29.001 29.094 29.456 29.094Z" fill="#0068FF"/>
                                    </svg>
                                    <span>Zalo</span>
                                </a>

                                <!-- Nút Sao chép liên kết -->
                                <button type="button" 
                                        class="cms-share-btn cms-btn-copy"
                                        id="cmsCopyLinkBtn"
                                        data-url="<?php echo esc_url(get_permalink()); ?>"
                                        title="Sao chép liên kết">
                                    <svg class="cms-copy-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                    </svg>
                                    <svg class="cms-check-icon d-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    <span class="cms-copy-text">Sao chép link</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- ================= HẾT MODULE 18 ================= -->


                    <!-- Bài viết liên quan -->
                    <?php
                    $related_args = array(
                        'posts_per_page' => 3,
                        'post__not_in'   => array($post_id),
                        'orderby'        => 'date',
                        'order'          => 'DESC',
                    );

                    if ($primary_cat) {
                        $related_args['cat'] = $primary_cat->term_id;
                    }

                    $related_query = new WP_Query($related_args);

                    if ($related_query->have_posts()) :
                    ?>
                        <section class="single-related-section">
                            <h3 class="related-heading">Bài viết liên quan</h3>
                            <div class="related-grid">
                                <?php while ($related_query->have_posts()) : $related_query->the_post(); 
                                    $rel_thumb = cms_nhomc_get_post_thumbnail_url(get_the_ID());
                                ?>
                                    <div class="related-item">
                                        <div class="related-thumb">
                                            <a href="<?php the_permalink(); ?>">
                                                <img src="<?php echo esc_url($rel_thumb); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                                            </a>
                                        </div>
                                        <h4 class="related-title">
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        </h4>
                                    </div>
                                <?php endwhile; wp_reset_postdata(); ?>
                            </div>
                        </section>
                    <?php endif; ?>

                </article>
            <?php endwhile; ?>
        </main>

        <!-- Cột phải: CHỈ CÓ MÌNH Recent post (Module 10) -->
        <aside class="cms-single-sidebar-right" aria-label="Bài viết mới">
            <?php cms_nhomc_render_recent_posts_widget(10, 'BÀI VIẾT MỚI'); ?>
        </aside>
    </div>

    <!-- Khối hàng bên dưới: Prev - Next Post (Module 7) -->
    <?php
    $prev_post = get_previous_post();
    $next_post = get_next_post();

    if (!empty($next_post) || !empty($prev_post)) :
    ?>
    <section class="cms-single-bottom-nav">
        <nav class="single-post-nav tdc-post-nav" aria-label="Điều hướng bài viết">
            <ul class="tdc-post-nav-list">
                <?php if (!empty($next_post)) : 
                    $next_day   = get_the_date('d', $next_post->ID);
                    $next_month = get_the_date('m', $next_post->ID);
                    $next_year  = get_the_date('y', $next_post->ID);
                ?>
                    <li class="tdc-post-nav-item tdc-nav-next">
                        <div class="tdc-nav-date">
                            <span class="tdc-day-month">
                                <span class="tdc-day"><?php echo esc_html($next_day); ?></span>
                                <span class="tdc-divider"></span>
                                <span class="tdc-month"><?php echo esc_html($next_month); ?></span>
                            </span>
                            <span class="tdc-year"><?php echo esc_html($next_year); ?></span>
                        </div>
                        <div class="tdc-nav-title-wrap">
                            <a href="<?php echo esc_url(get_permalink($next_post->ID)); ?>" class="tdc-nav-link">
                                <?php echo esc_html(get_the_title($next_post->ID)); ?>
                            </a>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if (!empty($prev_post)) : 
                    $prev_day   = get_the_date('d', $prev_post->ID);
                    $prev_month = get_the_date('m', $prev_post->ID);
                    $prev_year  = get_the_date('y', $prev_post->ID);
                ?>
                    <li class="tdc-post-nav-item tdc-nav-prev">
                        <div class="tdc-nav-date">
                            <span class="tdc-day-month">
                                <span class="tdc-day"><?php echo esc_html($prev_day); ?></span>
                                <span class="tdc-divider"></span>
                                <span class="tdc-month"><?php echo esc_html($prev_month); ?></span>
                            </span>
                            <span class="tdc-year"><?php echo esc_html($prev_year); ?></span>
                        </div>
                        <div class="tdc-nav-title-wrap">
                            <a href="<?php echo esc_url(get_permalink($prev_post->ID)); ?>" class="tdc-nav-link">
                                <?php echo esc_html(get_the_title($prev_post->ID)); ?>
                            </a>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </section>
    <?php endif; ?>

    <!-- Khối hàng bên dưới: Comments (Module 8) -->
    <?php
    if (comments_open() || get_comments_number()) :
    ?>
    <section class="cms-single-bottom-comments">
        <?php comments_template(); ?>
    </section>
    <?php endif; ?>
</div>

<!-- JavaScript xử lý sao chép liên kết (Module 18) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    var copyBtn = document.getElementById('cmsCopyLinkBtn');
    if (!copyBtn) return;

    var resetTimer = null;

    copyBtn.addEventListener('click', function (e) {
        e.preventDefault();
        var urlToCopy = this.getAttribute('data-url') || window.location.href;
        var textSpan = this.querySelector('.cms-copy-text');

        function copyText(text) {
            // Trường hợp 1: Trình duyệt hỗ trợ Clipboard API và ở secure context
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text);
            }
            // Trường hợp 2: Fallback bằng execCommand cho localhost / HTTP
            return new Promise(function (resolve, reject) {
                var textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.top = '-9999px';
                textArea.style.left = '-9999px';
                textArea.setAttribute('readonly', '');
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    var successful = document.execCommand('copy');
                    document.body.removeChild(textArea);
                    if (successful) {
                        resolve();
                    } else {
                        reject(new Error('execCommand copy failed'));
                    }
                } catch (err) {
                    document.body.removeChild(textArea);
                    reject(err);
                }
            });
        }

        copyText(urlToCopy).then(function () {
            // Đổi giao diện nút sang màu xanh lá và hiển thị 'Đã sao chép!'
            copyBtn.classList.add('copied');
            if (textSpan) textSpan.textContent = 'Đã sao chép!';

            // Tự động trở về trạng thái cũ sau 2 giây (2000ms)
            clearTimeout(resetTimer);
            resetTimer = setTimeout(function () {
                copyBtn.classList.remove('copied');
                if (textSpan) textSpan.textContent = 'Sao chép link';
            }, 2000);
        }).catch(function (error) {
            console.error('Không thể tự động sao chép: ', error);
            prompt('Hãy nhấn Ctrl+C để sao chép liên kết bài viết:', urlToCopy);
        });
    });
});
</script>

<?php
get_footer();



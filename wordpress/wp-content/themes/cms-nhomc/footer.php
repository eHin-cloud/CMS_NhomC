<?php
/**
 * Footer template cho Theme CMS Nhóm C
 * Thiết kế chính xác 100% theo mẫu: https://bootsnipp.com/snippets/rlXdE
 *
 * @package CMS_NhomC
 */

/**
 * Hiển thị widget_test_4 tại:
 * - Trang chủ (is_front_page() || is_home())
 * - Trang danh sách (is_archive() || is_search())
 * - Trang chi tiết (is_single())
 * Khu vực hiển thị: Phía trên Footer
 */
if ( is_front_page() || is_home() || is_archive() || is_search() || is_single() ) {
    if ( function_exists('cms_nhomc_render_widget_test_4') ) {
        cms_nhomc_render_widget_test_4();
    }
}
?>

<!-- Footer -->
<section id="footer">
    <!-- Nút 3 chấm góc phải trên nếu có theo mẫu Bootsnipp -->
    <div class="footer-top-options">
        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
    </div>

    <div class="container">
        <div class="row text-center text-xs-center text-sm-left text-md-left">
            <!-- Cột 1: Comments -->
            <div class="col-xs-12 col-sm-4 col-md-4">
                <h5>Comments</h5>
                <ul class="list-unstyled quick-links">
                    <?php
                    $footer_comments = get_comments(array(
                        'number'      => 10,
                        'status'      => 'approve',
                        'post_status' => 'publish',
                        'type'        => 'comment',
                    ));

                    $display_comments = array();
                    if (!empty($footer_comments)) {
                        foreach ($footer_comments as $cmt) {
                            if (strpos($cmt->comment_content, 'Xin chào, đây là một bình luận') !== false) {
                                continue;
                            }
                            $raw_content = wp_strip_all_tags($cmt->comment_content);
                            $author = !empty($cmt->comment_author) ? $cmt->comment_author : 'Guest';
                            $display_comments[] = array(
                                'text'  => $author . ': ' . wp_trim_words($raw_content, 6, '...'),
                                'title' => $author . ': ' . $raw_content,
                                'link'  => get_comment_link($cmt),
                            );
                            if (count($display_comments) >= 5) {
                                break;
                            }
                        }
                    }

                    if (empty($display_comments)) {
                        $sample_comments_text = array(
                            'Bài viết rất hay và chi tiết!',
                            'Cảm ơn tác giả đã chia sẻ nội dung này.',
                            'Bài viết thật sự hữu ích cho dự án của tôi.',
                            'Hướng dẫn rất rõ ràng, áp dụng được ngay.',
                            'Mong tác giả có thêm nhiều bài viết chất lượng.',
                        );
                        $recent_posts = get_posts(array('numberposts' => 5, 'post_status' => 'publish'));
                        foreach ($sample_comments_text as $idx => $sample_text) {
                            $link = isset($recent_posts[$idx]) ? get_permalink($recent_posts[$idx]->ID) : home_url('/');
                            $display_comments[] = array(
                                'text'  => $sample_text,
                                'title' => $sample_text,
                                'link'  => $link,
                            );
                        }
                    }

                    foreach ($display_comments as $item) :
                    ?>
                        <li>
                            <a href="<?php echo esc_url($item['link']); ?>" title="<?php echo esc_attr($item['title']); ?>">
                                <i class="fa fa-angle-double-right"></i><?php echo esc_html($item['text']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Cột 2: Categories -->
            <div class="col-xs-12 col-sm-4 col-md-4">
                <h5>Categories</h5>
                <ul class="list-unstyled quick-links">
                    <?php
                    $footer_categories = get_categories(array(
                        'orderby'    => 'count',
                        'order'      => 'DESC',
                        'number'     => 10,
                        'hide_empty' => false,
                    ));

                    $display_categories = array();
                    if (!empty($footer_categories)) {
                        foreach ($footer_categories as $cat) {
                            if ($cat->slug !== 'uncategorized' && $cat->slug !== 'chua-phan-loai') {
                                $display_categories[] = array(
                                    'name' => $cat->name,
                                    'link' => get_category_link($cat->term_id),
                                );
                            }
                        }
                    }

                    if (empty($display_categories)) {
                        $display_categories = array(
                            array('name' => '.Net Developer', 'link' => home_url('/category/net-developer/')),
                            array('name' => 'Thực Tập Sinh Tester', 'link' => home_url('/category/thuc-tap-sinh-tester/')),
                            array('name' => 'Trợ giảng lập trình - Part time', 'link' => home_url('/category/tro-giang-lap-trinh/')),
                            array('name' => 'Frontend Development', 'link' => home_url('/category/frontend/')),
                            array('name' => 'Backend Development', 'link' => home_url('/category/backend/')),
                        );
                    } else {
                        $display_categories = array_slice($display_categories, 0, 5);
                    }

                    foreach ($display_categories as $item) :
                    ?>
                        <li>
                            <a href="<?php echo esc_url($item['link']); ?>" title="<?php echo esc_attr($item['name']); ?>">
                                <i class="fa fa-angle-double-right"></i><?php echo esc_html($item['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Cột 3: Last Posts -->
            <div class="col-xs-12 col-sm-4 col-md-4">
                <h5>Last Posts</h5>
                <ul class="list-unstyled quick-links">
                    <?php
                    $footer_recent_posts = wp_get_recent_posts(array(
                        'numberposts' => 5,
                        'post_status' => 'publish',
                        'orderby'     => 'date',
                        'order'       => 'DESC',
                    ));

                    $display_posts = array();
                    if (!empty($footer_recent_posts)) {
                        foreach ($footer_recent_posts as $post_item) {
                            $display_posts[] = array(
                                'title' => get_the_title($post_item['ID']),
                                'link'  => get_permalink($post_item['ID']),
                            );
                        }
                    }

                    if (empty($display_posts)) {
                        $display_posts = array(
                            array('title' => 'New Web Design', 'link' => home_url('/')),
                            array('title' => '21 000 Job Seekers', 'link' => home_url('/')),
                            array('title' => 'Awesome Employers', 'link' => home_url('/')),
                            array('title' => 'Lập Trình Web Hiện Đại', 'link' => home_url('/')),
                            array('title' => 'Kỹ Năng Phỏng Vấn IT', 'link' => home_url('/')),
                        );
                    }

                    foreach ($display_posts as $item) :
                    ?>
                        <li>
                            <a href="<?php echo esc_url($item['link']); ?>" title="<?php echo esc_attr($item['title']); ?>">
                                <i class="fa fa-angle-double-right"></i><?php echo esc_html($item['title']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Hàng Social Icons -->
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 mt-2 mt-sm-5">
                <ul class="list-unstyled list-inline social text-center">
                    <li class="list-inline-item"><a href="https://www.facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fa fa-facebook"></i></a></li>
                    <li class="list-inline-item"><a href="https://twitter.com" target="_blank" rel="noopener noreferrer" aria-label="Twitter"><i class="fa fa-twitter"></i></a></li>
                    <li class="list-inline-item"><a href="https://www.instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fa fa-instagram"></i></a></li>
                    <li class="list-inline-item"><a href="https://plus.google.com" target="_blank" rel="noopener noreferrer" aria-label="Google Plus"><i class="fa fa-google-plus"></i></a></li>
                    <li class="list-inline-item"><a href="mailto:contact@nhomc.com" aria-label="Email"><i class="fa fa-envelope"></i></a></li>
                </ul>
            </div>
        </div>

        <!-- Hàng bản quyền pháp lý và tác giả -->
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 mt-2 mt-sm-2 text-center text-white">
                <p><a href="https://www.nationaltransaction.com/" target="_blank" rel="noopener noreferrer" class="footer-legal-link">National Transaction Corporation</a> is a Registered MSP/ISO of Elavon, Inc. Georgia [a wholly owned subsidiary of U.S. Bancorp, Minneapolis, MN]</p>
                <p class="h6">&copy; All right Reversed. <a class="text-green ml-2" href="https://bootsnipp.com/snippets/rlXdE" target="_blank" rel="noopener noreferrer">Sunlimetech</a></p>
            </div>
        </div>
    </div>
</section>
<!-- ./Footer -->

<!-- Nút Cuộn lên đầu trang (Back to Top) tối ưu trải nghiệm trên di động -->
<button type="button" id="backToTopBtn" class="back-to-top" aria-label="Cuộn lên đầu trang" title="Lên đầu trang">
    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="18 15 12 9 6 15"></polyline>
    </svg>
</button>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var backToTopBtn = document.getElementById('backToTopBtn');
    if (backToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 280) {
                backToTopBtn.classList.add('visible');
            } else {
                backToTopBtn.classList.remove('visible');
            }
        }, { passive: true });

        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
});
</script>

<?php wp_footer(); ?>
</body>
</html>

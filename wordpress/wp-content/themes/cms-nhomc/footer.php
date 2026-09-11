<?php
/**
 * Footer template cho Theme CMS Nhóm C
 * Thiết kế chuẩn theo mẫu Bootsnipp: https://bootsnipp.com/snippets/rlXdE
 * Dữ liệu động lấy trực tiếp từ Database: Comment, Categories, Last posts.
 *
 * @package CMS_NhomC
 */
?>

<!-- Footer -->
<section id="footer">
    <div class="container">
        <div class="row text-center text-xs-center text-sm-left text-md-left">
            <!-- Cột 1: Comment (Bình luận mới nhất) -->
            <div class="col-xs-12 col-sm-4 col-md-4">
                <h5>Comment</h5>
                <ul class="list-unstyled quick-links">
                    <?php
                    $recent_comments = get_comments(array(
                        'number'      => 5,
                        'status'      => 'approve',
                        'post_status' => 'publish',
                    ));

                    if (!empty($recent_comments)) :
                        foreach ($recent_comments as $c) :
                            $author = esc_html($c->comment_author);
                            $post_title = get_the_title($c->comment_post_ID);
                            $comment_link = esc_url(get_comment_link($c));
                            $truncated_post = esc_html(wp_trim_words($post_title, 4, '...'));
                            $item_text = $author . ' on ' . $truncated_post;
                    ?>
                        <li>
                            <a href="<?php echo $comment_link; ?>" title="<?php echo esc_attr($author . ' trên bài: ' . $post_title); ?>">
                                <i class="fa fa-angle-double-right"></i><?php echo $item_text; ?>
                            </a>
                        </li>
                    <?php 
                        endforeach;
                    else : 
                    ?>
                        <li><a href="<?php echo esc_url(home_url('/')); ?>"><i class="fa fa-angle-double-right"></i>Chưa có bình luận nào</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Cột 2: Categories (Chuyên mục) -->
            <div class="col-xs-12 col-sm-4 col-md-4">
                <h5>Categories</h5>
                <ul class="list-unstyled quick-links">
                    <?php
                    $categories = get_categories(array(
                        'orderby'    => 'count',
                        'order'      => 'DESC',
                        'hide_empty' => false,
                        'number'     => 5,
                    ));

                    if (!empty($categories)) :
                        foreach ($categories as $cat) :
                            $cat_link = esc_url(get_category_link($cat->term_id));
                            $cat_name = esc_html($cat->name);
                    ?>
                        <li>
                            <a href="<?php echo $cat_link; ?>" title="Xem chuyên mục <?php echo esc_attr($cat_name); ?>">
                                <i class="fa fa-angle-double-right"></i><?php echo $cat_name; ?>
                            </a>
                        </li>
                    <?php 
                        endforeach;
                    else : 
                    ?>
                        <li><a href="<?php echo esc_url(home_url('/')); ?>"><i class="fa fa-angle-double-right"></i>Chưa có chuyên mục</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Cột 3: Last posts (Bài viết mới nhất) -->
            <div class="col-xs-12 col-sm-4 col-md-4">
                <h5>Last posts</h5>
                <ul class="list-unstyled quick-links">
                    <?php
                    $recent_posts = wp_get_recent_posts(array(
                        'numberposts' => 5,
                        'post_status' => 'publish',
                    ));

                    if (!empty($recent_posts)) :
                        foreach ($recent_posts as $post_item) :
                            $post_link = esc_url(get_permalink($post_item['ID']));
                            $post_title = esc_html(wp_trim_words($post_item['post_title'], 5, '...'));
                    ?>
                        <li>
                            <a href="<?php echo $post_link; ?>" title="<?php echo esc_attr($post_item['post_title']); ?>">
                                <i class="fa fa-angle-double-right"></i><?php echo $post_title; ?>
                            </a>
                        </li>
                    <?php 
                        endforeach;
                    else : 
                    ?>
                        <li><a href="<?php echo esc_url(home_url('/')); ?>"><i class="fa fa-angle-double-right"></i>Chưa có bài viết mới</a></li>
                    <?php endif; ?>
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

<?php wp_footer(); ?>
</body>
</html>

<?php
/**
 * The template for displaying comments
 * Giao diện bình luận theo chuẩn Bootsnipp rNEdR
 *
 * @package CMS_NhomC
 */

if (post_password_required()) {
    return;
}
?>

<div id="comments" class="cms-comments-section">

    <?php if (comments_open()) : ?>
        <!-- Post Form Begins (Bootsnipp rNEdR) -->
        <section class="card cms-post-form-card" id="respond">
            <div class="card-header d-flex justify-content-between align-items-center">
                <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="posts-tab" data-toggle="tab" href="#posts" role="tab" aria-controls="posts" aria-selected="true">Make a Post</a>
                    </li>
                </ul>
                <div class="cms-cancel-reply">
                    <?php cancel_comment_reply_link(__('Hủy trả lời', 'cms-nhomc')); ?>
                </div>
            </div>
            <div class="card-body">
                <form action="<?php echo esc_url(site_url('/wp-comments-post.php')); ?>" method="post" id="commentform" class="cms-comment-form">
                    
                    <?php if (!is_user_logged_in()) : ?>
                        <?php if (get_option('comment_registration')) : ?>
                            <div class="cms-must-login-alert">
                                Bạn phải <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>">đăng nhập</a> để đăng bình luận.
                            </div>
                        <?php else : ?>
                            <div class="cms-guest-row">
                                <div class="cms-guest-col">
                                    <input type="text" name="author" id="author" class="form-control cms-guest-input" placeholder="Tên của bạn *" value="<?php echo esc_attr($commenter['comment_author'] ?? ''); ?>" required="required" />
                                </div>
                                <div class="cms-guest-col">
                                    <input type="email" name="email" id="email" class="form-control cms-guest-input" placeholder="Email của bạn *" value="<?php echo esc_attr($commenter['comment_author_email'] ?? ''); ?>" required="required" />
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="posts" role="tabpanel" aria-labelledby="posts-tab">
                            <div class="form-group">
                                <label class="sr-only" for="comment">post</label>
                                <textarea class="form-control" name="comment" id="comment" rows="3" placeholder="What are you thinking..." required="required"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="text-right">
                        <button type="submit" name="submit" id="submit" class="btn btn-primary">share</button>
                    </div>

                    <?php comment_id_fields(); ?>
                    <?php do_action('comment_form', get_the_ID()); ?>
                </form>
            </div>
        </section>
        <!-- Post Form Ends -->
    <?php endif; ?>

    <!-- Danh sách bình luận -->
    <?php if (have_comments()) : ?>
        <div class="cms-comments-list-wrapper">
            <h4 class="cms-comments-title">
                <i class="fa fa-comments-o"></i> Bình luận
                <span class="cms-badge-count"><?php echo get_comments_number(); ?></span>
            </h4>

            <ol class="cms-comment-list">
                <?php
                wp_list_comments(array(
                    'style'       => 'ol',
                    'short_ping'  => true,
                    'avatar_size' => 48,
                    'callback'    => 'cms_nhomc_comment_callback',
                ));
                ?>
            </ol>

            <?php if (get_comment_pages_count() > 1 && get_option('page_comments')) : ?>
                <nav class="cms-comment-navigation" role="navigation">
                    <div class="nav-previous"><?php previous_comments_link(__('&larr; Bình luận cũ hơn', 'cms-nhomc')); ?></div>
                    <div class="nav-next"><?php next_comments_link(__('Bình luận mới hơn &rarr;', 'cms-nhomc')); ?></div>
                </nav>
            <?php endif; ?>
        </div>
    <?php elseif (!comments_open() && post_type_supports(get_post_type(), 'comments')) : ?>
        <p class="cms-no-comments">Chức năng bình luận cho bài viết này tạm thời bị khóa.</p>
    <?php endif; ?>

</div>

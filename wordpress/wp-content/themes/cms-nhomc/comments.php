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

    <!-- 1. Danh sách bình luận (Task 14: Bootsnipp gNVj0) -->
    <?php if (have_comments()) : ?>
        <div class="cms-comments-list-wrapper">
            <h4 class="cms-comments-title">
                <i class="fa fa-comments-o"></i> (<?php echo get_comments_number(); ?>) Comments
            </h4>

            <ol class="cms-comment-list">
                <?php
                wp_list_comments(array(
                    'style'       => 'ol',
                    'short_ping'  => true,
                    'avatar_size' => 50,
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

    <!-- 2. Form gửi bình luận (Task 8: Bootsnipp rNEdR) -->
    <?php if (comments_open()) : ?>
        <?php if (is_user_logged_in()) : ?>
            <!-- Post Form Begins (Bootsnipp rNEdR - Dành cho người dùng đã đăng nhập) -->
            <section class="card cms-post-form-card mt-4" id="respond">
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
        <?php else : ?>
            <!-- Trường hợp chưa đăng nhập: Ẩn form Make a Post, hiển thị thông báo yêu cầu đăng nhập -->
            <div class="card cms-post-form-card cms-guest-notice-card mt-4" id="respond">
                <div class="card-body text-center py-4">
                    <i class="fa fa-lock fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-3" style="font-size: 14.5px;">Vui lòng <strong>đăng nhập</strong> để chia sẻ cảm nghĩ và tham gia bình luận.</p>
                    <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="btn btn-primary" style="padding: 7px 22px; font-size: 14px;">
                        <i class="fa fa-sign-in"></i> Đăng nhập
                    </a>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

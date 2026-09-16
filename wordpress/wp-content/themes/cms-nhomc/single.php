<?php
/**
 * Single post template cho Theme CMS Nhóm C
 *
 * @package CMS_NhomC
 */

get_header();
?>

<div class="site-main-wrapper">
    <div class="site-container">
        <div class="content-layout">
            <!-- Cột nội dung chi tiết bài viết bên trái -->
            <main class="primary-content">
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('content-card'); ?>>
                        <h1 style="font-size: 26px; color: #111827; margin-bottom: 12px;"><?php the_title(); ?></h1>
                        <div class="post-meta" style="font-size: 13px; color: #6b7280; margin-bottom: 20px; border-bottom: 1px solid #f3f4f6; padding-bottom: 12px;">
                            Đăng ngày <?php echo get_the_date(); ?> | Tác giả: <?php the_author(); ?> | Chuyên mục: <?php the_category(', '); ?>
                        </div>
                        <div class="post-body" style="font-size: 15.5px; line-height: 1.8; color: #374151;">
                            <?php the_content(); ?>
                        </div>
                    </article>
                <?php endwhile; ?>
            </main>

            <!-- Cột Sidebar hiển thị Categories bên phải -->
            <?php get_sidebar(); ?>
        </div>
    </div>
</div>

<?php
get_footer();

<?php
/**
 * Main template file
 *
 * @package CMS_NhomC
 */

get_header();
?>

<main class="site-content">
    <div class="content-card">
        <h1>Chào mừng bạn đến với Website của Nhóm C (Group C)</h1>
        <p>Header phía trên được tùy biến chuẩn theo mẫu thiết kế giao diện cho đồ án CMS.</p>
    </div>

    <?php if (have_posts()) : ?>
        <div class="posts-list">
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('content-card'); ?>>
                    <h2><a href="<?php the_permalink(); ?>" style="text-decoration: none; color: #1f2937;"><?php the_title(); ?></a></h2>
                    <div class="post-meta" style="font-size: 13px; color: #6b7280; margin: 8px 0 16px;">
                        Đăng ngày <?php echo get_the_date(); ?> | Tác giả: <?php the_author(); ?>
                    </div>
                    <div class="post-excerpt">
                        <?php the_excerpt(); ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    <?php else : ?>
        <div class="content-card">
            <p>Chưa có bài viết nào được đăng tải. Bạn có thể vào trang quản trị để thêm bài viết mới.</p>
        </div>
    <?php endif; ?>
</main>

<?php
get_footer();

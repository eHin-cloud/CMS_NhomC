<?php
/**
 * Archive template (Chuyên mục & Lưu trữ)
 *
 * @package CMS_NhomC
 */

get_header();
?>

<div class="site-main-wrapper">
    <div class="site-container">
        <div class="content-layout">
            <!-- Cột nội dung danh sách bài viết theo chuyên mục bên trái -->
            <main class="primary-content">
                <div class="content-card">
                    <h1 style="font-size: 24px; color: #1f2937; margin-bottom: 8px;">
                        Chuyên mục: <?php single_cat_title(); ?>
                    </h1>
                    <?php if (category_description()) : ?>
                        <div class="archive-description" style="color: #6b7280; font-size: 14px;">
                            <?php echo category_description(); ?>
                        </div>
                    <?php endif; ?>
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
                        <p>Hiện chưa có bài viết nào trong chuyên mục này.</p>
                    </div>
                <?php endif; ?>
            </main>

            <!-- Cột Sidebar hiển thị Categories bên phải -->
            <?php get_sidebar(); ?>
        </div>
    </div>
</div>

<?php
get_footer();

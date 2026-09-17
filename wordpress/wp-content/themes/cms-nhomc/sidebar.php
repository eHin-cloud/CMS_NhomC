<?php
/**
 * Sidebar template for CMS Nhóm C (Bao gồm Categories & Widget)
 *
 * @package CMS_NhomC
 */

$sidebar_type = isset($args['type']) ? $args['type'] : '';

if (empty($sidebar_type)) {
    if (is_single()) {
        $sidebar_type = 'recent'; // Detail: BÀI VIẾT MỚI
    } else {
        $sidebar_type = 'featured'; // Archive & Content: BÀI VIẾT NỔI BẬT
    }
}
?>
<aside id="secondary" class="widget-area site-sidebar cms-sidebar" aria-label="Sidebar">
    <?php
    // 1. Module 9: Hiển thị widget Categories theo mẫu thiết kế (Anh Quý)
    if (function_exists('cms_nhomc_render_categories_widget')) {
        cms_nhomc_render_categories_widget();
    }

    // 2. Hiển thị Bài viết mới hoặc Bài viết nổi bật
    if ($sidebar_type === 'recent') {
        cms_nhomc_render_recent_posts_widget(10, 'BÀI VIẾT MỚI');
    } else {
        cms_nhomc_render_featured_posts_widget(5, 'BÀI VIẾT NỔI BẬT');
    }

    // 3. Module 12: Hiển thị widget Comments theo mẫu thiết kế (Anh Quý)
    if (function_exists('cms_nhomc_render_comments_widget')) {
        cms_nhomc_render_comments_widget(3, 'Comments');
    }

    // 4. Module 15: Hiển thị widget Last Posts - Latest News Timeline (Bootsnipp xrKXW) (Xuân Hòa)
    if (function_exists('cms_nhomc_render_last_posts_widget')) {
        cms_nhomc_render_last_posts_widget(5, 'Latest News');
    }

    // 5. Hỗ trợ thêm các widget khác nếu được kéo thả trong Admin
    if (is_active_sidebar('main-sidebar')) :
        dynamic_sidebar('main-sidebar');
    endif;
    ?>
</aside>

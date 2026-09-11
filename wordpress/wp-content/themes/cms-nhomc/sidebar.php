<?php
/**
 * Sidebar template for CMS Nhóm C
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
<aside class="cms-sidebar" aria-label="Sidebar">
    <?php
    if ($sidebar_type === 'recent') {
        cms_nhomc_render_recent_posts_widget(5, 'BÀI VIẾT MỚI');
    } else {
        cms_nhomc_render_featured_posts_widget(5, 'BÀI VIẾT NỔI BẬT');
    }
    ?>
</aside>

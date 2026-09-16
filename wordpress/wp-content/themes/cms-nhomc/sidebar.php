<?php
/**
 * Sidebar template hiển thị khối Categories theo mẫu chuẩn
 *
 * @package CMS_NhomC
 */
?>
<aside id="secondary" class="widget-area site-sidebar">
    <div class="widget-categories-card">
        <h3 class="widget-cat-title">Categories</h3>
        <div class="widget-cat-stripe"></div>
        <div class="widget-cat-body">
            <ul class="widget-cat-list">
                <?php
                // Lấy danh sách chuyên mục thực tế từ WordPress
                $categories = get_categories(array(
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                    'hide_empty' => false,
                ));

                // Lọc bỏ danh mục mặc định Chưa phân loại nếu có các chuyên mục khác
                $filtered_cats = array();
                if (!empty($categories)) {
                    foreach ($categories as $cat) {
                        if ($cat->slug !== 'uncategorized' && $cat->slug !== 'chua-phan-loai') {
                            $filtered_cats[] = $cat;
                        }
                    }
                }

                if (!empty($filtered_cats)) {
                    foreach ($filtered_cats as $category) {
                        echo '<li>';
                        echo '<span class="cat-bullet"></span>';
                        echo '<a href="' . esc_url(get_category_link($category->term_id)) . '">' . esc_html($category->name) . '</a>';
                        echo '</li>';
                    }
                } else {
                    // Dữ liệu mẫu hiển thị trực tiếp chuẩn y hệt hình ảnh thiết kế
                    $sample_items = array(
                        array('name' => '.Net Developer', 'link' => home_url('/category/net-developer/')),
                        array('name' => 'Thực Tập Sinh Tester', 'link' => home_url('/category/thuc-tap-sinh-tester/')),
                        array('name' => 'Trợ giảng lập trình - Part time', 'link' => home_url('/category/tro-giang-lap-trinh/')),
                    );

                    foreach ($sample_items as $item) {
                        echo '<li>';
                        echo '<span class="cat-bullet"></span>';
                        echo '<a href="' . esc_url($item['link']) . '">' . esc_html($item['name']) . '</a>';
                        echo '</li>';
                    }
                }
                ?>
            </ul>
        </div>
    </div>

    <?php
    // Hỗ trợ thêm các widget khác nếu được kéo thả trong Admin
    if (is_active_sidebar('main-sidebar')) :
        dynamic_sidebar('main-sidebar');
    endif;
    ?>
</aside>

<?php
/**
 * Functions and definitions cho Theme CMS Nhóm C
 *
 * @package CMS_NhomC
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

function cms_nhomc_setup() {
    // Hỗ trợ thẻ Title tự động của WordPress
    add_theme_support('title-tag');

    // Hỗ trợ ảnh đại diện bài viết
    add_theme_support('post-thumbnails');

    // Hỗ trợ HTML5 cho các thẻ form, comment,...
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ));

    // Đăng ký vị trí Menu điều hướng
    register_nav_menus(array(
        'primary-menu' => __('Menu Chính (Header)', 'cms-nhomc'),
        'mobile-menu'  => __('Menu Di Động (3 Chấm)', 'cms-nhomc'),
    ));
}
add_action('after_setup_theme', 'cms_nhomc_setup');

/**
 * Nạp Style và Script
 */
function cms_nhomc_scripts() {
    // Nạp Font Awesome 4.7.0 cho các icon Footer và điều hướng
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), '4.7.0');

    // Nạp style.css của Theme
    wp_enqueue_style('cms-nhomc-style', get_stylesheet_uri(), array('font-awesome'), '1.2.0');
}
add_action('wp_enqueue_scripts', 'cms_nhomc_scripts');

/**
 * Đăng ký Widget Area (Sidebar)
 */
function cms_nhomc_widgets_init() {
    register_sidebar(array(
        'name'          => __('Sidebar Chính', 'cms-nhomc'),
        'id'            => 'main-sidebar',
        'description'   => __('Khu vực thanh bên cho giao diện', 'cms-nhomc'),
        'before_widget' => '<div id="%1$s" class="widget-categories-card %2$s">',
        'after_widget'  => '</div></div>',
        'before_title'  => '<h3 class="widget-cat-title">',
        'after_title'   => '</h3><div class="widget-cat-stripe"></div><div class="widget-cat-body">',
    ));
}
add_action('widgets_init', 'cms_nhomc_widgets_init');

/**
 * Tự động tạo sẵn 3 chuyên mục mẫu nếu chưa có trong Database
 */
function cms_nhomc_create_default_categories() {
    $default_cats = array(
        '.Net Developer',
        'Thực Tập Sinh Tester',
        'Trợ giảng lập trình - Part time'
    );
    foreach ($default_cats as $cat_name) {
        if (!term_exists($cat_name, 'category')) {
            wp_insert_term($cat_name, 'category');
        }
    }
}
add_action('after_setup_theme', 'cms_nhomc_create_default_categories');


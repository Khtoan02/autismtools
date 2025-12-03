<?php
/**
 * Thiết lập theme Autism Tools.
 *
 * @package AutismTools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Tránh truy cập trực tiếp.
}

/**
 * Kích hoạt các tính năng theme.
 */
function autismtools_setup() {
	load_theme_textdomain( 'autismtools', get_template_directory() . '/languages' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' )
	);
	register_nav_menus(
		array(
			'primary' => __( 'Menu chính', 'autismtools' ),
		)
	);
}
add_action( 'after_setup_theme', 'autismtools_setup' );

/**
 * Khai báo vùng widget cơ bản.
 */
function autismtools_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Sidebar chính', 'autismtools' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Khu vực hiển thị widget mặc định.', 'autismtools' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'autismtools_widgets_init' );

/**
 * Tải CSS/JS.
 */
function autismtools_assets() {
	$theme_version = wp_get_theme()->get( 'Version' );
	wp_enqueue_style(
		'autismtools-style',
		get_stylesheet_uri(),
		array(),
		$theme_version
	);
	wp_enqueue_script(
		'autismtools-main',
		get_template_directory_uri() . '/assets/js/main.js',
		array(),
		$theme_version,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'autismtools_assets' );


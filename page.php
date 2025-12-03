<?php
/**
 * Template trang tĩnh.
 *
 * @package AutismTools
 */

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/content', 'page' );
	comments_template();
endwhile;

get_footer();


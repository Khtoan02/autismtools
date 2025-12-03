<?php
/**
 * Hiển thị nội dung bài viết mặc định.
 *
 * @package AutismTools
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<?php
		if ( is_singular() ) {
			the_title( '<h1 class="entry-title">', '</h1>' );
		} else {
			the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
		}
		?>
	</header>

	<div class="entry-content">
		<?php
		the_content(
			sprintf(
				wp_kses(
					__( 'Tiếp tục đọc<span class="screen-reader-text"> "%s"</span>', 'autismtools' ),
					array( 'span' => array( 'class' => array() ) )
				),
				get_the_title()
			)
		);
		?>
	</div>
</article>


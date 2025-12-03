<?php
/**
 * Header template.
 *
 * @package AutismTools
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="site">
	<header class="site-header">
		<div class="site-branding">
			<?php
			if ( has_custom_logo() ) {
				the_custom_logo();
			} else {
				?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php bloginfo( 'name' ); ?>
				</a>
				<p><?php bloginfo( 'description' ); ?></p>
				<?php
			}
			?>
		</div>
		<nav class="site-nav" aria-label="<?php esc_attr_e( 'Menu chính', 'autismtools' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'menu_class'     => 'menu',
					'container'      => false,
				)
			);
			?>
		</nav>
	</header>
	<main class="site-main">


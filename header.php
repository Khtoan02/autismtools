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
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'text-slate-800 flex flex-col min-h-screen' ); ?>>
<?php wp_body_open(); ?>

<div class="text-slate-800 flex flex-col min-h-screen">
	<header class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex justify-between items-center h-16">
				<div class="flex items-center gap-2 cursor-pointer" onclick="window.location.href='<?php echo esc_url( home_url( '/' ) ); ?>';">
					<?php
					if ( has_custom_logo() ) {
						the_custom_logo();
					} else {
						?>
						<i data-lucide="brain-circuit" class="h-8 w-8 text-blue-600"></i>
						<span class="text-xl font-bold text-slate-900">DawnBridge <span class="text-blue-600">Autism Care</span></span>
						<?php
					}
					?>
				</div>
				<nav class="hidden md:flex space-x-8">
					<?php
					if ( has_nav_menu( 'primary' ) ) {
						wp_nav_menu(
							array(
								'theme_location' => 'primary',
								'menu_class'     => 'flex space-x-8',
								'container'      => false,
								'fallback_cb'    => false,
							)
						);
					} else {
						autismtools_fallback_menu();
					}
					?>
				</nav>
				<?php
				// Lấy trang đang sử dụng template bài viết để gắn link cho nút.
				$articles_page     = get_pages(
					array(
						'meta_key'   => '_wp_page_template',
						'meta_value' => 'template-articles.php',
						'number'     => 1,
					)
				);
				$articles_page_url = ! empty( $articles_page ) ? get_permalink( $articles_page[0]->ID ) : home_url( '/' );
				?>
				<a href="<?php echo esc_url( $articles_page_url ); ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium shadow-md">
					Bài viết chia sẻ
				</a>
			</div>
		</div>
	</header>

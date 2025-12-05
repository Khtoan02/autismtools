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
			'footer'  => __( 'Menu chân trang', 'autismtools' ),
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
	
	// Tailwind CSS cho toàn site
	wp_enqueue_script(
		'autismtools-tailwind',
		'https://cdn.tailwindcss.com',
		array(),
		null,
		false
	);

	// Google Fonts Inter
	wp_enqueue_style(
		'autismtools-fonts-inter',
		'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
		array(),
		null
	);

	// Lucide Icons
	wp_enqueue_script(
		'autismtools-lucide',
		'https://unpkg.com/lucide@latest',
		array(),
		null,
		true
	);

	// Theme styles
	wp_enqueue_style(
		'autismtools-style',
		get_stylesheet_uri(),
		array(),
		$theme_version
	);
	
	// Theme scripts
	wp_enqueue_script(
		'autismtools-main',
		get_template_directory_uri() . '/assets/js/main.js',
		array(),
		$theme_version,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'autismtools_assets' );

/**
 * Fallback menu nếu không có menu được đăng ký.
 */
function autismtools_fallback_menu() {
	?>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-blue-600 font-medium">Trang chủ</a>
	<?php
	$pages = get_pages( array( 'sort_column' => 'menu_order' ) );
	foreach ( $pages as $page ) {
		?>
		<a href="<?php echo esc_url( get_permalink( $page->ID ) ); ?>" class="text-slate-500 hover:text-blue-600">
			<?php echo esc_html( $page->post_title ); ?>
		</a>
		<?php
	}
}

/**
 * Thêm CSS cho menu WordPress và các class đặc biệt.
 */
function autismtools_menu_styles() {
	?>
	<style>
		/* Menu WordPress tương thích với Tailwind */
		.primary-menu {
			display: flex;
			space-x: 2rem;
			gap: 2rem;
			list-style: none;
			margin: 0;
			padding: 0;
		}
		.primary-menu li {
			margin: 0;
		}
		.primary-menu a {
			text-decoration: none;
			color: rgb(100 116 139);
			transition: color 0.2s;
		}
		.primary-menu a:hover {
			color: rgb(37 99 235);
		}
		.primary-menu .current-menu-item > a,
		.primary-menu .current_page_item > a {
			color: rgb(37 99 235);
			font-weight: 500;
		}
		
		/* CSS cho các class đặc biệt */
		body {
			font-family: 'Inter', sans-serif;
		}
		.card-hover:hover {
			transform: translateY(-4px);
			box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
		}
		.gradient-text {
			background-clip: text;
			-webkit-background-clip: text;
			color: transparent;
			background-image: linear-gradient(to right, #2563eb, #0891b2);
		}
		.filter-chip.active {
			background-color: #eff6ff;
			color: #2563eb;
			border-color: #bfdbfe;
			font-weight: 600;
		}
		.no-scrollbar::-webkit-scrollbar {
			display: none;
		}
		.no-scrollbar {
			-ms-overflow-style: none;
			scrollbar-width: none;
		}
	</style>
	<?php
}
add_action( 'wp_head', 'autismtools_menu_styles' );

/**
 * Tải thêm thư viện cho template bài viết hiện đại (Font Awesome).
 */
function autismtools_articles_template_assets() {
	if ( ! is_page_template( 'template-articles.php' ) ) {
		return;
	}

	wp_enqueue_style(
		'autismtools-fontawesome',
		'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
		array(),
		'6.4.0'
	);
}
add_action( 'wp_enqueue_scripts', 'autismtools_articles_template_assets' );

/**
 * Lấy bài viết mới nhất từ dawnbridge.vn thông qua REST API.
 *
 * @param int $quantity Số bài viết cần lấy.
 *
 * @return array[]
 */
function autismtools_get_remote_posts( $quantity = 3 ) {
	$quantity      = max( 1, absint( $quantity ) );
	$transient_key = 'autismtools_remote_posts_' . $quantity;

	$cached_posts = get_transient( $transient_key );
	if ( false !== $cached_posts && ! is_customize_preview() ) {
		return $cached_posts;
	}

	$api_url = add_query_arg(
		array(
			'_embed'    => '1',
			'per_page'  => $quantity,
		),
		'https://dawnbridge.vn/wp-json/wp/v2/posts'
	);

	$ssl_verify = 'local' === wp_get_environment_type() ? false : true;

	$response = wp_remote_get(
		$api_url,
		array(
			'timeout'   => 15,
			'sslverify' => $ssl_verify,
		)
	);

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return array();
	}

	$posts_data  = json_decode( wp_remote_retrieve_body( $response ), true );
	$final_posts = array();

	// Lấy categories một lần để map với category IDs (bao gồm cả parent để chọn đúng category con)
	$remote_cats    = autismtools_get_remote_categories();
	$categories_map = array();
	foreach ( $remote_cats as $cat ) {
		if ( isset( $cat['id'] ) ) {
			$categories_map[ $cat['id'] ] = array(
				'name'   => $cat['name'],
				'slug'   => $cat['slug'],
				'parent' => isset( $cat['parent'] ) ? absint( $cat['parent'] ) : 0,
			);
		}
	}

	if ( ! empty( $posts_data ) ) {
		foreach ( $posts_data as $post ) {
			$thumbnail = isset( $post['_embedded']['wp:featuredmedia'][0]['source_url'] )
				? esc_url_raw( $post['_embedded']['wp:featuredmedia'][0]['source_url'] )
				: 'https://via.placeholder.com/600x400?text=Dawnbridge';

			$category_name = __( 'Tin tức', 'autismtools' );
			$category_slug = 'tin-tuc';

			// Ưu tiên: Lấy category từ categories IDs của post.
			// Mỗi bài viết có thể thuộc nhiều category: thường có category cha là "blog"
			// và các category con như "chuan-doan", "can-thiep"...
			if ( ! empty( $post['categories'] ) && is_array( $post['categories'] ) ) {
				$category_ids   = array_map( 'absint', $post['categories'] );
				$matched_cat    = null;
				$fallback_blog  = null;

				foreach ( $category_ids as $cat_id ) {
					if ( ! isset( $categories_map[ $cat_id ] ) ) {
						continue;
					}

					$cat = $categories_map[ $cat_id ];

					// Lưu lại category "blog" làm fallback
					if ( 'blog' === $cat['slug'] || 0 === $cat['parent'] ) {
						if ( ! $fallback_blog ) {
							$fallback_blog = $cat;
						}
						continue;
					}

					// Ưu tiên category con (không phải blog)
					$matched_cat = $cat;
					break;
				}

				// Nếu tìm được category con thì dùng, nếu không dùng blog
				if ( $matched_cat ) {
					$category_name = $matched_cat['name'];
					$category_slug = $matched_cat['slug'];
				} elseif ( $fallback_blog ) {
					$category_name = $fallback_blog['name'];
					$category_slug = $fallback_blog['slug'];
				}
			}

			// Fallback cuối cùng: nếu vẫn chưa có category, thử lấy từ wp:term
			if ( 'tin-tuc' === $category_slug && isset( $post['_embedded']['wp:term'] ) && is_array( $post['_embedded']['wp:term'] ) ) {
				foreach ( $post['_embedded']['wp:term'] as $terms ) {
					if ( is_array( $terms ) && ! empty( $terms ) ) {
						foreach ( $terms as $term ) {
							if ( isset( $term['taxonomy'] ) && 'category' === $term['taxonomy'] ) {
								$category_name = sanitize_text_field( $term['name'] ?? '' );
								$category_slug = sanitize_title( $term['slug'] ?? $category_name );
								break 2;
							}
						}
					}
				}

				if ( 'tin-tuc' === $category_slug && isset( $post['_embedded']['wp:term'][0] ) && is_array( $post['_embedded']['wp:term'][0] ) && ! empty( $post['_embedded']['wp:term'][0] ) ) {
					$term = $post['_embedded']['wp:term'][0][0];
					if ( isset( $term['name'] ) ) {
						$category_name = sanitize_text_field( $term['name'] );
						$category_slug = sanitize_title( $term['slug'] ?? $category_name );
					}
				}
			}

			$content      = wp_strip_all_tags( $post['content']['rendered'] ?? '' );
			$word_count   = max( 1, str_word_count( $content ) );
			$read_minutes = max( 1, ceil( $word_count / 200 ) );

			$raw_title   = $post['title']['rendered'] ?? '';
			$raw_excerpt = $post['excerpt']['rendered'] ?? '';

			$title = wp_kses_decode_entities( wp_strip_all_tags( $raw_title ) );
			$excerpt = wp_kses_decode_entities(
				wp_trim_words( wp_strip_all_tags( $raw_excerpt ), 24, '...' )
			);

			$date = ! empty( $post['date'] ) ? date_i18n( 'd/m/Y', strtotime( $post['date'] ) ) : '';

			$final_posts[] = array(
				'title'     => $title,
				'category'  => $category_name,
				'category_slug' => $category_slug,
				'link'      => esc_url_raw( $post['link'] ?? '#' ),
				'permalink' => esc_url_raw( $post['link'] ?? '#' ),
				'excerpt'   => $excerpt,
				'image'     => $thumbnail,
				'date'      => $date,
				'read_time' => sprintf(
					/* translators: %s: số phút đọc ước tính */
					__( '%s phút đọc', 'autismtools' ),
					$read_minutes
				),
			);
		}

		set_transient( $transient_key, $final_posts, MINUTE_IN_SECONDS * 10 );
	}

	return $final_posts;
}

/**
 * Lấy danh mục từ dawnbridge.vn.
 *
 * @return array[]
 */
function autismtools_get_remote_categories() {
	$transient_key = 'autismtools_remote_categories';

	$cached = get_transient( $transient_key );
	if ( false !== $cached && ! is_customize_preview() ) {
		return $cached;
	}

	$api_url = add_query_arg(
		array(
			'per_page' => 50,
			'orderby'  => 'name',
			'order'    => 'asc',
		),
		'https://dawnbridge.vn/wp-json/wp/v2/categories'
	);

	$ssl_verify = 'local' === wp_get_environment_type() ? false : true;

	$response = wp_remote_get(
		$api_url,
		array(
			'timeout'   => 15,
			'sslverify' => $ssl_verify,
		)
	);

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return array();
	}

	$body       = json_decode( wp_remote_retrieve_body( $response ), true );
	$categories = array();

	if ( ! empty( $body ) ) {
		foreach ( $body as $cat ) {
			$categories[] = array(
				'id'     => absint( $cat['id'] ?? 0 ),
				'name'   => sanitize_text_field( $cat['name'] ?? '' ),
				'slug'   => sanitize_title( $cat['slug'] ?? '' ),
				'parent' => absint( $cat['parent'] ?? 0 ),
			);
		}

		set_transient( $transient_key, $categories, MINUTE_IN_SECONDS * 30 );
	}

	return $categories;
}

/**
 * Hiển thị danh sách bài viết remote dưới dạng shortcode.
 *
 * @param array $atts Shortcode attributes.
 *
 * @return string
 */
function autismtools_remote_posts_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'quantity' => 3,
			'title'    => __( 'Tin tức từ DawnBridge', 'autismtools' ),
		),
		$atts,
		'autismtools_remote_posts'
	);

	$posts = autismtools_get_remote_posts( absint( $atts['quantity'] ) );

	ob_start();
	?>
	<section class="remote-posts">
		<header class="remote-posts__header">
			<h2><?php echo esc_html( $atts['title'] ); ?></h2>
			<p><?php esc_html_e( 'Nội dung được đồng bộ trực tiếp từ dawnbridge.vn', 'autismtools' ); ?></p>
		</header>
		<div class="remote-posts__grid">
			<?php if ( empty( $posts ) ) : ?>
				<p class="remote-posts__empty"><?php esc_html_e( 'Hiện chưa có bài viết nào.', 'autismtools' ); ?></p>
			<?php else : ?>
				<?php foreach ( $posts as $post ) : ?>
					<article class="remote-post-card">
						<a class="remote-post-card__thumb" href="<?php echo esc_url( $post['link'] ); ?>" target="_blank" rel="noopener">
							<img src="<?php echo esc_url( $post['image'] ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $post['title'] ) ); ?>">
						</a>
						<div class="remote-post-card__body">
							<span class="remote-post-card__date"><?php echo esc_html( $post['date'] ); ?></span>
							<h3 class="remote-post-card__title">
								<a href="<?php echo esc_url( $post['link'] ); ?>" target="_blank" rel="noopener">
									<?php echo wp_kses_post( $post['title'] ); ?>
								</a>
							</h3>
							<p class="remote-post-card__excerpt"><?php echo esc_html( $post['excerpt'] ); ?></p>
						</div>
					</article>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
}
add_shortcode( 'autismtools_remote_posts', 'autismtools_remote_posts_shortcode' );


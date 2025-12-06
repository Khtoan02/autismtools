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

/**
 * Tạo Custom Post Type để lưu Checklist Results
 */
function autismtools_register_checklist_post_type() {
	register_post_type( 'checklist_result',
		array(
			'labels' => array(
				'name' => 'Checklist Results',
				'singular_name' => 'Checklist Result',
			),
			'public' => false,
			'show_ui' => false,
			'show_in_menu' => false,
			'capability_type' => 'post',
			'supports' => array( 'title', 'custom-fields' ),
		)
	);
}
add_action( 'init', 'autismtools_register_checklist_post_type' );

/**
 * REST API: Lưu kết quả checklist
 */
function autismtools_save_checklist_result( $request ) {
	$params = $request->get_json_params();
	
	$parent_name = sanitize_text_field( $params['parent_name'] ?? '' );
	$phone = sanitize_text_field( $params['phone'] ?? '' );
	$level = sanitize_text_field( $params['level'] ?? 'moderate' );
	$symptoms = $params['symptoms'] ?? array();
	$grouped_results = $params['grouped_results'] ?? array();
	$ai_summary = sanitize_textarea_field( $params['ai_summary'] ?? '' );
	$action = sanitize_text_field( $params['action'] ?? 'save_image' ); // 'save_image' hoặc 'call'
	
	if ( empty( $parent_name ) || empty( $phone ) ) {
		return new WP_Error( 'missing_data', 'Thiếu thông tin phụ huynh hoặc số điện thoại', array( 'status' => 400 ) );
	}
	
	// Tạo post để lưu dữ liệu
	$post_id = wp_insert_post( array(
		'post_type' => 'checklist_result',
		'post_title' => $parent_name . ' - ' . $phone,
		'post_status' => 'publish',
		'meta_input' => array(
			'parent_name' => $parent_name,
			'phone' => $phone,
			'level' => $level,
			'symptoms' => $symptoms,
			'grouped_results' => $grouped_results,
			'ai_summary' => $ai_summary,
			'action' => $action,
			'date_created' => current_time( 'mysql' ),
		),
	) );
	
	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}
	
	return new WP_REST_Response( array(
		'success' => true,
		'id' => $post_id,
		'message' => 'Đã lưu kết quả thành công',
	), 200 );
}

/**
 * Đăng ký REST API endpoint
 */
function autismtools_register_checklist_api() {
	register_rest_route( 'autismtools/v1', '/checklist/save', array(
		'methods' => 'POST',
		'callback' => 'autismtools_save_checklist_result',
		'permission_callback' => '__return_true', // Cho phép public (có thể thêm bảo mật sau)
	) );
	
	register_rest_route( 'autismtools/v1', '/checklist/stats', array(
		'methods' => 'GET',
		'callback' => 'autismtools_get_checklist_stats',
		'permission_callback' => function() {
			return current_user_can( 'manage_options' );
		},
	) );
	
	register_rest_route( 'autismtools/v1', '/checklist/leads', array(
		'methods' => 'GET',
		'callback' => 'autismtools_get_checklist_leads',
		'permission_callback' => function() {
			return current_user_can( 'manage_options' );
		},
	) );
	
	register_rest_route( 'autismtools/v1', '/checklist/detail/(?P<id>\d+)', array(
		'methods' => 'GET',
		'callback' => 'autismtools_get_checklist_detail',
		'permission_callback' => function() {
			return current_user_can( 'manage_options' );
		},
		'args' => array(
			'id' => array(
				'validate_callback' => function( $param ) {
					return is_numeric( $param );
				},
			),
		),
	) );
	
	register_rest_route( 'autismtools/v1', '/checklist/tracking', array(
		'methods' => 'POST',
		'callback' => 'autismtools_save_tracking_event',
		'permission_callback' => '__return_true', // Public để tracking
	) );
	
	register_rest_route( 'autismtools/v1', '/checklist/behavior-stats', array(
		'methods' => 'GET',
		'callback' => 'autismtools_get_behavior_stats',
		'permission_callback' => function() {
			return current_user_can( 'manage_options' );
		},
	) );
	
	register_rest_route( 'autismtools/v1', '/checklist/update-status', array(
		'methods' => 'POST',
		'callback' => 'autismtools_update_checklist_status',
		'permission_callback' => function() {
			return current_user_can( 'manage_options' );
		},
	) );
}
add_action( 'rest_api_init', 'autismtools_register_checklist_api' );

/**
 * Lấy thống kê checklist
 */
function autismtools_get_checklist_stats( $request ) {
	$days = absint( $request->get_param( 'days' ) ?? 7 );
	$date_from = date( 'Y-m-d', strtotime( "-{$days} days" ) );
	
	$args = array(
		'post_type' => 'checklist_result',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'date_query' => array(
			array(
				'after' => $date_from,
			),
		),
	);
	
	$query = new WP_Query( $args );
	$total = $query->found_posts;
	
	$levels = array( 'mild' => 0, 'moderate' => 0, 'severe' => 0 );
	$actions = array( 'save_image' => 0, 'call' => 0 );
	$completed = 0;
	
	while ( $query->have_posts() ) {
		$query->the_post();
		$level = get_post_meta( get_the_ID(), 'level', true );
		$action = get_post_meta( get_the_ID(), 'action', true );
		
		if ( $level && isset( $levels[ $level ] ) ) {
			$levels[ $level ]++;
		}
		if ( $action && isset( $actions[ $action ] ) ) {
			$actions[ $action ]++;
		}
		$completed++;
	}
	wp_reset_postdata();
	
	return new WP_REST_Response( array(
		'total' => $total,
		'completed' => $completed,
		'levels' => $levels,
		'actions' => $actions,
		'risk_distribution' => array(
			array( 'name' => 'Mức độ NẶNG', 'value' => $levels['severe'], 'color' => '#991b1b' ),
			array( 'name' => 'Mức TRUNG BÌNH', 'value' => $levels['moderate'], 'color' => '#ca8a04' ),
			array( 'name' => 'Mức độ NHẸ', 'value' => $levels['mild'], 'color' => '#166534' ),
		),
	), 200 );
}

/**
 * Lấy danh sách leads
 */
function autismtools_get_checklist_leads( $request ) {
	$limit = absint( $request->get_param( 'limit' ) ?? 50 );
	$search = sanitize_text_field( $request->get_param( 'search' ) ?? '' );
	$status = sanitize_text_field( $request->get_param( 'status' ) ?? '' );
	
	$args = array(
		'post_type' => 'checklist_result',
		'post_status' => 'publish',
		'posts_per_page' => $limit,
		'orderby' => 'date',
		'order' => 'DESC',
	);
	
	// Thêm meta query nếu có search hoặc status
	$meta_queries = array();
	
	// Thêm search query
	if ( ! empty( $search ) ) {
		$meta_queries[] = array(
			'relation' => 'OR',
			array(
				'key' => 'parent_name',
				'value' => $search,
				'compare' => 'LIKE',
			),
			array(
				'key' => 'phone',
				'value' => $search,
				'compare' => 'LIKE',
			),
		);
	}
	
	// Thêm filter theo status
	if ( ! empty( $status ) && in_array( $status, array( 'new', 'consulted', 'missed', 'called_back' ), true ) ) {
		if ( $status === 'new' ) {
			// Nếu filter theo 'new', tìm cả những posts không có status (mặc định là 'new')
			$meta_queries[] = array(
				'relation' => 'OR',
				array(
					'key' => 'status',
					'value' => 'new',
					'compare' => '=',
				),
				array(
					'key' => 'status',
					'compare' => 'NOT EXISTS',
				),
			);
		} else {
			// Các status khác thì tìm chính xác
			$meta_queries[] = array(
				'key' => 'status',
				'value' => $status,
				'compare' => '=',
			);
		}
	}
	
	// Nếu có meta queries, thêm vào args
	if ( ! empty( $meta_queries ) ) {
		if ( count( $meta_queries ) === 1 ) {
			// Chỉ có 1 query, dùng trực tiếp
			$args['meta_query'] = $meta_queries[0];
		} else {
			// Có nhiều queries, cần relation AND
			$args['meta_query'] = array(
				'relation' => 'AND',
			);
			foreach ( $meta_queries as $mq ) {
				$args['meta_query'][] = $mq;
			}
		}
	}
	
	$query = new WP_Query( $args );
	$leads = array();
	
	while ( $query->have_posts() ) {
		$query->the_post();
		$post_id = get_the_ID();
		
		$lead_status = get_post_meta( $post_id, 'status', true );
		// Nếu không có status, mặc định là 'new'
		if ( empty( $lead_status ) ) {
			$lead_status = 'new';
		}
		
		$leads[] = array(
			'id' => $post_id,
			'parent' => get_post_meta( $post_id, 'parent_name', true ),
			'phone' => get_post_meta( $post_id, 'phone', true ),
			'child' => '', // Có thể thêm field này sau
			'score' => ucfirst( get_post_meta( $post_id, 'level', true ) === 'severe' ? 'Nặng' : ( get_post_meta( $post_id, 'level', true ) === 'moderate' ? 'Trung Bình' : 'Nhẹ' ) ),
			'date' => get_the_date( 'H:i A' ),
			'action' => get_post_meta( $post_id, 'action', true ) === 'call' ? 'Gọi điện' : 'Lưu Ảnh',
			'status' => $lead_status,
			'details' => array(
				'symptoms' => get_post_meta( $post_id, 'symptoms', true ) ?: array(),
				'ai_summary' => get_post_meta( $post_id, 'ai_summary', true ) ?: '',
				'notes' => get_post_meta( $post_id, 'notes', true ) ?: '',
			),
		);
	}
	wp_reset_postdata();
	
	return new WP_REST_Response( $leads, 200 );
}

/**
 * Cập nhật trạng thái checklist
 */
function autismtools_update_checklist_status( $request ) {
	$params = $request->get_json_params();
	
	$post_id = absint( $params['id'] ?? 0 );
	$status = sanitize_text_field( $params['status'] ?? '' );
	$notes = sanitize_textarea_field( $params['notes'] ?? '' );
	
	if ( ! $post_id || empty( $status ) ) {
		return new WP_Error( 'missing_data', 'Thiếu thông tin', array( 'status' => 400 ) );
	}
	
	$valid_statuses = array( 'new', 'consulted', 'missed', 'called_back' );
	if ( ! in_array( $status, $valid_statuses, true ) ) {
		return new WP_Error( 'invalid_status', 'Trạng thái không hợp lệ', array( 'status' => 400 ) );
	}
	
	$post = get_post( $post_id );
	if ( ! $post || $post->post_type !== 'checklist_result' ) {
		return new WP_Error( 'not_found', 'Không tìm thấy kết quả', array( 'status' => 404 ) );
	}
	
	update_post_meta( $post_id, 'status', $status );
	if ( ! empty( $notes ) ) {
		update_post_meta( $post_id, 'notes', $notes );
	}
	
	return new WP_REST_Response( array(
		'success' => true,
		'id' => $post_id,
		'status' => $status,
		'message' => 'Đã cập nhật trạng thái thành công',
	), 200 );
}

/**
 * Lấy chi tiết một checklist result
 */
function autismtools_get_checklist_detail( $request ) {
	$post_id = absint( $request->get_param( 'id' ) );
	
	$post = get_post( $post_id );
	if ( ! $post || $post->post_type !== 'checklist_result' ) {
		return new WP_Error( 'not_found', 'Không tìm thấy kết quả', array( 'status' => 404 ) );
	}
	
	$grouped_results = get_post_meta( $post_id, 'grouped_results', true ) ?: array();
	$symptoms = get_post_meta( $post_id, 'symptoms', true ) ?: array();
	
	// Đảm bảo grouped_results có đầy đủ cấu trúc
	$grouped_results = wp_parse_args( $grouped_results, array(
		'frequent' => array(),
		'sometimes' => array(),
		'none' => array(),
	) );
	
	// Chuyển đổi grouped_results thành symptoms list (fallback nếu không có grouped_results)
	$symptoms_list = array();
	if ( ! empty( $grouped_results['frequent'] ) ) {
		foreach ( $grouped_results['frequent'] as $item ) {
			if ( isset( $item['q'] ) ) {
				$symptoms_list[] = $item['q'];
			}
		}
	}
	if ( ! empty( $grouped_results['sometimes'] ) ) {
		foreach ( $grouped_results['sometimes'] as $item ) {
			if ( isset( $item['q'] ) ) {
				$symptoms_list[] = $item['q'];
			}
		}
	}
	
	$detail = array(
		'id' => $post_id,
		'parent' => get_post_meta( $post_id, 'parent_name', true ),
		'phone' => get_post_meta( $post_id, 'phone', true ),
		'level' => get_post_meta( $post_id, 'level', true ),
		'score' => ucfirst( get_post_meta( $post_id, 'level', true ) === 'severe' ? 'Nặng' : ( get_post_meta( $post_id, 'level', true ) === 'moderate' ? 'Trung Bình' : 'Nhẹ' ) ),
		'date' => get_the_date( 'H:i A', $post_id ),
		'date_full' => get_the_date( 'd/m/Y H:i', $post_id ),
		'action' => get_post_meta( $post_id, 'action', true ) === 'call' ? 'Gọi điện' : 'Lưu Ảnh',
		'symptoms' => ! empty( $symptoms_list ) ? $symptoms_list : ( is_array( $symptoms ) ? $symptoms : array() ),
		'ai_summary' => get_post_meta( $post_id, 'ai_summary', true ) ?: '',
		'grouped_results' => $grouped_results,
	);
	
	return new WP_REST_Response( $detail, 200 );
}

/**
 * Đăng ký Custom Post Type cho Tracking
 */
function autismtools_register_tracking_post_type() {
	register_post_type( 'checklist_tracking',
		array(
			'labels' => array(
				'name' => 'Checklist Tracking',
				'singular_name' => 'Tracking Event',
			),
			'public' => false,
			'show_ui' => false,
			'show_in_menu' => false,
			'capability_type' => 'post',
			'supports' => array( 'title', 'custom-fields' ),
		)
	);
}
add_action( 'init', 'autismtools_register_tracking_post_type' );

/**
 * Lưu tracking event từ checklist
 */
function autismtools_save_tracking_event( $request ) {
	$params = $request->get_json_params();
	
	$event_type = sanitize_text_field( $params['event_type'] ?? '' );
	$session_id = sanitize_text_field( $params['session_id'] ?? '' );
	$timestamp = absint( $params['timestamp'] ?? time() );
	$data = $params['data'] ?? array();
	
	if ( empty( $event_type ) || empty( $session_id ) ) {
		return new WP_Error( 'missing_data', 'Thiếu thông tin tracking', array( 'status' => 400 ) );
	}
	
	// Lưu vào custom post type tracking
	$post_id = wp_insert_post( array(
		'post_type' => 'checklist_tracking',
		'post_title' => $event_type . ' - ' . $session_id,
		'post_status' => 'publish',
		'meta_input' => array(
			'event_type' => $event_type,
			'session_id' => $session_id,
			'timestamp' => $timestamp,
			'data' => $data,
			'date_created' => current_time( 'mysql' ),
		),
	) );
	
	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}
	
	return new WP_REST_Response( array(
		'success' => true,
		'id' => $post_id,
	), 200 );
}

/**
 * Lấy thống kê hành vi người dùng
 */
function autismtools_get_behavior_stats( $request ) {
	$days = absint( $request->get_param( 'days' ) ?? 7 );
	$date_from = date( 'Y-m-d', strtotime( "-{$days} days" ) );
	
	$args = array(
		'post_type' => 'checklist_tracking',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'date_query' => array(
			array(
				'after' => $date_from,
			),
		),
	);
	
	$query = new WP_Query( $args );
	
	// Phân tích events
	$funnel_data = array(
		'page_view' => 0,
		'start' => 0,
		'g1_complete' => 0,
		'g2_complete' => 0,
		'g3_complete' => 0,
		'view_result' => 0,
		'save_result' => 0,
	);
	
	$section_times = array(
		'g1' => array(),
		'g2' => array(),
		'g3' => array(),
	);
	
	$sessions = array();
	
	while ( $query->have_posts() ) {
		$query->the_post();
		$post_id = get_the_ID();
		
		$event_type = get_post_meta( $post_id, 'event_type', true );
		$session_id = get_post_meta( $post_id, 'session_id', true );
		$timestamp = get_post_meta( $post_id, 'timestamp', true );
		
		if ( ! isset( $sessions[ $session_id ] ) ) {
			$sessions[ $session_id ] = array();
		}
		
		$sessions[ $session_id ][] = array(
			'type' => $event_type,
			'timestamp' => $timestamp,
		);
		
		// Đếm funnel
		if ( isset( $funnel_data[ $event_type ] ) ) {
			$funnel_data[ $event_type ]++;
		}
		
		// Tính thời gian cho từng section
		if ( $event_type === 'g1_start' ) {
			$start_time = $timestamp;
			// Tìm g1_complete trong cùng session
			$complete_events = get_posts( array(
				'post_type' => 'checklist_tracking',
				'post_status' => 'publish',
				'meta_query' => array(
					array(
						'key' => 'session_id',
						'value' => $session_id,
					),
					array(
						'key' => 'event_type',
						'value' => 'g1_complete',
					),
				),
				'posts_per_page' => 1,
			) );
			
			if ( ! empty( $complete_events ) ) {
				$end_time = get_post_meta( $complete_events[0]->ID, 'timestamp', true );
				if ( $end_time && $start_time ) {
					$section_times['g1'][] = $end_time - $start_time;
				}
			}
		}
		
		if ( $event_type === 'g2_start' ) {
			$start_time = $timestamp;
			$complete_events = get_posts( array(
				'post_type' => 'checklist_tracking',
				'post_status' => 'publish',
				'meta_query' => array(
					array(
						'key' => 'session_id',
						'value' => $session_id,
					),
					array(
						'key' => 'event_type',
						'value' => 'g2_complete',
					),
				),
				'posts_per_page' => 1,
			) );
			
			if ( ! empty( $complete_events ) ) {
				$end_time = get_post_meta( $complete_events[0]->ID, 'timestamp', true );
				if ( $end_time && $start_time ) {
					$section_times['g2'][] = $end_time - $start_time;
				}
			}
		}
		
		if ( $event_type === 'g3_start' ) {
			$start_time = $timestamp;
			$complete_events = get_posts( array(
				'post_type' => 'checklist_tracking',
				'post_status' => 'publish',
				'meta_query' => array(
					array(
						'key' => 'session_id',
						'value' => $session_id,
					),
					array(
						'key' => 'event_type',
						'value' => 'g3_complete',
					),
				),
				'posts_per_page' => 1,
			) );
			
			if ( ! empty( $complete_events ) ) {
				$end_time = get_post_meta( $complete_events[0]->ID, 'timestamp', true );
				if ( $end_time && $start_time ) {
					$section_times['g3'][] = $end_time - $start_time;
				}
			}
		}
	}
	wp_reset_postdata();
	
	// Tính toán stats
	$avg_times = array();
	foreach ( $section_times as $section => $times ) {
		if ( ! empty( $times ) ) {
			$avg_times[ $section ] = round( array_sum( $times ) / count( $times ) );
		} else {
			$avg_times[ $section ] = 0;
		}
	}
	
	// Tính dropoff rates
	$total_sessions = $funnel_data['page_view'];
	$dropoff_rates = array();
	if ( $total_sessions > 0 ) {
		$dropoff_rates['start'] = round( ( ( $total_sessions - $funnel_data['start'] ) / $total_sessions ) * 100, 1 );
		$dropoff_rates['g1'] = $funnel_data['start'] > 0 ? round( ( ( $funnel_data['start'] - $funnel_data['g1_complete'] ) / $funnel_data['start'] ) * 100, 1 ) : 0;
		$dropoff_rates['g2'] = $funnel_data['g1_complete'] > 0 ? round( ( ( $funnel_data['g1_complete'] - $funnel_data['g2_complete'] ) / $funnel_data['g1_complete'] ) * 100, 1 ) : 0;
		$dropoff_rates['g3'] = $funnel_data['g2_complete'] > 0 ? round( ( ( $funnel_data['g2_complete'] - $funnel_data['g3_complete'] ) / $funnel_data['g2_complete'] ) * 100, 1 ) : 0;
		$dropoff_rates['view_result'] = $funnel_data['g3_complete'] > 0 ? round( ( ( $funnel_data['g3_complete'] - $funnel_data['view_result'] ) / $funnel_data['g3_complete'] ) * 100, 1 ) : 0;
		$dropoff_rates['save_result'] = $funnel_data['view_result'] > 0 ? round( ( ( $funnel_data['view_result'] - $funnel_data['save_result'] ) / $funnel_data['view_result'] ) * 100, 1 ) : 0;
	}
	
	// Tính conversion rates
	$conversion_rates = array();
	if ( $total_sessions > 0 ) {
		$conversion_rates['start'] = round( ( $funnel_data['start'] / $total_sessions ) * 100, 1 );
		$conversion_rates['complete'] = round( ( $funnel_data['g3_complete'] / $total_sessions ) * 100, 1 );
		$conversion_rates['view_result'] = round( ( $funnel_data['view_result'] / $total_sessions ) * 100, 1 );
		$conversion_rates['save_result'] = round( ( $funnel_data['save_result'] / $total_sessions ) * 100, 1 );
	}
	
	// Tính min, max, median time cho mỗi section
	$section_stats = array();
	foreach ( $section_times as $section => $times ) {
		if ( ! empty( $times ) ) {
			sort( $times );
			$section_stats[ $section ] = array(
				'avg' => round( array_sum( $times ) / count( $times ) ),
				'min' => min( $times ),
				'max' => max( $times ),
				'median' => $times[ floor( count( $times ) / 2 ) ],
				'count' => count( $times ),
			);
		} else {
			$section_stats[ $section ] = array(
				'avg' => 0,
				'min' => 0,
				'max' => 0,
				'median' => 0,
				'count' => 0,
			);
		}
	}
	
	// Đếm skipped sections
	$skipped_counts = array(
		'g1' => 0,
		'g2' => 0,
		'g3' => 0,
	);
	$skip_query = new WP_Query( array(
		'post_type' => 'checklist_tracking',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'date_query' => array(
			array(
				'after' => $date_from,
			),
		),
		'meta_query' => array(
			array(
				'key' => 'event_type',
				'value' => array( 'g1_skipped', 'g2_skipped', 'g3_skipped' ),
				'compare' => 'IN',
			),
		),
	) );
	
	while ( $skip_query->have_posts() ) {
		$skip_query->the_post();
		$event_type = get_post_meta( get_the_ID(), 'event_type', true );
		if ( $event_type === 'g1_skipped' ) $skipped_counts['g1']++;
		if ( $event_type === 'g2_skipped' ) $skipped_counts['g2']++;
		if ( $event_type === 'g3_skipped' ) $skipped_counts['g3']++;
	}
	wp_reset_postdata();
	
	return new WP_REST_Response( array(
		'funnel_data' => array(
			array( 
				'step' => 'Vào trang', 
				'users' => $funnel_data['page_view'], 
				'dropoff' => 0,
				'conversion' => 100.0,
				'percentage' => 100.0
			),
			array( 
				'step' => 'Bắt đầu', 
				'users' => $funnel_data['start'], 
				'dropoff' => $dropoff_rates['start'] ?? 0,
				'conversion' => $conversion_rates['start'] ?? 0,
				'percentage' => $total_sessions > 0 ? round( ( $funnel_data['start'] / $total_sessions ) * 100, 1 ) : 0
			),
			array( 
				'step' => 'Xong G1 (Tiêu hoá)', 
				'users' => $funnel_data['g1_complete'], 
				'dropoff' => $dropoff_rates['g1'] ?? 0,
				'conversion' => $total_sessions > 0 ? round( ( $funnel_data['g1_complete'] / $total_sessions ) * 100, 1 ) : 0,
				'percentage' => $funnel_data['start'] > 0 ? round( ( $funnel_data['g1_complete'] / $funnel_data['start'] ) * 100, 1 ) : 0
			),
			array( 
				'step' => 'Xong G2 (Hấp thu)', 
				'users' => $funnel_data['g2_complete'], 
				'dropoff' => $dropoff_rates['g2'] ?? 0,
				'conversion' => $total_sessions > 0 ? round( ( $funnel_data['g2_complete'] / $total_sessions ) * 100, 1 ) : 0,
				'percentage' => $funnel_data['g1_complete'] > 0 ? round( ( $funnel_data['g2_complete'] / $funnel_data['g1_complete'] ) * 100, 1 ) : 0
			),
			array( 
				'step' => 'Xong G3 (Vi sinh)', 
				'users' => $funnel_data['g3_complete'], 
				'dropoff' => $dropoff_rates['g3'] ?? 0,
				'conversion' => $total_sessions > 0 ? round( ( $funnel_data['g3_complete'] / $total_sessions ) * 100, 1 ) : 0,
				'percentage' => $funnel_data['g2_complete'] > 0 ? round( ( $funnel_data['g3_complete'] / $funnel_data['g2_complete'] ) * 100, 1 ) : 0
			),
			array( 
				'step' => 'Xem Kết quả', 
				'users' => $funnel_data['view_result'], 
				'dropoff' => $dropoff_rates['view_result'] ?? 0,
				'conversion' => $conversion_rates['view_result'] ?? 0,
				'percentage' => $funnel_data['g3_complete'] > 0 ? round( ( $funnel_data['view_result'] / $funnel_data['g3_complete'] ) * 100, 1 ) : 0
			),
			array( 
				'step' => 'Lưu Kết quả (SĐT)', 
				'users' => $funnel_data['save_result'], 
				'dropoff' => $dropoff_rates['save_result'] ?? 0,
				'conversion' => $conversion_rates['save_result'] ?? 0,
				'percentage' => $funnel_data['view_result'] > 0 ? round( ( $funnel_data['save_result'] / $funnel_data['view_result'] ) * 100, 1 ) : 0
			),
		),
		'section_performance' => array(
			array( 
				'name' => 'G1. Tiêu hoá & Đi ngoài', 
				'avgTime' => $avg_times['g1'] ?? 0,
				'minTime' => $section_stats['g1']['min'] ?? 0,
				'maxTime' => $section_stats['g1']['max'] ?? 0,
				'medianTime' => $section_stats['g1']['median'] ?? 0,
				'completed' => $section_stats['g1']['count'] ?? 0,
				'skipped' => $skipped_counts['g1'] ?? 0,
				'quitRate' => $dropoff_rates['g1'] ?? 0,
				'status' => ( $dropoff_rates['g1'] ?? 0 ) > 15 ? 'Cảnh báo' : 'Quan trọng'
			),
			array( 
				'name' => 'G2. Hấp thu & Ăn uống', 
				'avgTime' => $avg_times['g2'] ?? 0,
				'minTime' => $section_stats['g2']['min'] ?? 0,
				'maxTime' => $section_stats['g2']['max'] ?? 0,
				'medianTime' => $section_stats['g2']['median'] ?? 0,
				'completed' => $section_stats['g2']['count'] ?? 0,
				'skipped' => $skipped_counts['g2'] ?? 0,
				'quitRate' => $dropoff_rates['g2'] ?? 0,
				'status' => ( $dropoff_rates['g2'] ?? 0 ) > 15 ? 'Cảnh báo' : 'Quan trọng'
			),
			array( 
				'name' => 'G3. Vi sinh & Hành vi', 
				'avgTime' => $avg_times['g3'] ?? 0,
				'minTime' => $section_stats['g3']['min'] ?? 0,
				'maxTime' => $section_stats['g3']['max'] ?? 0,
				'medianTime' => $section_stats['g3']['median'] ?? 0,
				'completed' => $section_stats['g3']['count'] ?? 0,
				'skipped' => $skipped_counts['g3'] ?? 0,
				'quitRate' => $dropoff_rates['g3'] ?? 0,
				'status' => ( $dropoff_rates['g3'] ?? 0 ) > 15 ? 'Cảnh báo' : 'Ổn'
			),
		),
		'overall_metrics' => array(
			'total_sessions' => $total_sessions,
			'completion_rate' => $conversion_rates['complete'] ?? 0,
			'conversion_rate' => $conversion_rates['save_result'] ?? 0,
			'avg_total_time' => array_sum( array( $avg_times['g1'] ?? 0, $avg_times['g2'] ?? 0, $avg_times['g3'] ?? 0 ) ),
		),
	), 200 );
}

/**
 * Thêm menu Dashboard Checklist vào WordPress Admin.
 */
function autismtools_add_dashboard_menu() {
	add_menu_page(
		'Dashboard Checklist',           // Page title
		'Checklist Analytics',          // Menu title
		'manage_options',                // Capability
		'autismtools-checklist-dashboard', // Menu slug
		'autismtools_render_dashboard_page', // Callback function
		'dashicons-chart-line',          // Icon
		30                               // Position
	);
}
add_action( 'admin_menu', 'autismtools_add_dashboard_menu' );

/**
 * Enqueue scripts và styles cho Dashboard Admin.
 */
function autismtools_dashboard_admin_assets( $hook ) {
	// Chỉ load trên trang dashboard của chúng ta
	if ( 'toplevel_page_autismtools-checklist-dashboard' !== $hook ) {
		return;
	}
	
	// Google Fonts - Roboto với subset Vietnamese
	wp_enqueue_style(
		'autismtools-roboto',
		'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700;900&display=swap&subset=vietnamese',
		array(),
		null
	);
	
	// Tailwind CSS
	wp_enqueue_script(
		'autismtools-tailwind',
		'https://cdn.tailwindcss.com',
		array(),
		null,
		false
	);
	
	// Chart.js - Không cần React
	wp_enqueue_script(
		'autismtools-chartjs',
		'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
		array(),
		'4.4.0',
		false
	);
	
	// Lucide Icons
	wp_enqueue_script(
		'autismtools-lucide',
		'https://unpkg.com/lucide@latest',
		array(),
		null,
		false
	);
}
add_action( 'admin_enqueue_scripts', 'autismtools_dashboard_admin_assets' );

/**
 * Render trang Dashboard Checklist trong Admin.
 */
function autismtools_render_dashboard_page() {
	// Kiểm tra quyền truy cập
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Bạn không có quyền truy cập trang này.' );
	}
	
	// CSS tùy chỉnh cho dashboard (giữ sidebar admin)
	?>
	<style>
		/* Chỉ tùy chỉnh phần nội dung dashboard, giữ nguyên sidebar */
		#wpbody-content {
			padding: 20px !important;
		}
		
		/* Ẩn các notice không cần thiết */
		.notice,
		.update-nag {
			display: none !important;
		}
		
		/* Background cho body */
		body.wp-admin { 
			background-color: #f8fafc !important;
		}
		
		/* Dashboard container styles - Font Roboto cho tiếng Việt */
		#dashboard-root,
		#dashboard-root *,
		#dashboard-root *::before,
		#dashboard-root *::after {
			font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
		}
		
		/* Đảm bảo tất cả text trong dashboard dùng Roboto */
		#dashboard-root table,
		#dashboard-root button,
		#dashboard-root input,
		#dashboard-root select,
		#dashboard-root textarea,
		#dashboard-root th,
		#dashboard-root td,
		#dashboard-root span,
		#dashboard-root p,
		#dashboard-root div,
		#dashboard-root h1,
		#dashboard-root h2,
		#dashboard-root h3,
		#dashboard-root h4 {
			font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
		}
		
		/* Tùy chỉnh thanh cuộn cho bảng */
		#dashboard-root ::-webkit-scrollbar { width: 6px; height: 6px; }
		#dashboard-root ::-webkit-scrollbar-track { background: #f1f5f9; }
		#dashboard-root ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
		#dashboard-root ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
		
		/* Hiệu ứng Fade In */
		@keyframes fadeIn { 
			from { opacity: 0; transform: translateY(10px); } 
			to { opacity: 1; transform: translateY(0); } 
		}
		
		#dashboard-root .animate-fade-in { 
			animation: fadeIn 0.5s ease-out forwards; 
		}
		
		@keyframes slide-up {
			from {
				opacity: 0;
				transform: translateY(20px);
			}
			to {
				opacity: 1;
				transform: translateY(0);
			}
		}
		
		#dashboard-root .animate-slide-up {
			animation: slide-up 0.3s ease-out forwards;
		}
	</style>
	
	<?php
	// Include file dashboard vanilla JS (không dùng React)
	$dashboard_file = get_template_directory() . '/admin-dashboard-checklist-vanilla.php';
	if ( file_exists( $dashboard_file ) ) {
		include $dashboard_file;
	} else {
		echo '<div style="padding: 20px; color: red;">Không tìm thấy file dashboard!</div>';
	}
	?>
	<?php
}


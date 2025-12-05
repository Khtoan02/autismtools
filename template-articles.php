<?php
/**
 * Template Name: Autism Tools Articles
 *
 * @package AutismTools
 */

get_header();

the_post();

$remote_limit = get_post_meta( get_the_ID(), '_autismtools_remote_limit', true );
$remote_limit = $remote_limit ? absint( $remote_limit ) : 9;

$articles_data      = autismtools_get_remote_posts( $remote_limit );
$remote_categories  = autismtools_get_remote_categories();
$articles_json      = wp_json_encode( $articles_data );
$categories_json    = wp_json_encode( $remote_categories );
?>

<style>
	body.page-template-template-articles {
		font-family: 'Inter', 'Segoe UI', sans-serif;
		background-color: #f8fafc;
	}
	.line-clamp-2 {
		display: -webkit-box;
		-webkit-line-clamp: 2;
		-webkit-box-orient: vertical;
		overflow: hidden;
	}
	.card-hover:hover {
		transform: translateY(-5px);
		box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
	}
	.modal {
		transition: opacity 0.3s ease-in-out;
		opacity: 0;
		pointer-events: none;
	}
	.modal.open {
		opacity: 1;
		pointer-events: auto;
	}
	.modal-content {
		transform: scale(0.95);
		transition: transform 0.3s ease-in-out;
	}
	.modal.open .modal-content {
		transform: scale(1);
	}
</style>

<header class="bg-white pt-12 pb-10 text-center px-4 shadow-sm border-b border-slate-100">
	<div class="max-w-5xl mx-auto">
		<h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 mb-4 tracking-tight">
			<?php the_title(); ?>
		</h1>
		<p class="text-lg text-slate-500 max-w-2xl mx-auto mb-8">
			<?php echo esc_html( get_post_meta( get_the_ID(), '_autismtools_articles_intro', true ) ?: __( 'Cập nhật kiến thức và kinh nghiệm thực tiễn về tự kỷ, can thiệp hành vi và công nghệ dành cho cộng đồng Autism Tools.', 'autismtools' ) ); ?>
		</p>

		<div class="max-w-3xl mx-auto bg-white p-2 rounded-xl shadow-lg border border-slate-100 flex flex-col md:flex-row gap-2">
			<div class="relative flex-grow">
				<span class="absolute left-4 top-3.5 text-slate-400"><i class="fa-solid fa-magnifying-glass"></i></span>
				<input type="text" id="searchInput" placeholder="<?php esc_attr_e( 'Tìm kiếm bài viết...', 'autismtools' ); ?>"
					class="w-full pl-10 pr-4 py-3 rounded-lg border-none focus:ring-2 focus:ring-indigo-500 outline-none bg-slate-50 text-slate-700 placeholder-slate-400 transition">
			</div>
			<div class="md:w-48 relative">
				<span class="absolute left-3 top-3.5 text-slate-400"><i class="fa-solid fa-filter"></i></span>
				<select id="categoryFilter" class="w-full pl-9 pr-4 py-3 rounded-lg border-none focus:ring-2 focus:ring-indigo-500 outline-none bg-slate-50 text-slate-700 cursor-pointer appearance-none">
					<option value="all"><?php esc_html_e( 'Tất cả chủ đề', 'autismtools' ); ?></option>
				</select>
				<span class="absolute right-4 top-3.5 text-slate-400 pointer-events-none"><i class="fa-solid fa-chevron-down text-xs"></i></span>
			</div>
		</div>
	</div>
</header>

<section class="container mx-auto px-4 sm:px-6 lg:px-8 py-10 min-h-screen" aria-label="<?php esc_attr_e( 'Danh sách bài viết', 'autismtools' ); ?>">
	<div id="articlesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
		<!-- JS render -->
	</div>

	<div id="noResults" class="hidden text-center py-20">
		<div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
			<i class="fa-regular fa-face-frown text-3xl text-slate-400"></i>
		</div>
		<h3 class="text-xl font-semibold text-slate-700"><?php esc_html_e( 'Không tìm thấy bài viết nào', 'autismtools' ); ?></h3>
		<button id="resetSearch" class="mt-6 px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
			<?php esc_html_e( 'Xóa bộ lọc', 'autismtools' ); ?>
		</button>
	</div>
</section>

<div id="articleModal" class="modal fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 hidden">
	<div class="modal-content bg-white w-full max-w-4xl max-h-[90vh] rounded-2xl shadow-2xl overflow-hidden flex flex-col relative">
		<button id="closeModalBtn" class="absolute top-4 right-4 z-10 w-10 h-10 bg-black/20 hover:bg-black/40 text-white rounded-full flex items-center justify-center transition backdrop-blur-md">
			<i class="fa-solid fa-xmark text-lg"></i>
		</button>
		<div class="overflow-y-auto flex-grow" id="modalBody">
			<div class="grid md:grid-cols-2">
				<div class="h-64 md:h-auto relative">
					<img id="modalImage" src="" alt="Detail" class="absolute inset-0 w-full h-full object-cover">
				</div>
				<div class="p-8 md:p-10">
					<div class="flex items-center gap-2 mb-4">
						<span id="modalCategory" class="px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold uppercase tracking-wider rounded-full">Design</span>
						<span class="text-slate-400 text-sm"><i class="fa-regular fa-clock mr-1"></i> <span id="modalDate">01/01/2025</span></span>
					</div>
					<h2 id="modalTitle" class="text-3xl font-bold text-slate-900 mb-4 leading-tight">Tiêu đề bài viết</h2>
					<div class="prose text-slate-600 leading-relaxed mt-6">
						<p id="modalExcerpt" class="font-medium text-slate-800 mb-4 text-lg"></p>
					</div>
					<a id="modalReadMore" class="mt-8 w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition text-center inline-flex justify-center" href="#">
						<?php esc_html_e( 'Đọc toàn bộ bài viết', 'autismtools' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	const autismtoolsArticles = <?php echo $articles_json ? $articles_json : '[]'; ?>;
	const remoteCategories    = <?php echo $categories_json ? $categories_json : '[]'; ?>;

	const articlesGrid     = document.getElementById( 'articlesGrid' );
	const categoryFilter   = document.getElementById( 'categoryFilter' );
	const searchInput      = document.getElementById( 'searchInput' );
	const noResults        = document.getElementById( 'noResults' );
	const modal            = document.getElementById( 'articleModal' );
	const closeModalBtn    = document.getElementById( 'closeModalBtn' );
	const mTitle           = document.getElementById( 'modalTitle' );
	const mImage           = document.getElementById( 'modalImage' );
	const mCategory        = document.getElementById( 'modalCategory' );
	const mDate            = document.getElementById( 'modalDate' );
	const mExcerpt         = document.getElementById( 'modalExcerpt' );
	const mReadMore        = document.getElementById( 'modalReadMore' );

	const decodeHTML = ( str = '' ) => {
		const txt = document.createElement( 'textarea' );
		txt.innerHTML = str;
		return txt.value;
	};

	const slugify = ( str = '' ) => removeVietnameseTones( str ).replace( /\s+/g, '-' );

	function normalizeSlug( slug ) {
		if ( ! slug ) return '';
		// Slug từ WordPress API đã được normalize bằng sanitize_title()
		// Chỉ cần đảm bảo lowercase và trim, không normalize lại
		// Nếu slug có vẻ đã được normalize (có dấu gạch ngang, không có dấu tiếng Việt), giữ nguyên
		let normalized = slug.toLowerCase().trim();
		// Nếu slug chưa được normalize (có khoảng trắng hoặc dấu tiếng Việt), normalize lại
		if ( /[\sàáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶỸĐ]/.test( normalized ) ) {
			normalized = removeVietnameseTones( normalized );
			normalized = normalized.replace( /\s+/g, '-' );
			normalized = normalized.replace( /[^a-z0-9-]/g, '' );
			normalized = normalized.replace( /-+/g, '-' );
			normalized = normalized.replace( /^-+|-+$/g, '' );
		}
		return normalized;
	}

	const fallbackCategoryMap = new Map();

	// Tạo fallback categories từ chính dữ liệu bài viết (phòng khi API categories bị lỗi)
	autismtoolsArticles.forEach( ( post ) => {
		const decodedName = decodeHTML( post.category || '' );
		let slug          = post.category_slug || slugify( decodedName );
		slug = normalizeSlug( slug );

		if ( ! fallbackCategoryMap.has( slug ) ) {
			fallbackCategoryMap.set( slug, {
				name: decodedName || '<?php echo esc_js( __( 'Chưa phân loại', 'autismtools' ) ); ?>',
				slug: slug || 'chua-phan-loai',
			} );
		}
	} );

	// Luôn hiển thị toàn bộ chủ đề từ DawnBridge (remoteCategories).
	// Nếu API lỗi / rỗng thì mới dùng fallback từ bài viết.
	const categories = remoteCategories.length ? remoteCategories : [ ...fallbackCategoryMap.values() ];

	categories.forEach( ( category ) => {
		let categorySlug = category.slug || '';
		if ( ! categorySlug && category.name ) {
			categorySlug = slugify( decodeHTML( category.name ) );
		}
		categorySlug = normalizeSlug( categorySlug );

		const option = document.createElement( 'option' );
		option.value = categorySlug;
		option.textContent = decodeHTML( category.name || category.slug );
		categoryFilter.appendChild( option );
	} );

	function removeVietnameseTones( str ) {
		const map = [
			/[àáạảãâầấậẩẫăằắặẳẵ]/g, 'a',
			/[èéẹẻẽêềếệểễ]/g, 'e',
			/[ìíịỉĩ]/g, 'i',
			/[òóọỏõôồốộổỗơờớợởỡ]/g, 'o',
			/[ùúụủũưừứựửữ]/g, 'u',
			/[ỳýỵỷỹ]/g, 'y',
			/[đ]/g, 'd',
			/[ÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴ]/g, 'A',
			/[ÈÉẸẺẼÊỀẾỆỂỄ]/g, 'E',
			/[ÌÍỊỈĨ]/g, 'I',
			/[ÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠ]/g, 'O',
			/[ÙÚỤỦŨƯỪỨỰỬỮ]/g, 'U',
			/[ỲÝỴỶỸ]/g, 'Y',
			/[Đ]/g, 'D',
		];
		for ( let i = 0; i < map.length; i += 2 ) {
			str = str.replace( map[ i ], map[ i + 1 ] );
		}
		return str.normalize( 'NFD' ).replace( /[\u0300-\u036f]/g, '' ).toLowerCase().trim();
	}

	function renderArticles( data ) {
		articlesGrid.innerHTML = '';

		if ( ! data.length ) {
			articlesGrid.classList.add( 'hidden' );
			noResults.classList.remove( 'hidden' );
			return;
		}

		articlesGrid.classList.remove( 'hidden' );
		noResults.classList.add( 'hidden' );

		data.forEach( ( post ) => {
			const title   = decodeHTML( post.title );
			const excerpt = decodeHTML( post.excerpt );
			const category = decodeHTML( post.category );
			const card = document.createElement( 'article' );
			card.className = 'bg-white rounded-2xl overflow-hidden border border-slate-100 shadow-sm transition-all duration-300 card-hover flex flex-col h-full group cursor-pointer';
			card.innerHTML = `
				<div class="relative overflow-hidden h-56">
					<img src="${post.image}" alt="${title}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
					<div class="absolute top-4 left-4">
						<span class="px-3 py-1 bg-white/90 backdrop-blur text-xs font-bold uppercase tracking-wider text-indigo-600 rounded-full shadow-sm">
							${category}
						</span>
					</div>
				</div>
				<div class="p-6 flex flex-col flex-grow">
					<div class="flex items-center text-xs text-slate-400 mb-3 space-x-2">
						<span><i class="fa-regular fa-calendar mr-1"></i> ${post.date}</span>
						<span>&bull;</span>
						<span><i class="fa-regular fa-clock mr-1"></i> ${post.read_time}</span>
					</div>
					<h3 class="text-xl font-bold text-slate-800 mb-2 leading-tight group-hover:text-indigo-600 transition-colors">
						${title}
					</h3>
					<p class="text-slate-500 text-sm leading-relaxed mb-6 line-clamp-2 flex-grow">
						${excerpt}
					</p>
					<div class="pt-4 border-t border-slate-100 mt-auto text-right">
						<span class="text-indigo-600 text-sm font-semibold inline-flex items-center gap-1 group/link">
							<?php echo esc_html__( 'Xem chi tiết', 'autismtools' ); ?> <i class="fa-solid fa-arrow-right text-xs transition-transform group-hover/link:translate-x-1"></i>
						</span>
					</div>
				</div>
			`;
			card.addEventListener( 'click', () => openModal( post ) );
			articlesGrid.appendChild( card );
		} );
	}

	function filterHandler() {
		const searchTerm = removeVietnameseTones( decodeHTML( searchInput.value ) );
		const selectedCategory = categoryFilter.value;

		const filtered = autismtoolsArticles.filter( ( post ) => {
			const title = removeVietnameseTones( decodeHTML( post.title ) );
			
			// Lấy category slug từ post
			let postCategorySlug = post.category_slug || '';
			if ( ! postCategorySlug && post.category ) {
				postCategorySlug = slugify( decodeHTML( post.category ) );
			}
			// Normalize slug để đảm bảo khớp
			postCategorySlug = normalizeSlug( postCategorySlug );
			
			// Normalize selected category
			const normalizedSelected = selectedCategory === 'all' ? 'all' : normalizeSlug( selectedCategory );
			
			// Debug: log để kiểm tra
			if ( selectedCategory !== 'all' ) {
				console.log( 'Post:', {
					title: post.title,
					category: post.category,
					category_slug: post.category_slug,
					normalizedSlug: postCategorySlug,
					selectedCategory: selectedCategory,
					normalizedSelected: normalizedSelected,
					matches: postCategorySlug === normalizedSelected
				} );
			}
			
			const matchesSearch = ! searchTerm || title.includes( searchTerm );
			const matchesCategory = selectedCategory === 'all' || postCategorySlug === normalizedSelected;
			return matchesSearch && matchesCategory;
		} );

		console.log( 'Filtered articles:', filtered.length, 'from', autismtoolsArticles.length );
		renderArticles( filtered );
	}

	searchInput.addEventListener( 'input', filterHandler );
	categoryFilter.addEventListener( 'change', filterHandler );

	document.getElementById( 'resetSearch' ).addEventListener( 'click', () => {
		searchInput.value = '';
		categoryFilter.value = 'all';
		filterHandler();
	} );

	function openModal( post ) {
		mTitle.innerText    = decodeHTML( post.title );
		mImage.src          = post.image;
		mCategory.innerText = decodeHTML( post.category );
		mDate.innerText     = post.date;
		mExcerpt.innerText  = decodeHTML( post.excerpt );
		mReadMore.href      = post.permalink;

		modal.classList.remove( 'hidden' );
		setTimeout( () => modal.classList.add( 'open' ), 10 );
		document.body.style.overflow = 'hidden';
	}

	function closeModal() {
		modal.classList.remove( 'open' );
		setTimeout( () => {
			modal.classList.add( 'hidden' );
			document.body.style.overflow = '';
		}, 300 );
	}

	closeModalBtn.addEventListener( 'click', closeModal );

	modal.addEventListener( 'click', ( event ) => {
		if ( event.target === modal ) {
			closeModal();
		}
	} );

	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' && modal.classList.contains( 'open' ) ) {
			closeModal();
		}
	} );

	renderArticles( autismtoolsArticles );
</script>

<?php
get_footer();


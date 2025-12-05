<?php
/**
 * Footer template.
 *
 * @package AutismTools
 */
?>
	<footer class="bg-slate-900 text-slate-300 py-10 mt-auto">
		<div class="max-w-7xl mx-auto px-4 text-center md:text-left grid grid-cols-1 md:grid-cols-3 gap-8">
			<div>
				<div class="flex items-center justify-center md:justify-start gap-2 mb-4 text-white">
					<i data-lucide="brain-circuit" class="h-6 w-6"></i>
					<span class="text-lg font-bold">
						<?php
						if ( has_custom_logo() ) {
							the_custom_logo();
						} else {
							bloginfo( 'name' );
						}
						?>
					</span>
				</div>
				<p class="text-sm text-slate-400">
					<?php
					if ( get_bloginfo( 'description' ) ) {
						bloginfo( 'description' );
					} else {
						echo 'Nền tảng cơ sở dữ liệu công cụ đánh giá tự kỷ lớn nhất Việt Nam.';
					}
					?>
				</p>
			</div>
			<div>
				<h4 class="text-white font-bold mb-4">Thống kê</h4>
				<ul class="space-y-2 text-sm">
					<li><span class="text-slate-400">Tổng số công cụ:</span> <span class="text-white font-bold">54</span></li>
					<li><span class="text-slate-400">Danh mục:</span> <span class="text-white font-bold">9</span></li>
				</ul>
			</div>
			<div>
				<h4 class="text-white font-bold mb-4">Liên hệ</h4>
				<p class="text-sm text-slate-400">Email: support@autismtools.vn</p>
			</div>
		</div>
	</footer>
</div>
<?php wp_footer(); ?>
<script>
	// Khởi tạo Lucide icons sau khi trang load
	if (window.lucide) {
		window.lucide.createIcons();
	}
</script>
</body>
</html>

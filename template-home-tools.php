<?php
/**
 * Template Name: Thư Viện Công Cụ Đánh Giá
 *
 * @package AutismTools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

	<section class="bg-gradient-to-br from-blue-50 via-white to-cyan-50 py-12 border-b border-blue-100">
		<div class="max-w-6xl mx-auto px-4 text-center">
			<h1 class="text-3xl md:text-5xl font-bold mb-4 tracking-tight">
				Kho Tàng <span class="gradient-text">50+ Công Cụ Đánh Giá</span>
			</h1>
			<p class="text-lg text-slate-600 mb-8 max-w-3xl mx-auto leading-relaxed">
				Cơ sở dữ liệu đầy đủ nhất về các thang đo sàng lọc, chẩn đoán, ngôn ngữ, vận động và tâm lý thần kinh cho trẻ rối loạn phổ tự kỷ.
			</p>
			<div class="bg-white p-2 rounded-xl shadow-xl max-w-2xl mx-auto flex items-center border border-slate-200 focus-within:ring-2 ring-blue-500 ring-offset-2 transition-all">
				<div class="pl-3 text-slate-400">
					<i data-lucide="search" class="h-5 w-5"></i>
				</div>
				<input type="text" id="searchInput" class="flex-grow pl-3 pr-3 py-3 border-none rounded-lg focus:outline-none text-slate-900 placeholder-slate-400 text-base" placeholder="Tìm kiếm (VD: Giác quan, Ngôn ngữ, Asperger...)">
			</div>
		</div>
	</section>

	<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow">
		<div class="mb-8 overflow-hidden relative">
			<div class="flex gap-3 overflow-x-auto no-scrollbar pb-2" id="filterTagsContainer">
			</div>
			<div class="absolute right-0 top-0 bottom-2 w-12 bg-gradient-to-l from-slate-50 to-transparent pointer-events-none md:hidden"></div>
		</div>

		<div class="flex justify-between items-end mb-6 border-b border-slate-100 pb-2">
			<h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
				Danh sách công cụ
				<span class="text-slate-500 text-sm font-normal bg-slate-100 px-3 py-1 rounded-full" id="toolCount">...</span>
			</h2>
		</div>

		<div id="toolsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" aria-live="polite">
		</div>

		<div id="emptyState" class="hidden text-center py-20 bg-slate-50 rounded-2xl border border-dashed border-slate-300 mt-6">
			<div class="inline-flex p-4 rounded-full bg-white shadow-sm mb-4">
				<i data-lucide="search-x" class="h-8 w-8 text-slate-400"></i>
			</div>
			<h3 class="text-lg font-medium text-slate-900">Không tìm thấy kết quả phù hợp</h3>
			<p class="text-slate-500 mt-1">Hãy thử tìm bằng từ khóa khác hoặc chọn "Tất cả".</p>
			<button onclick="resetFilters()" class="mt-4 text-blue-600 font-medium hover:underline">Xóa bộ lọc</button>
		</div>

		<div id="disclaimer" class="mt-16 p-4 md:p-6 bg-amber-50 rounded-xl border border-amber-200 flex gap-4 items-start">
			<div class="p-2 bg-amber-100 rounded-lg text-amber-600 flex-shrink-0">
				<i data-lucide="alert-triangle" class="h-5 w-5"></i>
			</div>
			<div>
				<h4 class="font-bold text-amber-900">Lưu ý chuyên môn</h4>
				<p class="text-sm text-amber-800 mt-1 leading-relaxed">
					Danh sách này bao gồm cả các công cụ sàng lọc (có thể tự làm) và các công cụ chẩn đoán chuyên sâu (cần bác sĩ/chuyên gia tâm lý). Kết quả từ các bài test online chỉ mang tính tham khảo.
				</p>
			</div>
		</div>
	</section>

<script>
	const toolsData = [
		{ id: 'checklist', name: 'Checklist Tiêu Hóa', fullName: 'Checklist Tiêu Hóa Official', category: 'monitoring', age: 'Mọi độ tuổi', description: 'Bộ checklist tiêu hóa chính thức giúp phụ huynh rà soát dấu hiệu và nhận tư vấn nhanh.', tags: ['Checklist', 'Official'], icon: 'clipboard-check', link: '/check-list' },
		{ id: 'm-chat-rf', name: 'M-CHAT-R/F', fullName: 'Modified Checklist for Autism in Toddlers', category: 'screening', age: '16 - 30 tháng', description: 'Công cụ phổ biến nhất. 20 câu hỏi Yes/No phát hiện nguy cơ tự kỷ sớm.', tags: ['Phổ biến', 'Online'], icon: 'baby' },
		{ id: 'q-chat', name: 'Q-CHAT', fullName: 'Quantitative Checklist for Autism in Toddlers', category: 'screening', age: '18 - 24 tháng', description: 'Phiên bản định lượng của M-CHAT với thang điểm 5 mức độ, giúp giảm dương tính giả.', tags: ['Định lượng'], icon: 'list-checks' },
		{ id: 'itc', name: 'ITC', fullName: 'Infant-Toddler Checklist', category: 'screening', age: '6 - 24 tháng', description: 'Một phần của bộ CSBS-DP, cha mẹ đánh giá về cảm xúc và giao tiếp bằng mắt.', tags: ['Sớm'], icon: 'eye' },
		{ id: 'csbs-dp', name: 'CSBS-DP', fullName: 'Communication and Symbolic Behavior Scales', category: 'screening', age: '6 - 24 tháng', description: 'Đánh giá chi tiết hơn về kỹ năng giao tiếp sớm và biểu tượng.', tags: ['Giao tiếp'], icon: 'message-circle' },
		{ id: 'esat', name: 'ESAT', fullName: 'Early Screening of Autistic Traits', category: 'screening', age: '0 - 14 tháng', description: 'Sàng lọc các đặc điểm tự kỷ rất sớm (ít cười, ít nhìn mắt).', tags: ['Sơ sinh'], icon: 'footprints' },
		{ id: 'fyi', name: 'FYI', fullName: 'First Year Inventory', category: 'screening', age: '12 tháng', description: 'Bảng kiểm kê năm đầu đời để xác định nguy cơ tự kỷ.', tags: ['1 tuổi'], icon: 'calendar-clock' },
		{ id: 'aosi', name: 'AOSI', fullName: 'Autism Observation Scale for Infants', category: 'screening', age: '6 - 18 tháng', description: 'Thang quan sát dành cho trẻ sơ sinh có nguy cơ cao (ví dụ: có anh/chị bị tự kỷ).', tags: ['Nguy cơ cao'], icon: 'alert-circle' },
		{ id: 'stat', name: 'STAT', fullName: 'Screening Tool for Autism in Toddlers', category: 'screening', age: '24 - 36 tháng', description: 'Sàng lọc tương tác trực tiếp qua các trò chơi.', tags: ['Tương tác'], icon: 'puzzle' },
		{ id: 'asq-3', name: 'ASQ-3', fullName: 'Ages and Stages Questionnaires', category: 'screening', age: '1 - 66 tháng', description: 'Sàng lọc phát triển toàn diện 5 lĩnh vực.', tags: ['Tổng quát'], icon: 'activity' },
		{ id: 'asq-se', name: 'ASQ:SE-2', fullName: 'ASQ: Social-Emotional', category: 'screening', age: '1 - 72 tháng', description: 'Chuyên biệt về phát triển Cảm xúc & Xã hội.', tags: ['Cảm xúc'], icon: 'heart' },
		{ id: 'peds', name: 'PEDS', fullName: "Parents' Evaluation of Developmental Status", category: 'screening', age: '0 - 8 tuổi', description: 'Dựa trên mối lo ngại của cha mẹ để phân loại nguy cơ.', tags: ['Cha mẹ'], icon: 'users' },
		{ id: 'denver-2', name: 'Denver II', fullName: 'Denver Developmental Screening Test', category: 'screening', age: '0 - 6 tuổi', description: 'Công cụ kinh điển đánh giá mốc phát triển.', tags: ['Kinh điển'], icon: 'bar-chart' },
		{ id: 'cast', name: 'CAST', fullName: 'Childhood Autism Spectrum Test', category: 'screening', age: '4 - 11 tuổi', description: '39 câu hỏi sàng lọc cho trẻ tiểu học.', tags: ['Học đường'], icon: 'school' },
		{ id: 'assq', name: 'ASSQ', fullName: 'Autism-Spectrum Screening Questionnaire', category: 'screening', age: '6 - 17 tuổi', description: 'Tập trung vào các dấu hiệu Asperger/Chức năng cao ở độ tuổi đi học.', tags: ['Asperger'], icon: 'book' },
		{ id: 'scq', name: 'SCQ', fullName: 'Social Communication Questionnaire', category: 'screening', age: '> 4 tuổi', description: 'Bảng hỏi 40 câu dựa trên ADI-R, sàng lọc nhanh.', tags: ['Nhanh'], icon: 'zap' },
		{ id: 'ados-2', name: 'ADOS-2', fullName: 'Autism Diagnostic Observation Schedule', category: 'diagnostic', age: '12 tháng+', description: 'Tiêu chuẩn vàng. Quan sát hành vi trực tiếp.', tags: ['Tiêu chuẩn vàng'], icon: 'stethoscope' },
		{ id: 'adi-r', name: 'ADI-R', fullName: 'Autism Diagnostic Interview-Revised', category: 'diagnostic', age: '> 2 tuổi', description: 'Phỏng vấn sâu lịch sử phát triển.', tags: ['Lịch sử'], icon: 'file-text' },
		{ id: 'cars-2', name: 'CARS-2', fullName: 'Childhood Autism Rating Scale', category: 'diagnostic', age: '> 2 tuổi', description: 'Thang chấm điểm hành vi (bản ST cho tiêu chuẩn, bản HF cho chức năng cao).', tags: ['Phổ biến'], icon: 'clipboard-list' },
		{ id: 'disco', name: 'DISCO', fullName: 'Diagnostic Interview for Social/Communication', category: 'diagnostic', age: 'Mọi lứa tuổi', description: 'Phỏng vấn chẩn đoán chi tiết (Châu Âu).', tags: ['Chi tiết'], icon: 'message-square' },
		{ id: '3di', name: '3Di', fullName: 'Developmental, Dimensional and Diagnostic Interview', category: 'diagnostic', age: 'Mọi lứa tuổi', description: 'Phỏng vấn vi tính hóa đa chiều.', tags: ['Vi tính hóa'], icon: 'monitor' },
		{ id: 'srs-2', name: 'SRS-2', fullName: 'Social Responsiveness Scale', category: 'rating', age: '2.5 tuổi+', description: 'Đánh giá mức độ khiếm khuyết xã hội định lượng.', tags: ['Xã hội'], icon: 'users' },
		{ id: 'gars-3', name: 'GARS-3', fullName: 'Gilliam Autism Rating Scale', category: 'rating', age: '3 - 22 tuổi', description: 'Xác định xác suất tự kỷ và mức độ nghiêm trọng.', tags: ['Giáo dục'], icon: 'graduation-cap' },
		{ id: 'gads', name: 'GADS', fullName: "Gilliam Asperger's Disorder Scale", category: 'rating', age: '3 - 22 tuổi', description: 'Chuyên biệt cho hội chứng Asperger.', tags: ['Asperger'], icon: 'user-check' },
		{ id: 'asrs', name: 'ASRS', fullName: 'Autism Spectrum Rating Scales', category: 'rating', age: '2 - 18 tuổi', description: 'Đánh giá toàn diện các triệu chứng phổ tự kỷ.', tags: ['Đa chiều'], icon: 'layers' },
		{ id: 'abc', name: 'ABC', fullName: 'Autism Behavior Checklist', category: 'rating', age: '> 18 tháng', description: '57 mục hành vi (thuộc bộ ASIEP-3).', tags: ['Hành vi'], icon: 'list-todo' },
		{ id: 'pddbi', name: 'PDDBI', fullName: 'PDD Behavior Inventory', category: 'rating', age: '1.5 - 12.5 tuổi', description: 'Đánh giá cả hành vi điển hình và hành vi thích ứng.', tags: ['Thích ứng'], icon: 'smile' },
		{ id: 'casd', name: 'CASD', fullName: 'Checklist for Autism Spectrum Disorder', category: 'rating', age: '1 - 16 tuổi', description: 'Bảng kiểm nhanh 30 triệu chứng.', tags: ['Nhanh'], icon: 'check-square' },
		{ id: 'bayley-4', name: 'Bayley-4', fullName: 'Bayley Scales of Infant Development', category: 'cognitive', age: '1 - 42 tháng', description: 'Đánh giá phát triển trí tuệ trẻ nhỏ tốt nhất.', tags: ['Trẻ nhỏ'], icon: 'blocks' },
		{ id: 'msel', name: 'Mullen (MSEL)', fullName: 'Mullen Scales of Early Learning', category: 'cognitive', age: '0 - 68 tháng', description: 'Đánh giá nhận thức và vận động sớm.', tags: ['Sớm'], icon: 'book-open' },
		{ id: 'wisc-v', name: 'WISC-V', fullName: 'Wechsler Intelligence Scale for Children', category: 'cognitive', age: '6 - 16 tuổi', description: 'IQ tiêu chuẩn cho trẻ đi học.', tags: ['IQ'], icon: 'brain-circuit' },
		{ id: 'wppsi-iv', name: 'WPPSI-IV', fullName: 'Wechsler Preschool & Primary Scale', category: 'cognitive', age: '2.5 - 7 tuổi', description: 'IQ cho trẻ mầm non.', tags: ['IQ Mầm non'], icon: 'shapes' },
		{ id: 'leiter-3', name: 'Leiter-3', fullName: 'Leiter International Performance Scale', category: 'cognitive', age: '3 - 75+ tuổi', description: 'IQ phi ngôn ngữ (không cần nói).', tags: ['Phi ngôn ngữ'], icon: 'ear-off' },
		{ id: 'unit-2', name: 'UNIT-2', fullName: 'Universal Nonverbal Intelligence Test', category: 'cognitive', age: '5 - 21 tuổi', description: 'Bài test IQ phi ngôn ngữ công bằng văn hóa.', tags: ['Công bằng'], icon: 'globe' },
		{ id: 'pep-3', name: 'PEP-3', fullName: 'Psychoeducational Profile', category: 'cognitive', age: '2 - 7 tuổi', description: 'Hồ sơ tâm lý giáo dục cho trẻ rối loạn phát triển.', tags: ['Giáo dục'], icon: 'file-bar-chart' },
		{ id: 'brief-2', name: 'BRIEF-2', fullName: 'Behavior Rating Inventory of Executive Function', category: 'cognitive', age: '5 - 18 tuổi', description: 'Đánh giá chức năng điều hành não bộ.', tags: ['Chức năng não'], icon: 'cpu' },
		{ id: 'pls-5', name: 'PLS-5', fullName: 'Preschool Language Scales', category: 'language', age: '0 - 7 tuổi', description: 'Đánh giá ngôn ngữ chơi và giao tiếp sớm.', tags: ['Ngôn ngữ'], icon: 'mic' },
		{ id: 'celf-5', name: 'CELF-5', fullName: 'Clinical Evaluation of Language Fundamentals', category: 'language', age: '5 - 21 tuổi', description: 'Đánh giá ngôn ngữ học đường chuyên sâu.', tags: ['Học đường'], icon: 'book' },
		{ id: 'ppvt-4', name: 'PPVT-4', fullName: 'Peabody Picture Vocabulary Test', category: 'language', age: '2.5+', description: 'Kiểm tra vốn từ vựng thụ động (nghe hiểu hình ảnh).', tags: ['Từ vựng'], icon: 'image' },
		{ id: 'evt-2', name: 'EVT-2', fullName: 'Expressive Vocabulary Test', category: 'language', age: '2.5+', description: 'Kiểm tra vốn từ vựng diễn đạt (gọi tên).', tags: ['Từ vựng'], icon: 'message-square' },
		{ id: 'ccc-2', name: 'CCC-2', fullName: "Children's Communication Checklist", category: 'language', age: '4 - 16 tuổi', description: 'Đánh giá các vấn đề về ngữ dụng (giao tiếp xã hội) mà bài test thường bỏ qua.', tags: ['Ngữ dụng'], icon: 'messages-square' },
		{ id: 'vineland-3', name: 'Vineland-3', fullName: 'Vineland Adaptive Behavior Scales', category: 'sensory', age: 'Mọi lứa tuổi', description: 'Kỹ năng thích ứng và tự lập trong cuộc sống.', tags: ['Kỹ năng sống'], icon: 'home' },
		{ id: 'abas-3', name: 'ABAS-3', fullName: 'Adaptive Behavior Assessment System', category: 'sensory', age: 'Sơ sinh - Người lớn', description: 'Hệ thống đánh giá hành vi thích ứng toàn diện.', tags: ['Thích ứng'], icon: 'settings' },
		{ id: 'sensory-profile', name: 'Sensory Profile 2', fullName: 'Winnie Dunn Sensory Profile', category: 'sensory', age: '0 - 14 tuổi', description: 'Đánh giá mô hình xử lý giác quan.', tags: ['Giác quan'], icon: 'eye' },
		{ id: 'spm-2', name: 'SPM-2', fullName: 'Sensory Processing Measure', category: 'sensory', age: '4 tháng - Người lớn', description: 'Đánh giá xử lý giác quan tại nhà và trường học.', tags: ['Giác quan'], icon: 'activity' },
		{ id: 'pdms-2', name: 'PDMS-2', fullName: 'Peabody Developmental Motor Scales', category: 'sensory', age: '0 - 5 tuổi', description: 'Vận động thô và tinh chi tiết.', tags: ['Vận động'], icon: 'move' },
		{ id: 'bot-2', name: 'BOT-2', fullName: 'Bruininks-Oseretsky Test of Motor Proficiency', category: 'sensory', age: '4 - 21 tuổi', description: 'Đánh giá kỹ năng vận động cho trẻ lớn hơn.', tags: ['Vận động'], icon: 'run' },
		{ id: 'vb-mapp', name: 'VB-MAPP', fullName: 'Verbal Behavior Milestones Assessment', category: 'sensory', age: '0 - 4 tuổi', description: 'Đánh giá rào cản học tập (ABA).', tags: ['Giáo dục'], icon: 'book-open' },
		{ id: 'atec', name: 'ATEC', fullName: 'Autism Treatment Evaluation Checklist', category: 'monitoring', age: 'Mọi độ tuổi', description: 'Theo dõi hiệu quả điều trị miễn phí.', tags: ['Theo dõi'], icon: 'trending-up' },
		{ id: 'cbcl', name: 'CBCL', fullName: 'Child Behavior Checklist', category: 'monitoring', age: '1.5 - 18 tuổi', description: 'Đánh giá các vấn đề tâm lý đi kèm (trầm cảm, lo âu, chống đối).', tags: ['Tâm lý'], icon: 'frown' },
		{ id: 'aq', name: 'AQ', fullName: 'Autism-Spectrum Quotient', category: 'adults', age: 'Người lớn', description: 'Chỉ số tự kỷ (Tự đánh giá).', tags: ['Tự làm'], icon: 'user' },
		{ id: 'raads-r', name: 'RAADS-R', fullName: 'Ritvo Autism Asperger Diagnostic Scale', category: 'adults', age: '16+', description: 'Chẩn đoán Asperger người lớn.', tags: ['Chuyên sâu'], icon: 'user-check' },
		{ id: 'cat-q', name: 'CAT-Q', fullName: 'Camouflaging Autistic Traits', category: 'adults', age: '16+', description: 'Đánh giá hành vi che giấu (masking).', tags: ['Masking'], icon: 'venetian-mask' },
		{ id: 'eq', name: 'EQ', fullName: 'Empathy Quotient', category: 'adults', age: 'Người lớn', description: 'Đánh giá chỉ số đồng cảm.', tags: ['Đồng cảm'], icon: 'heart-handshake' },
		{ id: 'sq', name: 'SQ', fullName: 'Systemizing Quotient', category: 'adults', age: 'Người lớn', description: 'Đánh giá chỉ số hệ thống hóa (tư duy logic/quy luật).', tags: ['Logic'], icon: 'git-merge' }
	];

	const categories = {
		'all': { label: 'Tất cả (54)', color: 'border-slate-200 text-slate-600 hover:bg-slate-50' },
		'screening': { label: 'Sàng lọc', color: 'border-green-200 text-green-700 bg-green-50' },
		'diagnostic': { label: 'Chẩn đoán Chuẩn', color: 'border-purple-200 text-purple-700 bg-purple-50' },
		'rating': { label: 'Thang đo Hành vi', color: 'border-blue-200 text-blue-700 bg-blue-50' },
		'cognitive': { label: 'IQ & Nhận thức', color: 'border-rose-200 text-rose-700 bg-rose-50' },
		'language': { label: 'Ngôn ngữ', color: 'border-sky-200 text-sky-700 bg-sky-50' },
		'sensory': { label: 'Giác quan & Vận động', color: 'border-orange-200 text-orange-700 bg-orange-50' },
		'monitoring': { label: 'Theo dõi & Tâm lý', color: 'border-teal-200 text-teal-700 bg-teal-50' },
		'adults': { label: 'Người lớn', color: 'border-indigo-200 text-indigo-700 bg-indigo-50' }
	};

	const categoryBadges = {
		'screening': 'bg-green-100 text-green-700',
		'diagnostic': 'bg-purple-100 text-purple-700',
		'rating': 'bg-blue-100 text-blue-700',
		'cognitive': 'bg-rose-100 text-rose-700',
		'language': 'bg-sky-100 text-sky-700',
		'sensory': 'bg-orange-100 text-orange-700',
		'monitoring': 'bg-teal-100 text-teal-700',
		'adults': 'bg-indigo-100 text-indigo-700'
	};

	let currentCategory = 'all';
	let currentSearch = '';

	document.addEventListener('DOMContentLoaded', () => {
		renderFilterTags();
		renderTools();
		if (window.lucide) {
			window.lucide.createIcons();
		}
	});

	function renderFilterTags() {
		const container = document.getElementById('filterTagsContainer');
		container.innerHTML = '';

		Object.keys(categories).forEach(key => {
			const btn = document.createElement('button');
			const isActive = currentCategory === key;
			let className = 'filter-chip px-4 py-2 rounded-full border text-sm font-medium transition-all whitespace-nowrap flex-shrink-0 ';

			if (isActive) {
				if (key === 'diagnostic') className += 'text-purple-700 bg-purple-50 border-purple-200';
				else if (key === 'cognitive') className += 'text-rose-700 bg-rose-50 border-rose-200';
				else if (key === 'rating') className += 'text-blue-700 bg-blue-50 border-blue-200';
				else if (key === 'language') className += 'text-sky-700 bg-sky-50 border-sky-200';
				else if (key === 'sensory') className += 'text-orange-700 bg-orange-50 border-orange-200';
				else className += 'active border-blue-200 bg-blue-50 text-blue-700';
			} else {
				className += 'border-slate-200 text-slate-600 hover:bg-slate-50';
			}

			btn.className = className;
			btn.innerText = categories[key].label;
			btn.onclick = () => {
				currentCategory = key;
				renderFilterTags();
				renderTools();
			};
			container.appendChild(btn);
		});
	}

	function renderTools() {
		const grid = document.getElementById('toolsGrid');
		const emptyState = document.getElementById('emptyState');
		const countLabel = document.getElementById('toolCount');

		grid.innerHTML = '';

		const filteredData = toolsData.filter(tool => {
			const text = currentSearch.toLowerCase();
			const matchesText = tool.name.toLowerCase().includes(text) ||
				tool.fullName.toLowerCase().includes(text) ||
				tool.description.toLowerCase().includes(text);
			const matchesCategory = currentCategory === 'all' || tool.category === currentCategory;
			return matchesText && matchesCategory;
		});

		countLabel.innerText = filteredData.length.toString();

		if (filteredData.length === 0) {
			emptyState.classList.remove('hidden');
			grid.classList.add('hidden');
			return;
		}

		emptyState.classList.add('hidden');
		grid.classList.remove('hidden');

		filteredData.forEach(tool => {
			const badgeClass = categoryBadges[tool.category] || 'bg-slate-100 text-slate-700';
			const categoryLabel = categories[tool.category]?.label || tool.category;

			const tagsHtml = tool.tags.map(tag =>
				`<span class="inline-block px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 mr-1 border border-slate-200">${tag}</span>`
			).join('');

			const card = document.createElement('div');
			card.className = 'bg-white rounded-xl p-6 border border-slate-200 card-hover transition-all duration-300 flex flex-col h-full shadow-sm';
			card.innerHTML = `
				<div class="flex justify-between items-start mb-4">
					<div class="p-3 rounded-lg bg-slate-50 text-slate-600">
						<i data-lucide="${tool.icon}" class="h-6 w-6"></i>
					</div>
					<span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider ${badgeClass}">
						${categoryLabel}
					</span>
				</div>
				<h3 class="text-xl font-bold text-slate-900 mb-1 leading-tight">${tool.name}</h3>
				<p class="text-xs text-slate-500 font-medium mb-3 uppercase tracking-wide truncate" title="${tool.fullName}">${tool.fullName}</p>
				<div class="mb-4 flex flex-wrap gap-y-1">
					${tagsHtml}
				</div>
				<p class="text-slate-600 text-sm mb-5 flex-grow leading-relaxed">
					${tool.description}
				</p>
				<div class="border-t border-slate-100 pt-4 mt-auto flex items-center justify-between">
					<span class="text-xs text-slate-500 font-medium bg-slate-50 px-2 py-1 rounded border border-slate-100">
						<i data-lucide="clock" class="h-3 w-3 inline mr-1"></i> ${tool.age}
					</span>
					<a class="text-blue-600 text-sm font-bold hover:text-blue-800 flex items-center group transition-colors" href="${tool.link ? tool.link : '#'}" ${tool.link ? 'target="_self"' : 'onclick="alert(\'Thông tin chi tiết về ' + tool.name + ' đang được cập nhật.\'); return false;"'}>
						Chi tiết <i data-lucide="arrow-right" class="h-4 w-4 ml-1 transform group-hover:translate-x-1 transition-transform"></i>
					</a>
				</div>
			`;
			grid.appendChild(card);
		});

		if (window.lucide) {
			window.lucide.createIcons();
		}
	}

	document.getElementById('searchInput')?.addEventListener('input', (e) => {
		currentSearch = e.target.value.trim();
		renderTools();
	});

	function resetFilters() {
		currentSearch = '';
		currentCategory = 'all';
		const searchInput = document.getElementById('searchInput');
		if (searchInput) {
			searchInput.value = '';
		}
		renderFilterTags();
		renderTools();
	}
</script>

<?php
get_footer();


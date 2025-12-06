<!-- Dashboard Checklist - Vanilla JS Version (Không dùng React) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700;900&display=swap&subset=vietnamese" rel="stylesheet">
<div id="dashboard-root" class="min-h-screen bg-slate-50 font-sans text-slate-800 pb-12 relative p-4 sm:p-6" style="font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;"></div>

<script>
(function() {
	'use strict';
	
	// Đợi Chart.js và Lucide load xong
	async function initDashboard() {
		if (typeof Chart === 'undefined' || typeof lucide === 'undefined') {
			setTimeout(initDashboard, 100);
			return;
		}
		
		console.log('Dashboard đang khởi tạo...');
		
		// Lấy dữ liệu thực từ API
		let dashboardData = {
			riskDistribution: [
				{ name: 'Mức độ NẶNG', value: 0, color: '#991b1b' },
				{ name: 'Mức TRUNG BÌNH', value: 0, color: '#ca8a04' },
				{ name: 'Mức độ NHẸ', value: 0, color: '#166534' },
			],
			recentLeads: [],
			funnelData: {
				labels: ['Vào trang', 'Bắt đầu', 'Xong G1', 'Xong G2', 'Xong G3', 'Xem Kết quả', 'Lưu Kết quả'],
				values: [0, 0, 0, 0, 0, 0, 0]
			},
			stats: {
				total: 0,
				completed: 0,
				leads: 0,
				severeRate: 0
			}
		};
		
		try {
			// Lấy thống kê
			const statsResponse = await fetch('<?php echo esc_url( rest_url( 'autismtools/v1/checklist/stats' ) ); ?>?days=7', {
				headers: {
					'X-WP-Nonce': '<?php echo wp_create_nonce( 'wp_rest' ); ?>'
				}
			});
			
			if (statsResponse.ok) {
				const stats = await statsResponse.json();
				dashboardData.stats = {
					total: stats.total || 0,
					completed: stats.completed || 0,
					leads: stats.actions.save_image + stats.actions.call || 0,
					severeRate: stats.total > 0 ? Math.round((stats.levels.severe / stats.total) * 100) : 0
				};
				
				if (stats.risk_distribution) {
					dashboardData.riskDistribution = stats.risk_distribution;
				}
			}
			
			// Lấy danh sách leads
			const leadsResponse = await fetch('<?php echo esc_url( rest_url( 'autismtools/v1/checklist/leads' ) ); ?>?limit=50', {
				headers: {
					'X-WP-Nonce': '<?php echo wp_create_nonce( 'wp_rest' ); ?>'
				}
			});
			
			if (leadsResponse.ok) {
				const leads = await leadsResponse.json();
				dashboardData.recentLeads = leads.map(lead => ({
					...lead,
					phone: lead.phone ? lead.phone.replace(/(\d{3})\d{4}(\d{3})/, '$1***$2') : '',
					date: lead.date || new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' })
				}));
			}
			
			// Lấy behavior stats
			const behaviorResponse = await fetch('<?php echo esc_url( rest_url( 'autismtools/v1/checklist/behavior-stats' ) ); ?>?days=7', {
				headers: {
					'X-WP-Nonce': '<?php echo wp_create_nonce( 'wp_rest' ); ?>'
				}
			});
			
			if (behaviorResponse.ok) {
				const behavior = await behaviorResponse.json();
				dashboardData.behaviorStats = behavior;
				dashboardData.funnelData = {
					labels: behavior.funnel_data.map(d => d.step),
					values: behavior.funnel_data.map(d => d.users)
				};
			}
		} catch (error) {
			console.error('Lỗi khi lấy dữ liệu:', error);
		}
		
		// Render Dashboard với dữ liệu thực
		renderDashboard(dashboardData);
	}
	
	function renderDashboard(dashboardData) {
		const root = document.getElementById('dashboard-root');
		root.innerHTML = `
			<style>
				#dashboard-root, #dashboard-root *, #dashboard-root *::before, #dashboard-root *::after {
					font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
				}
			</style>
			<!-- Top Nav -->
			<nav class="bg-white border-b border-gray-200 rounded-2xl sticky top-4 z-30 px-6 py-4 shadow-sm mb-6">
				<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
					<div class="flex items-center gap-3">
						<div class="w-10 h-10 bg-teal-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg shadow-teal-200">
							DB
						</div>
						<div>
							<h1 class="text-xl font-bold text-gray-800 leading-tight">Analytics: Checklist Tiêu Hóa</h1>
							<p class="text-xs text-gray-500 font-medium">Phiên bản chính thức 2024</p>
						</div>
					</div>
					
					<div class="flex items-center gap-3 self-end md:self-auto">
						<div class="flex bg-gray-100 p-1 rounded-lg mr-2">
							<button onclick="switchTab('overview')" class="tab-btn active px-4 py-2 text-xs font-bold rounded-md transition-all bg-white text-teal-700 shadow-sm">Tổng quan</button>
							<button onclick="switchTab('behavior')" class="tab-btn px-4 py-2 text-xs font-bold rounded-md transition-all text-gray-500 hover:text-gray-700">Hành vi & UX</button>
							<button onclick="switchTab('medical')" class="tab-btn px-4 py-2 text-xs font-bold rounded-md transition-all text-gray-500 hover:text-gray-700">Y Khoa</button>
						</div>
					</div>
				</div>
			</nav>

			<div class="max-w-7xl mx-auto space-y-6">
				<!-- TAB 1: OVERVIEW -->
				<div id="tab-overview" class="tab-content space-y-6">
					<!-- KPI Cards -->
					<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
						<div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all">
							<div class="flex justify-between items-start">
								<div>
									<p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Lượt làm Checklist</p>
									<h3 class="text-2xl font-bold text-gray-800 mt-1" id="kpi-total">${dashboardData.stats.total.toLocaleString()}</h3>
								</div>
								<div class="p-2.5 rounded-lg bg-blue-100 text-blue-600 bg-opacity-10">
									<i data-lucide="clipboard-check" class="w-5 h-5"></i>
								</div>
							</div>
							<div class="mt-3 flex items-center text-xs font-medium text-gray-400">
								<span>7 ngày qua</span>
							</div>
						</div>
						
						<div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all">
							<div class="flex justify-between items-start">
								<div>
									<p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tỷ lệ Hoàn thành</p>
									<h3 class="text-2xl font-bold text-gray-800 mt-1" id="kpi-completed">${dashboardData.stats.total > 0 ? Math.round((dashboardData.stats.completed / dashboardData.stats.total) * 100) : 0}%</h3>
								</div>
								<div class="p-2.5 rounded-lg bg-indigo-100 text-indigo-600 bg-opacity-10">
									<i data-lucide="activity" class="w-5 h-5"></i>
								</div>
							</div>
							<div class="mt-3 flex items-center text-xs font-medium text-gray-400">
								<span>${dashboardData.stats.completed}/${dashboardData.stats.total} lượt hoàn thành</span>
							</div>
						</div>
						
						<div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all">
							<div class="flex justify-between items-start">
								<div>
									<p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Leads (Lưu/Gọi)</p>
									<h3 class="text-2xl font-bold text-gray-800 mt-1" id="kpi-leads">${dashboardData.stats.leads.toLocaleString()}</h3>
								</div>
								<div class="p-2.5 rounded-lg bg-teal-100 text-teal-600 bg-opacity-10">
									<i data-lucide="user-check" class="w-5 h-5"></i>
								</div>
							</div>
							<div class="mt-3 flex items-center text-xs font-medium text-gray-400">
								<span>Chất lượng Lead tốt</span>
							</div>
						</div>
						
						<div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all">
							<div class="flex justify-between items-start">
								<div>
									<p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Khách Nguy cơ Nặng</p>
									<h3 class="text-2xl font-bold text-gray-800 mt-1" id="kpi-severe">${dashboardData.stats.severeRate}%</h3>
								</div>
								<div class="p-2.5 rounded-lg bg-red-100 text-red-600 bg-opacity-10">
									<i data-lucide="alert-triangle" class="w-5 h-5"></i>
								</div>
							</div>
							<div class="mt-3 flex items-center text-xs font-medium text-gray-400">
								<span>Cần tư vấn ngay</span>
							</div>
						</div>
					</div>

					<!-- Leads Table & Pie Chart -->
					<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
						<div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
							<div class="p-5 border-b border-gray-100 bg-gray-50/50">
								<h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
									<i data-lucide="user-check" class="w-5 h-5 text-teal-600"></i>
									Leads Mới Nhất
								</h2>
								<p class="text-xs text-gray-500 mt-0.5">Dữ liệu từ form lưu ảnh & gọi điện</p>
							</div>
							<div class="overflow-x-auto">
								<table class="w-full text-sm text-left">
									<thead class="text-xs text-gray-500 uppercase bg-gray-50">
										<tr>
											<th class="px-6 py-3 font-semibold">Phụ huynh / SĐT</th>
											<th class="px-6 py-3 font-semibold">Bé / Nguy cơ</th>
											<th class="px-6 py-3 font-semibold text-center">Hành động</th>
											<th class="px-6 py-3 font-semibold text-right">Chi tiết</th>
										</tr>
									</thead>
									<tbody class="divide-y divide-gray-100" id="leads-tbody">
										${dashboardData.recentLeads.length > 0 ? dashboardData.recentLeads.map(lead => `
											<tr class="hover:bg-teal-50/30 transition-colors">
												<td class="px-6 py-4">
													<div class="font-bold text-gray-800">${lead.parent}</div>
													<div class="text-xs text-gray-500">${lead.phone}</div>
												</td>
												<td class="px-6 py-4">
													<div class="text-gray-700 font-medium">${lead.child || '-'}</div>
													<span class="inline-flex mt-1 px-2 py-0.5 rounded text-[10px] font-bold ${
														lead.score === 'Nặng' ? 'bg-red-100 text-red-700' : 
														lead.score === 'Trung Bình' ? 'bg-yellow-100 text-yellow-700' : 
														'bg-green-100 text-green-700'
													}">
														${lead.score}
													</span>
												</td>
												<td class="px-6 py-4 text-center">
													<div class="flex items-center justify-center gap-1 text-xs text-gray-600 bg-gray-100 py-1 px-2 rounded border border-gray-200">
														${lead.action}
													</div>
												</td>
												<td class="px-6 py-4 text-right">
													<button onclick="viewLeadDetail(${lead.id})" class="text-xs font-bold text-teal-600 hover:text-teal-800 flex items-center justify-end gap-1 ml-auto cursor-pointer">
														<i data-lucide="eye" class="w-3 h-3"></i> Xem
													</button>
												</td>
											</tr>
										`).join('') : '<tr><td colspan="4" class="px-6 py-8 text-center text-gray-400 italic">Chưa có dữ liệu</td></tr>'}
									</tbody>
								</table>
							</div>
						</div>

						<div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
							<h2 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
								<i data-lucide="filter" class="w-5 h-5 text-orange-500"></i>
								Phân tầng khách hàng
							</h2>
							<div class="h-[250px]">
								<canvas id="riskPieChart"></canvas>
							</div>
						</div>
					</div>
				</div>

				<!-- TAB 2: BEHAVIOR -->
				<div id="tab-behavior" class="tab-content hidden space-y-6">
					<!-- KPI Cards -->
					<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
						<div class="bg-gradient-to-br from-blue-50 to-blue-100 p-5 rounded-xl border border-blue-200 shadow-sm">
							<div class="flex justify-between items-start mb-2">
								<div>
									<p class="text-xs font-bold text-blue-600 uppercase tracking-wide">Tổng lượt truy cập</p>
									<h3 class="text-2xl font-black text-blue-900 mt-1">${dashboardData.behaviorStats?.overall_metrics?.total_sessions || 0}</h3>
								</div>
								<div class="p-2.5 rounded-lg bg-blue-200 text-blue-700">
									<i data-lucide="users" class="w-5 h-5"></i>
								</div>
							</div>
							<p class="text-xs text-blue-700 font-medium">7 ngày qua</p>
						</div>
						
						<div class="bg-gradient-to-br from-teal-50 to-teal-100 p-5 rounded-xl border border-teal-200 shadow-sm">
							<div class="flex justify-between items-start mb-2">
								<div>
									<p class="text-xs font-bold text-teal-600 uppercase tracking-wide">Tỷ lệ hoàn thành</p>
									<h3 class="text-2xl font-black text-teal-900 mt-1">${dashboardData.behaviorStats?.overall_metrics?.completion_rate?.toFixed(1) || 0}%</h3>
								</div>
								<div class="p-2.5 rounded-lg bg-teal-200 text-teal-700">
									<i data-lucide="check-circle" class="w-5 h-5"></i>
								</div>
							</div>
							<p class="text-xs text-teal-700 font-medium">Hoàn thành G3</p>
						</div>
						
						<div class="bg-gradient-to-br from-purple-50 to-purple-100 p-5 rounded-xl border border-purple-200 shadow-sm">
							<div class="flex justify-between items-start mb-2">
								<div>
									<p class="text-xs font-bold text-purple-600 uppercase tracking-wide">Tỷ lệ chuyển đổi</p>
									<h3 class="text-2xl font-black text-purple-900 mt-1">${dashboardData.behaviorStats?.overall_metrics?.conversion_rate?.toFixed(1) || 0}%</h3>
								</div>
								<div class="p-2.5 rounded-lg bg-purple-200 text-purple-700">
									<i data-lucide="target" class="w-5 h-5"></i>
								</div>
							</div>
							<p class="text-xs text-purple-700 font-medium">Lưu/Gọi điện</p>
						</div>
						
						<div class="bg-gradient-to-br from-orange-50 to-orange-100 p-5 rounded-xl border border-orange-200 shadow-sm">
							<div class="flex justify-between items-start mb-2">
								<div>
									<p class="text-xs font-bold text-orange-600 uppercase tracking-wide">Thời gian TB</p>
									<h3 class="text-2xl font-black text-orange-900 mt-1">${dashboardData.behaviorStats?.overall_metrics?.avg_total_time ? Math.floor(dashboardData.behaviorStats.overall_metrics.avg_total_time / 60) + ' phút' : '0 phút'}</h3>
								</div>
								<div class="p-2.5 rounded-lg bg-orange-200 text-orange-700">
									<i data-lucide="clock" class="w-5 h-5"></i>
								</div>
							</div>
							<p class="text-xs text-orange-700 font-medium">Hoàn thành checklist</p>
						</div>
					</div>
					
					<!-- User Flow Funnel Chart -->
					<div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
						<div class="flex justify-between items-center mb-4">
							<h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
								<i data-lucide="filter" class="w-5 h-5 text-indigo-500"></i>
								User Flow Funnel
							</h2>
							<span class="text-xs text-gray-500 font-medium">7 ngày qua</span>
						</div>
						<div class="h-[350px]">
							<canvas id="funnelChart"></canvas>
						</div>
					</div>
					
					<!-- Funnel Table -->
					<div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
						<h2 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
							<i data-lucide="table" class="w-5 h-5 text-indigo-500"></i>
							Bảng chi tiết Funnel
						</h2>
						<div class="overflow-x-auto">
							<table class="w-full text-sm">
								<thead class="bg-gray-50 border-b border-gray-200">
									<tr>
										<th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wide">Bước</th>
										<th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wide">Số người dùng</th>
										<th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wide">Tỷ lệ chuyển đổi</th>
										<th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wide">Tỷ lệ bỏ dở</th>
										<th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wide">% so với bước trước</th>
										<th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wide">Visual</th>
									</tr>
								</thead>
								<tbody class="divide-y divide-gray-100">
									${dashboardData.behaviorStats && dashboardData.behaviorStats.funnel_data ? dashboardData.behaviorStats.funnel_data.map((step, idx) => {
										const maxUsers = dashboardData.behaviorStats.funnel_data[0].users || 1;
										const widthPercent = (step.users / maxUsers) * 100;
										return `
											<tr class="hover:bg-gray-50 transition-colors">
												<td class="px-4 py-3">
													<div class="flex items-center gap-2">
														<div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">${idx + 1}</div>
														<span class="font-bold text-gray-800">${step.step}</span>
													</div>
												</td>
												<td class="px-4 py-3 text-center">
													<span class="text-lg font-black text-gray-900">${step.users}</span>
													<span class="text-xs text-gray-500 ml-1">người</span>
												</td>
												<td class="px-4 py-3 text-center">
													<span class="px-3 py-1 rounded-full text-xs font-bold ${
														step.conversion >= 80 ? 'bg-green-100 text-green-700' :
														step.conversion >= 50 ? 'bg-yellow-100 text-yellow-700' :
														'bg-red-100 text-red-700'
													}">${step.conversion.toFixed(1)}%</span>
												</td>
												<td class="px-4 py-3 text-center">
													<span class="px-3 py-1 rounded-full text-xs font-bold ${
														step.dropoff === 0 ? 'bg-green-100 text-green-700' :
														step.dropoff <= 10 ? 'bg-yellow-100 text-yellow-700' :
														'bg-red-100 text-red-700'
													}">${step.dropoff > 0 ? step.dropoff.toFixed(1) + '%' : '-'}</span>
												</td>
												<td class="px-4 py-3 text-center">
													<span class="text-sm font-bold text-gray-700">${step.percentage.toFixed(1)}%</span>
												</td>
												<td class="px-4 py-3">
													<div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
														<div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all" style="width: ${widthPercent}%"></div>
													</div>
												</td>
											</tr>
										`;
									}).join('') : '<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 italic">Chưa có dữ liệu</td></tr>'}
								</tbody>
							</table>
						</div>
					</div>
					
					<!-- Section Performance Cards -->
					<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
						${dashboardData.behaviorStats && dashboardData.behaviorStats.section_performance ? dashboardData.behaviorStats.section_performance.map((section, idx) => {
							const colors = [
								{ bg: 'from-red-50 to-red-100', border: 'border-red-200', text: 'text-red-900', icon: 'text-red-600', badge: 'bg-red-100 text-red-700' },
								{ bg: 'from-yellow-50 to-yellow-100', border: 'border-yellow-200', text: 'text-yellow-900', icon: 'text-yellow-600', badge: 'bg-yellow-100 text-yellow-700' },
								{ bg: 'from-teal-50 to-teal-100', border: 'border-teal-200', text: 'text-teal-900', icon: 'text-teal-600', badge: 'bg-teal-100 text-teal-700' }
							];
							const color = colors[idx] || colors[0];
							return `
								<div class="bg-gradient-to-br ${color.bg} p-5 rounded-xl border ${color.border} shadow-sm hover:shadow-md transition-all">
									<div class="flex justify-between items-start mb-4">
										<div>
											<h3 class="text-sm font-black ${color.text} mb-2">${section.name}</h3>
											<span class="px-2.5 py-1 rounded-full text-xs font-bold ${color.badge}">${section.status}</span>
										</div>
										<div class="p-2 rounded-lg bg-white/50 ${color.icon}">
											<i data-lucide="activity" class="w-5 h-5"></i>
										</div>
									</div>
									
									<div class="space-y-3">
										<div class="bg-white/60 rounded-lg p-3">
											<p class="text-xs text-gray-600 font-bold mb-1">Thời gian trung bình</p>
											<p class="text-lg font-black ${color.text}">${section.avgTime > 0 ? Math.floor(section.avgTime / 60) + ' phút ' + (section.avgTime % 60) + ' giây' : 'Chưa có'}</p>
										</div>
										
										<div class="grid grid-cols-2 gap-2">
											<div class="bg-white/60 rounded-lg p-2">
												<p class="text-[10px] text-gray-600 font-bold">Hoàn thành</p>
												<p class="text-sm font-black ${color.text}">${section.completed || 0}</p>
											</div>
											<div class="bg-white/60 rounded-lg p-2">
												<p class="text-[10px] text-gray-600 font-bold">Bỏ qua</p>
												<p class="text-sm font-black ${color.text}">${section.skipped || 0}</p>
											</div>
										</div>
										
										<div class="bg-white/60 rounded-lg p-2">
											<div class="flex justify-between items-center mb-1">
												<p class="text-[10px] text-gray-600 font-bold">Tỷ lệ bỏ dở</p>
												<p class="text-xs font-black ${section.quitRate > 15 ? 'text-red-600' : 'text-gray-700'}">${section.quitRate}%</p>
											</div>
											<div class="w-full bg-gray-200 rounded-full h-2">
												<div class="bg-gradient-to-r ${color.bg.replace('from-', 'from-').replace('to-', 'to-')} h-2 rounded-full transition-all" style="width: ${100 - section.quitRate}%"></div>
											</div>
										</div>
										
										${section.minTime > 0 ? `
											<div class="text-[10px] text-gray-600 space-y-1">
												<div class="flex justify-between">
													<span>Nhanh nhất:</span>
													<span class="font-bold">${Math.floor(section.minTime / 60)}:${String(section.minTime % 60).padStart(2, '0')}</span>
												</div>
												<div class="flex justify-between">
													<span>Chậm nhất:</span>
													<span class="font-bold">${Math.floor(section.maxTime / 60)}:${String(section.maxTime % 60).padStart(2, '0')}</span>
												</div>
											</div>
										` : ''}
									</div>
								</div>
							`;
						}).join('') : '<div class="col-span-3 text-center py-8 text-gray-400 italic">Chưa có dữ liệu tracking</div>'}
					</div>
					
					<!-- Section Performance Table -->
					<div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
						<h2 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
							<i data-lucide="activity" class="w-5 h-5 text-teal-500"></i>
							Bảng so sánh hiệu suất nhóm
						</h2>
						<div class="overflow-x-auto">
							<table class="w-full text-sm">
								<thead class="bg-gray-50 border-b border-gray-200">
									<tr>
										<th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wide">Nhóm</th>
										<th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wide">Hoàn thành</th>
										<th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wide">Bỏ qua</th>
										<th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wide">Thời gian TB</th>
										<th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wide">Nhanh nhất</th>
										<th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wide">Chậm nhất</th>
										<th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wide">Tỷ lệ bỏ dở</th>
										<th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wide">Trạng thái</th>
									</tr>
								</thead>
								<tbody class="divide-y divide-gray-100">
									${dashboardData.behaviorStats && dashboardData.behaviorStats.section_performance ? dashboardData.behaviorStats.section_performance.map(section => `
										<tr class="hover:bg-gray-50 transition-colors">
											<td class="px-4 py-3">
												<span class="font-bold text-gray-800">${section.name}</span>
											</td>
											<td class="px-4 py-3 text-center">
												<span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold">${section.completed || 0}</span>
											</td>
											<td class="px-4 py-3 text-center">
												<span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-bold">${section.skipped || 0}</span>
											</td>
											<td class="px-4 py-3 text-center">
												<span class="font-bold text-gray-800">${section.avgTime > 0 ? Math.floor(section.avgTime / 60) + ':' + String(section.avgTime % 60).padStart(2, '0') : '-'}</span>
											</td>
											<td class="px-4 py-3 text-center">
												<span class="text-xs text-gray-600">${section.minTime > 0 ? Math.floor(section.minTime / 60) + ':' + String(section.minTime % 60).padStart(2, '0') : '-'}</span>
											</td>
											<td class="px-4 py-3 text-center">
												<span class="text-xs text-gray-600">${section.maxTime > 0 ? Math.floor(section.maxTime / 60) + ':' + String(section.maxTime % 60).padStart(2, '0') : '-'}</span>
											</td>
											<td class="px-4 py-3 text-center">
												<span class="px-2 py-1 rounded text-xs font-bold ${
													section.quitRate > 20 ? 'bg-red-100 text-red-700' :
													section.quitRate > 10 ? 'bg-yellow-100 text-yellow-700' :
													'bg-green-100 text-green-700'
												}">${section.quitRate}%</span>
											</td>
											<td class="px-4 py-3 text-center">
												<span class="px-2 py-1 rounded-full text-xs font-bold ${
													section.status === 'Cảnh báo' ? 'bg-red-100 text-red-700' :
													section.status === 'Quan trọng' ? 'bg-yellow-100 text-yellow-700' :
													'bg-green-100 text-green-700'
												}">${section.status}</span>
											</td>
										</tr>
									`).join('') : '<tr><td colspan="8" class="px-4 py-8 text-center text-gray-400 italic">Chưa có dữ liệu</td></tr>'}
								</tbody>
							</table>
						</div>
					</div>
					
					<!-- Drop-off Analysis -->
					<div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
						<h2 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
							<i data-lucide="alert-triangle" class="w-5 h-5 text-orange-500"></i>
							Phân tích điểm bỏ dở chi tiết
						</h2>
						<div id="dropoff-analysis" class="space-y-3">
							${dashboardData.behaviorStats && dashboardData.behaviorStats.funnel_data ? dashboardData.behaviorStats.funnel_data.map((step, idx) => {
								if (idx === 0) return '';
								const prevStep = dashboardData.behaviorStats.funnel_data[idx - 1];
								const dropoff = step.dropoff || 0;
								const lostUsers = prevStep.users - step.users;
								const dropoffPercent = dropoff;
								return `
									<div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-all ${
										dropoff > 20 ? 'bg-red-50 border-red-200' :
										dropoff > 10 ? 'bg-yellow-50 border-yellow-200' :
										'bg-green-50 border-green-200'
									}">
										<div class="flex items-start justify-between mb-3">
											<div class="flex items-start gap-3 flex-1">
												<div class="w-10 h-10 rounded-full bg-white border-2 ${
													dropoff > 20 ? 'border-red-300 text-red-700' :
													dropoff > 10 ? 'border-yellow-300 text-yellow-700' :
													'border-green-300 text-green-700'
												} flex items-center justify-center text-xs font-bold shrink-0">${idx}</div>
												<div class="flex-1">
													<p class="text-sm font-bold text-gray-800 mb-1">${prevStep.step} → ${step.step}</p>
													<div class="flex items-center gap-4 text-xs text-gray-600">
														<span><strong>${prevStep.users}</strong> người → <strong>${step.users}</strong> người</span>
														${lostUsers > 0 ? `<span class="text-red-600 font-bold">-${lostUsers} người</span>` : ''}
													</div>
												</div>
											</div>
											<div class="text-right shrink-0">
												<p class="text-2xl font-black ${
													dropoff > 20 ? 'text-red-600' :
													dropoff > 10 ? 'text-yellow-600' :
													'text-green-600'
												}">${dropoff}%</p>
												<p class="text-xs text-gray-500 font-medium">Bỏ dở</p>
											</div>
										</div>
										<div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
											<div class="h-full rounded-full transition-all ${
												dropoff > 20 ? 'bg-gradient-to-r from-red-500 to-red-600' :
												dropoff > 10 ? 'bg-gradient-to-r from-yellow-500 to-yellow-600' :
												'bg-gradient-to-r from-green-500 to-green-600'
											}" style="width: ${dropoffPercent}%"></div>
										</div>
										${dropoff > 15 ? `
											<div class="mt-2 flex items-center gap-2 text-xs text-red-700">
												<i data-lucide="alert-circle" class="w-3 h-3"></i>
												<span class="font-bold">Cần cải thiện UX ở bước này</span>
											</div>
										` : ''}
									</div>
								`;
							}).join('') : '<p class="text-sm text-gray-400 italic text-center py-4">Chưa có dữ liệu</p>'}
						</div>
					</div>
				</div>

				<!-- TAB 3: MEDICAL -->
				<div id="tab-medical" class="tab-content hidden space-y-6">
					<div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
						<h2 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
							<i data-lucide="trending-up" class="w-5 h-5 text-indigo-500"></i>
							Xu hướng Sức khỏe
						</h2>
						<div class="h-[300px]">
							<canvas id="trendChart"></canvas>
						</div>
					</div>
				</div>
			</div>
			
			<!-- Modal Chi tiết Lead -->
			<div id="leadDetailModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" onclick="if(event.target === this) closeLeadDetail()">
				<div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-slide-up" onclick="event.stopPropagation()">
					<div id="modalHeader" class="p-6 text-white">
						<div class="flex justify-between items-start mb-4">
							<div>
								<h3 id="modalTitle" class="text-xl font-bold flex items-center gap-2"></h3>
								<div class="flex items-center gap-4 mt-1 text-white/90 text-sm">
									<span id="modalPhone" class="flex items-center gap-1"></span>
								</div>
							</div>
							<button onclick="closeLeadDetail()" class="bg-white/20 hover:bg-white/30 p-1.5 rounded-full transition-colors">
								<i data-lucide="x" class="w-5 h-5"></i>
							</button>
						</div>
						<div class="flex gap-2">
							<span id="modalScore" class="px-3 py-1 bg-white/20 rounded-full text-xs font-bold backdrop-blur-md border border-white/30 uppercase"></span>
							<span id="modalAction" class="px-3 py-1 bg-white/20 rounded-full text-xs font-bold backdrop-blur-md border border-white/30"></span>
						</div>
					</div>
					
					<div class="p-6 overflow-y-auto space-y-6 bg-slate-50/50" id="modalBody">
						<div class="text-center">
							<div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-teal-600"></div>
							<p class="text-sm text-gray-500 mt-2">Đang tải dữ liệu...</p>
						</div>
					</div>
					
					<div class="p-4 bg-white border-t border-gray-200 flex justify-end gap-3" id="modalFooter">
						<button onclick="closeLeadDetail()" class="px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm font-bold text-gray-600 hover:bg-gray-100 transition-all">
							Đóng
						</button>
						<a id="modalCallBtn" href="#" class="px-5 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white rounded-lg text-sm font-bold shadow-md flex items-center gap-2 transition-all transform active:scale-95">
							<i data-lucide="phone" class="w-4 h-4"></i> Gọi Tư Vấn
						</a>
					</div>
				</div>
			</div>
		`;
		
		// Khởi tạo Lucide icons
		if (window.lucide) {
			window.lucide.createIcons();
		}
		
		// Vẽ biểu đồ Pie
		const pieCtx = document.getElementById('riskPieChart');
		if (pieCtx) {
			new Chart(pieCtx, {
				type: 'pie',
				data: {
					labels: dashboardData.riskDistribution.map(d => d.name),
					datasets: [{
						data: dashboardData.riskDistribution.map(d => d.value),
						backgroundColor: dashboardData.riskDistribution.map(d => d.color),
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							position: 'bottom'
						}
					}
				}
			});
		}
		
		// Vẽ biểu đồ Funnel với dữ liệu thực
		const funnelCtx = document.getElementById('funnelChart');
		if (funnelCtx && dashboardData.funnelData && dashboardData.funnelData.labels) {
			const funnelLabels = dashboardData.funnelData.labels;
			const funnelValues = dashboardData.funnelData.values;
			
			new Chart(funnelCtx, {
				type: 'bar',
				data: {
					labels: funnelLabels,
					datasets: [{
						label: 'Số lượng người dùng',
						data: funnelValues,
						backgroundColor: funnelValues.map((val, idx) => {
							if (idx === 0) return 'rgba(59, 130, 246, 0.8)';
							if (idx === funnelValues.length - 1) return 'rgba(20, 184, 166, 0.8)';
							return 'rgba(139, 92, 246, 0.6)';
						}),
						borderColor: funnelValues.map((val, idx) => {
							if (idx === 0) return 'rgba(59, 130, 246, 1)';
							if (idx === funnelValues.length - 1) return 'rgba(20, 184, 166, 1)';
							return 'rgba(139, 92, 246, 1)';
						}),
						borderWidth: 2,
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							display: false
						},
						tooltip: {
							callbacks: {
								afterLabel: function(context) {
									if (dashboardData.behaviorStats && dashboardData.behaviorStats.funnel_data) {
										const stepData = dashboardData.behaviorStats.funnel_data[context.dataIndex];
										if (stepData && stepData.dropoff > 0) {
											return 'Tỷ lệ bỏ dở: ' + stepData.dropoff + '%';
										}
									}
									return '';
								}
							}
						}
					},
					scales: {
						y: {
							beginAtZero: true,
							ticks: {
								stepSize: 1
							}
						}
					}
				}
			});
		}
		
		// Vẽ biểu đồ Trend
		const trendCtx = document.getElementById('trendChart');
		if (trendCtx) {
			new Chart(trendCtx, {
				type: 'line',
				data: {
					labels: ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'],
					datasets: [{
						label: 'Mức Nặng',
						data: [35, 42, 38, 50, 48, 65, 60],
						borderColor: '#991b1b',
						backgroundColor: 'rgba(153, 27, 27, 0.1)',
						fill: true
					}, {
						label: 'Mức TB',
						data: [60, 55, 65, 70, 68, 85, 80],
						borderColor: '#ca8a04',
						backgroundColor: 'rgba(202, 138, 4, 0.1)',
						fill: true
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							position: 'top'
						}
					}
				}
			});
		}
		
		console.log('Dashboard đã được khởi tạo!');
	}
	
	// Function để switch tab
	window.switchTab = function(tabName) {
		// Ẩn tất cả tabs
		document.querySelectorAll('.tab-content').forEach(tab => {
			tab.classList.add('hidden');
		});
		
		// Hiện tab được chọn
		const selectedTab = document.getElementById('tab-' + tabName);
		if (selectedTab) {
			selectedTab.classList.remove('hidden');
		}
		
		// Update button states
		document.querySelectorAll('.tab-btn').forEach(btn => {
			btn.classList.remove('active', 'bg-white', 'text-teal-700', 'shadow-sm');
			btn.classList.add('text-gray-500');
		});
		
		event.target.classList.add('active', 'bg-white', 'text-teal-700', 'shadow-sm');
		event.target.classList.remove('text-gray-500');
	};
	
	// Function để xem chi tiết lead
	window.viewLeadDetail = async function(leadId) {
		const modal = document.getElementById('leadDetailModal');
		const modalBody = document.getElementById('modalBody');
		const modalTitle = document.getElementById('modalTitle');
		const modalPhone = document.getElementById('modalPhone');
		const modalScore = document.getElementById('modalScore');
		const modalAction = document.getElementById('modalAction');
		const modalHeader = document.getElementById('modalHeader');
		const modalCallBtn = document.getElementById('modalCallBtn');
		
		// Hiện modal
		modal.classList.remove('hidden');
		modal.classList.add('flex');
		
		// Hiện loading
		modalBody.innerHTML = `
			<div class="text-center">
				<div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-teal-600"></div>
				<p class="text-sm text-gray-500 mt-2">Đang tải dữ liệu...</p>
			</div>
		`;
		
		try {
			// Lấy chi tiết từ API
			const response = await fetch('<?php echo esc_url( rest_url( 'autismtools/v1/checklist/detail/' ) ); ?>' + leadId, {
				headers: {
					'X-WP-Nonce': '<?php echo wp_create_nonce( 'wp_rest' ); ?>'
				}
			});
			
			if (!response.ok) {
				throw new Error('Không thể tải dữ liệu');
			}
			
			const detail = await response.json();
			
			// Xác định màu sắc theo mức độ
			const getRiskColor = (score) => {
				if (score === 'Nặng' || score === 'severe') return 'bg-red-600';
				if (score === 'Trung Bình' || score === 'moderate') return 'bg-yellow-600';
				return 'bg-green-600';
			};
			
			const riskColor = getRiskColor(detail.score);
			modalHeader.className = 'p-6 text-white ' + riskColor;
			
			// Cập nhật header
			modalTitle.innerHTML = detail.parent + ' <span class="font-normal text-white/80 text-sm">(' + (detail.child || 'Chưa có tên bé') + ')</span>';
			modalPhone.innerHTML = '<i data-lucide="phone" class="w-3 h-3"></i> ' + detail.phone;
			modalScore.textContent = 'Nguy cơ: ' + detail.score;
			modalAction.textContent = 'Hành động: ' + detail.action;
			modalCallBtn.href = 'tel:' + detail.phone;
			
			// Cập nhật body
			const groupedResults = detail.grouped_results || {};
			const frequent = groupedResults.frequent || [];
			const sometimes = groupedResults.sometimes || [];
			const none = groupedResults.none || [];
			const aiSummary = detail.ai_summary || 'Chưa có đánh giá từ hệ thống.';
			
			// Tạo HTML cho từng nhóm
			let symptomsHTML = '';
			
			// Nhóm Thường xuyên (Cần chú ý)
			if (frequent.length > 0) {
				symptomsHTML += `
					<div class="bg-red-50 rounded-xl border border-red-200 p-4 shadow-sm mb-3">
						<div class="flex items-center gap-2 mb-3">
							<span class="flex h-3 w-3 relative">
								<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
								<span class="relative inline-flex rounded-full h-3 w-3 bg-red-600"></span>
							</span>
							<span class="text-xs font-black text-red-800 uppercase tracking-wide">THƯỜNG XUYÊN (Cần chú ý)</span>
						</div>
						<ul class="space-y-2">
							${frequent.map(item => `
								<li class="text-sm text-red-900 font-bold leading-relaxed flex items-start gap-2">
									<span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-red-400 shrink-0"></span>
									${item.q || item}
								</li>
							`).join('')}
						</ul>
					</div>
				`;
			}
			
			// Nhóm Thỉnh thoảng
			if (sometimes.length > 0) {
				symptomsHTML += `
					<div class="bg-yellow-50 rounded-xl border border-yellow-200 p-4 shadow-sm mb-3">
						<div class="flex items-center gap-2 mb-3">
							<span class="w-3 h-3 rounded-full bg-yellow-500"></span>
							<span class="text-xs font-black text-yellow-800 uppercase tracking-wide">THỈNH THOẢNG</span>
						</div>
						<ul class="space-y-2">
							${sometimes.map(item => `
								<li class="text-sm text-yellow-900 font-medium leading-relaxed flex items-start gap-2">
									<span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-yellow-400 shrink-0"></span>
									${item.q || item}
								</li>
							`).join('')}
						</ul>
					</div>
				`;
			}
			
			// Nhóm Không có dấu hiệu
			if (none.length > 0) {
				symptomsHTML += `
					<div class="bg-slate-100 rounded-xl border border-slate-200 p-3 opacity-70">
						<div class="flex items-center gap-2">
							<span class="w-2 h-2 rounded-full bg-slate-400"></span>
							<span class="text-xs font-bold text-slate-500 uppercase">Không có dấu hiệu (${none.length} mục)</span>
						</div>
					</div>
				`;
			}
			
			// Nếu không có dữ liệu grouped_results, fallback về symptoms cũ
			if (!symptomsHTML && detail.symptoms && detail.symptoms.length > 0) {
				symptomsHTML = `
					<div class="bg-white rounded-xl p-4 border border-gray-200">
						<ul class="space-y-2">
							${detail.symptoms.map(sym => `
								<li class="flex items-start gap-2 text-sm text-gray-700">
									<span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-red-500 shrink-0"></span>
									${sym}
								</li>
							`).join('')}
						</ul>
					</div>
				`;
			}
			
			if (!symptomsHTML) {
				symptomsHTML = '<p class="text-sm text-gray-500 italic">Không có triệu chứng được ghi nhận</p>';
			}
			
			modalBody.innerHTML = `
				<!-- AI Summary -->
				<div class="bg-white p-4 rounded-xl border border-indigo-100 shadow-sm">
					<h4 class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-2 flex items-center gap-2">
						<i data-lucide="brain-circuit" class="w-3 h-3"></i> Đánh giá sơ bộ
					</h4>
					<p class="text-sm text-slate-700 font-medium italic">"${aiSummary}"</p>
				</div>
				
				<!-- Triệu chứng phân nhóm -->
				<div>
					<h4 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-3 flex items-center gap-2">
						<i data-lucide="activity" class="w-4 h-4 text-red-500"></i> Kết quả ghi nhận
					</h4>
					<div class="space-y-3">
						${symptomsHTML}
					</div>
				</div>
				
				<!-- Thông tin bổ sung -->
				<div class="text-xs text-center text-gray-400 pt-2">
					Đã lưu lúc ${detail.date_full || detail.date}
				</div>
			`;
			
			// Khởi tạo lại icons
			if (window.lucide) {
				window.lucide.createIcons();
			}
			
		} catch (error) {
			console.error('Lỗi khi tải chi tiết:', error);
			modalBody.innerHTML = `
				<div class="text-center p-6">
					<p class="text-red-600 font-bold mb-2">Lỗi khi tải dữ liệu</p>
					<p class="text-sm text-gray-500">${error.message}</p>
				</div>
			`;
		}
	};
	
	// Function để đóng modal
	window.closeLeadDetail = function() {
		const modal = document.getElementById('leadDetailModal');
		modal.classList.add('hidden');
		modal.classList.remove('flex');
	};
	
	// Bắt đầu khởi tạo
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initDashboard);
	} else {
		initDashboard();
	}
})();
</script>


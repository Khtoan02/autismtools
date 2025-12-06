import React, { useState, useMemo } from 'react';
import { 
  BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, 
  PieChart, Pie, Cell, AreaChart, Area, LineChart, Line, ComposedChart, Scatter, Radar, RadarChart, PolarGrid, PolarAngleAxis, PolarRadiusAxis
} from 'recharts';
import { 
  LayoutDashboard, Activity, ClipboardCheck, UserCheck, AlertTriangle, 
  FileText, Download, Users, Filter, RefreshCw, Calendar, ChevronRight,
  Clock, MousePointerClick, Eye, BrainCircuit, Stethoscope, ArrowRight,
  Search, X, Phone, User, MapPin, Baby, TrendingUp, Link as LinkIcon
} from 'lucide-react';

// --- MOCK DATA ĐỒNG BỘ VỚI CHECKLIST MỚI ---

// 1. Phân loại mức độ nguy cơ (Theo logic mới: Nhẹ / TB / Nặng)
const riskDistributionData = [
  { name: 'Mức độ NẶNG', value: 280, color: '#991b1b', desc: 'Ảnh hưởng Trục Não - Ruột' },
  { name: 'Mức TRUNG BÌNH', value: 520, color: '#ca8a04', desc: 'Hấp thu kém & Năng lượng giảm' },
  { name: 'Mức độ NHẸ', value: 350, color: '#166534', desc: 'Rối loạn chưa ảnh hưởng nhiều' },
];

// 2. Phễu hành vi (User Flow) - Cập nhật 3 nhóm
const detailedFunnelData = [
  { step: 'Vào trang', users: 3800, dropoff: 0, time: '0s' },
  { step: 'Bắt đầu', users: 3100, dropoff: 18, time: '10s' },
  { step: 'Xong G1 (Tiêu hoá)', users: 2600, dropoff: 16, time: '40s' },
  { step: 'Xong G2 (Hấp thu)', users: 2200, dropoff: 15, time: '1m10s' }, 
  { step: 'Xong G3 (Vi sinh)', users: 1850, dropoff: 16, time: '1m50s' },
  { step: 'Xem Kết quả', users: 1800, dropoff: 3, time: '2m00s' },
  { step: 'Lưu Kết quả (SĐT)', users: 620, dropoff: 65, time: '3m15s' }, // Tỷ lệ này thường thấp do yêu cầu nhập SĐT
];

// 3. Hiệu suất từng nhóm (Section Performance)
const sectionPerformanceData = [
  { name: 'G1. Tiêu hoá & Đi ngoài', avgTime: 35, quitRate: 5, yesRate: 72, status: 'Quan trọng' },
  { name: 'G2. Hấp thu & Ăn uống', avgTime: 45, quitRate: 12, yesRate: 55, status: 'Cảnh báo' }, // Thường user suy nghĩ lâu ở phần này
  { name: 'G3. Vi sinh & Hành vi', avgTime: 40, quitRate: 8, yesRate: 48, status: 'Ổn' },
];

// 4. Heatmap Triệu chứng (Câu hỏi thực tế từ code mới)
const questionHeatmapData = [
  { question: 'Phân cứng/vón cục (G1)', count: 920, category: 'Tiêu hoá' },
  { question: 'Bé biếng ăn/Khó chịu (G2)', count: 850, category: 'Ăn uống' },
  { question: 'Xì hơi nhiều, đầy bụng (G3)', count: 780, category: 'Vi sinh' },
  { question: 'Bé ngủ kém/Thức đêm (G3)', count: 650, category: 'Giấc ngủ' },
  { question: 'Tăng cân chậm/Đứng cân (G2)', count: 610, category: 'Hấp thu' },
  { question: 'Đi ngoài không đều (G1)', count: 540, category: 'Tiêu hoá' },
];

// 5. Tương tác nội dung (Bài viết đề xuất theo Level)
const contentEngagementData = [
  { name: 'Bài: Chế độ GFCF (Mức Nặng)', views: 600, clicks: 180 }, // CTR cao do phụ huynh lo lắng
  { name: 'Bài: Chiến lược phục hồi (Mức TB)', views: 900, clicks: 150 },
  { name: 'Bài: Dinh dưỡng cơ bản (Mức Nhẹ)', views: 500, clicks: 40 },
];

// 6. Dữ liệu Xu hướng
const riskTrendData = [
  { date: 'T2', severe: 35, moderate: 60, mild: 40 },
  { date: 'T3', severe: 42, moderate: 55, mild: 38 },
  { date: 'T4', severe: 38, moderate: 65, mild: 45 },
  { date: 'T5', severe: 50, moderate: 70, mild: 42 },
  { date: 'T6', severe: 48, moderate: 68, mild: 40 },
  { date: 'T7', severe: 65, moderate: 85, mild: 55 }, 
  { date: 'CN', severe: 60, moderate: 80, mild: 50 },
];

// 7. Combo triệu chứng (Correlation)
const comorbidityData = [
  { name: 'Táo bón + Biếng ăn', value: 75, fullMark: 100 },
  { name: 'Đầy bụng + Khó ngủ', value: 62, fullMark: 100 },
  { name: 'Phân sống + Tăng cân chậm', value: 58, fullMark: 100 },
  { name: 'Dị ứng sữa + Cáu gắt', value: 45, fullMark: 100 },
  { name: 'Táo bón + Mệt mỏi', value: 40, fullMark: 100 },
];

// 8. Leads Mới (Real-time mock)
const recentLeads = [
  { 
    id: 101, parent: 'Chị Lan Anh', phone: '098***881', child: 'Bé Ken', score: 'Nặng', date: '10:45 AM', action: 'Lưu Ảnh',
    details: {
      symptoms: ['Đi ngoài không đều', 'Phân cứng/vón cục', 'Bé nhạy cảm/cáu gắt', 'Bé ngủ kém'],
      ai_summary: 'Bé có dấu hiệu táo bón mãn tính ảnh hưởng đến giấc ngủ và hành vi (Trục não ruột).',
      notes: 'Đã dùng men vi sinh 2 tháng không đỡ.'
    }
  },
  { 
    id: 102, parent: 'Mẹ Bắp', phone: '091***772', child: 'Bắp', score: 'Trung Bình', date: '10:15 AM', action: 'Gọi điện',
    details: {
      symptoms: ['Tăng cân chậm', 'Phân có thức ăn', 'Bé biếng ăn'],
      ai_summary: 'Hệ tiêu hóa hấp thu kém, cần bổ sung enzyme và vi chất.',
      notes: 'Bé 3 tuổi chỉ nặng 11kg.'
    }
  },
  { 
    id: 103, parent: 'Anh Hùng', phone: '036***993', child: 'Bé Sâu', score: 'Nhẹ', date: '09:30 AM', action: 'Lưu Ảnh',
    details: {
      symptoms: ['Thi thoảng đầy bụng'],
      ai_summary: 'Rối loạn tiêu hóa nhẹ, có thể điều chỉnh bằng chế độ ăn.',
      notes: 'Quan tâm sữa hạt.'
    }
  },
  { 
    id: 104, parent: 'Chị Mai', phone: '097***114', child: 'Bé Na', score: 'Nặng', date: 'Hôm qua', action: 'Lưu Ảnh',
    details: {
      symptoms: ['Xì hơi nhiều', 'Bé nhạy cảm quá mức', 'Phản ứng xấu với sữa bò', 'Ăn vào đau bụng'],
      ai_summary: 'Nghi ngờ bất dung nạp Lactose/Casein gây viêm ruột và ảnh hưởng hành vi.',
      notes: 'Bé tự kỷ nhẹ, đang can thiệp.'
    }
  },
  { 
    id: 105, parent: 'Mẹ Xoài', phone: '090***555', child: 'Xoài', score: 'Trung Bình', date: 'Hôm qua', action: 'Lưu Ảnh',
    details: {
      symptoms: ['Đi ngoài không đều', 'Bé ăn đủ nhưng mệt'],
      ai_summary: 'Táo bón chức năng gây tích tụ độc tố làm bé mệt mỏi.',
      notes: ''
    }
  },
];

// --- COMPONENTS ---

const KPI_Card = ({ title, value, subtext, icon: Icon, colorClass, trend }) => (
  <div className="bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all">
    <div className="flex justify-between items-start">
      <div>
        <p className="text-xs font-semibold text-gray-500 uppercase tracking-wide">{title}</p>
        <h3 className="text-2xl font-bold text-gray-800 mt-1">{value}</h3>
      </div>
      <div className={`p-2.5 rounded-lg ${colorClass} bg-opacity-10 text-opacity-100`}>
        <Icon size={20} />
      </div>
    </div>
    <div className="mt-3 flex items-center text-xs font-medium text-gray-400">
      {trend && <span className={trend > 0 ? "text-green-500 mr-1" : "text-red-500 mr-1"}>{trend > 0 ? '+' : ''}{trend}%</span>}
      {subtext}
    </div>
  </div>
);

const LeadDetailModal = ({ lead, onClose }) => {
  if (!lead) return null;
  
  const getRiskColor = (risk) => {
    if (risk === 'Nặng' || risk === 'Mức độ NẶNG') return 'bg-red-600';
    if (risk === 'Trung Bình' || risk === 'Mức TRUNG BÌNH') return 'bg-yellow-600';
    return 'bg-green-600';
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-in fade-in duration-200">
      <div className="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        
        {/* Header */}
        <div className={`p-6 text-white ${getRiskColor(lead.score)}`}>
          <div className="flex justify-between items-start mb-4">
            <div>
              <h3 className="text-xl font-bold flex items-center gap-2">
                {lead.parent} <span className="font-normal text-white/80 text-sm">({lead.child})</span>
              </h3>
              <div className="flex items-center gap-4 mt-1 text-white/90 text-sm">
                <span className="flex items-center gap-1"><Phone size={14}/> {lead.phone}</span>
              </div>
            </div>
            <button onClick={onClose} className="bg-white/20 hover:bg-white/30 p-1.5 rounded-full transition-colors">
              <X size={20} />
            </button>
          </div>
          <div className="flex gap-2">
            <span className="px-3 py-1 bg-white/20 rounded-full text-xs font-bold backdrop-blur-md border border-white/30 uppercase">
              Nguy cơ: {lead.score}
            </span>
            <span className="px-3 py-1 bg-white/20 rounded-full text-xs font-bold backdrop-blur-md border border-white/30">
              Hành động: {lead.action}
            </span>
          </div>
        </div>

        {/* Body */}
        <div className="p-6 overflow-y-auto space-y-6 bg-slate-50/50">
          
          {/* AI Summary */}
          <div className="bg-white p-4 rounded-xl border border-indigo-100 shadow-sm">
             <h4 className="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-2 flex items-center gap-2">
               <BrainCircuit size={14}/> Đánh giá sơ bộ
             </h4>
             <p className="text-sm text-slate-700 font-medium italic">"{lead.details?.ai_summary}"</p>
          </div>

          {/* Section: Vấn đề chính */}
          <div>
            <h4 className="text-sm font-bold text-gray-800 uppercase tracking-wider mb-3 flex items-center gap-2">
              <Activity size={16} className="text-red-500"/> Triệu chứng ghi nhận
            </h4>
            <div className="bg-white rounded-xl p-4 border border-gray-200">
              <ul className="space-y-2">
                {lead.details?.symptoms?.map((sym, idx) => (
                  <li key={idx} className="flex items-start gap-2 text-sm text-gray-700">
                    <span className="mt-1.5 w-1.5 h-1.5 rounded-full bg-red-500 shrink-0"></span>
                    {sym}
                  </li>
                )) || <li className="text-sm text-gray-500 italic">Không có dữ liệu chi tiết</li>}
              </ul>
            </div>
          </div>

          {/* Section: Ghi chú */}
          {lead.details?.notes && (
            <div>
              <h4 className="text-sm font-bold text-gray-800 uppercase tracking-wider mb-3 flex items-center gap-2">
                <FileText size={16} className="text-orange-500"/> Ghi chú bổ sung
              </h4>
              <div className="bg-yellow-50 rounded-xl p-4 border border-yellow-100 text-sm text-yellow-800">
                "{lead.details.notes}"
              </div>
            </div>
          )}

          <div className="text-xs text-center text-gray-400 pt-2">
            Đã lưu lúc {lead.date}
          </div>

        </div>

        {/* Footer Actions */}
        <div className="p-4 bg-white border-t border-gray-200 flex justify-end gap-3">
          <button onClick={onClose} className="px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm font-bold text-gray-600 hover:bg-gray-100 transition-all">
            Đóng
          </button>
          <a href={`tel:${lead.phone}`} className="px-5 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white rounded-lg text-sm font-bold shadow-md flex items-center gap-2 transition-all transform active:scale-95">
            <Phone size={16} /> Gọi Tư Vấn
          </a>
        </div>
      </div>
    </div>
  );
};

export default function UpdatedDashboard() {
  const [activeTab, setActiveTab] = useState('overview');
  const [timeRange, setTimeRange] = useState('7days');
  const [isLoading, setIsLoading] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedLead, setSelectedLead] = useState(null);

  const refreshData = () => {
    setIsLoading(true);
    setTimeout(() => setIsLoading(false), 800);
  };

  const filteredLeads = useMemo(() => {
    if (!searchTerm) return recentLeads;
    const lowerTerm = searchTerm.toLowerCase();
    return recentLeads.filter(lead => 
      lead.parent.toLowerCase().includes(lowerTerm) || 
      lead.child.toLowerCase().includes(lowerTerm) || 
      lead.phone.includes(lowerTerm)
    );
  }, [searchTerm]);

  return (
    <div className="min-h-screen bg-slate-50 font-sans text-slate-800 pb-12 relative">
      
      {selectedLead && (
        <LeadDetailModal lead={selectedLead} onClose={() => setSelectedLead(null)} />
      )}

      {/* Top Nav */}
      <nav className="bg-white border-b border-gray-200 sticky top-0 z-30 px-6 py-3 shadow-sm">
        <div className="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
          <div className="flex items-center gap-3">
            <div className="w-9 h-9 bg-teal-600 rounded-lg flex items-center justify-center text-white font-bold shadow-lg shadow-teal-200">
              DB
            </div>
            <div>
              <h1 className="text-lg font-bold text-gray-800 leading-tight">Analytics: Checklist Tiêu Hóa (New)</h1>
              <p className="text-[11px] text-gray-500 font-medium">Phiên bản chính thức 2024</p>
            </div>
          </div>
          
          <div className="flex items-center gap-3 self-end md:self-auto">
            <div className="flex bg-gray-100 p-1 rounded-lg mr-2">
              <button onClick={() => setActiveTab('overview')} className={`px-3 py-1.5 text-xs font-bold rounded-md transition-all ${activeTab === 'overview' ? 'bg-white text-teal-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}>Tổng quan</button>
              <button onClick={() => setActiveTab('behavior')} className={`px-3 py-1.5 text-xs font-bold rounded-md transition-all ${activeTab === 'behavior' ? 'bg-white text-teal-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}>Hành vi & UX</button>
              <button onClick={() => setActiveTab('medical')} className={`px-3 py-1.5 text-xs font-bold rounded-md transition-all ${activeTab === 'medical' ? 'bg-white text-teal-700 shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}>Y Khoa</button>
            </div>

            <select 
              value={timeRange} 
              onChange={(e) => setTimeRange(e.target.value)}
              className="bg-gray-50 border border-gray-200 text-gray-700 text-xs font-medium rounded-lg focus:ring-teal-500 focus:border-teal-500 block px-3 py-1.5"
            >
              <option value="today">Hôm nay</option>
              <option value="7days">7 ngày qua</option>
              <option value="30days">30 ngày qua</option>
            </select>
            <button onClick={refreshData} className="p-1.5 text-gray-400 hover:text-teal-600 transition-colors bg-gray-50 rounded-full hover:bg-gray-100 border border-gray-200">
              <RefreshCw size={16} className={isLoading ? "animate-spin" : ""} />
            </button>
          </div>
        </div>
      </nav>

      <div className="max-w-7xl mx-auto px-6 py-6 space-y-6">
        
        {/* TAB 1: OVERVIEW & LEADS */}
        {activeTab === 'overview' && (
          <div className="space-y-6 animate-fade-in">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <KPI_Card title="Lượt làm Checklist" value="1,850" trend={12} subtext="Lượt truy cập tăng" icon={ClipboardCheck} colorClass="bg-blue-100 text-blue-600" />
              <KPI_Card title="Tỷ lệ Hoàn thành" value="48.5%" trend={-1.2} subtext="Rớt nhiều ở bước nhập SĐT" icon={Activity} colorClass="bg-indigo-100 text-indigo-600" />
              <KPI_Card title="Leads (Lưu/Gọi)" value="620" trend={15.4} subtext="Chất lượng Lead tốt" icon={UserCheck} colorClass="bg-teal-100 text-teal-600" />
              <KPI_Card title="Khách Nguy cơ Nặng" value="28%" trend={3.0} subtext="Cần tư vấn ngay" icon={AlertTriangle} colorClass="bg-red-100 text-red-600" />
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <div className="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                <div className="p-5 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4 bg-gray-50/50">
                  <div>
                    <h2 className="text-base font-bold text-gray-800 flex items-center gap-2">
                      <UserCheck size={18} className="text-teal-600" />
                      Leads Mới Nhất
                    </h2>
                    <p className="text-xs text-gray-500 mt-0.5">Dữ liệu từ form lưu ảnh & gọi điện</p>
                  </div>
                  
                  <div className="relative w-full sm:w-64">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" size={14} />
                    <input 
                      type="text" 
                      placeholder="Tìm SĐT, Tên mẹ, Tên bé..." 
                      value={searchTerm}
                      onChange={(e) => setSearchTerm(e.target.value)}
                      className="w-full pl-9 pr-4 py-2 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                    />
                    {searchTerm && (
                      <button onClick={() => setSearchTerm('')} className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <X size={12} />
                      </button>
                    )}
                  </div>
                </div>
                
                <div className="overflow-x-auto flex-1">
                  <table className="w-full text-sm text-left">
                    <thead className="text-xs text-gray-500 uppercase bg-gray-50 sticky top-0">
                      <tr>
                        <th className="px-6 py-3 font-semibold">Phụ huynh / SĐT</th>
                        <th className="px-6 py-3 font-semibold">Bé / Nguy cơ</th>
                        <th className="px-6 py-3 font-semibold text-center">Hành động</th>
                        <th className="px-6 py-3 font-semibold text-right">Chi tiết</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                      {filteredLeads.length > 0 ? (
                        filteredLeads.map((lead) => (
                          <tr key={lead.id} onClick={() => setSelectedLead(lead)} className="hover:bg-teal-50/30 transition-colors cursor-pointer group">
                            <td className="px-6 py-4">
                              <div className="font-bold text-gray-800 group-hover:text-teal-700 transition-colors">{lead.parent}</div>
                              <div className="text-xs text-gray-500 flex items-center gap-1"><Phone size={10}/> {lead.phone}</div>
                            </td>
                            <td className="px-6 py-4">
                              <div className="text-gray-700 font-medium">{lead.child}</div>
                              <span className={`inline-flex mt-1 px-2 py-0.5 rounded text-[10px] font-bold ${
                                lead.score === 'Nặng' ? 'bg-red-100 text-red-700' : 
                                lead.score === 'Trung Bình' ? 'bg-yellow-100 text-yellow-700' : 
                                'bg-green-100 text-green-700'
                              }`}>
                                {lead.score}
                              </span>
                            </td>
                            <td className="px-6 py-4 text-center">
                               <div className="flex items-center justify-center gap-1 text-xs text-gray-600 bg-gray-100 py-1 px-2 rounded border border-gray-200">
                                  {lead.action === 'Lưu Ảnh' ? <Download size={12}/> : <Phone size={12}/>}
                                  {lead.action}
                               </div>
                            </td>
                            <td className="px-6 py-4 text-right">
                              <button className="text-xs font-bold text-teal-600 hover:text-teal-800 flex items-center justify-end gap-1 ml-auto">
                                <Eye size={12} /> Xem
                              </button>
                            </td>
                          </tr>
                        ))
                      ) : (
                        <tr>
                          <td colSpan="4" className="px-6 py-8 text-center text-gray-400 italic">
                            Không tìm thấy kết quả nào.
                          </td>
                        </tr>
                      )}
                    </tbody>
                  </table>
                </div>
              </div>

              <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h2 className="text-base font-bold text-gray-800 mb-4 flex items-center gap-2"><Filter size={18} className="text-orange-500"/> Phân tầng khách hàng</h2>
                <div className="h-[250px]">
                  <ResponsiveContainer width="100%" height="100%">
                    <PieChart>
                      <Pie data={riskDistributionData} cx="50%" cy="50%" innerRadius={60} outerRadius={80} paddingAngle={5} dataKey="value">
                        {riskDistributionData.map((entry, index) => <Cell key={`cell-${index}`} fill={entry.color} />)}
                      </Pie>
                      <Tooltip />
                      <Legend verticalAlign="bottom" height={36} />
                    </PieChart>
                  </ResponsiveContainer>
                </div>
                <div className="mt-2 text-xs text-center text-gray-500 italic">
                   Dựa trên tổng số lượt hoàn thành checklist
                </div>
              </div>
            </div>
          </div>
        )}

        {/* TAB 2: BEHAVIOR & UX */}
        {activeTab === 'behavior' && (
          <div className="space-y-6 animate-fade-in">
             <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div className="mb-6 flex justify-between items-end">
                  <div>
                    <h2 className="text-base font-bold text-gray-800 flex items-center gap-2">
                      <Filter size={18} className="text-indigo-500" /> User Flow (Theo 3 Nhóm Mới)
                    </h2>
                    <p className="text-xs text-gray-500 mt-1">Hành trình khách hàng qua 3 nhóm câu hỏi: Tiêu hoá - Hấp thu - Vi sinh.</p>
                  </div>
                </div>
                <div className="h-[300px] w-full">
                  <ResponsiveContainer width="100%" height="100%">
                    <AreaChart data={detailedFunnelData} margin={{ top: 10, right: 30, left: 0, bottom: 0 }}>
                      <defs>
                        <linearGradient id="colorUsers" x1="0" y1="0" x2="0" y2="1">
                          <stop offset="5%" stopColor="#0d9488" stopOpacity={0.8}/>
                          <stop offset="95%" stopColor="#0d9488" stopOpacity={0}/>
                        </linearGradient>
                      </defs>
                      <XAxis dataKey="step" tick={{fontSize: 11}} />
                      <YAxis tick={{fontSize: 11}} />
                      <Tooltip contentStyle={{ borderRadius: '8px' }} />
                      <CartesianGrid strokeDasharray="3 3" vertical={false} />
                      <Area type="monotone" dataKey="users" stroke="#0d9488" fillOpacity={1} fill="url(#colorUsers)" />
                    </AreaChart>
                  </ResponsiveContainer>
                </div>
             </div>
             
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                   <h2 className="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
                      <Clock size={18} className="text-orange-500" /> Hiệu suất từng nhóm (G1-G3)
                   </h2>
                   <div className="h-[300px]">
                      <ResponsiveContainer width="100%" height="100%">
                         <ComposedChart data={sectionPerformanceData} layout="vertical" margin={{ top: 0, right: 20, bottom: 0, left: 20 }}>
                            <CartesianGrid stroke="#f5f5f5" horizontal={false} />
                            <XAxis type="number" />
                            <YAxis dataKey="name" type="category" width={120} tick={{fontSize: 11}} />
                            <Tooltip />
                            <Legend />
                            <Bar dataKey="avgTime" name="Thời gian (giây)" barSize={12} fill="#f97316" radius={[0,4,4,0]} />
                            <Bar dataKey="quitRate" name="Tỷ lệ bỏ dở (%)" barSize={12} fill="#ef4444" radius={[0,4,4,0]} />
                         </ComposedChart>
                      </ResponsiveContainer>
                   </div>
                </div>

                <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                   <h2 className="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
                      <Eye size={18} className="text-blue-500" /> Tương tác Bài viết đề xuất
                   </h2>
                   <p className="text-xs text-gray-500 mb-4">Bài viết hiển thị tự động dựa trên mức độ nguy cơ của bé.</p>
                   <div className="space-y-4">
                      {contentEngagementData.map((item, idx) => {
                         const ctr = ((item.clicks / item.views) * 100).toFixed(1);
                         return (
                            <div key={idx} className="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-100">
                               <div>
                                  <p className="text-sm font-bold text-slate-700">{item.name}</p>
                                  <p className="text-xs text-slate-400">{item.views} lượt hiển thị</p>
                               </div>
                               <div className="text-right">
                                  <p className="text-sm font-bold text-indigo-600">{item.clicks} clicks</p>
                                  <p className={`text-[10px] font-bold ${ctr > 20 ? 'text-green-500' : 'text-slate-400'}`}>CTR: {ctr}%</p>
                               </div>
                            </div>
                         )
                      })}
                   </div>
                </div>
             </div>
          </div>
        )}

        {/* TAB 3: MEDICAL INSIGHTS */}
        {activeTab === 'medical' && (
          <div className="space-y-6 animate-fade-in">
             
             {/* 3.1: Risk Trends */}
             <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
               <div className="flex justify-between items-end mb-6">
                 <div>
                   <h2 className="text-base font-bold text-gray-800 flex items-center gap-2">
                     <TrendingUp size={18} className="text-indigo-500" /> Xu hướng Sức khỏe
                   </h2>
                   <p className="text-xs text-gray-500 mt-1">
                     Số lượng ca theo mức độ Nặng/TB/Nhẹ trong tuần qua.
                   </p>
                 </div>
               </div>
               <div className="h-[250px]">
                 <ResponsiveContainer width="100%" height="100%">
                   <AreaChart data={riskTrendData} margin={{ top: 10, right: 10, left: 0, bottom: 0 }}>
                     <defs>
                       <linearGradient id="colorSevere" x1="0" y1="0" x2="0" y2="1">
                         <stop offset="5%" stopColor="#991b1b" stopOpacity={0.8}/>
                         <stop offset="95%" stopColor="#991b1b" stopOpacity={0}/>
                       </linearGradient>
                       <linearGradient id="colorModerate" x1="0" y1="0" x2="0" y2="1">
                         <stop offset="5%" stopColor="#ca8a04" stopOpacity={0.8}/>
                         <stop offset="95%" stopColor="#ca8a04" stopOpacity={0}/>
                       </linearGradient>
                     </defs>
                     <XAxis dataKey="date" tick={{fontSize: 12}} />
                     <YAxis tick={{fontSize: 12}} />
                     <Tooltip contentStyle={{borderRadius: '8px'}}/>
                     <Legend />
                     <Area type="monotone" dataKey="severe" name="Mức Nặng" stroke="#991b1b" fillOpacity={1} fill="url(#colorSevere)" stackId="1" />
                     <Area type="monotone" dataKey="moderate" name="Mức TB" stroke="#ca8a04" fillOpacity={1} fill="url(#colorModerate)" stackId="1" />
                   </AreaChart>
                 </ResponsiveContainer>
               </div>
             </div>

             <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* 3.2: Symptom Heatmap */}
                <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                   <h2 className="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
                      <Stethoscope size={18} className="text-red-500" /> Top Triệu chứng phổ biến
                   </h2>
                   <p className="text-xs text-gray-500 mb-4">
                      Dựa trên số lần phụ huynh chọn "Thường xuyên" trong checklist.
                   </p>
                   <div className="h-[300px]">
                      <ResponsiveContainer width="100%" height="100%">
                         <BarChart data={questionHeatmapData} layout="vertical" margin={{ top: 0, right: 30, left: 20, bottom: 0 }}>
                            <CartesianGrid strokeDasharray="3 3" horizontal={false} />
                            <XAxis type="number" />
                            <YAxis dataKey="question" type="category" width={130} tick={{fontSize: 10}} />
                            <Tooltip contentStyle={{borderRadius: '8px'}} cursor={{fill: '#f8fafc'}} />
                            <Bar dataKey="count" name="Số ca gặp phải" fill="#ef4444" radius={[0, 4, 4, 0]} barSize={20} />
                         </BarChart>
                      </ResponsiveContainer>
                   </div>
                </div>

                {/* 3.3: Comorbidities (Combo bệnh lý) */}
                <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                   <h2 className="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
                      <LinkIcon size={18} className="text-purple-500" /> Combo Triệu chứng (Tương quan)
                   </h2>
                   <p className="text-xs text-gray-500 mb-4">
                      Tỷ lệ các vấn đề thường đi kèm với nhau (Dữ liệu từ AI phân tích).
                   </p>
                   <div className="h-[300px] flex items-center justify-center">
                      <ResponsiveContainer width="100%" height="100%">
                         <RadarChart cx="50%" cy="50%" outerRadius="80%" data={comorbidityData}>
                            <PolarGrid />
                            <PolarAngleAxis dataKey="name" tick={{fontSize: 11, fontWeight: 'bold'}} />
                            <PolarRadiusAxis angle={30} domain={[0, 100]} />
                            <Radar name="Tỷ lệ xuất hiện (%)" dataKey="value" stroke="#8b5cf6" fill="#8b5cf6" fillOpacity={0.6} />
                            <Tooltip />
                         </RadarChart>
                      </ResponsiveContainer>
                   </div>
                </div>
             </div>
             
             {/* 3.4: AI Insights Block */}
             <div className="bg-gradient-to-br from-teal-900 to-slate-900 text-white p-6 rounded-xl shadow-lg">
                <h2 className="text-base font-bold mb-4 flex items-center gap-2"><BrainCircuit size={18} className="text-yellow-400"/> AI Medical Summary</h2>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                   <div className="bg-white/10 p-4 rounded-xl border border-white/10">
                      <p className="text-xs text-teal-200 uppercase font-bold tracking-wider mb-2">Vấn đề nổi cộm</p>
                      <p className="text-sm font-medium">Nhóm G2 (Hấp thu) có tỷ lệ đáng báo động: 55% trẻ có dấu hiệu biếng ăn kèm tăng cân chậm.</p>
                   </div>
                   <div className="bg-white/10 p-4 rounded-xl border border-white/10">
                      <p className="text-xs text-teal-200 uppercase font-bold tracking-wider mb-2">Trục Não - Ruột</p>
                      <p className="text-sm font-medium">78% trẻ có vấn đề "Xì hơi nhiều" (G3) cũng gặp tình trạng "Ngủ kém/Thức đêm".</p>
                   </div>
                   <div className="bg-white/10 p-4 rounded-xl border border-white/10">
                      <p className="text-xs text-teal-200 uppercase font-bold tracking-wider mb-2">Dự báo</p>
                      <p className="text-sm font-medium">Xu hướng tìm kiếm về "Chế độ GFCF" đang tăng mạnh ở nhóm phụ huynh có con Nguy cơ Nặng.</p>
                   </div>
                </div>
             </div>

          </div>
        )}

      </div>
    </div>
  );
}
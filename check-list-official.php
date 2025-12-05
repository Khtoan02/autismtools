<?php
/**
 * Template Name: Check List tiêu hóa chính  thức
 *
 * @package AutismTools
 */
get_header();
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Checklist Tiêu Hoá (HQ Export) - DawnBridge</title>
    
    <!-- React & ReactDOM -->
    <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    
    <!-- Html2Canvas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Nunito', 'sans-serif'] },
                    colors: {
                        primary: { 50:'#eff6ff', 100:'#dbeafe', 500:'#3b82f6', 600:'#2563eb', 700:'#1d4ed8', 800:'#1e40af' },
                        teal: { 50: '#f0fdfa', 100: '#ccfbf1', 500:'#14b8a6', 600: '#0d9488', 700: '#0f766e', 800: '#115e59' },
                    },
                    boxShadow: {
                        'soft': '0 4px 20px -2px rgba(0, 0, 0, 0.05)',
                    },
                    animation: {
                        'slide-up': 'slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                        'fade-in': 'fadeIn 0.5s ease-out forwards',
                    },
                    keyframes: {
                        slideUp: { '0%': { transform: 'translateY(30px)', opacity: 0 }, '100%': { transform: 'translateY(0)', opacity: 1 } },
                        fadeIn: { '0%': { opacity: 0 }, '100%': { opacity: 1 } }
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #f1f5f9; -webkit-tap-highlight-color: transparent; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
        
        @media print {
            .no-print { display: none !important; }
            #root { display: none !important; }
            #print-container { display: block !important; }
            body { background-color: white; }
        }
        #print-container { display: none; }
    </style>
</head>
<body>

<div id="root"></div>
<div id="print-container"></div>

<script type="text/babel">
    const { useState, useMemo, useEffect, useRef } = React;
    const apiKey = "AIzaSyDnAXGfKUs77MJ4cMuMy51otSGHj4WBQB0"; 

    // --- DATA ---
    const SECTIONS = [
        { id: 'g1', title: 'TIÊU HOÁ & ĐI NGOÀI', icon: '🚽', questions: ['Bé đi ngoài không đều (2–3 ngày mới đi hoặc đi nhiều lần bất thường)', 'Phân cứng/vón cục hoặc lỏng/nát kéo dài', 'Bé ôm bụng, cong người, khó chịu trước khi đi tiêu', 'Sau khi đi ngoài, bé cáu gắt hoặc mệt lử'] },
        { id: 'g2', title: 'HẤP THU, NĂNG LƯỢNG & ĂN UỐNG', icon: '⚡', questions: ['Phân có thức ăn còn nguyên hoặc không tiêu hết', 'Bé ăn đủ nhưng vẫn mệt – uể oải – thiếu năng lượng', 'Bé tăng cân chậm hoặc đứng cân', 'Bé biếng ăn, hoặc ăn vào bé khó chịu – đau bụng'] },
        { id: 'g3', title: 'VI SINH, HÀNH VI & GIẤC NGỦ', icon: '🧠', questions: ['Bé xì hơi nhiều, đầy bụng, căng bụng', 'Bé nhạy cảm: dễ kích động, cáu gắt hoặc khó tập trung', 'Bé ngủ kém: khó ngủ – ngủ chập chờn – thức giữa đêm', 'Bé có phản ứng xấu với sữa bò (đau bụng, tiêu chảy, nổi mẩn…)'] }
    ];

    const OPTIONS = [
        { val: 0, label: 'Không', color: 'peer-checked:bg-slate-100 peer-checked:text-slate-600 peer-checked:border-slate-300' },
        { val: 1, label: 'Thỉnh thoảng', color: 'peer-checked:bg-yellow-100 peer-checked:text-yellow-800 peer-checked:border-yellow-300' },
        { val: 2, label: 'Thường xuyên', color: 'peer-checked:bg-red-100 peer-checked:text-red-800 peer-checked:border-red-300' }
    ];

    const ARTICLES = {
        mild: { title: "Dinh dưỡng cơ bản giúp bé ổn định tiêu hoá", desc: "Hướng dẫn mẹ cách chọn thực phẩm giàu chất xơ hoà tan và cân bằng nước giúp bé đi ngoài dễ dàng hơn.", link: "#" },
        moderate: { title: "Chiến lược phục hồi năng lượng và hấp thu", desc: "Thực đơn 7 ngày giúp bé tăng cân khoa học và giảm mệt mỏi nhờ các vi chất thiết yếu.", link: "#" },
        severe: { title: "Chế độ GFCF và Phục hồi đường ruột", desc: "Tìm hiểu về chế độ ăn loại bỏ Gluten/Casein để cải thiện hành vi và giấc ngủ cho trẻ nhạy cảm.", link: "#" }
    };

    const Icons = {
        Check: () => <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"/></svg>,
        Refresh: () => <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>,
        Close: () => <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"/></svg>,
        Sparkles: () => <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>,
        Edit: () => <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>,
        Phone: () => <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>,
        Camera: () => <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>,
        Book: () => <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>,
        Shield: () => <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>,
        Help: () => <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>,
        Alert: () => <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    };

    async function callGemini(prompt) {
        if (!apiKey) return null;
        try {
            const response = await fetch(
                `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=${apiKey}`,
                {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ contents: [{ parts: [{ text: prompt }] }], generationConfig: { responseMimeType: "application/json" } }),
                }
            );
            if (!response.ok) return null;
            const data = await response.json();
            return JSON.parse(data.candidates?.[0]?.content?.parts?.[0]?.text);
        } catch (error) { console.error("AI Error:", error); return null; }
    }

    function SectionCard({ section, status, onSetStatus, answers, onAnswer }) {
        const answeredCount = section.questions.filter((_, idx) => answers[`${section.id}_${idx}`] !== undefined).length;
        const totalQ = section.questions.length;
        
        const cardClasses = {
            pending: "border-slate-200 bg-white",
            active: "border-primary-500 ring-2 ring-primary-50 bg-white shadow-lg",
            skipped: "border-slate-100 bg-slate-50 opacity-60 hover:opacity-100 transition-opacity",
            completed: "border-teal-200 bg-teal-50/50"
        };

        return (
            <div className={`rounded-2xl border shadow-soft transition-all duration-300 overflow-hidden ${cardClasses[status]}`}>
                <div className="p-4 flex flex-col gap-4">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-4">
                            <span className="text-3xl filter drop-shadow-sm">{section.icon}</span>
                            <div>
                                <h3 className={`text-base font-extrabold uppercase tracking-wide ${status==='active'?'text-primary-700':'text-slate-700'}`}>{section.title}</h3>
                                {status === 'skipped' && <p className="text-xs text-slate-500 font-bold mt-0.5">Đã xác nhận: Không có dấu hiệu</p>}
                                {status === 'completed' && <p className="text-xs text-teal-600 font-bold flex items-center gap-1 mt-0.5"><Icons.Check/> Đã ghi nhận dấu hiệu</p>}
                            </div>
                        </div>
                        {(status === 'skipped' || status === 'completed') && (
                            <button onClick={() => onSetStatus('pending')} className="text-xs font-bold text-slate-500 hover:text-primary-600 flex items-center gap-1.5 bg-white border border-slate-200 px-3 py-1.5 rounded-full shadow-sm hover:shadow transition-transform active:scale-95"><Icons.Edit/> Sửa</button>
                        )}
                    </div>
                    {status === 'pending' && (
                        <div className="animate-fade-in bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <p className="text-sm text-slate-600 mb-4 font-bold text-center">Bé nhà mình có gặp vấn đề ở nhóm này không?</p>
                            <div className="grid grid-cols-2 gap-4">
                                <button onClick={() => onSetStatus('skipped')} className="py-3 rounded-lg border-2 border-slate-200 text-slate-500 font-extrabold text-sm hover:bg-slate-100 hover:text-slate-700 transition-colors">KHÔNG</button>
                                <button onClick={() => onSetStatus('active')} className="py-3 rounded-lg bg-gradient-to-r from-primary-600 to-primary-700 text-white font-extrabold text-sm shadow-md hover:shadow-lg hover:shadow-primary-500/30 transition-all transform active:scale-95">CÓ, BÉ CÓ GẶP</button>
                            </div>
                        </div>
                    )}
                </div>
                {status === 'active' && (
                    <div className="bg-slate-50/50 border-t border-slate-100 p-4 space-y-3 animate-slide-up">
                        {section.questions.map((q, idx) => {
                            const val = answers[`${section.id}_${idx}`];
                            return (
                                <div key={idx} className="bg-white p-3 rounded-xl border border-slate-200 shadow-sm transition-shadow hover:shadow-md">
                                    <p className="text-sm font-bold text-slate-700 mb-3 leading-relaxed">{q}</p>
                                    <div className="flex bg-slate-100 p-1 rounded-lg">
                                        {OPTIONS.map((opt) => (
                                            <label key={opt.val} className="flex-1 cursor-pointer relative text-center group">
                                                <input type="radio" name={`${section.id}_${idx}`} checked={val === opt.val} onChange={() => onAnswer(section.id, idx, opt.val)} className="peer sr-only" />
                                                <div className={`py-2.5 rounded-md text-xs font-extrabold text-slate-400 transition-all duration-200 border border-transparent group-hover:bg-white/50 ${opt.color}`}>{opt.label}</div>
                                            </label>
                                        ))}
                                    </div>
                                </div>
                            )
                        })}
                        {answeredCount === totalQ && (
                            <div className="pt-2"><button onClick={() => onSetStatus('completed')} className="text-sm font-bold text-teal-700 bg-teal-50 border border-teal-200 px-6 py-3.5 rounded-xl hover:bg-teal-100 transition-colors w-full flex items-center justify-center gap-2 shadow-sm"><Icons.Check/> XÁC NHẬN HOÀN THÀNH NHÓM NÀY</button></div>
                        )}
                    </div>
                )}
            </div>
        );
    }

    function GuideModal({ onClose }) {
        return (
            <div className="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md animate-fade-in" onClick={onClose}>
                <div className="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden animate-slide-up relative" onClick={e => e.stopPropagation()}>
                    <div className="bg-teal-50 p-5 border-b border-teal-100 flex justify-between items-center">
                        <h3 className="text-base font-black text-teal-800 flex items-center gap-2 uppercase tracking-wide">
                            <div className="w-6 h-6 rounded-full bg-teal-200 flex items-center justify-center text-teal-800"><Icons.Help /></div> Hướng dẫn sử dụng
                        </h3>
                        <button onClick={onClose} className="p-2 bg-white rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors shadow-sm"><Icons.Close /></button>
                    </div>
                    <div className="p-6 space-y-6 text-slate-600">
                        <div className="flex gap-4"><div className="w-8 h-8 rounded-full bg-white border-2 border-teal-200 text-teal-700 flex items-center justify-center font-black shrink-0 text-sm shadow-sm">1</div><div><h4 className="font-bold text-slate-800 text-sm mb-1">Quan sát (3-5 ngày)</h4><p className="text-xs leading-relaxed text-slate-500 font-medium">Dành thời gian quan sát kỹ các biểu hiện tiêu hóa, phân và hành vi ăn uống của bé.</p></div></div>
                        <div className="flex gap-4"><div className="w-8 h-8 rounded-full bg-white border-2 border-teal-200 text-teal-700 flex items-center justify-center font-black shrink-0 text-sm shadow-sm">2</div><div><h4 className="font-bold text-slate-800 text-sm mb-1">Đánh giá theo nhóm</h4><p className="text-xs leading-relaxed text-slate-500 mb-2 font-medium">Chọn <strong>"Có"</strong> nếu thấy dấu hiệu và đánh giá mức độ. Chọn <strong>"Không"</strong> nếu bé bình thường.</p><div className="bg-slate-50 p-3 rounded-lg border border-slate-100 text-[11px] space-y-1.5 shadow-inner"><div className="font-bold text-slate-700 mb-1">QUY ƯỚC MỨC ĐỘ:</div><p><span className="font-bold text-slate-500 bg-slate-200 px-1 rounded">Không:</span> Hầu như không có dấu hiệu.</p><p><span className="font-bold text-yellow-700 bg-yellow-100 px-1 rounded">Thỉnh thoảng:</span> &lt; 3 lần/tuần.</p><p><span className="font-bold text-red-700 bg-red-100 px-1 rounded">Thường xuyên:</span> &ge; 3 lần/tuần hoặc kéo dài.</p></div></div></div>
                        <div className="flex gap-4"><div className="w-8 h-8 rounded-full bg-white border-2 border-teal-200 text-teal-700 flex items-center justify-center font-black shrink-0 text-sm shadow-sm">3</div><div><h4 className="font-bold text-slate-800 text-sm mb-1">Nhận kết quả & Lưu</h4><p className="text-xs leading-relaxed text-slate-500 font-medium">Hệ thống tổng hợp, tính điểm và đưa ra lời khuyên từ Trợ lý chuyên môn.</p></div></div>
                        <div className="bg-slate-50 border-l-4 border-primary-200 p-3 text-[11px] text-slate-600 leading-relaxed font-medium"><strong className="text-primary-700">Lưu ý:</strong> Kết quả chỉ mang tính tham khảo. Sự trung thực khi đánh giá sẽ giúp kết quả phản ánh đúng thực trạng của bé.</div>
                    </div>
                    <div className="p-5 bg-white border-t border-slate-100 text-center sticky bottom-0">
                        <button onClick={onClose} className="px-8 py-3.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold text-sm shadow-lg shadow-teal-200 transition-all transform active:scale-95 w-full sm:w-auto">Đã hiểu, bắt đầu ngay</button>
                    </div>
                </div>
            </div>
        );
    }

    function HeroSection({ onShowGuide }) {
        return (
            <div className="bg-gradient-to-b from-white to-slate-50 pt-8 pb-6 px-4 text-center border-b border-slate-100">
                <div className="animate-fade-in max-w-3xl mx-auto">
                    <div className="mb-4 inline-block">
                        <span className="text-[10px] font-black tracking-widest text-primary-700 uppercase bg-primary-50 px-3 py-1.5 rounded-full border border-primary-200 shadow-sm">DawnBridge Autism Care</span>
                    </div>
                    <h1 className="text-3xl sm:text-4xl font-black text-slate-800 mb-3 leading-tight tracking-tight">
                        Checklist <span className="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-teal-500">Tiêu Hoá</span>
                    </h1>
                    <p className="text-base text-slate-500 max-w-lg mx-auto mb-6 leading-relaxed font-medium">
                        Công cụ rà soát dấu hiệu tiêu hóa & Trục Não - Ruột chuyên sâu dành cho trẻ đặc biệt.
                    </p>
                    <button onClick={onShowGuide} className="inline-flex items-center gap-2 px-6 py-2.5 bg-teal-50 border border-teal-200 rounded-full text-teal-800 text-xs font-bold shadow-sm hover:shadow-md hover:bg-teal-100 transition-all group">
                        <div className="w-5 h-5 rounded-full bg-teal-200 flex items-center justify-center text-[10px] font-black text-teal-800">?</div> 
                        <span>Hướng dẫn sử dụng</span>
                    </button>
                </div>
            </div>
        );
    }

    function App() {
        const [answers, setAnswers] = useState({});
        const [groupStatuses, setGroupStatuses] = useState({ g1: 'pending', g2: 'pending', g3: 'pending' });
        const [showResult, setShowResult] = useState(false);
        const [showGuide, setShowGuide] = useState(false);
        const [aiAnalysis, setAiAnalysis] = useState(null);
        const [lastAnalyzedHash, setLastAnalyzedHash] = useState(''); 
        const [parentName, setParentName] = useState('');
        const [phone, setPhone] = useState('');
        const [isSaving, setIsSaving] = useState(false);

        const result = useMemo(() => {
            let total = 0, oftenCount = 0, g1 = 0, g2 = 0, g3 = 0, answered = 0;
            const symptoms = [];
            const groupedResults = { frequent: [], sometimes: [], none: [] };

            SECTIONS.forEach(s => {
                const status = groupStatuses[s.id];
                if (status === 'skipped') {
                    answered += s.questions.length;
                    s.questions.forEach(q => groupedResults.none.push({ q, group: s.title }));
                } else if (status === 'active' || status === 'completed') {
                    s.questions.forEach((q, idx) => {
                        const val = answers[`${s.id}_${idx}`];
                        if (val !== undefined) {
                            answered++;
                            total += val;
                            if (val === 2) {
                                oftenCount++;
                                groupedResults.frequent.push({ q, group: s.title });
                                symptoms.push(`${q} (Thường xuyên)`);
                            } else if (val === 1) {
                                groupedResults.sometimes.push({ q, group: s.title });
                                symptoms.push(`${q} (Thỉnh thoảng)`);
                            } else {
                                groupedResults.none.push({ q, group: s.title });
                            }
                            if (s.id === 'g1') g1 += val;
                            if (s.id === 'g2') g2 += val;
                            if (s.id === 'g3') g3 += val;
                        }
                    });
                }
            });

            let level = 'moderate';
            if (total >= 16 || oftenCount >= 7 || g3 >= 6 || (g1 >= 4 && g2 >= 4)) level = 'severe';
            else if (total <= 7 && oftenCount <= 3 && g3 <= 3) level = 'mild';
            else level = 'moderate'; 

            return { level, progress: Math.round((answered / 12) * 100), symptoms, groupedResults, isComplete: answered === 12 };
        }, [answers, groupStatuses]);

        // Colors map for capture function (explicit hex codes to ensure rendering)
        const CAPTURE_COLORS = {
            mild: { bg: '#dcfce7', text: '#166534', icon: '#22c55e' },
            moderate: { bg: '#fef9c3', text: '#854d0e', icon: '#eab308' },
            severe: { bg: '#fee2e2', text: '#991b1b', icon: '#ef4444' }
        };

        const DATA = {
            mild: { 
                title: 'MỨC ĐỘ NHẸ', 
                desc: 'Rối loạn chưa ảnh hưởng nhiều',
                color: 'text-green-800', 
                bgHeader: 'bg-green-100', 
                border: 'border-green-300', 
                dot: 'bg-green-600',
                iconBg: 'bg-green-200'
            },
            moderate: { 
                title: 'MỨC TRUNG BÌNH', 
                desc: 'Hấp thu kém & Năng lượng giảm',
                color: 'text-yellow-800', 
                bgHeader: 'bg-yellow-100', 
                border: 'border-yellow-300', 
                dot: 'bg-yellow-600',
                iconBg: 'bg-yellow-200'
            },
            severe: { 
                title: 'MỨC ĐỘ NẶNG', 
                desc: 'Ảnh hưởng Trục Não - Ruột',
                color: 'text-red-800', 
                bgHeader: 'bg-red-100', 
                border: 'border-red-300', 
                dot: 'bg-red-600',
                iconBg: 'bg-red-200'
            }
        };

        const currentData = DATA[result.level];
        const currentArticle = ARTICLES[result.level];

        const handleSetStatus = (sId, status) => {
            setGroupStatuses(prev => ({ ...prev, [sId]: status }));
            if (status === 'skipped') {
                setAnswers(prev => {
                    const next = { ...prev };
                    Object.keys(next).forEach(k => { if (k.startsWith(sId)) delete next[k]; });
                    return next;
                });
            }
        };
        const handleAnswer = (sId, qIdx, val) => setAnswers(prev => ({ ...prev, [`${sId}_${qIdx}`]: val }));
        
        const resetForm = () => { if(confirm("Làm lại?")) { setAnswers({}); setGroupStatuses({ g1: 'pending', g2: 'pending', g3: 'pending' }); setShowResult(false); setAiAnalysis(null); setLastAnalyzedHash(''); window.scrollTo(0,0); }};

        const handleViewResult = () => {
            setShowResult(true);
            const currentHash = JSON.stringify(result.symptoms) + result.level;
            if (!aiAnalysis || currentHash !== lastAnalyzedHash) {
                setAiAnalysis(null);
                setLastAnalyzedHash(currentHash);
                const prompt = `Dựa trên danh sách triệu chứng: "${result.symptoms.join(', ')}" và mức độ nguy cơ hiện tại là: "${currentData.title}" (Lưu ý: Nguy cơ được chia thành 3 mức: Thấp, Trung bình, Cao). Hãy đóng vai một chuyên gia dinh dưỡng và sức khoẻ tiêu hoá nhi khoa, phân tích và trả về kết quả dưới dạng JSON object (không Markdown) 1 trường là: "summary": Viết đoạn văn ngắn (tối đa 2 câu, khoảng 40 từ), ngôn ngữ gần gũi, dễ hiểu với phụ huynh, tránh dùng từ chuyên môn y khoa sâu (như trục não ruột). Tóm tắt tình trạng tiêu hoá của bé và giải thích nhẹ nhàng ảnh hưởng của nó tới sinh hoạt/hành vi của bé.`;
                callGemini(prompt).then(res => { if (res) setAiAnalysis(res); else setLastAnalyzedHash(''); });
            }
        };

        const handleCall = () => {
            window.location.href = "tel:0985391881";
        };

        const handleSaveResult = async () => {
            if (!parentName || !phone) return;
            setIsSaving(true);
            
            // Build High-Fidelity Capture HTML
            const capColor = CAPTURE_COLORS[result.level];
            const captureContainer = document.createElement('div');
            Object.assign(captureContainer.style, {
                position: 'absolute', top: '-10000px', left: '0', width: '800px',
                backgroundColor: '#ffffff', fontFamily: '"Nunito", sans-serif', color: '#1e293b'
            });

            // 1. Build Symptoms HTML
            let symptomsHTML = '';
            
            // Frequent
            if (result.groupedResults.frequent.length > 0) {
                symptomsHTML += `
                    <div style="margin-bottom: 15px; border: 1px solid #fecaca; background-color: #fef2f2; border-radius: 8px; padding: 15px;">
                        <div style="display: flex; align-items: center; margin-bottom: 8px; color: #991b1b; font-weight: 800; font-size: 12px; text-transform: uppercase;">
                            <span style="width: 8px; height: 8px; background-color: #ef4444; border-radius: 50%; display: inline-block; margin-right: 8px;"></span> THƯỜNG XUYÊN (CẦN CHÚ Ý)
                        </div>
                        <ul style="margin: 0; padding-left: 20px; list-style-type: disc; color: #7f1d1d; font-size: 14px; line-height: 1.5;">
                            ${result.groupedResults.frequent.map(i => `<li style="margin-bottom: 4px;">${i.q}</li>`).join('')}
                        </ul>
                    </div>`;
            }
            
            // Sometimes
            if (result.groupedResults.sometimes.length > 0) {
                symptomsHTML += `
                    <div style="margin-bottom: 15px; border: 1px solid #fde047; background-color: #fefce8; border-radius: 8px; padding: 15px;">
                        <div style="display: flex; align-items: center; margin-bottom: 8px; color: #854d0e; font-weight: 800; font-size: 12px; text-transform: uppercase;">
                            <span style="width: 8px; height: 8px; background-color: #eab308; border-radius: 50%; display: inline-block; margin-right: 8px;"></span> THỈNH THOẢNG
                        </div>
                        <ul style="margin: 0; padding-left: 20px; list-style-type: disc; color: #713f12; font-size: 14px; line-height: 1.5;">
                            ${result.groupedResults.sometimes.map(i => `<li style="margin-bottom: 4px;">${i.q}</li>`).join('')}
                        </ul>
                    </div>`;
            }

            // None (Summary)
            if (result.groupedResults.none.length > 0) {
                symptomsHTML += `
                    <div style="margin-bottom: 15px; border: 1px solid #e2e8f0; background-color: #f8fafc; border-radius: 8px; padding: 12px; font-size: 12px; color: #64748b; font-weight: 700; text-transform: uppercase;">
                        <span style="width: 8px; height: 8px; background-color: #94a3b8; border-radius: 50%; display: inline-block; margin-right: 8px;"></span> KHÔNG CÓ DẤU HIỆU (${result.groupedResults.none.length} mục)
                    </div>`;
            }

            const aiText = aiAnalysis ? aiAnalysis.summary : "Đang cập nhật đánh giá...";

            captureContainer.innerHTML = `
                <!-- HEADER -->
                <div style="padding: 40px; background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%); border-bottom: 4px solid #0d9488; text-align: center;">
                    <div style="font-size: 14px; color: #0f766e; text-transform: uppercase; letter-spacing: 3px; font-weight: 900; margin-bottom: 10px;">DawnBridge Autism Care</div>
                    <h1 style="font-size: 32px; font-weight: 900; color: #115e59; margin: 0; line-height: 1.2;">PHIẾU KẾT QUẢ CHECKLIST TIÊU HOÁ</h1>
                </div>

                <div style="padding: 40px; background-color: #fff;">
                    <!-- INFO BOX -->
                    <div style="display: flex; justify-content: space-between; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 30px;">
                        <div>
                            <p style="margin: 0 0 5px; color: #64748b; font-size: 12px; font-weight: 800; text-transform: uppercase;">Phụ huynh</p>
                            <p style="margin: 0; color: #0f172a; font-size: 18px; font-weight: 700;">${parentName}</p>
                        </div>
                        <div style="text-align: right;">
                            <p style="margin: 0 0 5px; color: #64748b; font-size: 12px; font-weight: 800; text-transform: uppercase;">Liên hệ</p>
                            <p style="margin: 0; color: #0f172a; font-size: 18px; font-weight: 700;">${phone}</p>
                        </div>
                        <div style="text-align: right; border-left: 1px solid #e2e8f0; padding-left: 20px;">
                            <p style="margin: 0 0 5px; color: #64748b; font-size: 12px; font-weight: 800; text-transform: uppercase;">Ngày thực hiện</p>
                            <p style="margin: 0; color: #0f172a; font-size: 18px; font-weight: 700;">${new Date().toLocaleDateString('vi-VN')}</p>
                        </div>
                    </div>

                    <!-- RESULT DASHBOARD -->
                    <div style="text-align: center; margin-bottom: 30px; padding: 30px; background-color: ${capColor.bg}; border-radius: 16px; border: 2px solid ${capColor.text}20;">
                        <p style="font-size: 12px; font-weight: 900; letter-spacing: 2px; color: #64748b; margin-bottom: 10px; text-transform: uppercase;">Đánh giá tổng quan</p>
                        <h2 style="font-size: 40px; font-weight: 900; color: ${capColor.text}; margin: 0; line-height: 1; text-transform: uppercase;">${currentData.title}</h2>
                        <p style="font-size: 16px; color: ${capColor.text}; margin-top: 10px; font-weight: 600;">${currentData.desc}</p>
                    </div>

                    <!-- SYMPTOMS -->
                    <div style="margin-bottom: 30px;">
                        <h3 style="font-size: 14px; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">Chi tiết ghi nhận</h3>
                        ${symptomsHTML}
                    </div>

                    <!-- AI ANALYSIS -->
                    <div style="margin-bottom: 30px;">
                        <h3 style="font-size: 14px; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">Phân tích chuyên sâu</h3>
                        <div style="position: relative; padding: 25px; background-color: #fff; border-left: 6px solid ${capColor.icon}; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border-radius: 0 12px 12px 0;">
                            <p style="font-size: 16px; line-height: 1.6; color: #334155; margin: 0; font-style: italic;">"${aiText}"</p>
                            <div style="margin-top: 15px; font-size: 12px; font-weight: 700; color: ${capColor.text}; text-transform: uppercase;">— Hệ thống đánh giá tự động DawnBridge</div>
                        </div>
                    </div>

                    <!-- DISCLAIMER -->
                    <div style="padding: 15px; background-color: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; color: #0f172a; font-size: 12px; line-height: 1.5; text-align: justify;">
                        <strong>Lưu ý:</strong> Kết quả này được tạo ra dựa trên thông tin quý phụ huynh cung cấp và chỉ mang tính chất tham khảo sàng lọc. Đây không phải là chẩn đoán y khoa chính thức. Vui lòng tham khảo ý kiến bác sĩ chuyên khoa để có phác đồ điều trị chính xác nhất.
                    </div>
                </div>

                <!-- FOOTER -->
                <div style="padding: 20px; text-align: center; background-color: #1e293b; color: #fff;">
                    <div style="font-size: 16px; font-weight: 800; margin-bottom: 5px;">Hỗ trợ chuyên môn: 0985 39 1881</div>
                    <div style="font-size: 12px; opacity: 0.7;">© DawnBridge Autism Care - Đồng hành cùng bé đặc biệt</div>
                </div>
            `;

            document.body.appendChild(captureContainer);

            try {
                // High Quality Scale
                const canvas = await html2canvas(captureContainer, { scale: 3, useCORS: true, backgroundColor: '#ffffff' });
                const link = document.createElement('a');
                link.download = `KetQua_TieuHoa_${phone}.png`;
                link.href = canvas.toDataURL("image/png");
                link.click();
            } catch (err) { alert("Lỗi khi lưu ảnh."); } finally { document.body.removeChild(captureContainer); setIsSaving(false); }
        };

        const isFormValid = parentName.trim() !== '' && phone.trim() !== '';

        return (
            <div className="min-h-screen pb-32 font-sans bg-slate-50 text-slate-700 selection:bg-primary-100">
                <HeroSection onShowGuide={() => setShowGuide(true)} />

                <div className="container mx-auto px-4 pt-5 space-y-5 max-w-3xl">
                    {SECTIONS.map(s => (
                        <SectionCard key={s.id} section={s} status={groupStatuses[s.id]} onSetStatus={(status) => handleSetStatus(s.id, status)} answers={answers} onAnswer={handleAnswer} />
                    ))}
                    <div className="pt-5 pb-8">
                        <button onClick={handleViewResult} disabled={!result.isComplete} className={`w-full py-4 rounded-xl text-white text-sm font-bold shadow-lg transition-all transform active:scale-95 ${result.isComplete ? 'bg-gradient-to-r from-slate-800 to-slate-900 hover:shadow-slate-800/30' : 'bg-slate-300 cursor-not-allowed'}`}>
                            {result.isComplete ? 'XEM KẾT QUẢ PHÂN TÍCH' : `Vui lòng hoàn thành (${Math.round((result.progress/100)*12)}/12 câu)`}
                        </button>
                        <div className="text-center mt-6">
                            <button onClick={resetForm} className="text-xs text-slate-400 font-bold hover:text-primary-600 transition-colors flex items-center justify-center gap-1 mx-auto"><Icons.Refresh/> Làm lại từ đầu</button>
                        </div>
                    </div>
                    <div className="bg-slate-50 border border-slate-200 text-slate-700 text-[11px] leading-relaxed font-medium p-4 rounded-xl shadow-inner">
                        <strong className="uppercase tracking-wide text-[10px] text-slate-900 block mb-1">Lưu ý</strong>
                        Công cụ checklist chỉ mang tính sàng lọc hỗ trợ phụ huynh quan sát. Kết quả không thay thế cho tư vấn, chẩn đoán hay điều trị y khoa. Nếu bé có dấu hiệu bất thường, vui lòng liên hệ bác sĩ chuyên khoa để được thăm khám trực tiếp.
                    </div>
                </div>

                <div className="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-xl border-t border-slate-200 shadow-[0_-4px_20px_-5px_rgba(0,0,0,0.1)] z-40 pb-safe transition-transform duration-300">
                    <div className="w-full h-1.5 bg-slate-100">
                        <div className="bg-gradient-to-r from-primary-500 to-primary-600 h-1.5 transition-all duration-500 ease-out shadow-[0_0_15px_rgba(59,130,246,0.6)]" style={{width: `${result.progress}%`}}></div>
                    </div>
                    <div className="container mx-auto max-w-3xl px-5 py-3 flex items-center justify-between">
                        <div className="flex flex-col"><span className="text-[10px] uppercase font-extrabold text-slate-400 tracking-wider mb-0.5">Tiến độ</span><div className="flex items-baseline gap-1"><span className="text-lg font-black text-slate-800">{result.progress}%</span><span className="text-[11px] font-bold text-slate-400">({Math.round((result.progress/100)*12)}/12 câu)</span></div></div>
                        <div className="flex items-center gap-3 text-right">
                            <div>
                                <span className="text-[10px] uppercase font-extrabold text-slate-400 tracking-wider block mb-0.5">Đánh giá sơ bộ</span>
                                <span className={`text-sm font-black transition-colors duration-300 ${currentData.color}`}>{currentData.title}</span>
                            </div>
                            <div className={`w-4 h-4 rounded-full shadow-md border-2 border-white transition-colors duration-300 ${currentData.dot} animate-pulse`}></div>
                        </div>
                    </div>
                </div>

                {showGuide && <GuideModal onClose={() => setShowGuide(false)} />}

                {showResult && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm animate-fade-in">
                        <div className="bg-white w-full max-w-2xl rounded-3xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden animate-slide-up transform transition-all">
                            <div className={`shrink-0 p-6 flex flex-col items-center justify-center relative overflow-hidden border-b ${currentData.bgHeader} ${currentData.border}`}>
                                <button onClick={() => setShowResult(false)} className="absolute top-4 right-4 bg-white/60 hover:bg-white p-2 rounded-full transition-colors z-10"><Icons.Close/></button>
                                <div className="z-10 text-center">
                                    <p className="text-[10px] font-extrabold tracking-[0.2em] uppercase text-slate-500 mb-2">KẾT QUẢ ĐÁNH GIÁ</p>
                                    <div className="flex items-center justify-center gap-3 mb-2">
                                        <div className={`w-10 h-10 rounded-full flex items-center justify-center text-xl shadow-sm ${currentData.iconBg} ${currentData.color}`}><Icons.Sparkles/></div>
                                        <h2 className={`text-2xl sm:text-3xl font-black uppercase tracking-tight ${currentData.color}`}>{currentData.title}</h2>
                                    </div>
                                    <p className="text-sm font-bold text-slate-600 max-w-sm mx-auto">{currentData.desc}</p>
                                </div>
                                <div className={`absolute -bottom-10 -left-10 w-40 h-40 rounded-full opacity-20 blur-3xl ${currentData.dot}`}></div>
                                <div className={`absolute -top-10 -right-10 w-40 h-40 rounded-full opacity-20 blur-3xl ${currentData.dot}`}></div>
                            </div>

                            <div className="flex-1 overflow-y-auto custom-scrollbar p-5 space-y-6 bg-slate-50/30">
                                    <section>
                                        <h3 className="text-xs font-extrabold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                            <span className="w-8 h-0.5 bg-slate-300 rounded-full"></span> Kết quả ghi nhận
                                        </h3>
                                        <div className="grid gap-3">
                                            {result.groupedResults.frequent.length > 0 && (
                                                <div className="bg-red-50 rounded-xl border border-red-200 p-4 shadow-sm">
                                                    <div className="flex items-center gap-2 mb-3">
                                                        <span className="flex h-3 w-3 relative"><span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span><span className="relative inline-flex rounded-full h-3 w-3 bg-red-600"></span></span>
                                                        <span className="text-xs font-black text-red-800 uppercase tracking-wide">THƯỜNG XUYÊN (Cần chú ý)</span>
                                                    </div>
                                                    <ul className="space-y-2">
                                                        {result.groupedResults.frequent.map((item, idx) => (
                                                            <li key={idx} className="text-sm text-red-900 font-bold leading-relaxed flex items-start gap-2"><span className="mt-1.5 w-1.5 h-1.5 rounded-full bg-red-400 shrink-0"></span> {item.q}</li>
                                                        ))}
                                                    </ul>
                                                </div>
                                            )}
                                            {result.groupedResults.sometimes.length > 0 && (
                                                <div className="bg-yellow-50 rounded-xl border border-yellow-200 p-4 shadow-sm">
                                                    <div className="flex items-center gap-2 mb-3">
                                                        <span className="w-3 h-3 rounded-full bg-yellow-500"></span>
                                                        <span className="text-xs font-black text-yellow-800 uppercase tracking-wide">THỈNH THOẢNG</span>
                                                    </div>
                                                    <ul className="space-y-2">
                                                        {result.groupedResults.sometimes.map((item, idx) => (
                                                            <li key={idx} className="text-sm text-yellow-900 font-medium leading-relaxed flex items-start gap-2"><span className="mt-1.5 w-1.5 h-1.5 rounded-full bg-yellow-400 shrink-0"></span> {item.q}</li>
                                                        ))}
                                                    </ul>
                                                </div>
                                            )}
                                            {result.groupedResults.none.length > 0 && (
                                                <div className="bg-slate-100 rounded-xl border border-slate-200 p-3 opacity-70">
                                                    <div className="flex items-center gap-2">
                                                        <span className="w-2 h-2 rounded-full bg-slate-400"></span>
                                                        <span className="text-xs font-bold text-slate-500 uppercase">Không có dấu hiệu ({result.groupedResults.none.length} mục)</span>
                                                    </div>
                                                </div>
                                            )}
                                        </div>
                                    </section>

                                    <section>
                                        <h3 className="text-xs font-extrabold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                            <span className="w-8 h-0.5 bg-slate-300 rounded-full"></span> Đánh giá từ hệ thống
                                        </h3>
                                        <div className={`p-6 rounded-2xl border-l-4 bg-white shadow-soft relative overflow-hidden ${currentData.border.replace('border-', 'border-l-')}`}>
                                            {aiAnalysis ? (
                                                <div className="relative z-10 animate-fade-in">
                                                    <p className="text-base text-slate-700 leading-relaxed font-semibold text-justify">
                                                        {aiAnalysis.summary}
                                                    </p>
                                                </div>
                                            ) : (
                                                <div className="flex flex-col gap-3 animate-pulse">
                                                    <div className="h-4 bg-slate-100 rounded w-3/4"></div>
                                                    <div className="h-4 bg-slate-100 rounded w-full"></div>
                                                    <div className="h-4 bg-slate-100 rounded w-5/6"></div>
                                                    <p className="text-[10px] text-slate-400 mt-2 font-bold text-center uppercase tracking-wide">Hệ thống đang phân tích dữ liệu...</p>
                                                </div>
                                            )}
                                            <div className="absolute top-0 right-0 p-4 opacity-5"><Icons.Shield size={60}/></div>
                                        </div>
                                    </section>

                                    <section>
                                        <h3 className="text-xs font-extrabold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                            <span className="w-8 h-0.5 bg-slate-300 rounded-full"></span> Góc kiến thức
                                        </h3>
                                        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-soft hover:border-primary-400 hover:shadow-md transition-all cursor-pointer group">
                                            <div className="flex gap-4">
                                                <div className="w-12 h-12 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center text-2xl shrink-0"><Icons.Book/></div>
                                                <div>
                                                    <h4 className="font-bold text-slate-800 text-sm sm:text-base mb-1 group-hover:text-primary-700 transition-colors">{currentArticle.title}</h4>
                                                    <p className="text-sm text-slate-500 line-clamp-2 mb-2 leading-relaxed font-medium">{currentArticle.desc}</p>
                                                    <span className="text-[10px] font-bold text-primary-600 uppercase tracking-wide flex items-center gap-1 group-hover:gap-2 transition-all">Đọc chi tiết <span className="text-lg leading-none">&rarr;</span></span>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    <section className="space-y-6">
                                        <div className="bg-gradient-to-br from-white to-slate-50 p-6 rounded-2xl border border-slate-200 shadow-soft">
                                            <h3 className="text-sm font-extrabold text-slate-800 uppercase tracking-wide mb-5 text-center">Gửi kết quả tới Trợ lý dinh dưỡng chuyên môn</h3>
                                            <div className="space-y-4">
                                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <label className="text-[10px] font-bold text-slate-400 uppercase mb-1 block pl-1">Họ tên phụ huynh</label>
                                                        <input type="text" value={parentName} onChange={e => setParentName(e.target.value)} className="w-full text-sm p-3.5 rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all font-bold text-slate-700 placeholder-slate-300" placeholder="Nhập tên..." />
                                                    </div>
                                                    <div>
                                                        <label className="text-[10px] font-bold text-slate-400 uppercase mb-1 block pl-1">Số điện thoại</label>
                                                        <input type="tel" value={phone} onChange={e => setPhone(e.target.value)} className="w-full text-sm p-3.5 rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all font-bold text-slate-700 placeholder-slate-300" placeholder="Nhập SĐT..." />
                                                    </div>
                                                </div>
                                                
                                                <div className="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
                                                    <button 
                                                        onClick={handleCall}
                                                        disabled={!isFormValid}
                                                        className={`w-full py-4 rounded-xl font-bold text-white shadow-lg text-sm flex items-center justify-center gap-2 transition-all ${!isFormValid ? 'bg-slate-300 cursor-not-allowed opacity-70' : 'bg-gradient-to-r from-teal-500 to-teal-600 hover:shadow-teal-500/40 hover:-translate-y-0.5 active:translate-y-0'}`}
                                                    >
                                                        <Icons.Phone /> Gọi Ngay
                                                    </button>
                                                    
                                                    <button 
                                                        onClick={handleSaveResult}
                                                        disabled={!isFormValid || isSaving}
                                                        className={`w-full py-4 rounded-xl font-bold text-white shadow-lg text-sm flex items-center justify-center gap-2 transition-all ${!isFormValid ? 'bg-slate-300 cursor-not-allowed opacity-70' : 'bg-gradient-to-r from-primary-600 to-primary-700 hover:shadow-primary-500/40 hover:-translate-y-0.5 active:translate-y-0'}`}
                                                    >
                                                        {isSaving ? <span className="animate-spin">⏳</span> : <Icons.Camera />} Lưu Kết Quả
                                                    </button>
                                                </div>
                                                
                                                {!isFormValid && <p className="text-[10px] text-center text-red-400 mt-2 font-bold italic">* Vui lòng điền thông tin để mở khoá chức năng</p>}
                                            </div>
                                        </div>

                                        <div className="bg-slate-50 border border-slate-200 rounded-xl p-4 text-[11px] text-slate-700 leading-relaxed font-medium text-justify">
                                            <strong className="block mb-1 text-slate-900 uppercase tracking-wide text-[10px]">Lưu ý:</strong>
                                            Kết quả đánh giá này được xây dựng dựa trên các thuật toán sàng lọc sơ bộ và thông tin quý phụ huynh cung cấp. Đây <strong>không phải là chẩn đoán y khoa</strong> và không có giá trị thay thế cho việc thăm khám, tư vấn trực tiếp từ bác sĩ hoặc chuyên gia y tế. Vui lòng liên hệ với cơ sở y tế gần nhất để có kết luận chính xác nhất về tình trạng sức khỏe của bé.
                                        </div>
                                    </section>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        );
    }
    const root = ReactDOM.createRoot(document.getElementById('root'));
    root.render(<App />);
</script>
</body>
</html>
<?php
/**
 * Template Name: Check List tiêu hóa
 *
 * @package AutismTools
 */
get_header();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Checklist Tiêu Hoá & Trục Não Ruột - DawnBridge</title>
    
    <!-- React & ReactDOM -->
    <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    
    <!-- Html2Canvas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <!-- Google Fonts: Nunito -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Nunito', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0fdfa', 100: '#ccfbf1', 200: '#99f6e4', 300: '#5eead4', 400: '#2dd4bf',
                            500: '#14b8a6', 600: '#0d9488', 700: '#0f766e', 800: '#115e59', 900: '#134e4a',
                        },
                    },
                    boxShadow: {
                        'soft': '0 10px 40px -10px rgba(0,0,0,0.08)',
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.3s ease-out forwards',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(5px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Base styles */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #f0fdfa; }
        ::-webkit-scrollbar-thumb { background: #99f6e4; border-radius: 10px; }
        html { scroll-behavior: smooth; -webkit-tap-highlight-color: transparent; }
        body { background-color: #f8fafc; color: #1e293b; font-size: 15px; }
        .glass-effect { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px); }

        /* --- PRINT STYLES FOR PDF EXPORT --- */
        @media print {
            @page { margin: 0.5cm; size: A4 portrait; }
            body { background: white; color: black; }
            #root { display: none !important; } /* Hide app */
            #print-container { display: block !important; position: absolute; top: 0; left: 0; width: 100%; }
            
            /* Reset styles for print container */
            #print-container * { visibility: visible; }
            #print-container .overflow-y-auto { overflow: visible !important; height: auto !important; max-height: none !important; }
            #print-container .shadow-2xl { shadow: none !important; box-shadow: none !important; border: 1px solid #ddd; }
            #print-container button, #print-container #modal-close-btn, #print-container #modal-footer { display: none !important; }
            
            /* Typography adjustments for print */
            #print-container h2 { color: #000 !important; }
            #print-container p { font-size: 12pt; color: #333; }
        }
        #print-container { display: none; }
    </style>
</head>
<body>

<div id="root"></div>
<div id="print-container"></div>

<script type="text/babel">
    const { useState, useMemo, useEffect, useRef } = React;

    // --- CẤU HÌNH API ---
    // QUAN TRỌNG: Bạn cần điền API Key vào đây để phần Trợ lý chuyên môn hoạt động
    const apiKey = "AIzaSyBNxioLU7AaIgTHkr7vfxqwKoL12a7_xWo"; 

    // --- API & DATA ---
    async function callGemini(prompt, expectJson) {
        try {
            const response = await fetch(
                `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-09-2025:generateContent?key=${apiKey}`,
                {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ contents: [{ parts: [{ text: prompt }] }], generationConfig: expectJson ? { responseMimeType: "application/json" } : {} }),
                }
            );
            if (!response.ok) return null;
            const data = await response.json();
            const textResult = data.candidates?.[0]?.content?.parts?.[0]?.text;
            return expectJson ? JSON.parse(textResult) : textResult;
        } catch (error) { console.error(error); return null; }
    }

    async function analyzeWithGemini(symptoms, riskLevel) {
        if (!symptoms || symptoms.length === 0) return null;
        if (!apiKey) return { 
            summary: "Hệ thống đang bảo trì", 
            action: "Hiện tại, bạn có thể tham khảo các bài viết chuyên môn được gợi ý bên dưới dựa trên kết quả checklist." 
        };
        const prompt = `
        Bạn là trợ lý y khoa nhi khoa cho trẻ đặc biệt (Tự kỷ, tăng động giảm chú ý, ..). Dựa trên danh sách triệu chứng: "${symptoms.join(', ')}" và mức độ nguy cơ hiện tại là: "${riskLevel}" (Lưu ý: Nguy cơ được chia thành 3 mức: Thấp, Trung bình, Cao), hãy phân tích và trả về kết quả dưới dạng JSON object (không Markdown) gồm 2 trường:
                
                1. "summary": Viết đoạn văn ngắn (tối đa 2 câu ngắn gọn 35 từ khoảng 150 ký tự) tóm tắt tình trạng sức khỏe đường ruột của bé. Một cách tổng quan nhất hướng tới trục ruột não (có thể giải thích tại sao dẫn tới hành vi đó một cách gần gũi với phụ huynh nhất)
                2. "action": Viết đoạn văn ngắn (tối đa 2 câu ngắn gọn 35 từ khoảng 150 ký tự) đưa ra lời khuyên hành động, ưu tiên giải pháp dinh dưỡng và sinh hoạt cụ thể cho các triệu chứng trên lời khuyên hướng tới các dưỡng chất như là (Protein thực vật: Hạt đậu Hà Lan phân lập, hạt Quinoa (diêm mạch) hữu cơ, hạt Millet (kê) hữu cơ, gạo lứt và gạo lứt hữu cơ nảy mầm. Chất xơ hòa tan: Inulin và Fructooligosaccharides (FOS) từ cây rau diếp xoăn, giúp hỗ trợ tiêu hóa. Chất béo: Dầu hướng dương và dầu hạt lanh, cung cấp các axit béo không no thiết yếu như Omega 3, 6, 9. Các vitamin: Vitamin A, C, D, E, Axit Folic, B1, B2, B3, B6, B12, Biotin, Axit Pantothenic, K1. Các khoáng chất: Canxi, Choline, Magie, Sắt, Kẽm, I-ốt, Selen, Kali, Natri. Không chứa sữa bò và đậu nành, GABA trong gạo lứt nảy mầm (Không nhất thiết lúc nào cũng là tất cả nhé!)) Nếu Mức độ nguy cơ hiện tại không phải thấp thì đưa ra lời khuyên đầu tiên là đi khám ở cơ sở uy tín và đồng thời thêm giải pháp dinh dưỡng và sinh hoạt (Lưu ý cần có từ "Đồng thời nên" khi đưa ra lời khuyên đi khám).
                
                Ví dụ Output:
                {
                    "summary": "Bé đang có dấu hiệu táo bón rõ rệt kèm theo rối loạn vi sinh đường ruột.",
                    "action": "Nên tăng cường chất xơ hòa tan từ mồng tơi, khoai lang và loại bỏ đường tinh luyện khỏi chế độ ăn."
                }`;
        return await callGemini(prompt, true);
    }

    const Icons = {
        Check: ({size=20, className=""}) => <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round" className={className}><polyline points="20 6 9 17 4 12"/></svg>,
        Alert: ({size=20, className=""}) => <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>,
        Stomach: ({size=24, className=""}) => <svg width={size} height={size} viewBox="0 0 24 24" fill="currentColor" className={className}><path d="M18 2H6C3.79 2 2 3.79 2 6v8c0 3.31 2.69 6 6 6v2c0 .55.45 1 1 1s1-.45 1-1v-2h4v2c0 .55.45 1 1 1s1-.45 1-1v-2c3.31 0 6-2.69 6-6V6c0-2.21-1.79-4-4-4zm-2 12c0 1.1-.9 2-2 2h-4c-1.1 0-2-.9-2-2V8c0-1.1.9-2 2-2h4c1.1 0 2 .9 2 2v6z"/></svg>,
        Brain: ({size=24, className=""}) => <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}><path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 1.98-3A2.5 2.5 0 0 1 9.5 2Z"/><path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-1.98-3A2.5 2.5 0 0 0 14.5 2Z"/></svg>,
        Refresh: ({size=20, className=""}) => <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg>,
        Download: ({size=20, className=""}) => <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>,
        X: ({size=20, className=""}) => <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>,
        Sparkles: ({size=20, className=""}) => <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L12 3Z"/></svg>,
        Smile: ({size=20, className=""}) => <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>,
        Eye: ({size=20, className=""}) => <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>,
        Leaf: ({size=20, className=""}) => <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>,
        FileText: ({size=20, className=""}) => <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>,
        User: ({size=20, className=""}) => <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>,
        BookOpen: ({size=20, className=""}) => <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>,
        ExternalLink: ({size=20, className=""}) => <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>,
        Phone: ({size=20, className=""}) => <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>,
        Help: ({size=20, className=""}) => <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>,
        Microscope: ({size=20, className=""}) => <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}><path d="M6 18h8"/><path d="M3 22h18"/><path d="M14 22a7 7 0 1 0 0-14h-1"/><path d="M9 14h2"/><path d="M9 12a2 2 0 0 1-2-2V6h6v4a2 2 0 0 1-2 2Z"/><path d="M12 6V3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3"/></svg>,
    };

    const SECTIONS = [
        { id: 'tao_bon', title: 'Táo bón', description: 'Trẻ có dấu hiệu khó đi tiêu, phân cứng?', icon: '🧱', questions: ['2-3 ngày mới đi tiêu 1 lần', 'Phân cứng, dạng viên (phân dê)', 'Phải rặn mạnh, đỏ mặt', 'Sợ đi tiêu, tìm chỗ trốn', 'Bụng chướng, cứng khi sờ', 'Kích động trước khi đi vệ sinh', 'Ngủ kém vào những ngày khó đi'] },
        { id: 'tieu_chay', title: 'Tiêu chảy', description: 'Trẻ đi phân lỏng, đi nhiều lần?', icon: '💧', questions: ['Đi phân lỏng ≥ 2 lần/ngày', 'Phân có mùi nồng, rất khắm', 'Phân có bọt hoặc chất nhầy', 'Kêu đau bụng sau khi ăn', 'Tiêu chảy kéo dài > 3 ngày'] },
        { id: 'hap_thu_kem', title: 'Hấp thu kém', description: 'Trẻ ăn được nhưng không tăng cân?', icon: '📉', questions: ['Thức ăn còn nguyên trong phân', 'Phân nhạt màu, loãng, dính', 'Hay mệt mỏi dù ăn đủ', 'Khó tập trung, mắt lờ đờ', 'Dễ cáu gắt vào buổi chiều'] },
        { id: 'viem_ruot', title: 'Viêm ruột', description: 'Dấu hiệu đau, khó chịu mãn tính?', icon: '🔥', questions: ['Đau bụng âm ỉ (ôm bụng, nhăn nhó)', 'Hay cong người, nằm sấp đè bụng', 'Tính chất phân thay đổi liên tục', 'Hành vi xấu đi quanh lúc đi tiêu', 'Thức giấc giữa đêm khó ngủ lại', 'Biếng ăn những ngày bụng khó chịu', 'Gồng người, ném đồ không rõ lý do'] },
        { id: 'dysbiosis', title: 'Loạn khuẩn', description: 'Mất cân bằng vi sinh đường ruột?', icon: '🦠', questions: ['Xì hơi nhiều, mùi rất hôi', 'Bụng căng to sau khi ăn', 'Nhạy cảm quá mức với mùi/âm thanh', 'Khó ngồi yên, lo lắng, kích động', 'Thèm đồ ngọt dữ dội', 'Ngủ rất muộn hoặc ngủ chập chờn', 'Không dung nạp sữa động vật'] }
    ];

    const OPTIONS = [
        { value: 0, label: 'Không', color: 'bg-slate-100 text-slate-500 hover:bg-slate-200 border-slate-200' },
        { value: 1, label: 'Thỉnh thoảng', color: 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100 border-yellow-200' },
        { value: 2, label: 'Thường xuyên', color: 'bg-red-50 text-red-700 hover:bg-red-100 border-red-200' }
    ];

    // --- DATA BÀI VIẾT THAM KHẢO ---
    const ARTICLES = {
        green: {
            title: "Dinh dưỡng vàng cho hệ tiêu hóa khỏe mạnh của bé",
            desc: "Tìm hiểu cách duy trì hệ vi sinh đường ruột ổn định và phòng ngừa sớm các vấn đề tiêu hóa.",
            url: "#" 
        },
        yellow: {
            title: "Chiến lược 3 bước cải thiện rối loạn tiêu hóa tại nhà",
            desc: "Hướng dẫn chi tiết cách điều chỉnh thực đơn và thói quen sinh hoạt để giảm thiểu các triệu chứng.",
            url: "#"
        },
        red: {
            title: "Trục Não - Ruột & Chế độ ăn GFCF chuyên sâu",
            desc: "Phân tích mối liên hệ mật thiết giữa viêm ruột và hành vi, cùng lộ trình can thiệp dinh dưỡng GFCF.",
            url: "#"
        }
    };

    // --- BOTTOM STICKY BAR (Thay thế cho StickyHeader cũ) ---
    function BottomStickyBar({ completedCount, totalSections, risk }) {
        const progress = (completedCount / totalSections) * 100;
        return (
            <div className="fixed bottom-0 left-0 right-0 z-50 glass-effect border-t border-slate-200 shadow-[0_-4px_10px_rgba(0,0,0,0.05)] transition-all duration-300 no-print pb-safe">
                <div className="container mx-auto px-3 sm:px-4 h-16 flex items-center justify-between">
                    
                    {/* Progress Circle & Text (Mobile Optimized) */}
                    <div className="flex items-center gap-3 flex-1">
                        <div className="relative w-10 h-10 flex items-center justify-center">
                            <svg className="w-full h-full -rotate-90" viewBox="0 0 36 36">
                                <path className="text-slate-100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" strokeWidth="4" />
                                <path className="text-primary-500 transition-all duration-500" strokeDasharray={`${progress}, 100`} d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" strokeWidth="4" />
                            </svg>
                            <span className="absolute text-[10px] font-bold text-slate-600">{completedCount}/{totalSections}</span>
                        </div>
                        <div className="flex flex-col">
                            <span className="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Tiến độ</span>
                            <span className="text-xs font-bold text-slate-800">Đánh giá tiêu hóa</span>
                        </div>
                    </div>

                    {/* Risk Status Label */}
                    <div className={`flex items-center gap-2 px-3 py-1.5 rounded-full border ${risk.borderColor} ${risk.bg} transition-colors duration-500 shadow-sm`}>
                        <div className={`w-2 h-2 rounded-full ${risk.dotColor} animate-pulse`}></div>
                        <div className="flex flex-col items-start leading-none">
                            <span className="text-[8px] uppercase font-bold opacity-70 mb-0.5">Nguy cơ</span>
                            <span className={`text-xs font-bold ${risk.textColor}`}>{risk.label}</span>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    // --- GUIDE MODAL (POPUP HƯỚNG DẪN) ---
    function GuideModal({ onClose }) {
        return (
            <div className="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in no-print">
                <div className="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
                    <div className="p-5 border-b border-slate-100 flex justify-between items-center bg-primary-50">
                        <h3 className="text-lg font-extrabold text-slate-800 flex items-center gap-2">
                            <Icons.Help size={20} className="text-primary-600"/> Hướng dẫn sử dụng
                        </h3>
                        <button onClick={onClose} className="p-1.5 bg-white rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                            <Icons.X size={18} />
                        </button>
                    </div>
                    <div className="p-6 space-y-5 text-sm text-slate-600">
                        <div className="flex gap-4">
                            <div className="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-800 font-bold shrink-0">1</div>
                            <div>
                                <h4 className="font-bold text-slate-800 mb-1">Quan sát (3-5 ngày)</h4>
                                <p>Hãy dành thời gian quan sát kỹ các biểu hiện tiêu hóa, phân và hành vi ăn uống của bé trong vài ngày gần đây để có đánh giá chính xác nhất.</p>
                            </div>
                        </div>
                        <div className="flex gap-4">
                            <div className="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-800 font-bold shrink-0">2</div>
                            <div>
                                <h4 className="font-bold text-slate-800 mb-1">Đánh giá theo nhóm</h4>
                                <p>Đi lần lượt qua 5 nhóm vấn đề. Chọn <strong>"Có"</strong> nếu thấy dấu hiệu và đánh giá mức độ (Thỉnh thoảng/Thường xuyên). Chọn <strong>"Không"</strong> nếu bé bình thường.</p>
                            </div>
                        </div>
                        <div className="flex gap-4">
                            <div className="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-800 font-bold shrink-0">3</div>
                            <div>
                                <h4 className="font-bold text-slate-800 mb-1">Nhận kết quả & Lưu</h4>
                                <p>Hệ thống sẽ tổng hợp, tính điểm và đưa ra lời khuyên từ Trợ lý chuyên môn. Đừng quên điền tên và lưu kết quả về máy để theo dõi.</p>
                            </div>
                        </div>
                        <div className="bg-yellow-50 border border-yellow-100 p-3 rounded-lg text-xs text-yellow-800">
                            <strong>Lưu ý:</strong> Kết quả chỉ mang tính tham khảo. Sự trung thực khi đánh giá sẽ giúp kết quả phản ánh đúng thực trạng của bé.
                        </div>
                    </div>
                    <div className="p-4 bg-slate-50 border-t border-slate-100 text-center">
                        <button onClick={onClose} className="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-bold text-sm shadow-md transition-all">Đã hiểu, bắt đầu ngay</button>
                    </div>
                </div>
            </div>
        );
    }

    function SectionBlock({ section, answers, onAnswerChange, state, onStateChange }) {
        const isExpanded = state === 'yes';
        return (
            <div className={`group bg-white rounded-xl border transition-all duration-300 mb-3 overflow-hidden ${isExpanded ? 'border-primary-300 shadow-lg ring-2 ring-primary-50/50' : 'border-slate-100 shadow-card hover:shadow-md'}`}>
                <div className="p-3 sm:p-4 flex flex-col sm:flex-row gap-3 sm:items-center justify-between cursor-pointer select-none">
                    <div className="flex items-center gap-3">
                        <div className={`w-10 h-10 shrink-0 rounded-xl flex items-center justify-center text-xl transition-all duration-300 ${state === 'yes' ? 'bg-primary-100 text-primary-700' : state === 'no' ? 'bg-green-100 text-green-600' : 'bg-slate-100 text-slate-400'}`}>
                            {state === 'no' ? <Icons.Check size={20}/> : section.icon}
                        </div>
                        <div>
                            <h2 className="text-base font-bold text-slate-800 flex items-center gap-2">{section.title} {state === 'yes' && <span className="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-primary-50 text-primary-700 border border-primary-100 uppercase tracking-wider">Đang kiểm tra</span>}</h2>
                            <p className="text-xs text-slate-500 font-medium leading-tight">{section.description}</p>
                        </div>
                    </div>
                    <div className="flex p-0.5 bg-slate-100 rounded-lg shrink-0 self-start sm:self-center">
                        <button onClick={() => onStateChange(section.id, 'no')} className={`px-3 py-1.5 rounded-md text-xs font-bold transition-all duration-200 ${state === 'no' ? 'bg-white text-green-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'}`}>Không</button>
                        <button onClick={() => onStateChange(section.id, 'yes')} className={`px-3 py-1.5 rounded-md text-xs font-bold transition-all duration-200 ${state === 'yes' ? 'bg-white text-primary-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'}`}>Có</button>
                    </div>
                </div>
                {isExpanded && (
                    <div className="px-3 sm:px-4 pb-4 pt-0 animate-fade-in-up">
                        <div className="h-px w-full bg-slate-100 mb-3"></div>
                        <div className="space-y-1">
                            {section.questions.map((q, idx) => {
                                const currentVal = answers[`${section.id}_${idx}`];
                                return (
                                    <div key={idx} className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-2 rounded-lg hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
                                        <p className="text-sm font-medium text-slate-700 flex-1 leading-snug pl-1">{q}</p>
                                        <div className="flex gap-1.5 shrink-0 w-full sm:w-auto">
                                            {OPTIONS.map((opt) => (
                                                <button key={opt.value} onClick={() => onAnswerChange(section.id, idx, opt.value)} className={`flex-1 sm:flex-none px-2 py-1.5 rounded text-[10px] sm:text-xs font-bold border transition-all duration-200 ${currentVal === opt.value ? (opt.value===0?'bg-green-500 text-white border-green-500':opt.value===1?'bg-yellow-500 text-white border-yellow-500':'bg-red-500 text-white border-red-500') + ' shadow-md' : 'bg-white text-slate-500 border-slate-200 hover:border-primary-300'}`}>{opt.label}</button>
                                            ))}
                                        </div>
                                    </div>
                                )
                            })}
                        </div>
                    </div>
                )}
            </div>
        );
    }

    function ResultModal({ onClose, totalScore, risk, symptomsRaw, answers, sectionStates }) {
        const [aiResult, setAiResult] = useState(null);
        const [isThinking, setIsThinking] = useState(true);
        const [childName, setChildName] = useState("");
        const [parentName, setParentName] = useState("");
        const [phone, setPhone] = useState("");
        const [showError, setShowError] = useState(false);
        const contentRef = useRef(null);

        const article = ARTICLES[risk.theme] || ARTICLES.green; 

        const groupedResults = useMemo(() => {
            return SECTIONS.map(section => {
                if (sectionStates[section.id] !== 'yes') return { id: section.id, title: section.title, icon: section.icon, status: 'good', severe: [], moderate: [], normalCount: section.questions.length, isSkipped: true };
                const sectionAnswers = [];
                // Sửa lỗi tính điểm: Sử dụng trọng số điểm (0, 1, 2)
                section.questions.forEach((q, idx) => { 
                    const val = answers.hasOwnProperty(`${section.id}_${idx}`) ? answers[`${section.id}_${idx}`] : 0; 
                    sectionAnswers.push({ q, val }); 
                });
                const severe = sectionAnswers.filter(a => a.val === 2).map(a => a.q);
                const moderate = sectionAnswers.filter(a => a.val === 1).map(a => a.q);
                let status = 'good';
                if (severe.length > 0) status = 'critical';
                else if (moderate.length > 0) status = 'warning';
                return { id: section.id, title: section.title, icon: section.icon, status, severe, moderate, normalCount: sectionAnswers.filter(a => a.val === 0).length, isSkipped: false };
            });
        }, [answers, sectionStates]);

        useEffect(() => { setIsThinking(true); analyzeWithGemini(symptomsRaw, risk.label).then(res => { setAiResult(res); setIsThinking(false); }); }, []);

        const validate = () => {
            if (!parentName.trim() || !phone.trim()) {
                setShowError(true);
                alert("Vui lòng điền Tên phụ huynh và Số điện thoại để lưu kết quả!");
                return false;
            }
            return true;
        }

        const handlePrintPDF = () => {
            if (!validate()) return;
            const printContainer = document.getElementById('print-container');
            const modalContent = contentRef.current.innerHTML;
            printContainer.innerHTML = modalContent;
            
            const inputContainer = printContainer.querySelector('#input-container');
            if(inputContainer) {
                inputContainer.innerHTML = `
                    <div class="grid grid-cols-2 gap-4 text-white/90 text-sm mb-4 border-b border-white/20 pb-4">
                        <div><strong>Bé:</strong> ${childName || '...'}</div>
                        <div><strong>Phụ huynh:</strong> ${parentName}</div>
                        <div><strong>SĐT:</strong> ${phone}</div>
                        <div><strong>Ngày:</strong> ${new Date().toLocaleDateString('vi-VN')}</div>
                    </div>
                `;
            }
            window.print();
            printContainer.innerHTML = '';
        };

        const handleSaveImageFull = async () => {
            if (!validate()) return;
            const element = contentRef.current;
            if (!element) return;
            const clone = element.cloneNode(true);
            clone.style.position = 'fixed'; clone.style.top = '-10000px'; clone.style.left = '0';
            clone.style.width = '700px'; clone.style.height = 'auto'; clone.style.overflow = 'visible';
            clone.querySelector('.flex-1.overflow-y-auto').style.overflow = 'visible';
            clone.querySelector('.flex-1.overflow-y-auto').style.height = 'auto';
            
            const inputContainer = clone.querySelector('#input-container');
            if(inputContainer) {
                inputContainer.innerHTML = `
                    <div class="grid grid-cols-2 gap-4 text-white/90 text-sm mb-4 bg-white/10 p-3 rounded-xl border border-white/20">
                        <div><strong>Bé:</strong> ${childName || '...'}</div>
                        <div><strong>Phụ huynh:</strong> ${parentName}</div>
                        <div><strong>SĐT:</strong> ${phone}</div>
                        <div><strong>Ngày:</strong> ${new Date().toLocaleDateString('vi-VN')}</div>
                    </div>
                `;
            }
            
            const footer = clone.querySelector('#modal-footer'); if(footer) footer.style.display = 'none';
            const close = clone.querySelector('#modal-close-btn'); if(close) close.style.display = 'none';
            document.body.appendChild(clone);
            try {
                const canvas = await html2canvas(clone, { scale: 2, useCORS: true, backgroundColor: '#ffffff', windowHeight: clone.scrollHeight + 100 });
                const link = document.createElement('a'); link.href = canvas.toDataURL("image/png");
                link.download = `Ket-Qua-Checklist-${childName ? childName.replace(/\s+/g, '-') : 'Be'}-${new Date().toISOString().slice(0,10)}.png`;
                link.click();
            } catch (err) { alert("Lỗi lưu ảnh."); } finally { document.body.removeChild(clone); }
        };

        const headerGradient = risk.theme === 'red' ? 'from-red-500 to-red-700' : risk.theme === 'yellow' ? 'from-yellow-400 to-orange-500' : 'from-green-500 to-green-700';

        return (
            <div className="fixed inset-0 z-[60] flex items-center justify-center p-3 bg-slate-900/60 backdrop-blur-md animate-fade-in no-print">
                <div ref={contentRef} className="bg-white w-full max-w-2xl max-h-[95vh] rounded-2xl shadow-2xl overflow-hidden flex flex-col relative">
                    <div className={`bg-gradient-to-r ${headerGradient} p-6 text-white shrink-0 relative overflow-hidden transition-colors duration-500`}>
                        <div className="absolute top-0 right-0 p-4 opacity-10 transform translate-x-4 -translate-y-4"><Icons.Stomach size={120} /></div>
                        <div className="relative z-10">
                            <div className="flex justify-between items-start mb-4">
                                <div><h2 className="text-2xl font-extrabold mb-0.5">Kết Quả Checklist</h2><p className="text-white/80 text-xs font-medium">Đánh giá các dấu hiệu Tiêu hoá & Trục Não Ruột</p></div>
                                <button id="modal-close-btn" onClick={onClose} className="bg-white/10 hover:bg-white/20 p-2 rounded-full transition-colors backdrop-blur-md"><Icons.X size={18} /></button>
                            </div>
                            
                            <div id="input-container" className={`bg-white/10 p-3 rounded-xl backdrop-blur-sm border border-white/20 transition-all ${showError ? 'ring-2 ring-red-300 animate-shake' : ''}`}>
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div className="col-span-1 sm:col-span-2">
                                        <div className="flex items-center gap-2 text-white/80 text-[10px] mb-1"><Icons.User size={12}/> <span>Tên bé (Tuỳ chọn)</span></div>
                                        <input type="text" placeholder="Nhập tên bé..." value={childName} onChange={(e)=>setChildName(e.target.value)} className="w-full bg-transparent border-b border-white/30 text-white placeholder-white/50 focus:outline-none focus:border-white py-1 text-sm font-bold"/>
                                    </div>
                                    <div>
                                        <div className="flex items-center gap-2 text-white/80 text-[10px] mb-1"><Icons.User size={12}/> <span>Tên phụ huynh <span className="text-red-300">*</span></span></div>
                                        <input type="text" placeholder="Nhập tên mẹ/bố..." value={parentName} onChange={(e)=>{setParentName(e.target.value); setShowError(false)}} className="w-full bg-transparent border-b border-white/30 text-white placeholder-white/50 focus:outline-none focus:border-white py-1 text-sm"/>
                                    </div>
                                    <div>
                                        <div className="flex items-center gap-2 text-white/80 text-[10px] mb-1"><Icons.Phone size={12}/> <span>Số điện thoại <span className="text-red-300">*</span></span></div>
                                        <input type="tel" placeholder="09xxxxxxxx..." value={phone} onChange={(e)=>{setPhone(e.target.value); setShowError(false)}} className="w-full bg-transparent border-b border-white/30 text-white placeholder-white/50 focus:outline-none focus:border-white py-1 text-sm"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="flex-1 overflow-y-auto p-6 space-y-5 custom-scrollbar bg-slate-50">
                        {/* Score Summary */}
                        <div className="flex items-center gap-4 bg-white p-4 rounded-xl shadow-sm border border-slate-100">
                            <div className={`w-12 h-12 rounded-xl flex items-center justify-center text-2xl font-black ${risk.theme === 'red' ? 'bg-red-50 text-red-600' : risk.theme === 'yellow' ? 'bg-yellow-50 text-yellow-600' : 'bg-green-50 text-green-600'}`}>{totalScore}</div>
                            <div>
                                <p className="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Mức độ nguy cơ</p>
                                <div className={`inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-sm font-extrabold ${risk.label === 'CAO' ? 'bg-red-50 text-red-600' : risk.label === 'TRUNG BÌNH' ? 'bg-yellow-50 text-yellow-600' : 'bg-green-50 text-green-600'}`}>{risk.label}</div>
                            </div>
                        </div>

                        {/* Details */}
                        <section>
                            <h3 className="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2"><Icons.Stomach size={14}/> Chi tiết các dấu hiệu</h3>
                            <div className="grid gap-3">
                                {groupedResults.map(grp => {
                                    if(grp.status === 'good') return <div key={grp.id} className="flex items-center justify-between px-4 py-3 bg-white rounded-xl border border-slate-100 shadow-sm"><div className="flex items-center gap-3"><span className="text-xl">{grp.icon}</span><span className="font-bold text-slate-700 text-sm">{grp.title}</span></div><span className="text-[9px] font-bold text-green-600 bg-green-50 px-2 py-1 rounded border border-green-100">BÌNH THƯỜNG</span></div>;
                                    const isCrit = grp.status === 'critical';
                                    return <div key={grp.id} className={`bg-white rounded-xl border shadow-sm p-4 relative overflow-hidden ${isCrit ? 'border-red-100' : 'border-yellow-100'}`}><div className={`absolute left-0 top-0 w-1 h-full ${isCrit ? 'bg-red-500' : 'bg-yellow-500'}`}></div><div className="flex items-center gap-2 mb-2"><span className="text-lg">{grp.icon}</span><span className={`font-bold text-sm ${isCrit ? 'text-red-700' : 'text-yellow-700'}`}>{grp.title}</span></div><div className="space-y-1.5 pl-1">{grp.severe.map((s,i)=><div key={'s'+i} className="flex gap-2 text-xs"><span className="mt-1 w-1.5 h-1.5 rounded-full bg-red-500 shrink-0"></span><span className="text-slate-700 font-medium">{s}</span></div>)}{grp.moderate.map((s,i)=><div key={'m'+i} className="flex gap-2 text-xs"><span className="mt-1 w-1.5 h-1.5 rounded-full bg-yellow-500 shrink-0"></span><span className="text-slate-600 font-medium">{s}</span></div>)}</div></div>;
                                })}
                            </div>
                        </section>

                        {/* AI Advisor - Trợ Lý Chuyên Môn */}
                        <section className="bg-gradient-to-br from-primary-50 to-white rounded-xl border border-primary-100 shadow-soft p-5 relative">
                            <h3 className="text-primary-800 font-bold text-sm mb-4 flex items-center gap-2"><Icons.Sparkles size={16} className={isThinking ? "animate-spin text-primary-500" : "text-primary-500"} /> {isThinking ? "Đang phân tích..." : "Trợ Lý Chuyên Môn"}</h3>
                            <div className="space-y-4">
                                <div className="bg-white/80 p-3 rounded-lg border border-primary-50"><strong className="text-slate-400 text-[9px] uppercase tracking-wider block mb-1">Đánh giá tổng quan</strong><p className="text-slate-700 text-xs leading-relaxed font-medium">{aiResult ? aiResult.summary : <span className="block h-10 bg-slate-200/50 rounded animate-pulse"></span>}</p></div>
                                <div className="bg-white/80 p-3 rounded-lg border border-primary-50"><strong className="text-slate-400 text-[9px] uppercase tracking-wider block mb-1">Lời khuyên hành động</strong><p className="text-slate-700 text-xs leading-relaxed font-medium">{aiResult ? aiResult.action : <span className="block h-10 bg-slate-200/50 rounded animate-pulse"></span>}</p></div>
                            </div>
                        </section>

                        {/* Reference Articles - Góc Kiến Thức */}
                        <section className="mt-6 pt-6 border-t border-slate-100">
                            <h3 className="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                <Icons.BookOpen size={14}/> Góc Kiến Thức
                            </h3>
                            <div className="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-primary-300 hover:shadow-md transition-all cursor-pointer group">
                                <div className="flex items-start justify-between">
                                    <div>
                                        <h4 className="text-sm font-bold text-slate-800 mb-1 group-hover:text-primary-700 transition-colors">{article.title}</h4>
                                        <p className="text-xs text-slate-500 line-clamp-2">{article.desc}</p>
                                    </div>
                                    <div className="p-2 bg-slate-50 rounded-lg text-slate-400 group-hover:bg-primary-50 group-hover:text-primary-600 transition-colors">
                                        <Icons.ExternalLink size={16} />
                                    </div>
                                </div>
                            </div>
                        </section>

                        {/* Disclaimer */}
                        <div className="bg-slate-50 p-3 rounded-lg border border-slate-100 mt-4">
                            <p className="text-[9px] text-slate-400 text-justify leading-relaxed">
                                <strong className="block text-slate-500 mb-1 uppercase">Lưu ý quan trọng từ Dawn Bridge Autism Tools:</strong>
                                Kết quả từ công cụ này chỉ mang tính chất tham khảo và hỗ trợ sàng lọc sơ bộ dựa trên thông tin bạn cung cấp. Đây không phải là chẩn đoán y khoa chính thức. Vui lòng luôn tham khảo ý kiến của bác sĩ hoặc chuyên gia y tế trước khi áp dụng bất kỳ thay đổi lớn nào về chế độ dinh dưỡng hoặc điều trị cho trẻ.
                            </p>
                        </div>
                        
                        <div className="text-center pt-2"><p className="text-[9px] text-slate-300">© DawnBridge Autism Care</p></div>
                    </div>

                    <div id="modal-footer" className="p-4 bg-white border-t border-slate-100 flex flex-col sm:flex-row gap-3 justify-end shadow-[0_-5px_20px_-5px_rgba(0,0,0,0.05)] z-20">
                        <button onClick={handlePrintPDF} className="flex-1 sm:flex-none flex items-center justify-center gap-2 px-5 py-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl font-bold text-xs transition-all"><Icons.FileText size={16} /> Lưu PDF (In)</button>
                        <button onClick={handleSaveImageFull} className="flex-1 sm:flex-none flex items-center justify-center gap-2 px-6 py-3 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-bold text-xs transition-all shadow-lg hover:shadow-slate-800/20"><Icons.Download size={16} /> Lưu Ảnh</button>
                    </div>
                </div>
            </div>
        );
    }

    function ArticleSection() {
        return (
            <article className="container mx-auto px-4 py-12 max-w-3xl prose prose-slate prose-sm sm:prose-base lg:prose-lg text-slate-600 border-t border-slate-200 mt-8 mb-20">
                <h1 className="text-2xl sm:text-3xl font-bold text-slate-800 mb-6">Giải Mã Checklist Tiêu Hóa & Trục Não Ruột: Cơ Sở Khoa Học & Lộ Trình Cải Thiện Cho Trẻ</h1>
                
                <h2 className="text-xl font-bold text-slate-800 mt-8 mb-4">1. Tại sao cần Checklist này? Vấn đề thầm lặng ở trẻ đặc biệt</h2>
                <p className="mb-4 leading-relaxed">
                    Nhiều phụ huynh thường tập trung vào can thiệp hành vi và ngôn ngữ cho trẻ mà vô tình bỏ qua một "người hùng thầm lặng": <strong>Hệ tiêu hóa</strong>.
                </p>
                <p className="mb-4 leading-relaxed">
                    Thực tế, trẻ nhỏ, đặc biệt là trẻ có Rối loạn Phổ Tự Kỷ (ASD) hoặc Rối loạn Tăng động Giảm chú ý (ADHD), thường gặp khó khăn trong việc diễn đạt cơn đau hoặc sự khó chịu của cơ thể. Thay vì nói "Con đau bụng", trẻ có thể biểu hiện bằng cách <strong>đập đầu, ăn vạ, la hét, mất ngủ hoặc tự làm đau bản thân</strong>.
                </p>
                <div className="bg-primary-50 border-l-4 border-primary-500 p-4 rounded-r-lg mb-6 italic text-slate-700">
                    <strong>Thống kê đáng chú ý:</strong> Các nghiên cứu chỉ ra rằng trẻ tự kỷ có nguy cơ gặp các vấn đề tiêu hóa cao gấp <strong>3.5 lần</strong> so với trẻ phát triển điển hình, nhưng thường bị bỏ sót chẩn đoán [1].
                </div>

                <h2 className="text-xl font-bold text-slate-800 mt-8 mb-4">2. Cơ sở khoa học xây dựng Checklist</h2>
                <p className="mb-4 leading-relaxed">Bộ công cụ này được xây dựng dựa trên sự tổng hợp từ các tiêu chuẩn y khoa quốc tế:</p>
                <ul className="list-disc pl-5 mb-4 space-y-2">
                    <li><strong>Tiêu chuẩn ROME IV:</strong> "Bộ luật" quốc tế để chẩn đoán các rối loạn tiêu hóa chức năng ở trẻ em [2].</li>
                    <li><strong>Thang điểm Bristol:</strong> Giúp phân loại chính xác tình trạng phân (táo bón, tiêu chảy) để đưa ra gợi ý dinh dưỡng.</li>
                    <li><strong>Nghiên cứu của Adams et al. (2011):</strong> Chứng minh mối tương quan mạnh mẽ giữa độ nặng của triệu chứng tiêu hóa và mức độ nghiêm trọng của hành vi tự kỷ [3].</li>
                </ul>

                <h2 className="text-xl font-bold text-slate-800 mt-8 mb-4">3. Trục Não - Ruột: Chìa khóa giải mã hành vi</h2>
                <div className="bg-slate-100 rounded-xl p-8 mb-6 flex flex-col items-center justify-center text-center border border-slate-200">
                    <img 
                        src="https://tools.dawnbridge.vn/wp-content/uploads/2025/12/hoi_chung_ruot_kichthichs_306b3627b8.png" 
                        alt="Sơ đồ minh họa Trục Não - Ruột" 
                        className="w-full h-auto object-cover rounded-xl shadow-md mb-6"
                        onError={(e) => {
                            e.target.onerror = null; 
                            e.target.style.display = 'none';
                            e.target.nextSibling.style.display = 'flex'; // Show fallback
                        }}
                    />
                    <div className="hidden bg-slate-100 p-8 flex-col items-center justify-center text-center">
                         <div className="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-3 shadow-sm text-primary-500"><Icons.Stomach size={32}/></div>
                         <p className="text-sm font-bold text-slate-500 uppercase tracking-widest">Sơ đồ minh họa Trục Não - Ruột</p>
                    </div>
                    <p className="text-xs text-slate-400 mt-1">Minh họa mối liên hệ mật thiết giữa hệ tiêu hóa và não bộ</p>
                </div>
                <p className="mb-4 leading-relaxed">
                    Đây là con đường liên lạc hai chiều. 95% Serotonin (hormone điều chỉnh tâm trạng) được sản xuất tại ruột. Khi đường ruột bị viêm, độc tố có thể xâm nhập vào máu (Rò rỉ ruột), gây "sương mù não", giảm khả năng tập trung và kiểm soát cảm xúc của trẻ.
                </p>

                <h2 className="text-xl font-bold text-slate-800 mt-8 mb-4">4. Các bước tiếp theo sau khi nhận kết quả</h2>
                <ol className="list-decimal pl-5 mb-4 space-y-3">
                    <li><strong>Lưu trữ và Theo dõi:</strong> Sử dụng kết quả này như một "nhật ký sức khỏe". Thực hiện lại sau mỗi 2 tuần.</li>
                    <li><strong>Điều chỉnh dinh dưỡng:</strong>
                        <ul className="list-disc pl-5 mt-2 space-y-1 text-sm">
                            <li>Nếu Táo bón: Tăng chất xơ hòa tan (mồng tơi, khoai lang), nước.</li>
                            <li>Nếu Viêm ruột/Loạn khuẩn: Cân nhắc chế độ ăn GFCF (Không Gluten/Casein) dưới sự hướng dẫn chuyên gia [4].</li>
                        </ul>
                    </li>
                    <li><strong>Thăm khám chuyên sâu:</strong> Mang kết quả PDF này đến gặp bác sĩ để tiết kiệm thời gian khai thác bệnh sử.</li>
                </ol>

                <div className="mt-8 pt-6 border-t border-slate-200 text-xs text-slate-500">
                    <h3 className="font-bold text-slate-700 mb-2">Tài liệu tham khảo:</h3>
                    <ul className="space-y-1">
                        <li>[1] Chaidez et al. (2014). Journal of Autism and Developmental Disorders.</li>
                        <li>[2] Drossman, D. A. (2016). Gastroenterology.</li>
                        <li>[3] Adams, J. B., et al. (2011). BMC Gastroenterology.</li>
                        <li>[4] Ly et al. (2017). Nutrients.</li>
                    </ul>
                </div>
            </article>
        );
    }

    function App() {
        const [answers, setAnswers] = useState({});
        const [sectionStates, setSectionStates] = useState({});
        const [showModal, setShowModal] = useState(false);
        const [showGuide, setShowGuide] = useState(false); // State for Guide Popup

        const handleOptionSelect = (sId, qIdx, val) => setAnswers(prev => ({ ...prev, [`${sId}_${qIdx}`]: val }));
        const handleSectionStateChange = (sId, val) => { setSectionStates(prev => ({ ...prev, [sId]: val })); if (val !== 'yes') setAnswers(prev => { const next = { ...prev }; Object.keys(next).forEach(key => { if (key.startsWith(`${sId}_`)) delete next[key]; }); return next; }); };
        
        // 1. Tính tổng điểm dựa trên TRỌNG SỐ (0, 1, 2) thay vì chỉ đếm số lượng
        const sectionScores = useMemo(() => { 
            const scores = {}; 
            SECTIONS.forEach(s => { 
                if (sectionStates[s.id] !== 'yes') { scores[s.id] = 0; return; } 
                let score = 0;
                s.questions.forEach((_, idx) => { 
                    // Cộng dồn điểm: 0, 1 hoặc 2
                    score += (answers[`${s.id}_${idx}`] || 0); 
                }); 
                scores[s.id] = score; 
            }); 
            return scores; 
        }, [answers, sectionStates]);

        const totalScore = Object.values(sectionScores).reduce((a, b) => a + b, 0);
        
        // 2. Logic "Cờ Đỏ" + Cập nhật Theme màu
        const riskLevel = useMemo(() => { 
            // Kiểm tra xem có nhóm nào bị nặng (điểm >= 10) không
            const hasSevereSection = Object.values(sectionScores).some(s => s >= 10);

            if (totalScore >= 15 || hasSevereSection) {
                return { label: 'CAO', borderColor: 'border-red-200', bg: 'bg-red-50', textColor: 'text-red-700', dotColor: 'bg-red-500', theme: 'red' }; 
            }
            if (totalScore >= 6) {
                return { label: 'TRUNG BÌNH', borderColor: 'border-yellow-200', bg: 'bg-yellow-50', textColor: 'text-yellow-700', dotColor: 'bg-yellow-500', theme: 'yellow' }; 
            }
            return { label: 'THẤP', borderColor: 'border-green-200', bg: 'bg-green-50', textColor: 'text-green-700', dotColor: 'bg-green-500', theme: 'green' }; 
        }, [totalScore, sectionScores]);
        
        const symptomsRaw = useMemo(() => { const obs = []; SECTIONS.forEach(s => { if(sectionStates[s.id] !== 'yes') { obs.push(`- Nhóm ${s.title}: Bình thường.`); } else { s.questions.forEach((q, idx) => { const val = answers[`${s.id}_${idx}`]; if (val !== undefined) obs.push(`- ${q}: ${OPTIONS.find(o => o.value === val)?.label}`); }); } }); return obs; }, [answers, sectionStates]);
        const isChecklistComplete = SECTIONS.every(s => sectionStates[s.id]);
        const restart = () => { if(confirm('Làm lại?')) { setAnswers({}); setSectionStates({}); window.scrollTo(0, 0); } };

        return (
            <div className="font-sans min-h-screen bg-slate-50 selection:bg-primary-100 selection:text-primary-900 pb-safe">
                <div className="container mx-auto px-4 pt-4 pb-2 max-w-3xl text-center">
                    <h2 className="text-lg sm:text-2xl font-extrabold text-slate-800 mb-1">Checklist Sức Khoẻ <span className="text-transparent bg-clip-text bg-gradient-to-r from-primary-500 to-primary-700">Tiêu Hoá</span></h2>
                    <p className="text-slate-500 text-xs sm:text-sm font-medium mb-3">Công cụ rà soát dấu hiệu tiêu hóa & Trục Não - Ruột.</p>
                    <button onClick={() => setShowGuide(true)} className="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-primary-50 text-primary-700 text-xs font-bold hover:bg-primary-100 transition-colors border border-primary-100">
                        <Icons.Help size={14}/> Hướng dẫn sử dụng
                    </button>
                </div>
                <div className="container mx-auto px-2 sm:px-4 max-w-3xl mt-4">{SECTIONS.map(section => <SectionBlock key={section.id} section={section} answers={answers} onAnswerChange={handleOptionSelect} state={sectionStates[section.id]} onStateChange={handleSectionStateChange} />)}<div className="transition-all duration-500 ease-in-out">{isChecklistComplete ? <div className="mt-4 p-4 bg-white rounded-xl border border-primary-100 shadow-soft text-center animate-fade-in-up"><div className="w-10 h-10 bg-primary-50 rounded-full flex items-center justify-center mx-auto mb-2 text-primary-500 animate-pulse-slow shadow-sm"><Icons.Check size={20} /></div><h3 className="text-sm font-extrabold text-slate-800 mb-1">Đã hoàn tất!</h3><div className="flex justify-center gap-3"><button onClick={restart} className="px-4 py-2 rounded-lg font-bold text-slate-500 hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-all flex items-center justify-center gap-1.5 text-xs"><Icons.Refresh size={14} /> Làm lại</button><button onClick={() => setShowModal(true)} className="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg font-bold flex items-center justify-center gap-2 shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all text-xs"><Icons.Eye size={16} /> Xem Kết Quả</button></div></div> : <div className="mt-8 text-center pb-8 opacity-50"><p className="text-xs font-bold text-slate-400 italic flex items-center justify-center gap-1.5"><span className="w-1.5 h-1.5 rounded-full bg-slate-300 animate-pulse"></span> Vui lòng hoàn thành 5 nhóm</p></div>}</div></div>
                
                {/* Article Section */}
                <ArticleSection />

                <div className="container mx-auto px-4 text-center mt-6 mb-20 opacity-40 hover:opacity-100 transition-opacity"><p className="text-[9px] text-slate-400">DawnBridge © 2024. Sản phẩm hỗ trợ thông tin, không thay thế thuốc.</p></div>
                
                {/* Bottom Sticky Bar - Thay thế StickyHeader */}
                <BottomStickyBar completedCount={Object.keys(sectionStates).length} totalSections={SECTIONS.length} risk={riskLevel} />

                {/* Guide Modal */}
                {showGuide && <GuideModal onClose={() => setShowGuide(false)} />}
                
                {/* Result Modal */}
                {showModal && <ResultModal onClose={() => setShowModal(false)} totalScore={totalScore} risk={riskLevel} symptomsRaw={symptomsRaw} answers={answers} sectionStates={sectionStates} />}
            </div>
        );
    }
    const root = ReactDOM.createRoot(document.getElementById('root'));
    root.render(<App />);
</script>
</body>
</html>
<?php
// 404.php

// 1. เช็คว่าเป็น AJAX (ระบบเรียก) หรือไม่
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

// 2. เช็คว่าเป็นการเรียกหาไฟล์ JSON หรือไม่ (บางครั้ง Browser จะส่ง Accept header มา)
$is_json_request = (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

// ถ้าเป็น AJAX หรือ ระบบเรียกมาจริงๆ ค่อยพ่น JSON
if ($is_ajax || $is_json_request) {
    header("HTTP/1.1 404 Not Found");
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'error',
        'message' => '404 Not Found: ไม่พบข้อมูลหรือเซสชันหมดอายุ'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ถ้าไม่ใช่ (คือคนเปิดผ่าน Browser ตรงๆ) ให้ไหลลงไปแสดง HTML ด้านล่าง
header("HTTP/1.1 404 Not Found");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; }
        .text-glow { text-shadow: 0 0 40px rgba(124, 58, 237, 0.5); }
        .animate-float { animation: float 6s ease-in-out infinite; }
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
    </style>
</head>
<body class="bg-[#0b1120] text-white min-h-screen flex items-center justify-center overflow-hidden relative selection:bg-purple-500/30">

    <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-purple-600/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 pointer-events-none"></div>

    <div class="container mx-auto px-4 relative z-10 text-center">
        
        <div class="relative inline-block mb-6">
            <h1 class="text-[150px] md:text-[220px] font-black leading-none text-slate-800/50 select-none tracking-tighter">404</h1>
            <div class="absolute inset-0 flex items-center justify-center animate-float">
                <div class="relative">
                    <div class="absolute inset-0 bg-purple-500 blur-2xl opacity-30 rounded-full"></div>
                    <i class="fa-solid fa-user-astronaut text-8xl md:text-9xl bg-gradient-to-br from-blue-400 to-purple-500 bg-clip-text text-transparent drop-shadow-2xl"></i>
                </div>
            </div>
        </div>

        <div class="space-y-4 max-w-lg mx-auto mb-10">
            <h2 class="text-3xl md:text-5xl font-bold text-white tracking-wide">
                หลงทางในอวกาศ?
            </h2>
            <p class="text-slate-400 text-lg font-light leading-relaxed">
                ดูเหมือนหน้าที่คุณกำลังตามหาจะหายไปในหลุมดำ <br class="hidden md:block">
                หรือถูกย้ายพิกัดไปแล้ว
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <a href="/" class="group relative px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl font-bold text-white shadow-lg shadow-purple-500/30 hover:scale-105 hover:shadow-purple-500/50 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-white/20 group-hover:translate-x-full transition-transform duration-500 -skew-x-12 -translate-x-full"></div>
                <span class="relative flex items-center gap-2">
                    <i class="fa-solid fa-shuttle-space"></i> กลับยานแม่ (หน้าแรก)
                </span>
            </a>
            
            <button onclick="history.back()" class="px-8 py-3 bg-slate-800 border border-slate-700 rounded-xl font-semibold text-slate-300 hover:text-white hover:bg-slate-700 hover:border-slate-600 transition-all duration-300 flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> ย้อนกลับ
            </button>
        </div>

        <div class="mt-16 text-xs text-slate-600 font-mono">
            Error Code: 404_PAGE_NOT_FOUND
        </div>

    </div>

</body>
</html>
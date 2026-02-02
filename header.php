<?php
// header.php
if(!isset($pdo)) { require_once 'db.php'; }
$popup = false;
$stat_sold = 0;
$stat_users = 0;
$stat_stock = 0;

if (isset($pdo)) {
    // 1.1 ดึง Popup (สุ่ม 1 อัน)
    $stmt_pop = $pdo->prepare("SELECT * FROM popups WHERE popup_enable = 1 ORDER BY RAND() LIMIT 1");
    $stmt_pop->execute();
    $popup = $stmt_pop->fetch(PDO::FETCH_OBJ);

    // 1.2 ดึงสถิติ (Stats)
    // แก้ในส่วน try { ... } ของ header.php
try {
    // 1. ยอดขาย (นับจาก stocks ที่ขายแล้ว)
    $q_sold = $pdo->prepare("SELECT COUNT(*) FROM stocks WHERE is_sold = '1'"); 
    $q_sold->execute();
    $stat_sold = $q_sold->fetchColumn();

    // 2. สมาชิก
    $q_users = $pdo->prepare("SELECT COUNT(*) FROM users");
    $q_users->execute();
    $stat_users = $q_users->fetchColumn();

    // 3. สต็อกคงเหลือ (นับจาก stocks ที่ยังไม่ขาย)
    $q_stock = $pdo->prepare("SELECT COUNT(*) FROM stocks WHERE is_sold = '0'");
    $q_stock->execute();
    $stat_stock = $q_stock->fetchColumn();
    } catch (Exception $e) {
        echo "DB Error: " . $e->getMessage(); // สั่งให้โชว์ Error ออกมา
        exit; // หยุดการทำงานเพื่อดู Error
    }
}

// --- 1. จัดการ Data & Config พื้นฐาน ---
$bg_list = json_decode($web_config->background_list ?? '[]', true);
if (!$bg_list && !empty($web_config->background_img)) {
    $bg_list = [$web_config->background_img];
}
$emojis = isset($web_config->floating_emojis) ? array_filter(explode(',', $web_config->floating_emojis)) : [];

// สีหลักเดิมของเว็บ
$original_site_color = ($web_config->site_color == "red") ? "#ef4444" : (($web_config->site_color == "blue") ? "#3b82f6" : "#8b5cf6");
$site_main_color = $original_site_color;

// --- Config เพิ่มเติมสำหรับการปรับแต่ง (ถ้ามีใน DB ให้ดึงมาใช้แทนตรงนี้) ---
// ตัวอย่าง: $glass_level = $web_config->glass_opacity ?? 'blur-md';
$glass_effect = true; // เปิด/ปิด เอฟเฟกต์กระจก
$show_particles = true; // เปิด/ปิด เอฟเฟกต์ลอยๆ

// --- 2. ระบบตรวจสอบเทศกาล ---
$d = date('d');
$m = date('m');

$season = [
    'type' => 'normal',
    'color' => $original_site_color,
    'icon' => 'fa-gamepad',
    'title' => 'WELCOME',
    'sub' => 'Welcome to ' . $web_config->site_name,
    'effect' => 'normal' 
];

// Logic ตรวจสอบเทศกาล
if ($m == 12 && $d >= 20 || $m == 1 && $d <= 5) { // 🎄 ปีใหม่
    $season = [
        'type' => 'newyear',
        'color' => '#fbbf24',
        'icon' => 'fa-champagne-glasses',
        'title' => 'HAPPY NEW YEAR',
        'sub' => 'Wishing you happiness!',
        'effect' => ($d == 31 || $d == 1) ? 'fireworks' : 'snow'
    ];
} elseif ($m == 2 && $d >= 10 && $d <= 14) { // 🌹 วาเลนไทน์
    $season = [
        'type' => 'valentine',
        'color' => '#f43f5e',
        'icon' => 'fa-heart',
        'title' => 'HAPPY VALENTINE',
        'sub' => 'Love is in the air...',
        'effect' => 'hearts'
    ];
} elseif ($m == 4 && $d >= 10 && $d <= 16) { // 💦 สงกรานต์
    $season = [
        'type' => 'songkran',
        'color' => '#0ea5e9',
        'icon' => 'fa-water',
        'title' => 'HAPPY SONGKRAN',
        'sub' => 'Splash functionality loading...',
        'effect' => 'normal'
    ];
} elseif ($m == 10 && $d >= 25) { // 👻 ฮาโลวีน
    $season = [
        'type' => 'halloween',
        'color' => '#f97316',
        'icon' => 'fa-ghost',
        'title' => 'TRICK OR TREAT',
        'sub' => 'Spooky loading...',
        'effect' => 'spooky'
    ];
} elseif ($m == 11) { // 🌕 ลอยกระทง
    $season = [
        'type' => 'loykrathong',
        'color' => '#facc15',
        'icon' => 'fa-dharmachakra', 
        'title' => 'LOY KRATHONG',
        'sub' => 'Full moon loading...',
        'effect' => 'normal'
    ];
}

if ($season['type'] !== 'normal') {
    $site_main_color = $season['color'];
}

// Convert Hex to RGB for Tailwind Opacity
list($r, $g, $b) = sscanf($site_main_color, "#%02x%02x%02x");
$site_main_rgb = "$r $g $b";

?>
<!DOCTYPE html>
<html lang="th" class="scroll-smooth">
<head>
    <?php
// เชื่อมต่อฐานข้อมูล (ถ้าในไฟล์ header.php มีการเชื่อมต่อแล้ว ไม่ต้องใส่บรรทัดนี้)
require_once 'db.php'; 

// ดึงข้อมูลตั้งค่าจาก Database
if (!isset($config)) {
    $stmt = $pdo->prepare("SELECT * FROM settings LIMIT 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 1. จัดการ URL ของหน้าปัจจุบัน (เพื่อให้เวลาแชร์ ลิงก์จะตรงกับหน้าที่เปิดอยู่)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$current_url = "$protocol://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// 2. จัดการรูปภาพ Preview (เลือกรูปสไลด์แรกก่อน ถ้าไม่มีค่อยใช้โลโก้)
// ต้องถอดรหัส JSON จาก banner_img ก่อน
$banners = json_decode($config['banner_img'], true);
$og_image = (!empty($banners) && isset($banners[0])) ? $banners[0] : $config['site_logo'];

// ถ้า URL รูปเป็นแบบ relative (เช่น /uploads/...) ให้เติม domain เข้าไป
if (strpos($og_image, 'http') === false) {
    $og_image = "$protocol://$_SERVER[HTTP_HOST]/" . ltrim($og_image, '/');
}
?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?php echo htmlspecialchars($config['site_name']); ?> | <?php echo htmlspecialchars($config['site_description']); ?></title>
<meta name="description" content="<?php echo htmlspecialchars($config['site_description']); ?>">
<meta name="keywords" content="เติมเกม, ร้านค้าออนไลน์, <?php echo htmlspecialchars($config['site_name']); ?>">
<meta name="author" content="<?php echo htmlspecialchars($config['site_name']); ?>">

<meta property="og:type" content="website">
<meta property="og:url" content="<?php echo $current_url; ?>">
<meta property="og:title" content="<?php echo htmlspecialchars($config['site_name']); ?>">
<meta property="og:description" content="<?php echo htmlspecialchars($config['site_description']); ?>">
<meta property="og:image" content="<?php echo $og_image; ?>">
<meta property="og:site_name" content="<?php echo htmlspecialchars($config['site_name']); ?>">
<meta property="og:locale" content="th_TH">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="<?php echo $current_url; ?>">
<meta name="twitter:title" content="<?php echo htmlspecialchars($config['site_name']); ?>">
<meta name="twitter:description" content="<?php echo htmlspecialchars($config['site_description']); ?>">
<meta name="twitter:image" content="<?php echo $og_image; ?>">

<link rel="icon" href="<?php echo $config['site_logo']; ?>" type="image/x-icon">
<link rel="shortcut icon" href="<?php echo $config['site_logo']; ?>" type="image/x-icon">

<meta name="theme-color" content="#6366f1">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $web_config->site_name; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Kanit', 'sans-serif'] },
                    colors: {
                        theme: {
                            main: 'rgb(<?php echo $site_main_rgb; ?>)', // ใช้ RGB เพื่อรองรับ opacity class (เช่น bg-theme-main/50)
                            hover: 'rgb(<?php echo $site_main_rgb; ?> / 0.8)',
                            dark: '#0f172a',
                            surface: '#1e293b',
                        }
                    },
                    animation: {
                        'float-slow': 'float 8s ease-in-out infinite',
                        'float-medium': 'float 6s ease-in-out infinite',
                        'pulse-glow': 'pulseGlow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'entrance': 'entrance 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards',
                        'spin-slow': 'spin 3s linear infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        pulseGlow: {
                            '0%, 100%': { opacity: '1', boxShadow: '0 0 20px rgb(<?php echo $site_main_rgb; ?> / 0.2)' },
                            '50%': { opacity: '.8', boxShadow: '0 0 30px rgb(<?php echo $site_main_rgb; ?> / 0.5)' },
                        },
                        entrance: {
                            '0%': { opacity: '0', transform: 'translateY(20px) scale(0.95)' },
                            '100%': { opacity: '1', transform: 'translateY(0) scale(1)' },
                        }
                    }
                }
            }
        }
    </script>

    <script>
        // Check Landing Overlay Session
        if (sessionStorage.getItem('enteredSite')) {
            document.write('<style>#landing-overlay { display: none !important; }</style>');
        }
    </script>

    <style>
        /* Base Setup */
        body { 
            color: #f8fafc; 
            font-family: 'Kanit', sans-serif; 
            overflow-x: hidden; 
            background-color: #0f172a; 
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 99px; border: 2px solid #0f172a; }
        ::-webkit-scrollbar-thumb:hover { background: rgb(var(--theme-main)); }

        /* Background Systems */
        #dynamic-bg { position: fixed; inset: 0; z-index: -20; overflow: hidden; background: #0f172a; }
        .bg-slide { 
            position: absolute; inset: 0; 
            background-size: cover; background-position: center; 
            opacity: 0; transition: opacity 1.5s ease-in-out, transform 8s ease; 
            transform: scale(1.1); filter: brightness(0.6);
        }
        .bg-slide.active { opacity: 1; transform: scale(1); }
        
        #video-bg { position: fixed; top: 50%; left: 50%; min-width: 100%; min-height: 100%; transform: translate(-50%, -50%); z-index: -20; object-fit: cover; opacity: 0.6; }
        
        /* Modern Overlay Gradients */
        .bg-overlay { 
            position: fixed; inset: 0; 
            background: radial-gradient(circle at center, transparent 0%, #0f172a 120%),
                        linear-gradient(to bottom, rgba(15, 23, 42, 0.3), rgba(15, 23, 42, 0.9));
            z-index: -10; pointer-events: none; 
        }
        /* Noise Texture for Premium Feel */
        .bg-noise {
            position: fixed; inset: 0; z-index: -9;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
        }

        /* Canvas Effect */
        #season-canvas { position: fixed; inset: 0; z-index: -5; pointer-events: none; }

        /* --- Landing Overlay (Modern Glass) --- */
        #landing-overlay {
            position: fixed; inset: 0; z-index: 9999;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px);
            display: flex; align-items: center; justify-content: center;
            transition: all 0.7s cubic-bezier(0.4, 0, 0.2, 1);
        }
        #landing-overlay.hidden-overlay { opacity: 0; visibility: hidden; pointer-events: none; transform: scale(1.1); }
        
        /* Menu Cards */
        .glass-card-hover {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card-hover:hover {
            background: rgba(var(--theme-main), 0.1); /* Fallback variable usage handled by Tailwind */
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.5);
        }

        /* --- Navigation Bar (Premium Glass) --- */
        .navbar-glass {
            background: rgba(15, 23, 42, 0.1);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255,255,255,0.02);
            transition: all 0.4s ease;
        }
        .navbar-glass.scrolled {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }
        
        .nav-item-pill {
            position: relative; overflow: hidden;
        }
        .nav-item-pill::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.1), transparent);
            transform: translateX(-100%); transition: transform 0.5s;
        }
        .nav-item-pill:hover::before { transform: translateX(100%); }

        /* Mobile Drawer */
        #mobile-menu-drawer { 
            transition: transform 0.5s cubic-bezier(0.32, 0.725, 0, 1); 
            transform: translateX(100%); 
        }
        #mobile-menu-drawer.open { transform: translateX(0); }

        /* Emoji Floating */
        .floating-emoji { position: fixed; z-index: -5; animation: floatUp linear forwards; pointer-events: none; opacity: 0; filter: blur(0px); }
        @keyframes floatUp { 0% { transform: translateY(110vh) rotate(0deg) scale(0.8); opacity: 0; } 10% { opacity: 0.8; } 100% { transform: translateY(-10vh) rotate(360deg) scale(1.2); opacity: 0; } }
        
        /* Utility */
        .text-glow { text-shadow: 0 0 15px currentColor; }
        /* Animation เพิ่มเติมสำหรับ Overlay */
@keyframes tilt {
    0%, 50%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(0.5deg); }
    75% { transform: rotate(-0.5deg); }
}
.animate-tilt {
    animation: tilt 10s infinite linear;
}

/* เอฟเฟกต์แสงวิ่งผ่านปุ่ม */
@keyframes shine-move {
    0% { left: -100%; opacity: 0; }
    50% { opacity: 0.5; }
    100% { left: 200%; opacity: 0; }
}
.btn-shine-effect {
    position: absolute;
    top: 0; left: 0; width: 50%; height: 100%;
    background: linear-gradient(to right, transparent, rgba(255,255,255,0.6), transparent);
    transform: skewX(-20deg);
    animation: shine-move 3s infinite ease-in-out;
    pointer-events: none;
}

/* ปรับ Overlay ให้ดู Glassmorphism แบบเข้มๆ */
#landing-overlay {
    background: radial-gradient(circle at center, rgba(15, 23, 42, 0.85) 0%, rgba(2, 6, 23, 0.98) 100%);
    backdrop-filter: blur(20px);
}

    </style>
</head>
<body class="flex flex-col min-h-screen selection:bg-theme-main selection:text-white">

    <style>
    @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap');

    /* Animations */
    @keyframes slideInLeft { 0% { opacity: 0; transform: translateX(-50px); } 100% { opacity: 1; transform: translateX(0); } }
    @keyframes slideInRight { 0% { opacity: 0; transform: translateX(50px); } 100% { opacity: 1; transform: translateX(0); } }
    @keyframes slideUp { 0% { opacity: 0; transform: translateY(20px); } 100% { opacity: 1; transform: translateY(0); } }
    @keyframes pulse-green { 0% { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(74, 222, 128, 0); } 100% { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0); } }

    .animate-slide-left { animation: slideInLeft 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
    .animate-slide-right { animation: slideInRight 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
    .animate-slide-up { animation: slideUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }

    /* Glass Styles */
    .lobby-glass { background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); }
    .stats-glass { background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(10px); border-top: 1px solid rgba(255, 255, 255, 0.1); }
    
    /* Font & Misc */
    .digital-clock { font-family: 'Orbitron', sans-serif; text-shadow: 0 0 10px rgba(var(--theme-main), 0.8); }
    .btn-skew { transform: skewX(-10deg); }
    .btn-skew > * { transform: skewX(10deg); }
    
    /* Popup Animation */
    @keyframes popupEntrance { 0% { opacity: 0; transform: scale(0.9) translateY(20px); } 100% { opacity: 1; transform: scale(1) translateY(0); } }
    .popup-animate-in { animation: popupEntrance 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
</style>

<div id="landing-overlay" class="fixed inset-0 z-[9990] overflow-hidden flex flex-col bg-[#0f172a]">
    
    <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
        <?php if($web_config->site_logo): ?>
            <div class="absolute inset-0 bg-cover bg-center opacity-20 blur-2xl scale-125 animate-[pulse_10s_infinite]" 
                 style="background-image: url('<?php echo $web_config->site_logo; ?>');"></div>
        <?php endif; ?>
        
        <div class="absolute inset-0 bg-gradient-to-t from-[#020617] via-[#0f172a]/95 to-[#0f172a]/90"></div>
        
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:40px_40px]"></div>
    </div>

    <div class="relative z-10 container mx-auto h-full px-6 flex flex-col lg:flex-row items-center justify-center lg:justify-between pt-10 pb-28 lg:py-0">
        
        <div class="flex flex-col items-center lg:items-start text-center lg:text-left mb-8 lg:mb-0">
            <div class="relative mb-8 group animate-[fadeInUp_0.8s_ease-out]">
                <div class="absolute inset-0 bg-theme-main blur-3xl opacity-20 group-hover:opacity-50 transition-opacity duration-500"></div>
                <div class="relative w-32 h-32 lg:w-40 lg:h-40 rounded-3xl bg-white/5 backdrop-blur-2xl border border-white/10 flex items-center justify-center shadow-2xl ring-1 ring-white/10 group-hover:scale-105 transition-transform duration-500">
                    <?php if($web_config->site_logo): ?>
                        <img src="<?php echo $web_config->site_logo; ?>" class="w-full h-full object-cover rounded-3xl p-1">
                    <?php else: ?>
                        <i class="fa-solid fa-gamepad text-6xl text-white/80"></i>
                    <?php endif; ?>
                </div>
            </div>

            <h1 class="text-5xl lg:text-8xl font-black text-white tracking-tighter leading-none mb-4 drop-shadow-2xl animate-[fadeInUp_0.8s_0.2s_ease-out_both]">
                <?php echo $web_config->site_name; ?>
            </h1>
            
            <button onclick="enterSite()" class="mt-6 group relative px-10 py-4 bg-gradient-to-r from-theme-main to-indigo-600 text-white font-bold rounded-full shadow-[0_0_20px_-5px_rgba(var(--theme-main),0.5)] hover:shadow-[0_0_40px_-5px_rgba(var(--theme-main),0.7)] transition-all duration-300 hover:-translate-y-1 animate-[fadeInUp_0.8s_0.4s_ease-out_both]">
                <span class="relative z-10 flex items-center gap-3 text-lg">
                    ENTER WORLD <i class="fa-solid fa-play text-xs group-hover:translate-x-1 transition-transform"></i>
                </span>
                <div class="absolute inset-0 bg-white/20 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
            </button>
        </div>

        <div class="w-full max-w-sm lg:w-[320px] animate-[fadeInRight_0.8s_0.6s_ease-out_both]">
            <div class="bg-slate-900/40 backdrop-blur-md border border-white/5 rounded-2xl p-4 lg:p-6 shadow-2xl">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-widest">Navigation</span>
                    <div class="flex gap-1">
                        <div class="w-1.5 h-1.5 rounded-full bg-red-500"></div>
                        <div class="w-1.5 h-1.5 rounded-full bg-yellow-500"></div>
                        <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <?php
                    $quick_menu = [
                        ['icon'=>'fa-cart-shopping', 'txt'=>'SHOP', 'link'=>'shop.php', 'c'=>'text-blue-400', 'b'=>'hover:border-blue-500/50', 'bg'=>'bg-blue-500/10'],
                        ['icon'=>'fa-wallet', 'txt'=>'TOPUP', 'link'=>'topup.php', 'c'=>'text-emerald-400', 'b'=>'hover:border-emerald-500/50', 'bg'=>'bg-emerald-500/10'],
                        ['icon'=>'fa-gift', 'txt'=>'CODE', 'link'=>'redeem.php', 'c'=>'text-purple-400', 'b'=>'hover:border-purple-500/50', 'bg'=>'bg-purple-500/10'],
                        ['icon'=>'fa-headset', 'txt'=>'CONTACT', 'link'=>'contact.php', 'c'=>'text-orange-400', 'b'=>'hover:border-orange-500/50', 'bg'=>'bg-orange-500/10'],
                    ];
                    foreach($quick_menu as $qm): ?>
                    <a href="<?php echo $qm['link']; ?>" class="group flex flex-col items-center justify-center gap-2 p-3 rounded-xl bg-white/5 border border-white/5 <?php echo $qm['b']; ?> transition-all hover:bg-white/10">
                        <div class="w-10 h-10 rounded-full <?php echo $qm['bg']; ?> flex items-center justify-center <?php echo $qm['c']; ?> text-lg group-hover:scale-110 transition-transform">
                            <i class="fa-solid <?php echo $qm['icon']; ?>"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-300 group-hover:text-white"><?php echo $qm['txt']; ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="absolute bottom-6 left-0 w-full z-20 px-4 animate-[fadeInUp_0.8s_0.8s_ease-out_both]">
        <div class="container mx-auto">
            <div class="grid grid-cols-3 gap-3 md:gap-6 max-w-4xl mx-auto">
                
                <div class="group relative bg-[#0f1f18]/80 backdrop-blur-md border border-green-500/20 rounded-2xl p-3 md:p-4 flex flex-col items-center justify-center hover:border-green-500/50 transition-all hover:-translate-y-1">
                    <div class="absolute -top-3 bg-[#0f1f18] border border-green-500/30 px-3 py-0.5 rounded-full">
                        <i class="fa-solid fa-fire text-[10px] text-green-400 animate-pulse"></i>
                    </div>
                    <h3 class="text-2xl md:text-3xl font-black text-white counter-up" data-target="<?php echo $stat_sold; ?>">0</h3>
                    <span class="text-[10px] md:text-xs text-green-400/80 uppercase tracking-wider font-bold">Items Sold</span>
                </div>

                <div class="group relative bg-[#1f160f]/80 backdrop-blur-md border border-orange-500/20 rounded-2xl p-3 md:p-4 flex flex-col items-center justify-center hover:border-orange-500/50 transition-all hover:-translate-y-1">
                     <div class="absolute -top-3 bg-[#1f160f] border border-orange-500/30 px-3 py-0.5 rounded-full">
                        <i class="fa-solid fa-box text-[10px] text-orange-400"></i>
                    </div>
                    <h3 class="text-2xl md:text-3xl font-black text-white counter-up" data-target="<?php echo $stat_stock; ?>">0</h3>
                    <span class="text-[10px] md:text-xs text-orange-400/80 uppercase tracking-wider font-bold">In Stock</span>
                </div>

                <div class="group relative bg-[#0f172a]/80 backdrop-blur-md border border-blue-500/20 rounded-2xl p-3 md:p-4 flex flex-col items-center justify-center hover:border-blue-500/50 transition-all hover:-translate-y-1">
                     <div class="absolute -top-3 bg-[#0f172a] border border-blue-500/30 px-3 py-0.5 rounded-full">
                        <i class="fa-solid fa-users text-[10px] text-blue-400"></i>
                    </div>
                    <h3 class="text-2xl md:text-3xl font-black text-white counter-up" data-target="<?php echo $stat_users; ?>">0</h3>
                    <span class="text-[10px] md:text-xs text-blue-400/80 uppercase tracking-wider font-bold">Happy Users</span>
                </div>

            </div>
        </div>
    </div>

</div>

<script>
    // ฟังก์ชันวิ่งตัวเลข
    function animateCounter(el) {
        const target = +el.getAttribute('data-target');
        const duration = 1500; // เวลาที่ใช้วิ่ง (ms)
        const start = 0;
        const startTime = performance.now();

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function (เริ่มช้า จบเร็ว)
            const ease = 1 - Math.pow(1 - progress, 3);
            
            el.innerText = Math.floor(start + (target - start) * ease).toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                el.innerText = target.toLocaleString(); // จบที่เลขเป๊ะๆ
            }
        }
        requestAnimationFrame(update);
    }

    // เริ่มรันเมื่อโหลดเสร็จ
    document.addEventListener("DOMContentLoaded", () => {
        const counters = document.querySelectorAll('.counter-up');
        counters.forEach(counter => animateCounter(counter));
    });
</script>

<style>
    /* Keyframes เพิ่มเติม */
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeInRight { from { opacity: 0; transform: translateX(30px); } to { opacity: 1; transform: translateX(0); } }
</style>

<?php if($popup): ?>
<div id="promo-popup" class="fixed inset-0 z-[9999] flex items-center justify-center hidden px-4 transition-opacity duration-300 opacity-0" 
     style="background-color: rgba(0,0,0,0.7); backdrop-filter: blur(5px);">
    
    <div class="popup-content relative w-full max-w-4xl bg-slate-900 border border-white/10 rounded-2xl shadow-2xl overflow-hidden flex flex-col md:flex-row max-h-[85vh] opacity-0 transform scale-95 transition-all duration-500">
        
        <button onclick="closePopup()" class="absolute top-3 right-3 z-30 w-8 h-8 bg-black/50 hover:bg-red-500 text-white rounded-full flex items-center justify-center transition">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <?php if(!empty($popup->popup_img)): ?>
        <div class="w-full md:w-1/2 h-48 md:h-auto bg-black relative">
            <img src="<?php echo $popup->popup_img; ?>" class="w-full h-full object-cover">
        </div>
        <?php endif; ?>

        <div class="w-full md:w-1/2 p-6 flex flex-col justify-center relative">
            <h3 class="text-2xl font-bold text-white mb-2"><?php echo htmlspecialchars($popup->popup_title); ?></h3>
            <div class="w-12 h-1 bg-theme-main rounded-full mb-4"></div>
            <p class="text-gray-300 mb-6 text-sm whitespace-pre-line"><?php echo htmlspecialchars($popup->popup_desc); ?></p>
            
            <a href="<?php echo $popup->popup_link; ?>" class="w-full py-3 rounded-lg bg-theme-main hover:bg-theme-main/90 text-white font-bold text-center mb-3">
                <?php echo $popup->popup_btn_text; ?>
            </a>
            
            <div class="flex items-center justify-center gap-2">
                <input type="checkbox" id="dontShowAgain" class="rounded bg-slate-800 border-slate-600 text-theme-main">
                <label for="dontShowAgain" class="text-xs text-gray-500">ไม่ต้องแสดงอีก 1 ชั่วโมง</label>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    // 1. นาฬิกา Real-time
    function updateClock() {
        const now = new Date();
        const timeEl = document.getElementById('clock-time');
        const dateEl = document.getElementById('clock-date');
        if(timeEl) timeEl.innerText = now.toLocaleTimeString('en-GB', { hour12: false });
        if(dateEl) dateEl.innerText = now.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'short' });
    }
    setInterval(updateClock, 1000);
    updateClock();

    // 2. ฟังก์ชันเรียก Popup (ทำงานหลังจากกด Enter Site)
    function triggerPopupDisplay() {
        const popupContainer = document.getElementById('promo-popup');
        if(!popupContainer) return;

        const popupContent = popupContainer.querySelector('.popup-content');
        const lastClosedTime = localStorage.getItem('popupClosedTime');
        const now = new Date().getTime();
        const oneHour = 60 * 60 * 1000;

        if (!lastClosedTime || (now - lastClosedTime > oneHour)) {
            setTimeout(() => {
                popupContainer.classList.remove('hidden');
                setTimeout(() => {
                    popupContainer.classList.remove('opacity-0');
                    popupContainer.classList.add('opacity-100');
                    popupContent.classList.remove('opacity-0', 'scale-95');
                    popupContent.classList.add('popup-animate-in');
                }, 50);
            }, 500); // Delay 0.5 วินาทีหลังเข้าเว็บ
        }
    }

    // 3. ฟังก์ชัน Enter Site (กดปุ่มแล้วซ่อน Overlay -> เรียก Popup)
    function enterSite() {
        const overlay = document.getElementById('landing-overlay');
        
        // Animation ออก
        overlay.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        overlay.style.opacity = '0';
        overlay.style.transform = 'scale(1.1)';
        
        setTimeout(() => {
            overlay.classList.add('hidden'); // ซ่อนถาวร
            // เรียก Popup ให้ทำงานต่อทันที
            triggerPopupDisplay();
        }, 500);
        
        sessionStorage.setItem('enteredSite', 'true');
    }

    // 4. ฟังก์ชันปิด Popup
    function closePopup() {
        const popupContainer = document.getElementById('promo-popup');
        const checkbox = document.getElementById('dontShowAgain');
        
        popupContainer.classList.add('opacity-0');
        setTimeout(() => { popupContainer.classList.add('hidden'); }, 300);

        if (checkbox && checkbox.checked) {
            localStorage.setItem('popupClosedTime', new Date().getTime());
        }
    }

    // 5. เช็ค Session (ถ้าเคยเข้าแล้ว ให้ซ่อน Overlay เลย แล้วลองเรียก Popup)
    document.addEventListener("DOMContentLoaded", function() {
        if (sessionStorage.getItem('enteredSite')) {
            const overlay = document.getElementById('landing-overlay');
            if(overlay) overlay.classList.add('hidden');
            triggerPopupDisplay();
        }
    });
</script>
</div>
    <?php
// --- 4. ส่วนจัดการ Popup (แบบสุ่มจากตาราง popups) ---
// ค้นหา Popup ที่เปิดใช้งาน (enable=1) มา 1 อัน แบบสุ่ม (RAND)
// ตรวจสอบตัวแปร $pdo ว่ามีการเชื่อมต่อฐานข้อมูลแล้วหรือไม่ก่อนหน้านี้
if (isset($pdo)) {
    $stmt_pop = $pdo->prepare("SELECT * FROM popups WHERE popup_enable = 1 ORDER BY RAND() LIMIT 1");
    $stmt_pop->execute();
    $popup = $stmt_pop->fetch(PDO::FETCH_OBJ);
} else {
    $popup = false;
}
?>

<?php if($popup): ?>

<style>
    /* Custom Scrollbar ให้ดูโมเดิร์น */
    .popup-scroll::-webkit-scrollbar { width: 6px; }
    .popup-scroll::-webkit-scrollbar-track { background: rgba(0,0,0,0.1); }
    .popup-scroll::-webkit-scrollbar-thumb { background: rgba(124, 58, 237, 0.5); border-radius: 10px; }
    .popup-scroll::-webkit-scrollbar-thumb:hover { background: rgba(124, 58, 237, 0.8); }

    /* Animation สำหรับ Popup Content */
    @keyframes popupEntrance {
        0% { opacity: 0; transform: scale(0.9) translateY(20px); }
        100% { opacity: 1; transform: scale(1) translateY(0); }
    }
    
    .popup-animate-in {
        animation: popupEntrance 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    /* Gradient Text Effect (Optional) */
    .text-gradient {
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-image: linear-gradient(to right, #fff, #c084fc);
    }
</style>

<div id="promo-popup" class="fixed inset-0 z-[9999] flex items-center justify-center hidden px-4 transition-opacity duration-300 opacity-0" 
     style="background-color: rgba(0,0,0,0.6); backdrop-filter: blur(8px);">
    
    <div class="popup-content relative w-full max-w-4xl bg-slate-900 border border-white/10 rounded-2xl shadow-2xl overflow-hidden flex flex-col md:flex-row max-h-[85vh] opacity-0 transform scale-95 transition-all duration-500">
        
        <button onclick="closePopup()" class="absolute top-3 right-3 z-30 w-9 h-9 bg-black/50 hover:bg-red-500/80 text-white rounded-full flex items-center justify-center transition backdrop-blur-sm border border-white/10 group">
            <i class="fa-solid fa-xmark group-hover:rotate-90 transition-transform duration-300"></i>
        </button>

        <?php if(!empty($popup->popup_img)): ?>
        <div class="w-full md:w-1/2 h-60 md:h-auto bg-black relative overflow-hidden group">
            <img src="<?php echo $popup->popup_img; ?>" 
                 alt="Promo" 
                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
            <div class="absolute inset-0 bg-gradient-to-t md:bg-gradient-to-r from-black/60 via-transparent to-transparent"></div>
        </div>
        <?php endif; ?>

        <div class="w-full <?php echo (!empty($popup->popup_img)) ? 'md:w-1/2' : 'md:w-full'; ?> p-6 md:p-8 flex flex-col justify-center relative bg-slate-900/95">
            
            <div class="popup-scroll overflow-y-auto pr-2 max-h-[50vh] md:max-h-[60vh]">
                <h3 class="text-2xl md:text-3xl font-bold text-white mb-3 leading-tight text-gradient">
                    <?php echo htmlspecialchars($popup->popup_title); ?>
                </h3>
                
                <div class="w-16 h-1 bg-gradient-to-r from-theme-main to-purple-500 rounded-full mb-4"></div>

                <p class="text-gray-300 mb-6 text-sm md:text-base font-light whitespace-pre-line leading-relaxed">
                    <?php echo htmlspecialchars($popup->popup_desc); ?>
                </p>
            </div>

            <div class="mt-auto pt-4 border-t border-white/10 flex flex-col gap-3 items-center">
                <a href="<?php echo $popup->popup_link; ?>" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-center shadow-lg shadow-purple-500/20 hover:shadow-purple-500/40 transform hover:-translate-y-1 transition-all duration-300">
                    <?php echo $popup->popup_btn_text; ?> <i class="fa-solid fa-arrow-right ml-2 text-sm"></i>
                </a>
                
                <div class="flex items-center justify-center gap-2 cursor-pointer group select-none" onclick="document.getElementById('dontShowAgain').click()">
                    <input type="checkbox" id="dontShowAgain" class="rounded bg-slate-800 border-slate-600 text-purple-600 focus:ring-0 focus:ring-offset-0 cursor-pointer w-4 h-4">
                    <label for="dontShowAgain" class="text-xs text-gray-500 group-hover:text-gray-300 transition cursor-pointer">
                        ไม่ต้องแสดงอีกใน 1 ชั่วโมง
                    </label>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // สร้างฟังก์ชันแยกออกมา เพื่อเรียกใช้เมื่อไหร่ก็ได้
    function triggerPopupDisplay() {
        const popupContainer = document.getElementById('promo-popup');
        if(!popupContainer) return;

        const popupContent = popupContainer.querySelector('.popup-content');
        const lastClosedTime = localStorage.getItem('popupClosedTime');
        const now = new Date().getTime();
        const oneHour = 60 * 60 * 1000; 

        // เช็คว่าปิดไปนานเกิน 1 ชม. หรือยัง
        if (!lastClosedTime || (now - lastClosedTime > oneHour)) {
            
            // ตั้งเวลา Delay ให้ขึ้นหลังจากกด Enter Site สักนิด (เช่น 1 วินาที)
            setTimeout(() => {
                popupContainer.classList.remove('hidden');
                
                setTimeout(() => {
                    popupContainer.classList.remove('opacity-0');
                    popupContainer.classList.add('opacity-100');
                    
                    popupContent.classList.remove('opacity-0', 'scale-95');
                    popupContent.classList.add('popup-animate-in'); 
                }, 50);
                
            }, 1000); // <-- Delay หลังจากกด Enter Site
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        // กรณีที่ User เคยเข้าเว็บแล้ว (Refresh หน้า) ให้เช็คและแสดง Popup ได้เลย
        // โดยไม่ต้องรอกดปุ่ม Enter Site อีกรอบ
        if (sessionStorage.getItem('enteredSite')) {
            triggerPopupDisplay();
        }
    });

    function closePopup() {
        const popupContainer = document.getElementById('promo-popup');
        const checkbox = document.getElementById('dontShowAgain');
        const popupContent = popupContainer.querySelector('.popup-content');

        popupContent.style.transform = 'scale(0.9) translateY(20px)';
        popupContent.style.opacity = '0';
        popupContainer.style.opacity = '0'; 

        setTimeout(() => {
            popupContainer.classList.add('hidden');
        }, 300);

        if (checkbox && checkbox.checked) {
            localStorage.setItem('popupClosedTime', new Date().getTime());
        }
    }
</script>
<?php endif; ?>
    <?php if ($web_config->background_type == 'video' && !empty($bg_list)): ?>
        <video autoplay muted loop playsinline id="video-bg"><source src="<?php echo $bg_list[0]; ?>" type="video/mp4"></video>
    <?php else: ?>
        <div id="dynamic-bg">
            <?php foreach($bg_list as $index => $img): ?>
                <div class="bg-slide <?php echo $index === 0 ? 'active' : ''; ?>" style="background-image: url('<?php echo $img; ?>');"></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="bg-overlay"></div>
    <div class="bg-noise"></div>
    <canvas id="season-canvas"></canvas>

    <nav id="main-nav" class="navbar-glass fixed top-0 w-full z-50 h-20 transition-all duration-300">
        <div class="container mx-auto px-4 lg:px-6 h-full flex justify-between items-center">
            
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="relative w-10 h-10 lg:w-11 lg:h-11 overflow-hidden rounded-xl bg-slate-800 shadow-inner border border-white/10 group-hover:border-theme-main transition-colors duration-300">
                    <?php if($web_config->site_logo): ?>
                        <img src="<?php echo $web_config->site_logo; ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center"><i class="fa-solid fa-gamepad text-theme-main"></i></div>
                    <?php endif; ?>
                </div>
                <div class="hidden sm:flex flex-col">
                    <span class="text-lg font-bold text-white tracking-wide group-hover:text-theme-main transition duration-300 line-clamp-1"><?php echo $web_config->site_name; ?></span>
                </div>
            </a>

            <div class="hidden lg:flex items-center bg-slate-900/30 backdrop-blur-md p-1.5 rounded-full border border-white/5 shadow-lg">
                <?php 
                $menu_items = [
                    ['url' => 'index.php', 'icon' => 'fa-house', 'label' => 'หน้าหลัก'],
                    ['url' => 'shop.php', 'icon' => 'fa-store', 'label' => 'ร้านค้า'],
                    ['url' => 'topup.php', 'icon' => 'fa-wallet', 'label' => 'เติมเงิน'],
                    ['url' => 'redeem.php', 'icon' => 'fa-ticket', 'label' => 'แลกโค้ด'],
                    ['url' => 'contact.php', 'icon' => 'fa-headset', 'label' => 'ช่วยเหลือ'],
                ];
                foreach($menu_items as $item): 
                    $isActive = basename($_SERVER['PHP_SELF']) == $item['url'];
                ?>
                    <a href="<?php echo $item['url']; ?>" 
                       class="nav-item-pill px-5 py-2 rounded-full text-sm font-medium transition-all duration-300 flex items-center gap-2
                       <?php echo $isActive ? 'bg-theme-main text-white shadow-lg shadow-theme-main/25' : 'text-slate-300 hover:text-white hover:bg-white/5'; ?>">
                       <i class="fa-solid <?php echo $item['icon']; ?>"></i> 
                       <?php echo $item['label']; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="flex items-center gap-3 lg:gap-5">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="hidden lg:flex flex-col items-end cursor-pointer group/balance" onclick="updateBalance()">
                        <div class="flex items-center gap-1.5 text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                            Balance <i id="refresh-icon" class="fa-solid fa-rotate-right group-hover/balance:rotate-180 transition-transform duration-500"></i>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-amber-400 drop-shadow-[0_0_10px_rgba(251,191,36,0.5)]"><i class="fa-solid fa-coins"></i></span>
                            <span class="font-bold text-white text-lg leading-none" id="user-balance"><?php echo number_format($_SESSION['point'], 2); ?></span>
                        </div>
                    </div>

                    <div class="relative group z-50">
                        <button class="relative w-10 h-10 rounded-full p-0.5 border-2 border-transparent group-hover:border-theme-main transition-all duration-300">
                            <img src="<?php echo $_SESSION['profile_img'] ? $_SESSION['profile_img'] : 'https://ui-avatars.com/api/?name='.$_SESSION['username']; ?>" class="w-full h-full rounded-full object-cover bg-slate-800">
                            <div class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 rounded-full border-2 border-slate-900 animate-pulse"></div>
                        </button>

                        <div class="absolute right-0 mt-4 w-72 bg-[#1e293b]/90 backdrop-blur-xl border border-white/10 rounded-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform origin-top-right scale-95 group-hover:scale-100 shadow-2xl shadow-black/50 overflow-hidden">
                            <div class="p-5 bg-gradient-to-br from-theme-main/20 to-transparent border-b border-white/5">
                                <p class="text-xs text-theme-main font-bold uppercase mb-1">Signed in as</p>
                                <p class="text-white font-bold text-lg truncate"><?php echo $_SESSION['username']; ?></p>
                                <p class="text-slate-400 text-xs">Member ID: #<?php echo str_pad($_SESSION['user_id'], 5, '0', STR_PAD_LEFT); ?></p>
                            </div>
                            <div class="p-2">
                                <a href="profile.php" class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-white/5 text-sm text-slate-300 hover:text-white transition">
                                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-400"><i class="fa-regular fa-id-card"></i></div>
                                    โปรไฟล์
                                </a>
                                <a href="history.php" class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-white/5 text-sm text-slate-300 hover:text-white transition">
                                    <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-400"><i class="fa-solid fa-clock-rotate-left"></i></div>
                                    ประวัติซื้อ
                                </a>
                                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                                <a href="admin/dashboard.php" class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-theme-main/10 text-sm text-theme-main transition">
                                    <div class="w-8 h-8 rounded-lg bg-theme-main/20 flex items-center justify-center text-theme-main"><i class="fa-solid fa-gauge"></i></div>
                                    หลังบ้าน
                                </a>
                                <?php endif; ?>
                                <div class="h-px bg-white/5 my-1 mx-2"></div>
                                <a href="logout.php" class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-red-500/10 text-sm text-red-400 hover:text-red-300 transition">
                                    <div class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center text-red-400"><i class="fa-solid fa-power-off"></i></div>
                                    ออกจากระบบ
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="hidden lg:flex items-center gap-2">
                        <a href="login.php" class="px-5 py-2 rounded-full text-sm font-bold text-slate-300 hover:text-white hover:bg-white/5 transition-all">เข้าสู่ระบบ</a>
                        <a href="register.php" class="px-5 py-2 rounded-full text-sm font-bold bg-theme-main text-white shadow-lg shadow-theme-main/30 hover:shadow-theme-main/50 hover:-translate-y-0.5 transition-all">
                            สมัครสมาชิก
                        </a>
                    </div>
                <?php endif; ?>

                <button onclick="toggleMobileMenu()" class="lg:hidden w-10 h-10 flex flex-col justify-center items-center gap-1.5 group rounded-full hover:bg-white/5 transition-colors">
                    <span class="w-5 h-0.5 bg-white rounded-full transition-all group-hover:w-6"></span>
                    <span class="w-5 h-0.5 bg-white rounded-full transition-all group-hover:w-4"></span>
                    <span class="w-5 h-0.5 bg-white rounded-full transition-all group-hover:w-5"></span>
                </button>
            </div>
        </div>
    </nav>

    <div id="mobile-menu-overlay" onclick="toggleMobileMenu()" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] opacity-0 pointer-events-none transition-opacity duration-300"></div>
    <div id="mobile-menu-drawer" class="fixed top-0 right-0 h-full w-[85%] max-w-[320px] bg-[#0f172a]/95 backdrop-blur-xl z-[70] flex flex-col border-l border-white/10 shadow-2xl">
        <div class="p-6 border-b border-white/5 flex justify-between items-center bg-gradient-to-r from-slate-900 to-slate-800">
            <h2 class="text-xl font-bold text-white tracking-wide">Menu</h2>
            <button onclick="toggleMobileMenu()" class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-slate-400 hover:text-white hover:bg-red-500/80 transition-all">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-2">
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-5 rounded-2xl border border-white/5 mb-6 relative overflow-hidden group shadow-lg">
                    <div class="absolute -right-4 -bottom-4 text-theme-main/10 text-8xl"><i class="fa-solid fa-wallet"></i></div>
                    <div class="relative z-10 flex items-center gap-4">
                        <img src="<?php echo $_SESSION['profile_img'] ? $_SESSION['profile_img'] : 'https://ui-avatars.com/api/?name='.$_SESSION['username']; ?>" class="w-14 h-14 rounded-full border-2 border-theme-main shadow-lg">
                        <div>
                            <div class="text-white font-bold text-lg"><?php echo $_SESSION['username']; ?></div>
                            <div class="text-theme-main text-sm font-semibold flex items-center gap-2 cursor-pointer bg-black/20 px-2 py-0.5 rounded-md w-fit mt-1" onclick="updateBalance()">
                                ฿ <span id="mobile-balance"><?php echo number_format($_SESSION['point'], 2); ?></span> <i class="fa-solid fa-rotate-right text-xs"></i>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <a href="login.php" class="py-3 text-center rounded-xl bg-slate-800 text-slate-300 border border-white/5 hover:bg-slate-700 transition">เข้าสู่ระบบ</a>
                    <a href="register.php" class="py-3 text-center rounded-xl bg-theme-main text-white shadow-lg shadow-theme-main/20 hover:bg-theme-main/90 transition">สมัครสมาชิก</a>
                </div>
            <?php endif; ?>

            <div class="space-y-1">
                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider px-2 mb-2">Main Menu</p>
                <?php foreach($menu_items as $mItem): $isMActive = basename($_SERVER['PHP_SELF']) == $mItem['url']; ?>
                <a href="<?php echo $mItem['url']; ?>" class="flex items-center gap-4 px-4 py-3.5 rounded-xl transition-all <?php echo $isMActive ? 'bg-theme-main text-white shadow-lg shadow-theme-main/20' : 'text-slate-400 hover:bg-white/5 hover:text-white hover:pl-6'; ?>">
                    <div class="w-6 text-center"><i class="fa-solid <?php echo $mItem['icon']; ?>"></i></div>
                    <span class="font-medium"><?php echo $mItem['label']; ?></span>
                </a>
                <?php endforeach; ?>
            </div>

            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="h-px bg-white/5 my-4"></div>
                <div class="space-y-1">
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider px-2 mb-2">Account</p>
                    <a href="profile.php" class="flex items-center gap-4 px-4 py-3 rounded-xl text-slate-400 hover:bg-white/5 hover:text-white transition-all">
                        <i class="fa-regular fa-id-card w-6 text-center"></i> ข้อมูลส่วนตัว
                    </a>
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <a href="admin/dashboard.php" class="flex items-center gap-4 px-4 py-3 rounded-xl text-theme-main hover:bg-theme-main/10 transition-all">
                            <i class="fa-solid fa-gauge w-6 text-center"></i> จัดการระบบ
                        </a>
                    <?php endif; ?>
                    <a href="logout.php" class="flex items-center gap-4 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 transition-all">
                        <i class="fa-solid fa-right-from-bracket w-6 text-center"></i> ออกจากระบบ
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if(!empty($web_config->marquee_text)): ?>
    <div class="pt-24 pb-2 container mx-auto px-4 animate-entrance" style="animation-delay: 0.5s;">
        <div class="bg-slate-900/50 backdrop-blur-sm border border-white/5 rounded-full py-2 px-4 flex items-center gap-4 overflow-hidden relative shadow-lg">
            <div class="flex-shrink-0 bg-theme-main/10 text-theme-main px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider flex items-center gap-2 border border-theme-main/20">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-theme-main opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-theme-main"></span>
                </span>
                News
            </div>
            <marquee scrollamount="5" class="text-sm font-light text-slate-300">
                <?php echo $web_config->marquee_text; ?>
            </marquee>
        </div>
    </div>
    <?php else: ?>
    <div class="pt-24"></div>
    <?php endif; ?>

    <main class="flex-grow container mx-auto px-4 py-6 relative z-10 animate-entrance" style="animation-delay: 0.6s;">

    <script>
        // --- Overlay & Entrance ---
        function enterSite() {
    const overlay = document.getElementById('landing-overlay');
    overlay.classList.add('hidden-overlay');
    sessionStorage.setItem('enteredSite', 'true');
    
    // --- เพิ่มบรรทัดนี้: เรียก Popup ให้ทำงานหลังจากกดเข้าเว็บ ---
    if(typeof triggerPopupDisplay === 'function') {
        triggerPopupDisplay();
    }
}

        // --- Navbar Scroll Effect ---
        const navbar = document.getElementById('main-nav');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) { navbar.classList.add('scrolled'); } 
            else { navbar.classList.remove('scrolled'); }
        });

        // --- Mobile Menu Toggle ---
        function toggleMobileMenu() {
            const overlay = document.getElementById('mobile-menu-overlay');
            const drawer = document.getElementById('mobile-menu-drawer');
            if (drawer.classList.contains('open')) {
                drawer.classList.remove('open'); 
                overlay.classList.remove('opacity-100', 'pointer-events-auto'); 
                overlay.classList.add('opacity-0', 'pointer-events-none'); 
                document.body.style.overflow = '';
            } else {
                drawer.classList.add('open'); 
                overlay.classList.remove('opacity-0', 'pointer-events-none'); 
                overlay.classList.add('opacity-100', 'pointer-events-auto'); 
                document.body.style.overflow = 'hidden';
            }
        }

        // --- AJAX Update Balance (Auto & Manual) ---
        async function updateBalance() {
            const icon = document.getElementById('refresh-icon');
            const balanceText = document.getElementById('user-balance');
            const mobileBalance = document.getElementById('mobile-balance');

            if(icon) icon.classList.add('animate-spin');

            try {
                const response = await fetch('api/check_balance.php'); 
                if (!response.ok) throw new Error("API Error");

                const data = await response.json();
                if (data.status === 'success') {
                    if(balanceText) {
                        // Text Count Animation
                        animateValue(balanceText, parseFloat(balanceText.innerText.replace(/,/g, '')), parseFloat(data.point), 1000);
                        balanceText.classList.add('text-theme-main');
                        setTimeout(() => balanceText.classList.remove('text-theme-main'), 500);
                    }
                    if(mobileBalance) mobileBalance.innerText = data.point;
                } else {
                    // Session might be expired
                    window.location.reload();
                }
            } catch (error) {
                console.warn('Balance refresh failed');
            } finally {
                setTimeout(() => { if(icon) icon.classList.remove('animate-spin'); }, 1000);
            }
        }

        function animateValue(obj, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                obj.innerHTML = (progress * (end - start) + start).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }

        // --- BG Slideshow ---
        <?php if ($web_config->background_type == 'image' && count($bg_list) > 1): ?>
        const slides = document.querySelectorAll('.bg-slide');
        let currentSlide = 0;
        setInterval(() => {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }, 8000); 
        <?php endif; ?>

        // --- Season/Particle Canvas Logic ---
        const seasonType = '<?php echo $season['effect']; ?>';
        const canvas = document.getElementById('season-canvas');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            let width, height;
            const resize = () => { width = canvas.width = window.innerWidth; height = canvas.height = window.innerHeight; };
            window.addEventListener('resize', resize);
            resize();

            class Particle {
                constructor(type) { this.type = type; this.reset(); }
                reset() {
                    this.x = Math.random() * width; 
                    this.y = Math.random() * height - height;
                    this.size = Math.random() * 3 + 1; 
                    this.speed = Math.random() * 1 + 0.5;
                    this.opacity = Math.random() * 0.5 + 0.2;
                    if(this.type === 'hearts') { this.y = height + Math.random() * 100; this.size = Math.random() * 10 + 10; }
                }
                update() {
                    if (this.type === 'snow') {
                        this.y += this.speed; 
                        this.x += Math.sin(this.y * 0.01) * 0.5;
                        if (this.y > height) this.reset();
                    } else if (this.type === 'hearts') {
                        this.y -= this.speed;
                        this.x += Math.sin(this.y * 0.02);
                        if(this.y < -50) this.reset();
                    } else if (this.type === 'spooky') {
                         this.x += Math.random() - 0.5; this.y += Math.random() - 0.5;
                         if(Math.random() < 0.01) this.reset();
                    }
                }
                draw() {
                    ctx.globalAlpha = this.opacity;
                    if (this.type === 'snow') {
                        ctx.fillStyle = '#FFF'; ctx.beginPath(); ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2); ctx.fill();
                    } else if (this.type === 'hearts') {
                        ctx.font = this.size + 'px serif'; ctx.fillText('❤️', this.x, this.y);
                    } else if (this.type === 'spooky') {
                        ctx.fillStyle = '#fff'; ctx.beginPath(); ctx.arc(this.x, this.y, Math.random()*2, 0, Math.PI*2); ctx.fill();
                    }
                }
            }
            
            // Fireworks Logic
            let fireworks = [];
            class Firework { 
                constructor() { this.x = Math.random() * width; this.y = height; this.targetY = height * 0.2 + Math.random() * (height * 0.5); this.speed = 12; this.particles = []; this.exploded = false; this.color = `hsl(${Math.random() * 360}, 100%, 60%)`; }
                update() {
                    if (!this.exploded) { this.y -= this.speed; this.speed *= 0.98; if (this.y <= this.targetY || this.speed < 1) this.explode(); } 
                    else { for (let i = this.particles.length - 1; i >= 0; i--) { this.particles[i].update(); if (this.particles[i].alpha <= 0) this.particles.splice(i, 1); } }
                }
                draw() { if (!this.exploded) { ctx.fillStyle = this.color; ctx.beginPath(); ctx.arc(this.x, this.y, 3, 0, Math.PI*2); ctx.fill(); } else { this.particles.forEach(p => p.draw()); } }
                explode() { this.exploded = true; for (let i = 0; i < 40; i++) this.particles.push(new FireworkParticle(this.x, this.y, this.color)); }
            }
            class FireworkParticle {
                constructor(x, y, color) { this.x = x; this.y = y; this.color = color; const a = Math.random() * Math.PI * 2; const s = Math.random() * 5; this.vx = Math.cos(a) * s; this.vy = Math.sin(a) * s; this.alpha = 1; this.decay = Math.random() * 0.03 + 0.01; }
                update() { this.x += this.vx; this.y += this.vy; this.vy += 0.1; this.alpha -= this.decay; }
                draw() { ctx.globalAlpha = this.alpha; ctx.fillStyle = this.color; ctx.beginPath(); ctx.arc(this.x, this.y, 2, 0, Math.PI*2); ctx.fill(); }
            }

            const particles = [];
            const pCount = seasonType === 'snow' ? 100 : (seasonType === 'hearts' ? 40 : (seasonType === 'spooky' ? 50 : 0));
            for(let i=0; i<pCount; i++) particles.push(new Particle(seasonType));

            function animate() {
                ctx.clearRect(0, 0, width, height);
                particles.forEach(p => { p.update(); p.draw(); });
                
                if (seasonType === 'fireworks') {
                    if (Math.random() < 0.04) fireworks.push(new Firework());
                    for (let i = fireworks.length - 1; i >= 0; i--) { fireworks[i].update(); fireworks[i].draw(); if (fireworks[i].exploded && fireworks[i].particles.length === 0) fireworks.splice(i, 1); }
                }
                requestAnimationFrame(animate);
            }
            if(seasonType !== 'normal') animate();
        }

        // --- Emoji Floating ---
        <?php if ($season['effect'] == 'normal' && !empty($emojis)): ?>
        const emojiList = <?php echo json_encode(array_values($emojis)); ?>;
        setInterval(() => {
            if (document.hidden || emojiList.length === 0) return;
            const emoji = document.createElement('div');
            emoji.innerText = emojiList[Math.floor(Math.random() * emojiList.length)];
            emoji.classList.add('floating-emoji');
            emoji.style.left = Math.random() * 100 + 'vw';
            emoji.style.fontSize = (Math.random() * 20 + 20) + 'px';
            emoji.style.animationDuration = (Math.random() * 5 + 5) + 's';
            document.body.appendChild(emoji);
            setTimeout(() => emoji.remove(), 10000);
        }, 1500); 
        <?php endif; ?>
    </script>
</body>
</html>

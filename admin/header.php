<?php 
// admin/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสอบ path ของ db.php (รองรับทั้งระดับเดียวกันและ folder แม่)
if (file_exists('../db.php')) {
    require_once '../db.php';
} elseif (file_exists('../../db.php')) {
    require_once '../../db.php';
}

// --- Admin Auth Check (Logic เดิม) ---
if (!function_exists('checkAdmin')) {
    function checkAdmin($pdo) {
        if (!isset($_SESSION['admin_id'])) {
            header("Location: ../login.php");
            exit;
        }
    }
}
checkAdmin($pdo);

$current_page = basename($_SERVER['PHP_SELF']);
$admin_name = $_SESSION['username'] ?? 'Administrator';
$admin_img = $_SESSION['profile_img'] ?? "https://ui-avatars.com/api/?name=$admin_name&background=random";

// Config สีหลัก (เปลี่ยนธีมได้ที่นี่)
$accent_color = '#8b5cf6'; // Violet theme
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Admin Control - <?php echo $admin_name; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Kanit', 'sans-serif'] },
                    colors: {
                        theme: {
                            primary: '<?php echo $accent_color; ?>',
                            dark: '#0f172a',    // Slate 900
                            surface: '#1e293b', // Slate 800
                        }
                    },
                    boxShadow: {
                        'glow': '0 0 20px rgba(139, 92, 246, 0.3)',
                    }
                }
            }
        }
    </script>

    <style>
        /* --- Base & Background --- */
        body {
            background-color: #020617; /* Slate 950 */
            color: #e2e8f0;
            /* พื้นหลังแบบ Mesh Gradient นิ่งๆ ดูแพง */
            background-image: 
                radial-gradient(at 0% 0%, rgba(139, 92, 246, 0.15) 0px, transparent 50%), 
                radial-gradient(at 100% 100%, rgba(56, 189, 248, 0.1) 0px, transparent 50%);
            background-attachment: fixed;
            -webkit-tap-highlight-color: transparent;
        }

        /* --- Scrollbar สวยๆ --- */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #64748b; }

        /* --- Components --- */
        .glass-sidebar {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        .glass-header-mobile {
            background: rgba(2, 6, 23, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Nav Item Styles */
        .nav-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: #94a3b8; /* Slate 400 */
            border-radius: 0.75rem;
            transition: all 0.25s ease;
            font-weight: 400;
        }
        .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.03);
            padding-left: 1.25rem; /* Slide effect on hover */
        }
        .nav-link.active {
            background: linear-gradient(90deg, rgba(139, 92, 246, 0.15) 0%, transparent 100%);
            color: #a78bfa; /* Violet 400 */
            border-left: 3px solid #8b5cf6;
            padding-left: 1.25rem; /* Keep slide position */
        }
        .nav-link.active i {
            filter: drop-shadow(0 0 8px rgba(139, 92, 246, 0.5));
            color: #fff;
        }

        /* --- Mobile Drawer Animation --- */
        #sidebar-drawer {
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .drawer-closed { transform: translateX(-100%); }
        .drawer-open { transform: translateX(0); }
        
        #overlay-backdrop {
            transition: opacity 0.3s ease;
        }
    </style>
</head>
<body class="flex flex-col md:flex-row h-screen overflow-hidden">

    <header class="md:hidden flex items-center justify-between px-5 h-16 glass-header-mobile fixed top-0 w-full z-40 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-violet-500/20">
                <i class="fa-solid fa-shield-cat text-sm"></i>
            </div>
            <div>
                <h1 class="font-bold text-white text-base leading-none tracking-wide">Admin<span class="text-violet-400">Panel</span></h1>
                <p class="text-[10px] text-slate-400 font-light mt-0.5">Manage System</p>
            </div>
        </div>
        <button onclick="toggleDrawer()" class="w-10 h-10 flex items-center justify-center rounded-full bg-white/5 text-slate-300 active:scale-95 transition hover:bg-white/10 hover:text-white">
            <i class="fa-solid fa-bars-staggered text-lg"></i>
        </button>
    </header>

    <div id="overlay-backdrop" onclick="toggleDrawer()" class="fixed inset-0 bg-black/60 backdrop-blur-[2px] z-50 opacity-0 pointer-events-none md:hidden"></div>

    <aside id="sidebar-drawer" class="fixed inset-y-0 left-0 z-[60] w-[80%] max-w-[280px] md:w-[280px] glass-sidebar flex flex-col drawer-closed md:translate-x-0 md:relative shadow-2xl md:shadow-none">
        
        <div class="h-20 flex items-center justify-between px-6 border-b border-white/5 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-theme-primary to-indigo-600 flex items-center justify-center text-white text-lg shadow-glow">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <div class="hidden md:block">
                    <h1 class="font-bold text-lg text-white tracking-wide">Console</h1>
                    <p class="text-[10px] text-slate-400 uppercase tracking-wider">v.2.0.1</p>
                </div>
                <div class="md:hidden">
                    <h1 class="font-bold text-lg text-white">Menu</h1>
                </div>
            </div>
            <button onclick="toggleDrawer()" class="md:hidden w-8 h-8 rounded-lg bg-red-500/10 text-red-400 flex items-center justify-center hover:bg-red-500 hover:text-white transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-8">
            
            <div>
                <p class="px-4 mb-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Dashboard</p>
                <div class="space-y-1">
                    <a href="dashboard.php" class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                        <span>ภาพรวมระบบ</span>
                    </a>
                </div>
            </div>

            <div>
                <p class="px-4 mb-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Store Manager</p>
                <div class="space-y-1">
                    <a href="products.php" class="nav-link <?php echo strpos($current_page, 'product') !== false ? 'active' : ''; ?>">
                        <i class="fa-solid fa-box w-5 text-center"></i>
                        <span>จัดการสินค้า</span>
                    </a>
                    <a href="categories.php" class="nav-link <?php echo strpos($current_page, 'categor') !== false ? 'active' : ''; ?>">
                        <i class="fa-solid fa-tags w-5 text-center"></i>
                        <span>หมวดหมู่</span>
                    </a>
                    <a href="manage_codes.php" class="nav-link <?php echo $current_page == 'manage_codes.php' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-barcode w-5 text-center"></i>
                        <span>สต็อก Code</span>
                    </a>
                </div>
            </div>

            <div>
                <p class="px-4 mb-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">System</p>
                <div class="space-y-1">
                    <a href="users.php" class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-users w-5 text-center"></i>
                        <span>สมาชิก</span>
                    </a>
                    <a href="admin_config.php" class="nav-link <?php echo $current_page == 'admin_config.php' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-sliders w-5 text-center"></i>
                        <span>Popup โฆษณา</span>
                    </a>
                    <a href="settings.php" class="nav-link <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-sliders w-5 text-center"></i>
                        <span>ตั้งค่าเว็บไซต์</span>
                    </a>
                </div>
            </div>
        </nav>

        <div class="p-4 border-t border-white/5 bg-black/20">
            <div class="flex items-center gap-3 mb-4 px-2">
                <img src="<?php echo $admin_img; ?>" class="w-10 h-10 rounded-full border-2 border-slate-700 bg-slate-800">
                <div class="overflow-hidden">
                    <p class="text-sm font-bold text-white truncate"><?php echo $admin_name; ?></p>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[10px] text-emerald-400">Online</span>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <a href="../index.php" class="flex items-center justify-center gap-2 py-2 rounded-lg bg-slate-800 text-slate-400 text-xs hover:bg-slate-700 hover:text-white transition">
                    <i class="fa-solid fa-store"></i> หน้าร้าน
                </a>
                <button onclick="logoutConfirm()" class="flex items-center justify-center gap-2 py-2 rounded-lg bg-red-500/10 text-red-400 text-xs hover:bg-red-500 hover:text-white transition">
                    <i class="fa-solid fa-power-off"></i> ออกระบบ
                </button>
            </div>
        </div>
    </aside>

    <main class="flex-grow h-full overflow-y-auto w-full pt-16 md:pt-0 relative">
        <div class="fixed top-20 right-20 w-96 h-96 bg-theme-primary/10 rounded-full blur-[100px] pointer-events-none -z-10"></div>

        <div class="p-4 md:p-8 w-full max-w-7xl mx-auto min-h-full pb-20">

        <script>
            // Toggle Mobile Drawer
            function toggleDrawer() {
                const drawer = document.getElementById('sidebar-drawer');
                const backdrop = document.getElementById('overlay-backdrop');
                const body = document.body;

                if (drawer.classList.contains('drawer-closed')) {
                    // Open
                    drawer.classList.remove('drawer-closed');
                    drawer.classList.add('drawer-open');
                    
                    backdrop.classList.remove('opacity-0', 'pointer-events-none');
                    backdrop.classList.add('opacity-100', 'pointer-events-auto');
                    
                    body.style.overflow = 'hidden'; // Lock Scroll
                } else {
                    // Close
                    drawer.classList.add('drawer-closed');
                    drawer.classList.remove('drawer-open');
                    
                    backdrop.classList.remove('opacity-100', 'pointer-events-auto');
                    backdrop.classList.add('opacity-0', 'pointer-events-none');
                    
                    body.style.overflow = ''; // Unlock Scroll
                }
            }

            // Close drawer when screen resizes to desktop
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 768) {
                    const drawer = document.getElementById('sidebar-drawer');
                    const backdrop = document.getElementById('overlay-backdrop');
                    
                    if(drawer.classList.contains('drawer-open')) {
                        drawer.classList.remove('drawer-open');
                        drawer.classList.add('drawer-closed');
                        backdrop.classList.add('opacity-0', 'pointer-events-none');
                        document.body.style.overflow = '';
                    }
                    // For desktop, remove the translate styling to let it sit relatively
                    drawer.style.transform = ''; 
                } else {
                    // Reset transform style for mobile class handling
                    const drawer = document.getElementById('sidebar-drawer');
                    drawer.style.transform = ''; 
                }
            });

            // Logout Confirmation
            function logoutConfirm() {
                Swal.fire({
                    title: 'ออกจากระบบ?',
                    text: "คุณต้องการออกจากระบบจัดการใช่หรือไม่",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#334155',
                    confirmButtonText: 'ใช่, ออกจากระบบ',
                    cancelButtonText: 'ยกเลิก',
                    background: '#1e293b',
                    color: '#fff'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '../logout.php';
                    }
                })
            }
        </script>

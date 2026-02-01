<?php 
require_once 'db.php';

// 1. เช็ค Login ก่อนเริ่มโหลด HTML (Best Practice)
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error_msg = '';
$login_success = false;

// 2. Logic การล็อกอิน
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC); // บังคับคืนค่าเป็น Array

        if ($user && password_verify($password, $user['password'])) {
            // Login สำเร็จ
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['point'] = $user['point'];
            $_SESSION['profile_img'] = $user['profile_img'] ?? 'default.png'; // กัน Error ถ้าไม่มีรูป

            $login_success = true; // ตั้งค่าตัวแปรเพื่อไปแสดงผล SweetAlert ด้านล่าง
        } else {
            $error_msg = "ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง";
        }
    } catch (PDOException $e) {
        $error_msg = "เกิดข้อผิดพลาดของระบบ: " . $e->getMessage();
    }
}

require_once 'header.php'; 
?>



<div class="flex items-center justify-center min-h-[calc(100vh-80px)] py-10 px-4">
    <div class="glass w-full max-w-md p-8 rounded-2xl shadow-2xl relative overflow-hidden border border-slate-700/50 backdrop-blur-xl bg-slate-800/40">
        
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-theme-main rounded-full blur-[80px] opacity-20 animate-pulse"></div>
        <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-blue-500 rounded-full blur-[80px] opacity-20 animate-pulse" style="animation-delay: 1s;"></div>

        <div class="relative z-10">
            <h2 class="text-3xl font-bold text-center mb-2 text-white drop-shadow-lg">ยินดีต้อนรับกลับ</h2>
            <p class="text-slate-400 text-center mb-8 text-sm">เข้าสู่ระบบเพื่อจัดการสินค้าของคุณ</p>

            <?php if($error_msg): ?>
                <div class="bg-red-500/10 border border-red-500/50 text-red-200 p-3 rounded-xl mb-6 text-center text-sm flex items-center justify-center gap-2 animate-bounce-short">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-slate-300 mb-2 text-sm font-medium ml-1">ชื่อผู้ใช้งาน</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-user text-slate-500 group-focus-within:text-theme-main transition-colors"></i>
                        </div>
                        <input type="text" name="username" 
                               class="w-full bg-slate-900/50 border border-slate-700 text-white rounded-xl py-3 pl-11 pr-4 
                                      focus:outline-none focus:border-theme-main focus:ring-1 focus:ring-theme-main 
                                      placeholder-slate-600 transition-all duration-300" 
                               placeholder="Username" required autocomplete="username">
                    </div>
                </div>

                <div class="mb-2">
                    <label class="block text-slate-300 mb-2 text-sm font-medium ml-1">รหัสผ่าน</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-slate-500 group-focus-within:text-theme-main transition-colors"></i>
                        </div>
                        <input type="password" name="password" 
                               class="w-full bg-slate-900/50 border border-slate-700 text-white rounded-xl py-3 pl-11 pr-4 
                                      focus:outline-none focus:border-theme-main focus:ring-1 focus:ring-theme-main 
                                      placeholder-slate-600 transition-all duration-300" 
                               placeholder="Password" required autocomplete="current-password">
                    </div>
                </div>

                <div class="flex justify-end mb-6">
                    <a href="#" class="text-xs text-slate-400 hover:text-theme-main transition">ลืมรหัสผ่าน?</a>
                </div>

                <button type="submit" name="login" 
                        class="w-full bg-gradient-to-r from-theme-main to-purple-600 hover:from-purple-600 hover:to-theme-main 
                               text-white font-bold py-3.5 rounded-xl shadow-lg shadow-purple-500/20 
                               transition-all duration-300 transform hover:-translate-y-1 hover:shadow-purple-500/40">
                    <i class="fa-solid fa-right-to-bracket mr-2"></i> เข้าสู่ระบบ
                </button>
            </form>

            <div class="flex items-center justify-between my-6">
                <hr class="w-full border-slate-700/50">
                <span class="px-3 text-slate-500 text-xs whitespace-nowrap">หรือเข้าสู่ระบบด้วย</span>
                <hr class="w-full border-slate-700/50">
            </div>

            <a href="api/discord_login.php" 
               class="block w-full bg-[#5865F2]/10 hover:bg-[#5865F2] border border-[#5865F2]/50 hover:border-[#5865F2] 
                      text-[#5865F2] hover:text-white font-bold py-3 rounded-xl transition-all duration-300 
                      text-center group">
                <i class="fa-brands fa-discord mr-2 text-xl group-hover:scale-110 transition-transform"></i> Discord
            </a>

            <div class="mt-8 text-center text-sm text-slate-400">
                ยังไม่มีบัญชี? <a href="register.php" class="text-theme-main font-bold hover:text-purple-400 transition underline decoration-2 decoration-transparent hover:decoration-purple-400">สมัครสมาชิกใหม่</a>
            </div>
        </div>
    </div>
</div>

<?php if($login_success): ?>
<script>
    // รอให้หน้าเว็บโหลดเสร็จก่อนเรียก SweetAlert
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'ยินดีต้อนรับ!',
            text: 'กำลังเข้าสู่ระบบ...',
            background: '#1e293b',
            color: '#fff',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
        }).then(() => {
            window.location = 'index.php';
        });
    });
</script>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
<?php 
require_once 'db.php';

// 1. เช็คว่าล็อกอินอยู่แล้วหรือไม่
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error_msg = '';
$register_success = false;

// 2. Logic การสมัครสมาชิก
if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error_msg = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error_msg = "ชื่อผู้ใช้งานต้องเป็นภาษาอังกฤษหรือตัวเลขเท่านั้น";
    } elseif (strlen($password) < 6) {
        $error_msg = "รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร";
    } elseif ($password !== $confirm_password) {
        $error_msg = "รหัสผ่านยืนยันไม่ตรงกัน";
    } else {
        try {
            // เช็ค Username ซ้ำ
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetchColumn() > 0) {
                $error_msg = "ชื่อผู้ใช้นี้ถูกใช้งานแล้ว";
            } else {
                // บันทึกข้อมูล
                $hash_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role, point, profile_img) VALUES (?, ?, 'member', 0, 'default.png')");
                
                if ($stmt->execute([$username, $hash_password])) {
                    $register_success = true;
                } else {
                    $error_msg = "เกิดข้อผิดพลาด: ไม่สามารถบันทึกข้อมูลได้";
                }
            }
        } catch (PDOException $e) {
            $error_msg = "Database Error: " . $e->getMessage();
        }
    }
}

require_once 'header.php'; 
?>



<div class="flex items-center justify-center min-h-[calc(100vh-80px)] py-10 px-4">
    <div class="glass w-full max-w-md p-8 rounded-2xl shadow-2xl relative overflow-hidden border border-slate-700/50 backdrop-blur-xl bg-slate-800/40">
        
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-theme-main rounded-full blur-[80px] opacity-20 animate-pulse"></div>
        <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-blue-500 rounded-full blur-[80px] opacity-20 animate-pulse" style="animation-delay: 1.5s;"></div>

        <div class="relative z-10">
            <h2 class="text-3xl font-bold text-center mb-2 text-white drop-shadow-lg">สมัครสมาชิกใหม่</h2>
            <p class="text-slate-400 text-center mb-6 text-sm">สร้างบัญชีเพื่อเริ่มต้นสั่งซื้อสินค้า</p>

            <?php if($error_msg): ?>
                <div class="bg-red-500/10 border border-red-500/50 text-red-200 p-3 rounded-xl mb-6 text-center text-sm flex items-center justify-center gap-2 animate-bounce-short">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-slate-300 mb-2 text-sm font-medium ml-1">ชื่อผู้ใช้งาน <span class="text-xs text-slate-500">(A-Z, 0-9)</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-user text-slate-500 group-focus-within:text-theme-main transition-colors"></i>
                        </div>
                        <input type="text" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               class="w-full bg-slate-900/50 border border-slate-700 text-white rounded-xl py-3 pl-11 pr-4 
                                      focus:outline-none focus:border-theme-main focus:ring-1 focus:ring-theme-main 
                                      placeholder-slate-600 transition-all duration-300" 
                               placeholder="ตั้งชื่อผู้ใช้งาน..." required pattern="[a-zA-Z0-9_]+" title="ภาษาอังกฤษและตัวเลขเท่านั้น">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-slate-300 mb-2 text-sm font-medium ml-1">รหัสผ่าน</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-slate-500 group-focus-within:text-theme-main transition-colors"></i>
                        </div>
                        <input type="password" name="password" 
                               class="w-full bg-slate-900/50 border border-slate-700 text-white rounded-xl py-3 pl-11 pr-4 
                                      focus:outline-none focus:border-theme-main focus:ring-1 focus:ring-theme-main 
                                      placeholder-slate-600 transition-all duration-300" 
                               placeholder="กำหนดรหัสผ่าน (ขั้นต่ำ 6 ตัว)..." required minlength="6">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-slate-300 mb-2 text-sm font-medium ml-1">ยืนยันรหัสผ่าน</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-circle-check text-slate-500 group-focus-within:text-theme-main transition-colors"></i>
                        </div>
                        <input type="password" name="confirm_password" 
                               class="w-full bg-slate-900/50 border border-slate-700 text-white rounded-xl py-3 pl-11 pr-4 
                                      focus:outline-none focus:border-theme-main focus:ring-1 focus:ring-theme-main 
                                      placeholder-slate-600 transition-all duration-300" 
                               placeholder="กรอกรหัสผ่านอีกครั้ง..." required minlength="6">
                    </div>
                </div>

                <button type="submit" name="register" 
                        class="w-full bg-gradient-to-r from-theme-main to-purple-600 hover:from-purple-600 hover:to-theme-main 
                               text-white font-bold py-3.5 rounded-xl shadow-lg shadow-purple-500/20 
                               transition-all duration-300 transform hover:-translate-y-1 hover:shadow-purple-500/40">
                    <i class="fa-solid fa-user-plus mr-2"></i> สมัครสมาชิก
                </button>
            </form>

            <div class="flex items-center justify-between my-6">
                <hr class="w-full border-slate-700/50">
                <span class="px-3 text-slate-500 text-xs whitespace-nowrap">หรือสมัครด้วย</span>
                <hr class="w-full border-slate-700/50">
            </div>

            <a href="api/discord_login.php" 
               class="block w-full bg-[#5865F2]/10 hover:bg-[#5865F2] border border-[#5865F2]/50 hover:border-[#5865F2] 
                      text-[#5865F2] hover:text-white font-bold py-3 rounded-xl transition-all duration-300 
                      text-center group">
                <i class="fa-brands fa-discord mr-2 text-xl group-hover:scale-110 transition-transform"></i> Discord
            </a>

            <div class="mt-8 text-center text-sm text-slate-400">
                มีบัญชีอยู่แล้ว? <a href="login.php" class="text-theme-main font-bold hover:text-purple-400 transition underline decoration-2 decoration-transparent hover:decoration-purple-400">เข้าสู่ระบบที่นี่</a>
            </div>
        </div>
    </div>
</div>

<?php if($register_success): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'สมัครสมาชิกสำเร็จ!',
            text: 'กำลังพาท่านไปหน้าเข้าสู่ระบบ...',
            background: '#1e293b',
            color: '#fff',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        }).then(() => {
            window.location = 'login.php';
        });
    });
</script>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
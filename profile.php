<?php 
require_once 'db.php';
require_once 'header.php';

// ตรวจสอบ Login
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$msg_type = '';
$msg_text = '';

// ดึงข้อมูล User ล่าสุด
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Logic: อัปเดตข้อมูล
if (isset($_POST['update_profile'])) {
    
    // 1. จัดการรูปภาพ
    $img_path = $current_user['profile_img'];
    $upload_ok = true;

    if (!empty($_FILES['avatar']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['avatar']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $filesize = $_FILES['avatar']['size'];

        if (!in_array($ext, $allowed)) {
            $msg_type = 'error'; $msg_text = 'รองรับเฉพาะไฟล์ JPG, PNG, WEBP เท่านั้น'; $upload_ok = false;
        } elseif ($filesize > 2 * 1024 * 1024) { // 2MB
            $msg_type = 'error'; $msg_text = 'ขนาดไฟล์ต้องไม่เกิน 2MB'; $upload_ok = false;
        } else {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
            
            $new_name = "user_" . $user_id . "_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_dir . $new_name)) {
                $img_path = $new_name; // เก็บแค่ชื่อไฟล์ หรือ path ตาม structure เดิม
            }
        }
    }

    // 2. จัดการรหัสผ่าน (เพิ่ม Security: ต้องใส่รหัสเดิม)
    $pass_sql = "";
    $params = [$img_path];

    if ($upload_ok) {
        if (!empty($_POST['new_password'])) {
            $old_password = $_POST['old_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if (empty($old_password)) {
                $msg_type = 'error'; $msg_text = 'กรุณากรอกรหัสผ่านเดิมเพื่อยืนยันตัวตน';
            } elseif (!password_verify($old_password, $current_user['password'])) {
                $msg_type = 'error'; $msg_text = 'รหัสผ่านเดิมไม่ถูกต้อง';
            } elseif ($new_password !== $confirm_password) {
                $msg_type = 'error'; $msg_text = 'รหัสผ่านยืนยันไม่ตรงกัน';
            } elseif (strlen($new_password) < 6) {
                $msg_type = 'error'; $msg_text = 'รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร';
            } else {
                $pass_sql = ", password = ?";
                $params[] = password_hash($new_password, PASSWORD_DEFAULT);
            }
        }

        // 3. อัปเดตลง Database ถ้าไม่มี Error ก่อนหน้า
        if (empty($msg_type)) {
            $params[] = $user_id; // สำหรับ WHERE
            $sql = "UPDATE users SET profile_img = ? $pass_sql WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute($params)) {
                $_SESSION['profile_img'] = $img_path; // อัปเดต Session
                
                // Refresh ข้อมูลใหม่มาแสดง
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $current_user = $stmt->fetch(PDO::FETCH_ASSOC);

                $msg_type = 'success';
                $msg_text = 'อัปเดตข้อมูลสำเร็จ';
            } else {
                $msg_type = 'error';
                $msg_text = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
            }
        }
    }
}
?>



<div class="container mx-auto py-12 px-4 min-h-[calc(100vh-80px)]">
    <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-1">
            <div class="glass p-8 rounded-2xl border border-slate-700/50 relative overflow-hidden text-center sticky top-24">
                <div class="absolute top-0 left-0 w-full h-28 bg-gradient-to-br from-theme-main/80 to-blue-600/80"></div>
                
                <div class="relative z-10 -mt-2">
                    <div class="relative inline-block group">
                        <img src="<?php echo (strpos($current_user['profile_img'], 'http') !== false) ? $current_user['profile_img'] : 'uploads/' . $current_user['profile_img']; ?>" 
                             class="w-32 h-32 rounded-full border-4 border-slate-900 object-cover shadow-2xl bg-slate-800" 
                             id="sidebarPreview"
                             onerror="this.src='https://ui-avatars.com/api/?name=<?php echo $current_user['username']; ?>&background=random'">
                        
                        <div class="absolute bottom-2 right-2 bg-green-500 w-5 h-5 rounded-full border-2 border-slate-900" title="Online"></div>
                    </div>

                    <h2 class="text-2xl font-bold text-white mt-4"><?php echo htmlspecialchars($current_user['username']); ?></h2>
                    <span class="inline-block bg-theme-main/20 text-theme-main text-xs px-2 py-1 rounded mt-1 border border-theme-main/30 uppercase">
                        <?php echo htmlspecialchars($current_user['role']); ?>
                    </span>
                    
                    <div class="bg-slate-800/60 p-5 rounded-xl border border-slate-600/50 mt-6 backdrop-blur-md">
                        <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">ยอดเงินคงเหลือ</p>
                        <div class="text-3xl font-bold text-green-400 drop-shadow-sm">
                            ฿ <?php echo number_format($current_user['point'], 2); ?>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <a href="history.php" class="flex items-center justify-center w-full bg-slate-700/50 hover:bg-slate-700 text-slate-200 py-3 rounded-xl transition border border-slate-600 group">
                            <i class="fa-solid fa-clock-rotate-left mr-2 text-slate-400 group-hover:text-white transition"></i> ประวัติการสั่งซื้อ
                        </a>
                        <a href="logout.php" class="flex items-center justify-center w-full bg-red-500/10 hover:bg-red-500/20 text-red-400 py-3 rounded-xl transition border border-red-500/30">
                            <i class="fa-solid fa-right-from-bracket mr-2"></i> ออกจากระบบ
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="glass p-8 rounded-2xl border border-slate-700/50">
                <h3 class="text-2xl font-bold mb-1 flex items-center gap-3 text-white">
                    <div class="w-10 h-10 rounded-lg bg-theme-main flex items-center justify-center shadow-lg shadow-purple-500/20">
                        <i class="fa-solid fa-user-gear text-lg"></i>
                    </div>
                    ตั้งค่าบัญชี
                </h3>
                <p class="text-slate-400 text-sm mb-8 ml-14">จัดการข้อมูลส่วนตัวและรหัสผ่านของคุณ</p>

                <form method="POST" enctype="multipart/form-data">
                    
                    <div class="mb-8 p-6 bg-slate-800/40 rounded-xl border border-slate-700/50 flex flex-col sm:flex-row items-center gap-6">
                        <div class="relative group cursor-pointer" onclick="document.getElementById('avatarInput').click()">
                            <img src="<?php echo (strpos($current_user['profile_img'], 'http') !== false) ? $current_user['profile_img'] : 'uploads/' . $current_user['profile_img']; ?>" 
                                 id="formPreview"
                                 class="w-24 h-24 rounded-full object-cover border-2 border-slate-600 group-hover:border-theme-main transition opacity-100 group-hover:opacity-50"
                                 onerror="this.src='https://ui-avatars.com/api/?name=<?php echo $current_user['username']; ?>'">
                            
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300">
                                <i class="fa-solid fa-camera text-white text-2xl drop-shadow-md"></i>
                            </div>
                        </div>
                        <div class="flex-1 text-center sm:text-left">
                            <h4 class="text-white font-semibold mb-1">รูปโปรไฟล์</h4>
                            <p class="text-xs text-slate-400 mb-3">รองรับไฟล์: JPG, PNG, WEBP (Max 2MB)</p>
                            <input type="file" name="avatar" id="avatarInput" class="hidden" accept="image/png, image/jpeg, image/webp" onchange="previewImage(this)">
                            <button type="button" onclick="document.getElementById('avatarInput').click()" 
                                    class="text-sm bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-lg transition border border-slate-600">
                                เลือกรูปภาพใหม่
                            </button>
                        </div>
                    </div>

                    <div class="bg-slate-800/40 rounded-xl border border-slate-700/50 p-6 mb-6">
                        <h4 class="text-lg font-bold mb-4 text-white flex items-center gap-2">
                            <i class="fa-solid fa-lock text-theme-main"></i> เปลี่ยนรหัสผ่าน
                        </h4>
                        
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-slate-400 mb-2 text-sm">รหัสผ่านปัจจุบัน <span class="text-red-400">*</span></label>
                                <div class="relative">
                                    <i class="fa-solid fa-key absolute left-3 top-3.5 text-slate-500"></i>
                                    <input type="password" name="old_password" class="w-full bg-slate-900/50 border border-slate-600 rounded-lg py-3 pl-10 pr-4 text-white focus:border-theme-main focus:outline-none transition placeholder-slate-600" placeholder="กรอกรหัสผ่านเดิมเพื่อยืนยัน">
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-slate-400 mb-2 text-sm">รหัสผ่านใหม่</label>
                                    <div class="relative">
                                        <i class="fa-solid fa-lock absolute left-3 top-3.5 text-slate-500"></i>
                                        <input type="password" name="new_password" class="w-full bg-slate-900/50 border border-slate-600 rounded-lg py-3 pl-10 pr-4 text-white focus:border-theme-main focus:outline-none transition placeholder-slate-600" placeholder="กำหนดรหัสผ่านใหม่">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-slate-400 mb-2 text-sm">ยืนยันรหัสผ่านใหม่</label>
                                    <div class="relative">
                                        <i class="fa-solid fa-circle-check absolute left-3 top-3.5 text-slate-500"></i>
                                        <input type="password" name="confirm_password" class="w-full bg-slate-900/50 border border-slate-600 rounded-lg py-3 pl-10 pr-4 text-white focus:border-theme-main focus:outline-none transition placeholder-slate-600" placeholder="พิมพ์อีกครั้ง">
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-circle-info mr-1"></i> เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน</p>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" name="update_profile" class="bg-gradient-to-r from-theme-main to-purple-600 hover:from-purple-600 hover:to-theme-main text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-purple-500/30 transition transform hover:-translate-y-1 flex items-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i> บันทึกการเปลี่ยนแปลง
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    // ฟังก์ชัน Preview รูปภาพก่อนอัปโหลด
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('formPreview').src = e.target.result;
                document.getElementById('sidebarPreview').src = e.target.result; // เปลี่ยนรูปที่ Sidebar ด้วย
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // แสดง SweetAlert จาก PHP Logic
    <?php if(!empty($msg_type)): ?>
    Swal.fire({
        icon: '<?php echo $msg_type; ?>',
        title: '<?php echo ($msg_type == 'success') ? 'สำเร็จ' : 'แจ้งเตือน'; ?>',
        text: '<?php echo $msg_text; ?>',
        background: '#1e293b',
        color: '#fff',
        confirmButtonColor: '#8b5cf6',
        timer: <?php echo ($msg_type == 'success') ? 2000 : 5000; ?>,
        timerProgressBar: true
    });
    <?php endif; ?>
</script>

<?php require_once 'footer.php'; ?>
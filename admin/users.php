<?php include 'admin_auth.php'; ?>
<?php
// admin/users.php
require_once 'header.php';

// --- Logic: เติมเงิน / แก้ไขข้อมูล ---
if (isset($_POST['update_user'])) {
    // CSRF Protection (ควรมีใน Production)
    // if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) { ... }

    $uid = $_POST['user_id'];
    $point = $_POST['point'];
    $role = $_POST['role'];
    
    // เตรียม Query
    $params = [$point, $role];
    $pass_sql = "";
    
    // ถ้ามีการกรอกรหัสผ่านใหม่ ให้ Hash และอัปเดต
    if (!empty($_POST['new_password'])) {
        $pass_sql = ", password = ?";
        $params[] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    }
    
    $params[] = $uid; // ปิดท้ายด้วย WHERE id

    $stmt = $pdo->prepare("UPDATE users SET point = ?, role = ? $pass_sql WHERE id = ?");
    
    if ($stmt->execute($params)) {
        echo "<script>
            Swal.fire({
                title: 'บันทึกสำเร็จ',
                text: 'อัปเดตข้อมูลสมาชิกเรียบร้อยแล้ว',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false,
                background: '#1e293b', color: '#fff'
            }).then(() => window.location='users.php');
        </script>";
    }
}

// --- Logic: ลบสมาชิก ---
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    
    // ป้องกันการลบตัวเอง
    if ($delete_id == $_SESSION['admin_id'] || $delete_id == $_SESSION['user_id']) {
        echo "<script>Swal.fire('ข้อผิดพลาด', 'คุณไม่สามารถลบบัญชีตัวเองได้', 'error');</script>";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$delete_id])) {
            echo "<script>Swal.fire('ลบสำเร็จ', 'ลบสมาชิกเรียบร้อยแล้ว', 'success').then(() => window.location='users.php');</script>";
        }
    }
}

// --- Search & Fetch Data ---
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM users WHERE username LIKE ? OR id = ? ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$search%", $search]); // ค้นหาได้ทั้งชื่อและ ID
$users = $stmt->fetchAll();

// คำนวณยอดรวม (Optional Statistics)
$total_points = 0;
foreach($users as $u) $total_points += $u->point;
?>

<div class="container mx-auto px-4 pb-12 pt-6 max-w-7xl">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="md:col-span-2">
            <h2 class="text-3xl font-bold text-white flex items-center gap-3">
                <i class="fa-solid fa-users-gear text-blue-400"></i> จัดการสมาชิก
            </h2>
            <p class="text-slate-400 mt-1">รายชื่อสมาชิกและจัดการเครดิต</p>
        </div>
        
        <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700 flex justify-between items-center">
            <div>
                <div class="text-slate-400 text-xs">สมาชิกทั้งหมด</div>
                <div class="text-2xl font-bold text-white"><?php echo number_format(count($users)); ?> <span class="text-sm font-normal text-slate-500">คน</span></div>
            </div>
            <div class="text-right">
                <div class="text-slate-400 text-xs">เครดิตในระบบ</div>
                <div class="text-xl font-bold text-theme-main">฿<?php echo number_format($total_points); ?></div>
            </div>
        </div>
    </div>

    <div class="flex justify-end mb-6">
        <form class="relative w-full md:w-80">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                   placeholder="ค้นหาชื่อ หรือ ID..." 
                   class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 pl-11 text-white focus:outline-none focus:border-theme-main transition shadow-lg">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-slate-500"></i>
            <?php if($search): ?>
                <a href="users.php" class="absolute right-3 top-2.5 text-slate-500 hover:text-white bg-slate-800 px-2 py-0.5 rounded text-xs">ล้าง</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="hidden md:block glass rounded-2xl overflow-hidden border border-slate-700/50 shadow-xl">
        <table class="w-full text-left">
            <thead class="bg-slate-900/90 text-slate-400 uppercase text-xs tracking-wider">
                <tr>
                    <th class="p-5">ผู้ใช้งาน</th>
                    <th class="p-5">สถานะ</th>
                    <th class="p-5 text-right">เครดิตคงเหลือ</th>
                    <th class="p-5 text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/50 text-slate-300">
                <?php foreach($users as $u): ?>
                <tr class="hover:bg-slate-800/40 transition-colors">
                    <td class="p-4">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center overflow-hidden border border-slate-600">
                                <?php if($u->profile_img): ?>
                                    <img src="../<?php echo $u->profile_img; ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i class="fa-solid fa-user text-slate-400"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="font-bold text-white"><?php echo htmlspecialchars($u->username); ?></div>
                                <div class="text-xs text-slate-500">ID: #<?php echo $u->id; ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="p-4">
                        <?php if($u->role == 'admin'): ?>
                            <span class="px-2 py-1 rounded-md text-xs font-bold bg-red-500/10 text-red-400 border border-red-500/20">
                                <i class="fa-solid fa-shield-halved mr-1"></i> Admin
                            </span>
                        <?php else: ?>
                            <span class="px-2 py-1 rounded-md text-xs font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                Member
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4 text-right">
                        <span class="font-mono text-emerald-400 font-bold text-lg">฿<?php echo number_format($u->point, 2); ?></span>
                    </td>
                    <td class="p-4 text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick='editUser(<?php echo json_encode($u); ?>)' 
                                    class="w-8 h-8 rounded-lg bg-yellow-500/20 text-yellow-500 hover:bg-yellow-500 hover:text-white transition flex items-center justify-center"
                                    title="แก้ไข / เติมเงิน">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            
                            <?php if($u->id != $_SESSION['user_id']): ?>
                            <button onclick="confirmDelete(<?php echo $u->id; ?>)" 
                                    class="w-8 h-8 rounded-lg bg-red-500/20 text-red-500 hover:bg-red-500 hover:text-white transition flex items-center justify-center"
                                    title="ลบสมาชิก">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                            <?php else: ?>
                            <div class="w-8 h-8"></div> <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(count($users) == 0): ?>
                    <tr><td colspan="4" class="p-8 text-center text-slate-500">ไม่พบข้อมูลสมาชิก</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="md:hidden grid grid-cols-1 gap-4">
        <?php foreach($users as $u): ?>
        <div class="glass p-4 rounded-xl border border-slate-700">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-700 overflow-hidden">
                        <?php if($u->profile_img): ?>
                            <img src="../<?php echo $u->profile_img; ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center"><i class="fa-solid fa-user text-slate-400"></i></div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="font-bold text-white"><?php echo htmlspecialchars($u->username); ?></div>
                        <span class="text-xs px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700">#<?php echo $u->id; ?></span>
                    </div>
                </div>
                <?php if($u->role == 'admin'): ?>
                    <span class="text-xs bg-red-500/20 text-red-400 px-2 py-1 rounded border border-red-500/20">Admin</span>
                <?php else: ?>
                    <span class="text-xs bg-blue-500/20 text-blue-400 px-2 py-1 rounded border border-blue-500/20">Member</span>
                <?php endif; ?>
            </div>
            
            <div class="flex justify-between items-center py-3 border-t border-b border-slate-700/50 mb-3">
                <span class="text-slate-400 text-sm">เครดิตคงเหลือ</span>
                <span class="text-emerald-400 font-bold font-mono text-lg">฿<?php echo number_format($u->point, 2); ?></span>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <button onclick='editUser(<?php echo json_encode($u); ?>)' class="bg-yellow-600/20 text-yellow-500 hover:bg-yellow-600 hover:text-white py-2 rounded-lg text-sm font-bold transition">
                    <i class="fa-solid fa-edit mr-1"></i> แก้ไข
                </button>
                <?php if($u->id != $_SESSION['user_id']): ?>
                <button onclick="confirmDelete(<?php echo $u->id; ?>)" class="bg-red-600/20 text-red-500 hover:bg-red-600 hover:text-white py-2 rounded-lg text-sm font-bold transition">
                    <i class="fa-solid fa-trash mr-1"></i> ลบ
                </button>
                <?php else: ?>
                <button disabled class="bg-slate-800 text-slate-600 py-2 rounded-lg text-sm font-bold cursor-not-allowed">
                    <i class="fa-solid fa-lock mr-1"></i> ลบไม่ได้
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<div id="editModal" class="fixed inset-0 bg-slate-900/90 hidden items-center justify-center z-50 p-4 backdrop-blur-sm opacity-0 transition-opacity duration-300">
    <div class="bg-[#1e293b] max-w-lg w-full rounded-2xl border border-slate-600 shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300" id="modalContent">
        
        <div class="px-6 py-4 bg-slate-800 border-b border-slate-700 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white"><i class="fa-solid fa-user-pen mr-2"></i> จัดการสมาชิก</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-white transition"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>

        <form method="POST" class="p-6 space-y-5">
            <input type="hidden" name="user_id" id="modal_uid">
            
            <div class="flex items-center gap-4 mb-2 bg-slate-800/50 p-3 rounded-lg border border-slate-700">
                <div class="w-12 h-12 rounded-full bg-slate-700 flex items-center justify-center text-xl text-slate-400">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div>
                    <div class="text-slate-400 text-xs">กำลังแก้ไขผู้ใช้</div>
                    <div class="text-xl font-bold text-white" id="modal_username">Username</div>
                </div>
            </div>

            <div>
                <label class="block text-slate-400 text-xs mb-1">ยอดเงินคงเหลือ (บาท)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-slate-400">฿</span>
                    </div>
                    <input type="number" step="0.01" name="point" id="modal_point" 
                           class="w-full bg-slate-900 border border-slate-600 rounded-xl py-3 pl-8 pr-4 text-white text-xl font-bold focus:border-theme-main focus:outline-none placeholder-slate-600">
                </div>
                <p class="text-[10px] text-slate-500 mt-1">* แก้ไขตัวเลขนี้เพื่อ เพิ่ม/ลด เงินโดยตรง</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-400 text-xs mb-1">สิทธิ์การใช้งาน</label>
                    <select name="role" id="modal_role" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-2.5 text-white focus:border-theme-main focus:outline-none">
                        <option value="member">Member (ทั่วไป)</option>
                        <option value="admin">Admin (ผู้ดูแล)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-400 text-xs mb-1">เปลี่ยนรหัสผ่าน (เว้นว่างไว้ถ้าไม่เปลี่ยน)</label>
                    <input type="text" name="new_password" placeholder="ตั้งรหัสใหม่..." class="w-full bg-slate-900 border border-slate-600 rounded-lg p-2.5 text-white focus:border-theme-main focus:outline-none">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" name="update_user" class="w-full bg-gradient-to-r from-theme-main to-purple-600 hover:from-purple-500 hover:to-theme-main text-white py-3 rounded-xl font-bold shadow-lg shadow-purple-500/30 transition-all transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-save mr-2"></i> บันทึกข้อมูล
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function editUser(user) {
        document.getElementById('modal_uid').value = user.id;
        document.getElementById('modal_username').innerText = user.username;
        document.getElementById('modal_point').value = user.point;
        document.getElementById('modal_role').value = user.role;
        
        // Show Modal with Animation
        const modal = document.getElementById('editModal');
        const content = document.getElementById('modalContent');
        
        modal.classList.remove('hidden');
        // Small delay to allow display:block to apply before opacity transition
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.classList.add('flex');
            content.classList.remove('scale-95');
            content.classList.add('scale-100');
        }, 10);
    }

    function closeModal() {
        const modal = document.getElementById('editModal');
        const content = document.getElementById('modalContent');
        
        modal.classList.add('opacity-0');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: "ข้อมูลสมาชิกและประวัติการสั่งซื้อจะหายไปทั้งหมด!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'ลบเลย',
            cancelButtonText: 'ยกเลิก',
            background: '#1e293b', color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location = `users.php?delete=${id}`;
            }
        })
    }
</script>

<?php require_once 'footer.php'; ?>
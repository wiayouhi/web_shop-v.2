<?php include 'admin_auth.php'; ?>
<?php
// admin/manage_codes.php
require_once 'header.php'; 

// --- Logic เดิม (ห้ามแก้ไข) ---
// ลบโค้ด
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM redeem_codes WHERE id = ?")->execute([$_GET['delete']]);
    echo "<script>window.location='manage_codes.php';</script>";
}

// เพิ่มโค้ด
if (isset($_POST['add_code'])) {
    $code = strtoupper(trim($_POST['code']));
    $reward = $_POST['reward'];
    $max_uses = $_POST['max_uses'];

    try {
        $stmt = $pdo->prepare("INSERT INTO redeem_codes (code, reward, max_uses) VALUES (?, ?, ?)");
        $stmt->execute([$code, $reward, $max_uses]);
        echo "<script>Swal.fire('สำเร็จ', 'สร้างโค้ดเรียบร้อย', 'success');</script>";
    } catch (PDOException $e) {
        echo "<script>Swal.fire('Error', 'โค้ดซ้ำหรือเกิดข้อผิดพลาด', 'error');</script>";
    }
}

$codes = $pdo->query("SELECT * FROM redeem_codes ORDER BY id DESC")->fetchAll();
// -----------------------------
?>

<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translate3d(0, 20px, 0); }
        to { opacity: 1; transform: translate3d(0, 0, 0); }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        opacity: 0;
    }
    .delay-100 { animation-delay: 0.1s; }
    .delay-200 { animation-delay: 0.2s; }

    .glass-panel {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    }

    .modern-input {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(148, 163, 184, 0.2);
        transition: all 0.3s ease;
    }
    .modern-input:focus {
        border-color: #8b5cf6;
        box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.2);
        outline: none;
    }
</style>

<div class="container mx-auto px-4 pb-12 pt-6 max-w-7xl">
    
    <div class="animate-fade-in-up mb-8 flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-pink-500 to-rose-500 flex items-center justify-center text-white shadow-lg shadow-pink-500/30">
            <i class="fa-solid fa-ticket text-2xl"></i>
        </div>
        <div>
            <h2 class="text-3xl font-bold text-white">จัดการโค้ดรางวัล</h2>
            <p class="text-slate-400 text-sm">สร้างคูปองและจัดการสิทธิ์การใช้งาน (Redeem Codes)</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <div class="animate-fade-in-up delay-100 lg:sticky lg:top-6 z-10">
            <div class="glass-panel p-6 rounded-2xl border-t-4 border-pink-500">
                <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-pink-400"></i> สร้างโค้ดใหม่
                </h3>
                
                <form method="POST" class="space-y-5">
                    <div>
                        <label class="block text-slate-400 mb-2 text-sm font-medium">รหัสโค้ด (Code)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-slate-500"><i class="fa-solid fa-font"></i></span>
                            <input type="text" name="code" 
                                   class="modern-input w-full rounded-xl py-2.5 pl-10 pr-4 text-white placeholder-slate-600 uppercase font-mono tracking-wider" 
                                   placeholder="FREE50" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-400 mb-2 text-sm font-medium">จำนวนเงินรางวัล (฿)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-slate-500"><i class="fa-solid fa-coins"></i></span>
                            <input type="number" step="0.01" name="reward" 
                                   class="modern-input w-full rounded-xl py-2.5 pl-10 pr-4 text-white placeholder-slate-600" 
                                   placeholder="50.00" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-400 mb-2 text-sm font-medium">จำนวนสิทธิ์ (คน)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-slate-500"><i class="fa-solid fa-users"></i></span>
                            <input type="number" name="max_uses" 
                                   class="modern-input w-full rounded-xl py-2.5 pl-10 pr-4 text-white placeholder-slate-600" 
                                   value="100" required>
                        </div>
                    </div>

                    <button type="submit" name="add_code" 
                            class="w-full bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-500 hover:to-rose-500 text-white font-bold py-3 rounded-xl transition-all duration-300 shadow-lg shadow-pink-900/20 transform hover:-translate-y-1 flex items-center justify-center gap-2 group">
                        <i class="fa-solid fa-wand-magic-sparkles group-hover:rotate-12 transition-transform"></i> สร้างโค้ดทันที
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2 animate-fade-in-up delay-200">
            <div class="glass-panel rounded-2xl overflow-hidden flex flex-col h-full">
                <div class="p-5 border-b border-slate-700/50 bg-slate-800/30 flex justify-between items-center">
                    <h3 class="font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-slate-400"></i> รายการโค้ดทั้งหมด
                    </h3>
                    <span class="text-xs font-mono text-slate-500 bg-slate-900 px-2 py-1 rounded">Total: <?php echo count($codes); ?></span>
                </div>

                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-900/80 text-slate-400 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="p-4">รหัสโค้ด</th>
                                <th class="p-4">รางวัล</th>
                                <th class="p-4 w-1/3">สถานะการใช้งาน</th>
                                <th class="p-4 text-right">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50 text-slate-300">
                            <?php foreach($codes as $c): 
                                $percent = ($c->max_uses > 0) ? ($c->used_count / $c->max_uses) * 100 : 0;
                                $is_full = $c->used_count >= $c->max_uses;
                            ?>
                            <tr class="hover:bg-slate-700/30 transition-colors group">
                                <td class="p-4">
                                    <span class="font-mono font-bold text-lg text-white bg-slate-800 px-3 py-1 rounded border border-slate-600 border-dashed tracking-widest select-all">
                                        <?php echo $c->code; ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="font-bold text-emerald-400">+฿<?php echo number_format($c->reward, 2); ?></span>
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex justify-between text-xs mb-1">
                                            <span class="<?php echo $is_full ? 'text-red-400' : 'text-slate-400'; ?>">
                                                <?php echo $is_full ? 'สิทธิ์เต็มแล้ว' : 'กำลังใช้งาน'; ?>
                                            </span>
                                            <span class="text-white"><?php echo $c->used_count; ?> / <?php echo $c->max_uses; ?></span>
                                        </div>
                                        <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-500 <?php echo $is_full ? 'bg-red-500' : 'bg-blue-500'; ?>" 
                                                 style="width: <?php echo $percent; ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="manage_codes.php?delete=<?php echo $c->id; ?>" 
                                       onclick="return confirm('ยืนยันการลบโค้ด <?php echo $c->code; ?>?')" 
                                       class="w-9 h-9 rounded-lg bg-slate-800 text-red-400 hover:bg-red-500 hover:text-white flex items-center justify-center transition-all ml-auto shadow hover:shadow-red-500/30">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if(empty($codes)): ?>
                                <tr>
                                    <td colspan="4" class="p-10 text-center text-slate-500">
                                        <i class="fa-solid fa-ticket-simple text-4xl mb-3 opacity-50"></i>
                                        <p>ยังไม่มีโค้ดกิจกรรมในระบบ</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden p-4 space-y-4">
                    <?php foreach($codes as $c): 
                        $percent = ($c->max_uses > 0) ? ($c->used_count / $c->max_uses) * 100 : 0;
                        $is_full = $c->used_count >= $c->max_uses;
                    ?>
                    <div class="glass-panel p-4 rounded-xl relative overflow-hidden">
                        <div class="absolute right-0 top-0 w-20 h-full bg-gradient-to-l from-slate-800/80 to-transparent pointer-events-none"></div>

                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <span class="font-mono font-bold text-xl text-white tracking-widest"><?php echo $c->code; ?></span>
                                <div class="text-emerald-400 font-bold text-sm mt-1">+฿<?php echo number_format($c->reward, 2); ?></div>
                            </div>
                            <a href="manage_codes.php?delete=<?php echo $c->id; ?>" 
                               onclick="return confirm('ลบ?')" 
                               class="text-red-400 hover:text-red-300 p-2 bg-slate-800 rounded-lg">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </div>
                        
                        <div class="mt-4">
                            <div class="flex justify-between text-xs text-slate-400 mb-1">
                                <span><?php echo $is_full ? 'Full' : 'Used'; ?></span>
                                <span><?php echo $c->used_count; ?> / <?php echo $c->max_uses; ?></span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full <?php echo $is_full ? 'bg-red-500' : 'bg-blue-500'; ?>" style="width: <?php echo $percent; ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if(empty($codes)): ?>
                        <div class="text-center py-10 text-slate-500">ยังไม่มีข้อมูล</div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<?php echo "</div></main></body></html>"; ?>
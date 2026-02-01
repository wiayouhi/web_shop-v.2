<?php include 'admin_auth.php'; ?>
<?php
// admin/categories.php
require_once 'header.php';

// --- Logic เดิม (ห้ามแก้ไข) ---
if (isset($_POST['save_cat'])) {
    $name = $_POST['name'];
    $img = $_POST['img_url']; 
    
    if(!empty($_POST['id'])) {
        $stmt = $pdo->prepare("UPDATE categories SET name=?, img=? WHERE id=?");
        $stmt->execute([$name, $img, $_POST['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (name, img) VALUES (?, ?)");
        $stmt->execute([$name, $img]);
    }
    echo "<script>window.location='categories.php';</script>";
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id=?");
    $stmt->execute([$_GET['delete']]);
    echo "<script>window.location='categories.php';</script>";
}

$cats = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
$edit_cat = null;
if(isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_cat = $stmt->fetch();
}
// -----------------------------
?>

<style>
    /* Animation Fade In Up */
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

    /* Glass Panel */
    .glass-panel {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    }

    /* Input Styling */
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
    
    <div class="animate-fade-in-up mb-8 flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-violet-500/30">
            <i class="fa-solid fa-layer-group text-xl"></i>
        </div>
        <div>
            <h2 class="text-3xl font-bold text-white">จัดการหมวดหมู่</h2>
            <p class="text-slate-400 text-sm">เพิ่ม แก้ไข หรือลบหมวดหมู่สินค้า</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <div class="animate-fade-in-up delay-100 lg:sticky lg:top-6 z-10">
            <div class="glass-panel p-6 rounded-2xl border-t-4 border-violet-500">
                <h3 class="text-xl font-bold text-white mb-6 flex items-center justify-between">
                    <span><?php echo $edit_cat ? 'แก้ไขข้อมูล' : 'เพิ่มหมวดหมู่ใหม่'; ?></span>
                    <?php if($edit_cat): ?>
                        <span class="text-xs bg-yellow-500/20 text-yellow-400 px-2 py-1 rounded">Editing ID: <?php echo $edit_cat->id; ?></span>
                    <?php endif; ?>
                </h3>

                <form method="POST" class="space-y-5">
                    <?php if($edit_cat): ?><input type="hidden" name="id" value="<?php echo $edit_cat->id; ?>"><?php endif; ?>
                    
                    <div>
                        <label class="block text-slate-400 mb-2 text-sm font-medium">ชื่อหมวดหมู่</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-slate-500"><i class="fa-solid fa-tag"></i></span>
                            <input type="text" name="name" 
                                   value="<?php echo $edit_cat->name ?? ''; ?>" 
                                   class="modern-input w-full rounded-xl py-2.5 pl-10 pr-4 text-white placeholder-slate-600" 
                                   placeholder="เช่น เครื่องดื่ม, อาหารทานเล่น" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-400 mb-2 text-sm font-medium">ลิงก์รูปภาพ (URL)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-slate-500"><i class="fa-solid fa-image"></i></span>
                            <input type="text" name="img_url" 
                                   id="imgInput"
                                   value="<?php echo $edit_cat->img ?? ''; ?>" 
                                   class="modern-input w-full rounded-xl py-2.5 pl-10 pr-4 text-white placeholder-slate-600" 
                                   placeholder="https://example.com/image.jpg">
                        </div>
                        <?php if($edit_cat && !empty($edit_cat->img)): ?>
                            <div class="mt-3 p-2 bg-slate-900/50 rounded-lg text-center">
                                <p class="text-xs text-slate-500 mb-1">รูปปัจจุบัน</p>
                                
                                <img src="<?php echo $edit_cat->img; ?>" class="h-20 mx-auto rounded-md object-cover border border-slate-700">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="pt-2">
                        <button type="submit" name="save_cat" 
                                class="w-full bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-bold py-3 rounded-xl transition-all duration-300 shadow-lg shadow-violet-900/20 flex items-center justify-center gap-2 group">
                            <i class="fa-solid fa-save group-hover:scale-110 transition-transform"></i>
                            <?php echo $edit_cat ? 'อัปเดตข้อมูล' : 'บันทึกรายการ'; ?>
                        </button>
                        
                        <?php if($edit_cat): ?>
                            <a href="categories.php" class="block w-full text-center mt-3 py-2 text-slate-400 hover:text-white transition-colors text-sm">
                                <i class="fa-solid fa-times mr-1"></i> ยกเลิกการแก้ไข
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2 animate-fade-in-up delay-200">
            <div class="glass-panel rounded-2xl overflow-hidden flex flex-col h-full">
                <div class="p-5 border-b border-slate-700/50 bg-slate-800/30 flex justify-between items-center">
                    <h3 class="font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-list-ul text-slate-400"></i> รายการทั้งหมด
                    </h3>
                    <span class="text-xs font-mono text-slate-500 bg-slate-900 px-2 py-1 rounded">Total: <?php echo count($cats); ?></span>
                </div>

                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-900/80 text-slate-400 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="p-4 w-20 text-center">รูปภาพ</th>
                                <th class="p-4">ชื่อหมวดหมู่</th>
                                <th class="p-4 text-right">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50 text-slate-300">
                            <?php foreach($cats as $c): ?>
                            <tr class="hover:bg-slate-700/30 transition-colors group">
                                <td class="p-4 text-center">
                                    <div class="w-12 h-12 rounded-lg bg-slate-800 overflow-hidden mx-auto border border-slate-700 group-hover:border-violet-500/50 transition-colors">
                                        <img src="<?php echo $c->img; ?>" 
                                             alt="<?php echo htmlspecialchars($c->name); ?>" 
                                             class="w-full h-full object-cover"
                                             onerror="this.src='https://via.placeholder.com/150?text=No+Img'">
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="font-semibold text-white text-lg"><?php echo htmlspecialchars($c->name); ?></span>
                                    <div class="text-xs text-slate-500 mt-1 truncate max-w-[200px]"><?php echo htmlspecialchars($c->img); ?></div>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="categories.php?edit=<?php echo $c->id; ?>" 
                                           class="w-8 h-8 rounded-lg bg-slate-800 text-blue-400 hover:bg-blue-500 hover:text-white flex items-center justify-center transition-all"
                                           title="แก้ไข">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="categories.php?delete=<?php echo $c->id; ?>" 
                                           onclick="return confirm('ยืนยันลบหมวดหมู่ <?php echo $c->name; ?> หรือไม่?')" 
                                           class="w-8 h-8 rounded-lg bg-slate-800 text-red-400 hover:bg-red-500 hover:text-white flex items-center justify-center transition-all"
                                           title="ลบ">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(count($cats) == 0): ?>
                            <tr>
                                <td colspan="3" class="p-8 text-center text-slate-500">
                                    <i class="fa-solid fa-folder-open text-4xl mb-2 opacity-50"></i>
                                    <p>ยังไม่มีหมวดหมู่</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden p-4 space-y-4">
                    <?php foreach($cats as $c): ?>
                    <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700/50 flex gap-4 items-center relative overflow-hidden">
                        <div class="absolute right-0 top-0 w-16 h-full bg-gradient-to-l from-slate-900/80 to-transparent pointer-events-none"></div>
                        
                        <div class="w-16 h-16 rounded-lg bg-slate-700 overflow-hidden shrink-0 border border-slate-600">
                            <img src="<?php echo $c->img; ?>" class="w-full h-full object-cover" onerror="this.src='https://via.placeholder.com/150?text=No+Img'">
                        </div>
                        
                        <div class="flex-1 min-w-0 z-10">
                            <h4 class="text-white font-bold text-lg truncate"><?php echo htmlspecialchars($c->name); ?></h4>
                            <div class="flex gap-3 mt-2">
                                <a href="categories.php?edit=<?php echo $c->id; ?>" class="text-xs px-3 py-1.5 rounded bg-blue-500/10 text-blue-400 border border-blue-500/30">
                                    <i class="fa-solid fa-edit"></i> แก้ไข
                                </a>
                                <a href="categories.php?delete=<?php echo $c->id; ?>" onclick="return confirm('ลบ?')" class="text-xs px-3 py-1.5 rounded bg-red-500/10 text-red-400 border border-red-500/30">
                                    <i class="fa-solid fa-trash"></i> ลบ
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if(count($cats) == 0): ?>
                        <div class="text-center py-10 text-slate-500">ยังไม่มีข้อมูล</div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<?php echo "</div></main></body></html>"; ?>
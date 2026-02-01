<?php include 'admin_auth.php'; ?>
<?php
// admin/products.php
require_once 'header.php';

// --- Logic เดิม (คงไว้) ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // ลบรูปภาพเก่า
    $stmt = $pdo->prepare("SELECT img FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists("../" . $img)) { unlink("../" . $img); }

    // ลบจาก Database
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo "<script>Swal.fire('ลบสำเร็จ', 'ลบสินค้าเรียบร้อยแล้ว', 'success').then(() => window.location='products.php');</script>";
    }
}

// ดึงข้อมูลสินค้า
$sql = "SELECT p.*, c.name as category_name, 
        (SELECT COUNT(*) FROM stocks s WHERE s.product_id = p.id AND s.is_sold = 0) as stock_count
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.id DESC";
$products = $pdo->query($sql)->fetchAll();
?>

<style>
    .glass-panel {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .badge-gacha {
        background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
        color: #000;
        text-shadow: 0px 1px 0px rgba(255,255,255,0.4);
    }
</style>

<div class="container mx-auto px-4 pb-12 pt-6 max-w-7xl">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-bold text-white flex items-center gap-3">
                <i class="fa-solid fa-boxes-stacked text-indigo-400"></i> จัดการสินค้า
            </h2>
            <p class="text-slate-400 mt-1">รายการสินค้าทั้งหมด <span class="text-indigo-400 font-bold"><?php echo count($products); ?></span> รายการ</p>
        </div>
        
        <a href="product_form.php" 
           class="group relative inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/30 transition-all hover:-translate-y-1 overflow-hidden">
            <span class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></span>
            <i class="fa-solid fa-plus relative z-10"></i> 
            <span class="relative z-10">เพิ่มสินค้าใหม่</span>
        </a>
    </div>

    <div class="hidden md:block glass-panel rounded-2xl overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-900/80 text-slate-400 uppercase text-xs tracking-wider font-semibold">
                <tr>
                    <th class="p-5 w-24 text-center">รูปภาพ</th>
                    <th class="p-5">ชื่อสินค้า</th>
                    <th class="p-5">หมวดหมู่</th>
                    <th class="p-5 text-right">ราคา</th>
                    <th class="p-5 text-center">สถานะสต็อก</th>
                    <th class="p-5 text-center w-40">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/50 text-slate-300">
                <?php if(count($products) == 0): ?>
                    <tr><td colspan="6" class="p-12 text-center text-slate-500">
                        <i class="fa-solid fa-box-open text-4xl mb-3 opacity-50"></i><br>ยังไม่มีสินค้าในระบบ
                    </td></tr>
                <?php endif; ?>

                <?php foreach($products as $p): ?>
                <tr class="hover:bg-slate-800/40 transition-colors group">
                    <td class="p-4 text-center">
                        <div class="w-16 h-16 rounded-lg overflow-hidden border border-slate-600 mx-auto relative">
                            <img src="../<?php echo $p->img; ?>" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                            <?php if($p->is_gacha): ?>
                                <div class="absolute top-0 right-0 badge-gacha text-[10px] px-1 font-bold shadow-sm">Gacha</div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="p-4">
                        <div class="font-bold text-white text-lg group-hover:text-indigo-400 transition-colors"><?php echo $p->name; ?></div>
                        <div class="text-xs text-slate-500 mt-1">ID: #<?php echo $p->id; ?></div>
                    </td>
                    <td class="p-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-300 border border-slate-600">
                            <?php echo $p->category_name ?? 'Uncategorized'; ?>
                        </span>
                    </td>
                    <td class="p-4 text-right">
                        <span class="font-mono text-emerald-400 font-bold text-lg">฿<?php echo number_format($p->price, 2); ?></span>
                    </td>
                    <td class="p-4 text-center">
                        <?php if($p->stock_count > 0): ?>
                            <div class="inline-flex flex-col items-center">
                                <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    พร้อมส่ง
                                </span>
                                <span class="text-xs text-slate-500 mt-1"><?php echo number_format($p->stock_count); ?> ชิ้น</span>
                            </div>
                        <?php else: ?>
                            <span class="bg-rose-500/10 text-rose-400 border border-rose-500/20 px-3 py-1 rounded-full text-xs font-bold">
                                หมด (Out of Stock)
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4">
                        <div class="flex justify-center items-center gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                            <a href="product_form.php?id=<?php echo $p->id; ?>" 
                               class="w-9 h-9 flex items-center justify-center rounded-lg bg-indigo-500/20 text-indigo-400 hover:bg-indigo-500 hover:text-white transition-colors border border-indigo-500/30" 
                               title="แก้ไข / เติมของ">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <button onclick="confirmDelete(<?php echo $p->id; ?>)" 
                                    class="w-9 h-9 flex items-center justify-center rounded-lg bg-rose-500/20 text-rose-400 hover:bg-rose-500 hover:text-white transition-colors border border-rose-500/30" 
                                    title="ลบสินค้า">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:hidden">
        <?php foreach($products as $p): ?>
        <div class="glass-panel p-4 rounded-xl relative overflow-hidden group">
            
            <?php if($p->is_gacha): ?>
                <div class="absolute top-3 right-3 z-10">
                    <span class="badge-gacha text-xs px-2 py-1 rounded-md font-bold shadow-lg">
                        <i class="fa-solid fa-dice"></i> Gacha
                    </span>
                </div>
            <?php endif; ?>

            <div class="flex gap-4">
                <div class="w-20 h-20 rounded-lg overflow-hidden border border-slate-600 shrink-0 bg-slate-900">
                    <img src="../<?php echo $p->img; ?>" class="w-full h-full object-cover">
                </div>
                
                <div class="flex-grow min-w-0">
                    <h3 class="text-white font-bold truncate pr-14"><?php echo $p->name; ?></h3>
                    <div class="text-xs text-slate-400 mb-1"><?php echo $p->category_name ?? '-'; ?></div>
                    <div class="flex items-center justify-between mt-2">
                        <div class="text-emerald-400 font-bold font-mono">฿<?php echo number_format($p->price, 2); ?></div>
                        
                        <?php if($p->stock_count > 0): ?>
                            <span class="text-xs text-emerald-400 flex items-center gap-1 bg-emerald-500/10 px-2 py-0.5 rounded">
                                <i class="fa-solid fa-check-circle"></i> <?php echo $p->stock_count; ?>
                            </span>
                        <?php else: ?>
                            <span class="text-xs text-rose-400 bg-rose-500/10 px-2 py-0.5 rounded">หมด</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 mt-4 pt-3 border-t border-slate-700/50">
                <a href="product_form.php?id=<?php echo $p->id; ?>" 
                   class="flex items-center justify-center py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-indigo-400 text-sm font-medium transition-colors">
                    <i class="fa-solid fa-pen-to-square mr-2"></i> แก้ไข
                </a>
                <button onclick="confirmDelete(<?php echo $p->id; ?>)" 
                        class="flex items-center justify-center py-2 rounded-lg bg-slate-800 hover:bg-rose-900/30 text-rose-400 text-sm font-medium transition-colors">
                    <i class="fa-solid fa-trash mr-2"></i> ลบ
                </button>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if(count($products) == 0): ?>
            <div class="col-span-full text-center py-10 text-slate-500">
                <i class="fa-solid fa-box-open text-4xl mb-2"></i>
                <p>ไม่มีสินค้า</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: "ข้อมูลสินค้าและสต็อกทั้งหมดจะหายไป กู้คืนไม่ได้นะ!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48', // Rose 600
            cancelButtonColor: '#334155', // Slate 700
            confirmButtonText: 'ใช่, ลบเลย!',
            cancelButtonText: 'ยกเลิก',
            background: '#1e293b',
            color: '#fff',
            iconColor: '#fb7185'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location = `products.php?delete=${id}`;
            }
        })
    }
</script>

<?php 
echo "</div></main></body></html>"; 
?>
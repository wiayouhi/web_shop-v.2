<?php include 'db.php'; ?>
<?php
require_once 'header.php';

// 1. รับค่าค้นหาและหมวดหมู่
$search = $_GET['search'] ?? '';
$cat_id = $_GET['cat'] ?? '';

// 2. สร้าง Query แบบ Dynamic
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND name LIKE ?";
    $params[] = "%$search%";
}

if ($cat_id && $cat_id != 'all') { // เพิ่มเงื่อนไข != all เผื่อมีการส่งค่านี้มา
    $sql .= " AND category_id = ?";
    $params[] = $cat_id;
}

$sql .= " ORDER BY id DESC"; // สินค้าใหม่มาก่อน
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// 3. ดึงหมวดหมู่ทั้งหมดมาทำเมนู
$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC")->fetchAll();
?>

<style>
    /* ซ่อน Scrollbar แต่ยังเลื่อนได้ */
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Animation: Fade Up */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .fade-up-element {
        opacity: 0; /* เริ่มต้นซ่อนไว้ก่อน */
        animation: fadeUp 0.6s ease-out forwards;
    }

    /* ปุ่ม Shine Effect */
    .btn-shine {
        position: relative;
        overflow: hidden;
    }
    .btn-shine::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: 0.5s;
    }
    .group:hover .btn-shine::after {
        left: 100%;
    }
</style>

<div class="container mx-auto py-12 px-4 min-h-screen">
    
    <div class="flex flex-col md:flex-row justify-between items-end gap-6 mb-10 fade-up-element" style="animation-delay: 0s;">
        <div>
            <h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white via-gray-200 to-gray-400 drop-shadow-lg mb-2">
                ร้านค้าทั้งหมด
            </h1>
            <p class="text-gray-400 font-light text-lg">เลือกซื้อสินค้าดิจิทัลคุณภาพเยี่ยม ราคาสุดคุ้ม</p>
        </div>

        <form class="w-full md:w-1/3 relative group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fa-solid fa-magnifying-glass text-gray-400 group-focus-within:text-theme-main transition-colors"></i>
            </div>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                   placeholder="ค้นหาสินค้า (ชื่อเกม, ไอเทม)..." 
                   class="w-full bg-slate-800/50 backdrop-blur border border-slate-600/50 rounded-2xl py-3.5 pl-12 pr-4 text-white placeholder-gray-500 focus:border-theme-main focus:ring-1 focus:ring-theme-main focus:bg-slate-800/80 transition-all shadow-lg">
            <?php if($cat_id): ?>
                <input type="hidden" name="cat" value="<?php echo $cat_id; ?>">
            <?php endif; ?>
        </form>
    </div>

    <div class="flex flex-wrap gap-3 mb-10 overflow-x-auto pb-4 scrollbar-hide fade-up-element" style="animation-delay: 0.1s;">
        <a href="shop.php" class="px-6 py-2.5 rounded-full border transition-all whitespace-nowrap font-medium text-sm md:text-base 
            <?php echo ($cat_id == '' || $cat_id == 'all') 
                ? 'bg-theme-main border-theme-main text-white shadow-[0_0_15px_rgba(var(--theme-main-rgb),0.4)]' 
                : 'bg-slate-800/40 border-slate-700 text-gray-400 hover:border-theme-main hover:text-white hover:bg-slate-800'; ?>">
            <i class="fa-solid fa-border-all mr-2"></i>ทั้งหมด
        </a>
        <?php foreach($categories as $c): ?>
            <a href="shop.php?cat=<?php echo $c->id; ?>" class="px-6 py-2.5 rounded-full border transition-all whitespace-nowrap font-medium text-sm md:text-base 
                <?php echo $cat_id == $c->id 
                    ? 'bg-theme-main border-theme-main text-white shadow-[0_0_15px_rgba(var(--theme-main-rgb),0.4)]' 
                    : 'bg-slate-800/40 border-slate-700 text-gray-400 hover:border-theme-main hover:text-white hover:bg-slate-800'; ?>">
                <?php echo $c->name; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if(count($products) > 0): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach($products as $index => $p): 
                // เช็คสต็อก
                $stock_stmt = $pdo->prepare("SELECT COUNT(*) FROM stocks WHERE product_id = ? AND is_sold = 0");
                $stock_stmt->execute([$p->id]);
                $stock_count = $stock_stmt->fetchColumn();
                
                // คำนวณ delay ให้เด้งขึ้นทีละชิ้น
                $delay = 0.2 + ($index * 0.05); 
            ?>
            
            <div class="fade-up-element group relative bg-slate-800/40 backdrop-blur-sm border border-white/10 rounded-2xl overflow-hidden flex flex-col h-full hover:-translate-y-2 hover:shadow-2xl hover:shadow-theme-main/10 transition-all duration-300" 
                 style="animation-delay: <?php echo $delay; ?>s;">
                
                <?php if($p->is_gacha): ?>
                    <div class="absolute top-3 right-3 bg-gradient-to-r from-yellow-500 to-amber-600 text-white text-[10px] font-bold px-2 py-1 rounded-md shadow-lg z-20 flex items-center gap-1 animate-pulse">
                        <i class="fa-solid fa-star"></i> GACHA
                    </div>
                <?php endif; ?>

                <div class="h-48 overflow-hidden relative">
                    <img src="<?php echo $p->img; ?>" class="w-full h-full object-cover transition duration-700 group-hover:scale-110 group-hover:rotate-1">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-transparent to-transparent opacity-60"></div>
                    
                    <?php if($stock_count == 0): ?>
                        <div class="absolute inset-0 bg-black/80 backdrop-blur-[2px] z-10 flex items-center justify-center">
                            <span class="text-red-500 font-black border-4 border-red-500 px-6 py-2 rounded-xl -rotate-12 text-xl tracking-widest opacity-90">SOLD OUT</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="p-5 flex flex-col flex-grow">
                    <h3 class="font-bold text-lg text-white mb-1 truncate group-hover:text-theme-main transition-colors" title="<?php echo $p->name; ?>">
                        <?php echo $p->name; ?>
                    </h3>
                    <p class="text-xs text-gray-400 mb-4 line-clamp-2 leading-relaxed">
                        <?php echo strip_tags($p->description); ?>
                    </p>
                    
                    <div class="mt-auto pt-4 border-t border-white/10 flex items-end justify-between">
                        <div>
                            <p class="text-[10px] text-gray-500 mb-0.5">ราคา</p>
                            <div class="text-xl md:text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-theme-main to-purple-400">
                                ฿ <?php echo number_format($p->price, 0); ?>
                            </div>
                        </div>
                        
                        <a href="product_detail.php?id=<?php echo $p->id; ?>" 
                           class="btn-shine relative overflow-hidden px-4 py-2 rounded-xl transition-all font-bold text-sm shadow-lg flex items-center gap-2
                           <?php echo $stock_count > 0 
                                ? 'bg-white text-slate-900 hover:bg-theme-main hover:text-white hover:shadow-theme-main/40' 
                                : 'bg-slate-700 text-gray-400 cursor-not-allowed opacity-50'; ?>">
                            <?php if($stock_count > 0): ?>
                                <span>ซื้อ</span> <i class="fa-solid fa-cart-shopping"></i>
                            <?php else: ?>
                                <span>หมด</span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="fade-up-element text-center py-24 bg-slate-800/30 backdrop-blur rounded-3xl border border-dashed border-slate-700">
            <div class="w-24 h-24 bg-slate-700/50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fa-solid fa-box-open text-5xl text-gray-500"></i>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">ไม่พบสินค้าที่คุณค้นหา</h3>
            <p class="text-gray-400 mb-6">ลองเปลี่ยนคำค้นหา หรือเลือกหมวดหมู่อื่น</p>
            <a href="shop.php" class="inline-flex items-center gap-2 text-theme-main hover:text-white transition bg-theme-main/10 hover:bg-theme-main px-6 py-2 rounded-full font-medium">
                <i class="fa-solid fa-rotate-left"></i> ดูสินค้าทั้งหมด
            </a>
        </div>
    <?php endif; ?>

</div>

<?php require_once 'footer.php'; ?>
<?php include 'db.php'; ?>
<?php
require_once 'header.php';

// --- 1. ดึงข้อมูลสถิติ (Stats) ---
$user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$sold_count = $pdo->query("SELECT COUNT(*) FROM stocks WHERE is_sold = '1'")->fetchColumn(); 
$stock_count = $pdo->query("SELECT COUNT(*) FROM stocks WHERE is_sold = '0'")->fetchColumn();

// --- 2. ดึงหมวดหมู่และสินค้า ---
$cats = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC")->fetchAll();
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 8")->fetchAll();

// --- 3. ส่วนจัดการข้อมูลแบนเนอร์หลัก ---
$banners = [];
if (!empty($web_config->banner_img)) {
    $decoded = json_decode($web_config->banner_img, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $banners = $decoded; 
    } else {
        $banners[] = $web_config->banner_img; 
    }
}
?>


<div class="mb-8 relative rounded-3xl overflow-hidden shadow-2xl group h-[350px] md:h-[450px] bg-slate-900 border border-slate-700/50">
    
    <?php if(!empty($banners)): ?>
        <div id="banner-slider" class="relative w-full h-full">
            <?php foreach($banners as $index => $img): ?>
                <div class="banner-slide absolute inset-0 transition-opacity duration-1000 ease-in-out <?php echo $index === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0'; ?>">
                    <img src="<?php echo trim($img); ?>" class="banner-img w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/20 to-transparent opacity-90"></div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if(count($banners) > 1): ?>
        <div class="absolute bottom-6 right-6 md:right-auto md:left-1/2 md:transform md:-translate-x-1/2 flex space-x-3 z-30">
            <?php foreach($banners as $index => $img): ?>
                <button onclick="changeSlide(<?php echo $index; ?>)" class="w-10 h-1.5 rounded-full transition-all duration-300 banner-dot <?php echo $index === 0 ? 'bg-white w-12 shadow-[0_0_10px_rgba(255,255,255,0.8)]' : 'bg-white/30 hover:bg-white/60'; ?>"></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="w-full h-full bg-gradient-to-br from-violet-900 to-slate-900 flex items-center justify-center relative overflow-hidden">
             <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-20"></div>
            <h1 class="text-4xl md:text-7xl font-black text-white/10 select-none tracking-widest">STORE</h1>
        </div>
    <?php endif; ?>
    
    <div class="absolute bottom-0 left-0 w-full p-6 md:p-12 z-20 pointer-events-none">
        <div class="fade-up-element" style="animation-delay: 0.2s;">
            <h2 class="text-3xl md:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white via-gray-200 to-gray-400 drop-shadow-lg mb-2">
                <?php echo $web_config->site_name; ?>
            </h2>
            <p class="text-gray-300 text-base md:text-xl font-light max-w-2xl drop-shadow-md leading-relaxed line-clamp-2">
                <?php 
                    echo !empty($web_config->site_description) 
                        ? $web_config->site_description 
                        : 'แหล่งรวมไอดีเกมและสินค้าดิจิตอลคุณภาพ อันดับ 1'; 
                ?>
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    let currentSlide = 0;
    const slides = document.querySelectorAll('.banner-slide');
    const imgs = document.querySelectorAll('.banner-img');
    const dots = document.querySelectorAll('.banner-dot');
    const totalSlides = slides.length;
    let slideInterval;

    function showSlide(index) {
        if (totalSlides <= 1) return;
        slides.forEach((s, i) => {
            s.classList.remove('opacity-100', 'z-10');
            s.classList.add('opacity-0', 'z-0');
            imgs[i].classList.remove('animate-kenburns');
        });
        dots.forEach(d => {
            d.classList.remove('bg-white', 'w-12', 'shadow-[0_0_10px_rgba(255,255,255,0.8)]');
            d.classList.add('bg-white/30');
        });
        slides[index].classList.remove('opacity-0', 'z-0');
        slides[index].classList.add('opacity-100', 'z-10');
        imgs[index].classList.add('animate-kenburns');
        if (dots[index]) {
            dots[index].classList.remove('bg-white/30');
            dots[index].classList.add('bg-white', 'w-12', 'shadow-[0_0_10px_rgba(255,255,255,0.8)]');
        }
        currentSlide = index;
    }
    function nextSlide() {
        let next = (currentSlide + 1) % totalSlides;
        showSlide(next);
    }
    window.changeSlide = function (index) {
        showSlide(index);
        clearInterval(slideInterval);
        slideInterval = setInterval(nextSlide, 6000);
    }
    if (totalSlides > 1) {
        imgs[0].classList.add('animate-kenburns');
        slideInterval = setInterval(nextSlide, 6000);
    }
});
</script>


<div class="mb-12 grid grid-cols-2 md:grid-cols-4 gap-4 px-2">
    <div class="bg-slate-800/50 backdrop-blur border border-white/5 p-4 rounded-2xl flex items-center gap-4 hover:bg-slate-800 transition">
        <div class="w-12 h-12 rounded-xl bg-green-500/10 text-green-500 flex items-center justify-center text-2xl">
            <i class="fa-solid fa-cart-arrow-down"></i>
        </div>
        <div>
            <h4 class="text-2xl font-bold text-white"><?php echo number_format($sold_count); ?></h4>
            <p class="text-xs text-gray-400">สินค้าที่ขายแล้ว</p>
        </div>
    </div>
    
    <div class="bg-slate-800/50 backdrop-blur border border-white/5 p-4 rounded-2xl flex items-center gap-4 hover:bg-slate-800 transition">
        <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center text-2xl">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <h4 class="text-2xl font-bold text-white"><?php echo number_format($user_count); ?></h4>
            <p class="text-xs text-gray-400">สมาชิกทั้งหมด</p>
        </div>
    </div>

    <div class="bg-slate-800/50 backdrop-blur border border-white/5 p-4 rounded-2xl flex items-center gap-4 hover:bg-slate-800 transition">
        <div class="w-12 h-12 rounded-xl bg-orange-500/10 text-orange-500 flex items-center justify-center text-2xl">
            <i class="fa-solid fa-box"></i>
        </div>
        <div>
            <h4 class="text-2xl font-bold text-white"><?php echo number_format($stock_count); ?></h4>
            <p class="text-xs text-gray-400">สินค้าพร้อมส่ง</p>
        </div>
    </div>

    <div class="bg-slate-800/50 backdrop-blur border border-white/5 p-4 rounded-2xl flex items-center gap-4 hover:bg-slate-800 transition">
        <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center text-2xl">
            <i class="fa-regular fa-clock"></i>
        </div>
        <div>
            <h4 class="text-xl font-bold text-white" id="clock">00:00:00</h4>
            <p class="text-xs text-gray-400"><?php echo date('d M Y'); ?></p>
        </div>
    </div>
</div>

<script>
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('th-TH', { hour12: false });
        document.getElementById('clock').innerText = timeString;
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>


<div class="mb-16 px-2">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-1.5 h-8 bg-gradient-to-b from-theme-main to-purple-600 rounded-full"></div>
        <h3 class="text-2xl font-bold text-white tracking-wide">หมวดหมู่สินค้า</h3>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <a href="shop.php?cat=all" class="relative group overflow-hidden bg-slate-800/40 backdrop-blur-md border border-white/5 p-4 rounded-2xl text-center hover:bg-theme-main/90 transition-all duration-300 hover:-translate-y-2 hover:shadow-lg hover:shadow-theme-main/20">
            <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition duration-500"></div>
            <div class="w-16 h-16 mx-auto bg-slate-700/50 rounded-2xl flex items-center justify-center mb-3 group-hover:bg-white/20 group-hover:scale-110 transition duration-300 shadow-inner">
                <i class="fa-solid fa-border-all text-2xl text-gray-400 group-hover:text-white"></i>
            </div>
            <span class="font-semibold text-gray-300 group-hover:text-white tracking-wide">ทั้งหมด</span>
        </a>

        <?php foreach($cats as $cat): ?>
        <a href="shop.php?cat=<?php echo $cat->id; ?>" class="relative group overflow-hidden bg-slate-800/40 backdrop-blur-md border border-white/5 p-4 rounded-2xl text-center hover:bg-theme-main/90 transition-all duration-300 hover:-translate-y-2 hover:shadow-lg hover:shadow-theme-main/20">
            <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition duration-500"></div>
            <img src="<?php echo $cat->img; ?>" class="w-16 h-16 mx-auto rounded-2xl object-cover mb-3 bg-slate-800 shadow-md group-hover:scale-110 transition duration-300">
            <span class="font-semibold text-gray-300 group-hover:text-white tracking-wide"><?php echo $cat->name; ?></span>
        </a>
        <?php endforeach; ?>
    </div>
</div>


<div class="px-2">
    <div class="flex justify-between items-end mb-8 border-b border-white/10 pb-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-orange-500/10 flex items-center justify-center text-orange-500">
                <i class="fa-solid fa-fire text-2xl animate-pulse"></i>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-white leading-none">สินค้ามาใหม่</h3>
                <p class="text-sm text-gray-400 mt-1">อัพเดทล่าสุดวันนี้</p>
            </div>
        </div>
        <a href="shop.php" class="group flex items-center gap-2 text-slate-400 hover:text-white text-sm transition font-medium bg-slate-800/50 px-4 py-2 rounded-lg hover:bg-slate-700">
            ดูทั้งหมด <i class="fa-solid fa-arrow-right transform group-hover:translate-x-1 transition"></i>
        </a>
    </div>

    <?php if(count($products) > 0): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 md:gap-8">
        <?php foreach($products as $index => $p): 
            // Check Stock
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM stocks WHERE product_id = ? AND is_sold = 0");
            $stmt->execute([$p->id]);
            $stock = $stmt->fetchColumn();
            $delay = $index * 0.1; 
        ?>
        
        <div class="product-card fade-up-element group relative bg-slate-800/40 backdrop-blur-sm border border-white/10 rounded-2xl overflow-hidden flex flex-col h-full" style="animation-delay: <?php echo $delay; ?>s;">
            
            <?php if(isset($p->is_gacha) && $p->is_gacha): ?>
                <div class="absolute top-3 right-3 bg-gradient-to-r from-yellow-500 to-amber-600 text-white text-[10px] font-bold px-2 py-1 rounded-md shadow-lg z-20 flex items-center gap-1">
                    <i class="fa-solid fa-star animate-spin-slow"></i> GACHA
                </div>
            <?php endif; ?>

            <div class="relative overflow-hidden aspect-[16/9] md:aspect-[4/3]">
                <img src="<?php echo $p->img; ?>" class="w-full h-full object-cover transition duration-700 group-hover:scale-110 group-hover:rotate-1">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent opacity-60"></div>
                
                <?php if($stock == 0): ?>
                    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-10">
                        <span class="text-red-500 font-black border-4 border-red-500 px-6 py-2 rounded-xl -rotate-12 text-2xl tracking-widest opacity-80">SOLD OUT</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="p-5 flex flex-col flex-grow relative">
                <h4 class="font-bold text-lg text-white mb-2 line-clamp-1 group-hover:text-theme-main transition-colors" title="<?php echo $p->name; ?>"><?php echo $p->name; ?></h4>
                
                <div class="flex items-center gap-2 text-sm mb-4 bg-slate-900/50 w-fit px-3 py-1 rounded-full border border-white/5">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full <?php echo $stock > 0 ? 'bg-green-400' : 'bg-red-400'; ?> opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 <?php echo $stock > 0 ? 'bg-green-500' : 'bg-red-500'; ?>"></span>
                    </span>
                    <span class="text-gray-400">สต็อก: <span class="<?php echo $stock > 0 ? 'text-green-400 font-bold' : 'text-red-400'; ?>"><?php echo $stock; ?></span></span>
                </div>

                <div class="mt-auto pt-4 border-t border-white/10 flex justify-between items-center gap-3">
                    <div class="flex flex-col">
                        <span class="text-xs text-gray-500 font-light">ราคา</span>
                        <span class="text-xl md:text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-theme-main to-purple-400">
                            ฿<?php echo number_format($p->price, 0); ?>
                        </span>
                    </div>
                    
                    <a href="product_detail.php?id=<?php echo $p->id; ?>" class="btn-shine relative overflow-hidden bg-white text-slate-900 hover:bg-theme-main hover:text-white px-5 py-2.5 rounded-xl transition-all font-bold shadow-lg shadow-white/5 hover:shadow-theme-main/40 text-sm flex items-center gap-2 group/btn">
                        <span>ซื้อสินค้า</span>
                        <i class="fa-solid fa-cart-shopping transition-transform group-hover/btn:translate-x-1"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <div class="flex flex-col items-center justify-center py-20 bg-slate-800/30 backdrop-blur-md rounded-3xl border border-dashed border-slate-600">
            <div class="w-24 h-24 bg-slate-700/50 rounded-full flex items-center justify-center mb-6 animate-bounce">
                <i class="fa-solid fa-box-open text-4xl text-slate-400"></i>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">ยังไม่มีสินค้าในหมวดหมู่นี้</h3>
            <p class="text-slate-400">กรุณากลับมาเช็คใหม่ภายหลัง</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
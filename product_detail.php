<?php include 'db.php'; ?>
<?php
require_once 'header.php';

// รับค่า ID สินค้า
if (!isset($_GET['id'])) {
    echo "<script>window.location='index.php';</script>";
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='container mx-auto py-20 text-center text-red-500'>ไม่พบสินค้านี้</div>";
    require_once 'footer.php';
    exit;
}

// เช็คสต็อกสินค้า
$stmt = $pdo->prepare("SELECT COUNT(*) FROM stocks WHERE product_id = ? AND is_sold = 0");
$stmt->execute([$id]);
$stock = $stmt->fetchColumn();

// --- ดึงรูปภาพ Gallery เพิ่มเติม ---
$stmt_img = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ?");
$stmt_img->execute([$id]);
$gallery = $stmt_img->fetchAll();
?>

<style>
    /* Animation Fade Up */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .fade-up-element {
        animation: fadeUp 0.6s ease-out forwards;
    }

    /* ปุ่ม Shine */
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
    .btn-shine:hover::after {
        left: 100%;
    }

    /* Custom Scrollbar สำหรับ Description */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(0,0,0,0.1);
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.2);
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(255,255,255,0.4);
    }
</style>

<div class="container mx-auto py-12 px-4 min-h-screen">
    
    <div class="mb-6 fade-up-element" style="animation-delay: 0s;">
        <a href="shop.php" class="inline-flex items-center gap-2 text-gray-400 hover:text-white transition group">
            <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> กลับไปหน้าร้านค้า
        </a>
    </div>

    <div class="bg-slate-800/40 backdrop-blur-md rounded-3xl p-6 md:p-10 shadow-2xl border border-white/10 relative overflow-hidden fade-up-element" style="animation-delay: 0.1s;">
        
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-theme-main/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 relative z-10">
            
            <div class="flex flex-col gap-4">
                <div class="relative group w-full aspect-square rounded-2xl overflow-hidden bg-slate-900/50 border border-white/5 shadow-inner">
                    
                    <img id="mainImage" src="<?php echo $product->img; ?>" class="w-full h-full object-contain p-4 transition-all duration-500 group-hover:scale-105">
                    
                    <?php if($product->is_gacha): ?>
                        <div class="absolute top-4 right-4 bg-gradient-to-r from-yellow-500 to-amber-600 text-white font-bold px-4 py-1.5 rounded-full shadow-lg z-10 animate-pulse flex items-center gap-2">
                            <i class="fa-solid fa-star animate-spin-slow"></i> สินค้าสุ่ม
                        </div>
                    <?php endif; ?>

                    <?php if($stock == 0): ?>
                        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-20">
                            <span class="text-red-500 font-black border-4 border-red-500 px-8 py-3 rounded-2xl -rotate-12 text-4xl tracking-widest uppercase">Sold Out</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-5 gap-3 select-none">
                    <div class="cursor-pointer rounded-xl overflow-hidden border-2 border-theme-main opacity-100 transition-all duration-300 hover:opacity-100 aspect-square bg-slate-900 thumbnail-item"
                         onclick="changeImage('<?php echo $product->img; ?>', this)">
                        <img src="<?php echo $product->img; ?>" class="w-full h-full object-cover">
                    </div>
                    
                    <?php foreach($gallery as $img): ?>
                        <div class="cursor-pointer rounded-xl overflow-hidden border-2 border-transparent hover:border-white/50 opacity-60 hover:opacity-100 transition-all duration-300 aspect-square bg-slate-900 thumbnail-item"
                             onclick="changeImage('<?php echo $img->img_path; ?>', this)">
                            <img src="<?php echo $img->img_path; ?>" class="w-full h-full object-cover">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex flex-col h-full">
                <h1 class="text-3xl md:text-5xl font-bold text-white mb-4 leading-tight drop-shadow-lg">
                    <?php echo $product->name; ?>
                </h1>

                <div class="flex items-end gap-4 mb-8 pb-6 border-b border-white/10">
                    <div>
                        <p class="text-gray-400 text-sm mb-1">ราคา</p>
                        <div class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-theme-main to-purple-400">
                            ฿ <?php echo number_format($product->price, 0); ?>
                        </div>
                    </div>
                    <div class="h-10 w-[1px] bg-white/10 mx-2"></div>
                    <div>
                        <p class="text-gray-400 text-sm mb-1">สถานะ</p>
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-3 w-3">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full <?php echo $stock > 0 ? 'bg-green-400' : 'bg-red-400'; ?> opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-3 w-3 <?php echo $stock > 0 ? 'bg-green-500' : 'bg-red-500'; ?>"></span>
                            </span>
                            <span class="font-bold text-lg <?php echo $stock > 0 ? 'text-green-400' : 'text-red-400'; ?>">
                                <?php echo $stock > 0 ? "เหลือ {$stock} ชิ้น" : "สินค้าหมด"; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex-grow mb-8">
                    <h3 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-align-left text-theme-main"></i> รายละเอียดสินค้า
                    </h3>
                    <div class="bg-slate-900/50 rounded-2xl p-5 border border-white/5 h-[200px] overflow-y-auto custom-scrollbar">
                        <div class="text-gray-300 text-sm leading-relaxed whitespace-pre-line">
                            <?php echo $product->description ? $product->description : 'ไม่มีรายละเอียดเพิ่มเติม'; ?>
                        </div>
                    </div>
                </div>

                <?php if($stock > 0): ?>
                    <div class="bg-slate-900/80 p-5 rounded-2xl border border-white/10 shadow-lg">
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                                <div class="flex items-center justify-between bg-slate-800 rounded-xl border border-slate-600 w-full sm:w-40 h-14 px-2">
                                    <button onclick="updateQty(-1)" class="w-10 h-10 rounded-lg hover:bg-slate-700 text-gray-400 hover:text-white transition flex items-center justify-center">
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                    <input type="number" id="qty" value="1" min="1" max="<?php echo $stock; ?>" class="w-full bg-transparent text-center text-white font-bold text-xl focus:outline-none" onchange="checkMax(this, <?php echo $stock; ?>)">
                                    <button onclick="updateQty(1)" class="w-10 h-10 rounded-lg hover:bg-slate-700 text-gray-400 hover:text-white transition flex items-center justify-center">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>

                                <button onclick="buyItem(<?php echo $product->id; ?>)" class="btn-shine flex-1 bg-gradient-to-r from-theme-main to-purple-600 hover:to-purple-500 text-white font-bold text-lg h-14 rounded-xl shadow-lg shadow-theme-main/30 transition transform hover:-translate-y-1 flex justify-center items-center gap-2">
                                    <?php if($product->is_gacha): ?>
                                        <i class="fa-solid fa-dice"></i> สุ่มรางวัลเลย
                                    <?php else: ?>
                                        <i class="fa-solid fa-cart-shopping"></i> สั่งซื้อสินค้า
                                    <?php endif; ?>
                                </button>
                            </div>
                        <?php else: ?>
                            <a href="login.php" class="flex items-center justify-center gap-2 w-full bg-slate-700 hover:bg-slate-600 text-white font-bold py-4 rounded-xl transition shadow-lg">
                                <i class="fa-solid fa-right-to-bracket"></i> เข้าสู่ระบบเพื่อสั่งซื้อ
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <button disabled class="w-full bg-slate-800/50 border border-slate-700 text-gray-500 font-bold py-4 rounded-xl cursor-not-allowed uppercase tracking-wide">
                        สินค้าหมดชั่วคราว
                    </button>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
    // ฟังก์ชันเปลี่ยนรูปภาพหลัก (พร้อม Animation)
    function changeImage(src, element) {
        const mainImg = document.getElementById('mainImage');
        
        // Reset border styling
        document.querySelectorAll('.thumbnail-item').forEach(el => {
            el.classList.remove('border-theme-main', 'opacity-100');
            el.classList.add('border-transparent', 'opacity-60');
        });

        // Highlight clicked thumbnail
        element.classList.remove('border-transparent', 'opacity-60');
        element.classList.add('border-theme-main', 'opacity-100');

        // Fade Out -> Change -> Fade In
        mainImg.style.opacity = '0';
        setTimeout(() => {
            mainImg.src = src;
            mainImg.style.opacity = '1';
        }, 200);
    }

    function updateQty(change) {
        let qtyInput = document.getElementById('qty');
        let currentQty = parseInt(qtyInput.value);
        let maxQty = parseInt(qtyInput.getAttribute('max'));
        
        let newQty = currentQty + change;
        if (newQty >= 1 && newQty <= maxQty) {
            qtyInput.value = newQty;
        }
    }

    function checkMax(input, max) {
        if (parseInt(input.value) > max) input.value = max;
        if (parseInt(input.value) < 1) input.value = 1;
    }

    function buyItem(productId) {
        let qty = document.getElementById('qty').value;
        
        Swal.fire({
            title: 'ยืนยันการสั่งซื้อ?',
            html: `คุณต้องการซื้อสินค้านี้จำนวน <span class="text-theme-main font-bold text-xl">${qty}</span> ชิ้น หรือไม่?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#7c3aed', // Theme Color
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'ใช่, สั่งซื้อเลย',
            cancelButtonText: 'ยกเลิก',
            background: '#1e293b',
            color: '#fff',
            customClass: {
                popup: 'rounded-2xl border border-slate-700 shadow-2xl'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'กำลังทำรายการ...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading() },
                    background: '#1e293b', color: '#fff'
                });

                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('qty', qty);

                fetch('api/buy.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'สั่งซื้อสำเร็จ!',
                            text: 'สินค้าถูกจัดส่งไปยังประวัติการซื้อของคุณแล้ว',
                            background: '#1e293b', color: '#fff',
                            confirmButtonColor: '#7c3aed'
                        }).then(() => {
                            window.location = 'history.php';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: data.message,
                            background: '#1e293b', color: '#fff'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'System Error',
                        text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                        background: '#1e293b', color: '#fff'
                    });
                });
            }
        });
    }
</script>

<?php require_once 'footer.php'; ?>
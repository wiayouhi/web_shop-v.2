<?php include 'admin_auth.php'; ?>
<?php
// admin/product_form.php
require_once 'header.php';

$id = isset($_GET['id']) ? $_GET['id'] : null;
$product = null;
$gallery_images = [];
$unsold_stock = [];

// --- ACTION: ลบรูปภาพแกลเลอรี่ ---
if (isset($_GET['action']) && $_GET['action'] == 'delete_img' && isset($_GET['img_id'])) {
    $img_id = $_GET['img_id'];
    $stmt = $pdo->prepare("DELETE FROM product_images WHERE id = ?");
    $stmt->execute([$img_id]);
    
    echo "<script>window.location='product_form.php?id=$id';</script>";
    exit;
}

// --- ACTION: ลบสต็อกรายชิ้น ---
if (isset($_GET['action']) && $_GET['action'] == 'delete_stock' && isset($_GET['stock_id'])) {
    $stock_id = $_GET['stock_id'];
    $stmt = $pdo->prepare("DELETE FROM stocks WHERE id = ? AND is_sold = 0");
    $stmt->execute([$stock_id]);
    
    echo "<script>window.location='product_form.php?id=$id';</script>";
    exit;
}

// --- QUERY: ดึงข้อมูลสินค้าและสต็อก ---
if ($id) {
    // 1. สินค้า
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    // 2. Gallery
    $stmt_img = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY id ASC");
    $stmt_img->execute([$id]);
    $gallery_images = $stmt_img->fetchAll();

    // 3. Stock
    $stmt_stock = $pdo->prepare("SELECT * FROM stocks WHERE product_id = ? AND is_sold = 0 ORDER BY id ASC");
    $stmt_stock->execute([$id]);
    $unsold_stock = $stmt_stock->fetchAll();
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();

// --- LOGIC: บันทึกข้อมูล ---
if (isset($_POST['save_product'])) {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $is_gacha = isset($_POST['is_gacha']) ? 1 : 0;
    
    // จัดการรูปปก
    $img_path = trim($_POST['img_url']); 

    try {
        if ($id) {
            // Update สินค้า
            $sql = "UPDATE products SET name=?, category_id=?, price=?, description=?, img=?, is_gacha=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $category_id, $price, $description, $img_path, $is_gacha, $id]);
            $product_id = $id;

            // Update สต็อกเก่า
            if (isset($_POST['edit_stock']) && is_array($_POST['edit_stock'])) {
                $stock_update_stmt = $pdo->prepare("UPDATE stocks SET contents = ? WHERE id = ? AND is_sold = 0");
                foreach ($_POST['edit_stock'] as $sid => $content) {
                    $content = trim($content);
                    if (!empty($content)) {
                        $stock_update_stmt->execute([$content, $sid]);
                    }
                }
            }

        } else {
            // Insert สินค้าใหม่
            $sql = "INSERT INTO products (name, category_id, price, description, img, is_gacha) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $category_id, $price, $description, $img_path, $is_gacha]);
            $product_id = $pdo->lastInsertId();
        }

        // --- ส่วนที่เพิ่มรูป Gallery ---
        if (!empty($_POST['gallery_urls'])) {
            $gallery_sql = "INSERT INTO product_images (product_id, img_path) VALUES (?, ?)";
            $gallery_stmt = $pdo->prepare($gallery_sql);
            
            // แยกบรรทัดและลบช่องว่าง
            $urls = explode("\n", str_replace("\r", "", $_POST['gallery_urls']));
            foreach ($urls as $url) {
                $url = trim($url);
                if (!empty($url)) {
                    $gallery_stmt->execute([$product_id, $url]);
                }
            }
        }

        // เพิ่มสต็อกใหม่
        $stock_content = trim($_POST['add_stock']);
        if (!empty($stock_content)) {
            $items = explode("\n", str_replace("\r", "", $stock_content));
            $stock_sql = "INSERT INTO stocks (product_id, contents) VALUES (?, ?)";
            $stock_stmt = $pdo->prepare($stock_sql);
            foreach ($items as $item) {
                $item = trim($item);
                if (!empty($item)) {
                    $stock_stmt->execute([$product_id, $item]);
                }
            }
        }

        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'บันทึกสำเร็จ',
                text: 'ข้อมูลสินค้าถูกอัปเดตเรียบร้อยแล้ว',
                showConfirmButton: false, timer: 1500
            }).then(() => { window.location='product_form.php?id=$product_id'; });
        </script>";

    } catch (PDOException $e) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Database Error',
                text: '" . addslashes($e->getMessage()) . "'
            });
        </script>";
    }
}
?>

<style>
    .glass-panel {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .form-input {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(148, 163, 184, 0.2);
        color: white;
        transition: all 0.2s;
    }
    .form-input:focus {
        border-color: #6366f1; /* Indigo 500 */
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        outline: none;
    }
    /* Toggle Switch */
    .toggle-checkbox:checked {
        right: 0;
        border-color: #68D391;
    }
    .toggle-checkbox:checked + .toggle-label {
        background-color: #68D391;
    }
</style>

<div class="container mx-auto px-4 pb-12 pt-6 max-w-7xl">
    
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <a href="products.php" class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-700 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                    <?php if($id): ?>
                        <i class="fa-solid fa-pen-to-square text-indigo-400"></i> แก้ไขสินค้า
                    <?php else: ?>
                        <i class="fa-solid fa-plus-circle text-emerald-400"></i> เพิ่มสินค้าใหม่
                    <?php endif; ?>
                </h2>
                <p class="text-slate-400 text-sm mt-1">จัดการรายละเอียดสินค้า รูปภาพ และสต็อกสินค้า</p>
            </div>
        </div>
    </div>

    <form method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <div class="lg:col-span-8 space-y-6">
            
            <div class="glass-panel p-6 rounded-2xl">
                <h3 class="text-lg font-bold text-white mb-4 border-b border-slate-700/50 pb-2">
                    <i class="fa-solid fa-box text-indigo-400 mr-2"></i> ข้อมูลพื้นฐาน
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-slate-400 text-sm font-medium mb-1.5">ชื่อสินค้า</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($product->name ?? ''); ?>" 
                               class="form-input w-full rounded-lg px-4 py-2.5" placeholder="กรอกชื่อสินค้า..." required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-slate-400 text-sm font-medium mb-1.5">ราคา (บาท)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-slate-500">฿</span>
                                <input type="number" step="0.01" name="price" value="<?php echo $product->price ?? ''; ?>" 
                                       class="form-input w-full rounded-lg pl-8 pr-4 py-2.5" placeholder="0.00" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-slate-400 text-sm font-medium mb-1.5">หมวดหมู่</label>
                            <select name="category_id" class="form-input w-full rounded-lg px-4 py-2.5 appearance-none cursor-pointer">
                                <option value="">-- เลือกหมวดหมู่ --</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat->id; ?>" <?php echo ($product && $product->category_id == $cat->id) ? 'selected' : ''; ?>>
                                        <?php echo $cat->name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="glass-panel p-6 rounded-2xl">
                <h3 class="text-lg font-bold text-white mb-4 border-b border-slate-700/50 pb-2">
                    <i class="fa-regular fa-images text-pink-400 mr-2"></i> รูปภาพ
                </h3>

                <div class="mb-6">
                    <label class="block text-slate-400 text-sm font-medium mb-1.5">รูปปกสินค้า (URL)</label>
                    <div class="flex gap-4 items-start">
                        <div class="flex-grow">
                            <input type="text" name="img_url" value="<?php echo $product->img ?? ''; ?>" 
                                   class="form-input w-full rounded-lg px-4 py-2.5" placeholder="https://..." required>
                            <p class="text-xs text-slate-500 mt-1">แนะนำรูปสี่เหลี่ยมจัตุรัส หรือ 16:9</p>
                        </div>
                        <?php if(isset($product->img) && $product->img): ?>
                        <div class="w-20 h-20 rounded-lg border border-slate-600 overflow-hidden shrink-0 bg-slate-800">
                            <img src="<?php echo $product->img; ?>" class="w-full h-full object-cover">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-400 text-sm font-medium mb-2">รูปเพิ่มเติม (Gallery)</label>
                    
                    <?php if(!empty($gallery_images)): ?>
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3 mb-4">
                        <?php foreach($gallery_images as $g_img): ?>
                            <div class="relative group aspect-square">
                                <img src="<?php echo $g_img->img_path; ?>" class="w-full h-full object-cover rounded-lg border border-slate-600 bg-slate-800">
                                <a href="product_form.php?id=<?php echo $id; ?>&action=delete_img&img_id=<?php echo $g_img->id; ?>" 
                                   onclick="return confirm('ลบรูปนี้?')"
                                   class="absolute top-1 right-1 bg-rose-500 hover:bg-rose-600 text-white w-6 h-6 rounded flex items-center justify-center opacity-0 group-hover:opacity-100 transition shadow-md">
                                    <i class="fa-solid fa-xmark text-sm"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <textarea name="gallery_urls" rows="3" placeholder="วางลิงก์รูปภาพที่นี่ (บรรทัดละ 1 ลิงก์)" 
                              class="form-input w-full rounded-lg px-4 py-2 text-sm font-mono"></textarea>
                </div>
            </div>

            <div class="glass-panel p-6 rounded-2xl">
                 <h3 class="text-lg font-bold text-white mb-4 border-b border-slate-700/50 pb-2">
                    <i class="fa-solid fa-list-ul text-emerald-400 mr-2"></i> รายละเอียด & ตั้งค่า
                </h3>
                
                <div class="mb-4">
                    <label class="block text-slate-400 text-sm font-medium mb-1.5">รายละเอียดสินค้า (รองรับ HTML)</label>
                    <textarea name="description" rows="5" class="form-input w-full rounded-lg px-4 py-2.5"><?php echo $product->description ?? ''; ?></textarea>
                </div>

                <div class="flex items-center justify-between bg-slate-800/50 p-4 rounded-xl border border-slate-700/50">
                    <div>
                        <span class="block text-white font-medium">ระบบสุ่ม (Gacha)</span>
                        <span class="text-xs text-slate-400">ถ้าเปิดใช้งาน สินค้าจะถูกแสดงในหน้าสุ่มรางวัล</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_gacha" value="1" class="sr-only peer" <?php echo ($product && $product->is_gacha) ? 'checked' : ''; ?>>
                        <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                    </label>
                </div>
            </div>

        </div>

        <div class="lg:col-span-4 space-y-6">
            
            <button type="submit" name="save_product" 
                    class="w-full bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-indigo-500/30 transition-all transform hover:-translate-y-1 flex items-center justify-center gap-2">
                <i class="fa-solid fa-save"></i> บันทึกข้อมูลทั้งหมด
            </button>

            <div class="glass-panel p-6 rounded-2xl border-t-4 border-emerald-500">
                <h3 class="text-lg font-bold text-white mb-3">
                    <i class="fa-solid fa-plus text-emerald-400 mr-1"></i> เพิ่มสต็อกใหม่
                </h3>
                <p class="text-xs text-slate-400 mb-3">วาง ID/Pass หรือ Code (บรรทัดละ 1 ชิ้น)</p>
                <textarea name="add_stock" rows="6" 
                          class="form-input w-full rounded-lg px-3 py-2 text-sm font-mono text-emerald-300 placeholder-slate-600" 
                          placeholder="user:pass&#10;user:pass"></textarea>
            </div>

            <?php if($id): ?>
            <div class="glass-panel p-6 rounded-2xl border-t-4 border-amber-500 flex flex-col h-[500px]">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-white">
                        <i class="fa-solid fa-layer-group text-amber-400 mr-1"></i> สต็อกคงเหลือ
                    </h3>
                    <span class="bg-amber-500/20 text-amber-400 text-xs font-bold px-2 py-1 rounded-md border border-amber-500/30">
                        <?php echo count($unsold_stock); ?> ชิ้น
                    </span>
                </div>

                <div class="flex-grow overflow-y-auto pr-2 custom-scrollbar space-y-2">
                    <?php if(count($unsold_stock) > 0): ?>
                        <?php foreach($unsold_stock as $stock): ?>
                            <div class="flex gap-2 group">
                                <input type="text" 
                                       name="edit_stock[<?php echo $stock->id; ?>]" 
                                       value="<?php echo htmlspecialchars($stock->contents); ?>" 
                                       class="form-input w-full rounded-md px-2 py-1.5 text-xs font-mono text-slate-300 focus:text-white border-slate-700 focus:border-amber-500 bg-slate-900/50">
                                
                                <a href="product_form.php?id=<?php echo $id; ?>&action=delete_stock&stock_id=<?php echo $stock->id; ?>" 
                                   onclick="return confirm('ยืนยันลบสต็อกรายการนี้?')"
                                   class="shrink-0 w-8 h-8 flex items-center justify-center rounded-md bg-rose-500/10 text-rose-500 hover:bg-rose-500 hover:text-white border border-rose-500/20 transition-colors"
                                   title="ลบรายการนี้">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="text-center text-xs text-slate-500 mt-4 pb-2">
                            -- แก้ไขข้อความแล้วกดบันทึก --
                        </div>
                    <?php else: ?>
                        <div class="h-full flex flex-col items-center justify-center text-slate-500 opacity-60">
                            <i class="fa-solid fa-box-open text-4xl mb-2"></i>
                            <span class="text-sm">สินค้าหมดสต็อก</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </form>
</div>
<?php echo "</div></main></body></html>"; ?>
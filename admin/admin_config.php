<?php
// 1. เชื่อมต่อฐานข้อมูล
if (file_exists('../db.php')) include '../db.php';
elseif (file_exists('db.php')) include 'db.php';

require_once 'header.php'; // ถ้ามี header

// --- ส่วนจัดการข้อมูล (PHP Logic) ---
$msg = "";

// 1. ลบข้อมูล (Delete)
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM popups WHERE id = ?");
    if ($stmt->execute([$_GET['delete_id']])) {
        $msg = "<div class='bg-green-500/20 text-green-400 p-3 rounded-lg mb-4'>ลบข้อมูลสำเร็จ!</div>";
    }
}

// 2. บันทึกข้อมูล (Add / Edit)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_save_popup'])) {
    $id = $_POST['popup_id'] ?? ''; // ถ้ามี ID แปลว่าแก้ไข, ถ้าไม่มีแปลว่าเพิ่มใหม่
    $enable = isset($_POST['popup_enable']) ? 1 : 0;
    
    // ข้อมูล input
    $data = [
        $enable,
        $_POST['popup_img'],
        $_POST['popup_title'],
        $_POST['popup_desc'],
        $_POST['popup_btn_text'],
        $_POST['popup_link']
    ];

    if (!empty($id)) {
        // --- แก้ไข (UPDATE) ---
        $sql = "UPDATE popups SET popup_enable=?, popup_img=?, popup_title=?, popup_desc=?, popup_btn_text=?, popup_link=? WHERE id=?";
        $data[] = $id; // เพิ่ม ID ไปใน array ตัวสุดท้าย
        $stmt = $pdo->prepare($sql);
        if($stmt->execute($data)) $msg = "<div class='bg-blue-500/20 text-blue-400 p-3 rounded-lg mb-4'>แก้ไขข้อมูลเรียบร้อย!</div>";
    } else {
        // --- เพิ่มใหม่ (INSERT) ---
        $sql = "INSERT INTO popups (popup_enable, popup_img, popup_title, popup_desc, popup_btn_text, popup_link) VALUES (?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        if($stmt->execute($data)) $msg = "<div class='bg-green-500/20 text-green-400 p-3 rounded-lg mb-4'>เพิ่ม Popup ใหม่เรียบร้อย!</div>";
    }
}

// 3. ดึงข้อมูลทั้งหมดออกมาแสดง
$stmt = $pdo->query("SELECT * FROM popups ORDER BY id DESC");
$popups = $stmt->fetchAll(PDO::FETCH_OBJ);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการ Popups แบบสุ่ม</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>@import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap'); body{font-family:'Kanit',sans-serif;}</style>
</head>
<body class="bg-slate-900 text-white min-h-screen p-6 font-sans">

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-8">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg shadow-orange-500/20">
                    <i class="fa-solid fa-layer-group text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold">ระบบสุ่ม Popup</h1>
                    <p class="text-slate-400">สร้างหลายรายการ ระบบจะสุ่มโชว์ให้ลูกค้าเห็น</p>
                </div>
            </div>
            <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-3 rounded-xl shadow-lg shadow-blue-500/30 flex items-center gap-2 transition hover:-translate-y-1">
                <i class="fa-solid fa-plus"></i> เพิ่ม Popup ใหม่
            </button>
        </div>

        <?php echo $msg; ?>

        <?php if (count($popups) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($popups as $p): ?>
                    <div class="bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden hover:border-slate-500 transition group relative">
                        <div class="absolute top-3 right-3 px-2 py-1 rounded text-xs font-bold <?php echo $p->popup_enable ? 'bg-green-500 text-black' : 'bg-red-500 text-white'; ?>">
                            <?php echo $p->popup_enable ? 'เปิดใช้งาน' : 'ปิดอยู่'; ?>
                        </div>

                        <div class="h-40 w-full bg-slate-900 flex items-center justify-center overflow-hidden">
                            <?php if($p->popup_img): ?>
                                <img src="<?php echo $p->popup_img; ?>" class="w-full h-full object-cover opacity-70 group-hover:opacity-100 transition">
                            <?php else: ?>
                                <i class="fa-regular fa-image text-4xl text-slate-700"></i>
                            <?php endif; ?>
                        </div>

                        <div class="p-5">
                            <h3 class="font-bold text-lg truncate"><?php echo $p->popup_title; ?></h3>
                            <p class="text-slate-400 text-sm line-clamp-2 mt-1 h-10"><?php echo $p->popup_desc; ?></p>
                            
                            <div class="flex gap-2 mt-4 pt-4 border-t border-slate-700">
                                <button onclick='editPopup(<?php echo json_encode($p); ?>)' class="flex-1 bg-slate-700 hover:bg-slate-600 py-2 rounded-lg text-sm transition">
                                    <i class="fa-solid fa-pen"></i> แก้ไข
                                </button>
                                <a href="?delete_id=<?php echo $p->id; ?>" onclick="return confirm('ยืนยันที่จะลบ?')" class="px-4 py-2 bg-red-500/10 hover:bg-red-500 hover:text-white text-red-500 rounded-lg transition">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20 bg-slate-800/50 rounded-3xl border border-dashed border-slate-700">
                <i class="fa-solid fa-box-open text-6xl text-slate-600 mb-4"></i>
                <p class="text-slate-400">ยังไม่มี Popup สร้างเลยสักอัน!</p>
            </div>
        <?php endif; ?>
    </div>

    <div id="popupModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        
        <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
            <div class="bg-slate-900 border border-slate-700 w-full max-w-5xl h-[90vh] rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row pointer-events-auto transform scale-95 opacity-0 transition-all duration-300" id="modalPanel">
                
                <div class="w-full md:w-1/2 p-6 md:p-8 overflow-y-auto custom-scrollbar bg-slate-800">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold" id="modalTitle">เพิ่ม Popup ใหม่</h2>
                        <button onclick="closeModal()" class="w-8 h-8 rounded-full bg-slate-700 hover:bg-slate-600 flex items-center justify-center transition"><i class="fa-solid fa-xmark"></i></button>
                    </div>

                    <form method="post">
                        <input type="hidden" name="popup_id" id="input_id">
                        
                        <div class="flex items-center justify-between bg-slate-700/30 p-4 rounded-xl mb-6">
                            <span class="text-sm font-bold text-slate-300">สถานะการแสดงผล</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="popup_enable" id="input_enable" value="1" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                            </label>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="text-xs text-slate-400 font-bold uppercase">หัวข้อ</label>
                                <input type="text" name="popup_title" id="input_title" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 mt-1 focus:ring-2 focus:ring-blue-500 focus:outline-none transition" placeholder="หัวข้อโปรโมชั่น" required>
                            </div>
                            <div>
                                <label class="text-xs text-slate-400 font-bold uppercase">รายละเอียด</label>
                                <textarea name="popup_desc" id="input_desc" rows="3" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 mt-1 focus:ring-2 focus:ring-blue-500 focus:outline-none transition" placeholder="รายละเอียด..."></textarea>
                            </div>
                            <div>
                                <label class="text-xs text-slate-400 font-bold uppercase">ลิงก์รูปภาพ</label>
                                <div class="flex gap-2">
                                    <input type="text" name="popup_img" id="input_img" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 mt-1 focus:ring-2 focus:ring-blue-500 focus:outline-none transition" placeholder="https://...">
                                    <button type="button" onclick="document.getElementById('input_img').value=''; updatePreview();" class="mt-1 px-3 bg-slate-700 rounded-lg hover:bg-slate-600"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs text-slate-400 font-bold uppercase">ข้อความปุ่ม</label>
                                    <input type="text" name="popup_btn_text" id="input_btn" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 mt-1 focus:ring-2 focus:ring-blue-500 focus:outline-none transition" value="ดูรายละเอียด">
                                </div>
                                <div>
                                    <label class="text-xs text-slate-400 font-bold uppercase">ลิ้งก์ปุ่ม</label>
                                    <input type="text" name="popup_link" id="input_link" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 mt-1 focus:ring-2 focus:ring-blue-500 focus:outline-none transition" value="#">
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 pt-4 border-t border-slate-700">
                            <button type="submit" name="btn_save_popup" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl shadow-lg transition transform hover:scale-[1.02]">
                                <i class="fa-solid fa-save mr-2"></i> บันทึกข้อมูล
                            </button>
                        </div>
                    </form>
                </div>

                <div class="w-full md:w-1/2 bg-black flex flex-col items-center justify-center p-8 relative overflow-hidden">
                    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-20"></div>
                    
                    <div class="text-center mb-4 z-10">
                        <span class="text-xs font-bold text-slate-500 bg-slate-900 px-3 py-1 rounded-full border border-slate-800">LIVE PREVIEW</span>
                    </div>

                    <div class="relative w-full max-w-xs bg-slate-900 border border-white/10 rounded-2xl shadow-2xl overflow-hidden flex flex-col transform transition-all duration-300" id="preview_box">
                        <div class="absolute top-3 right-3 w-7 h-7 bg-black/40 text-white rounded-full flex items-center justify-center border border-white/10 z-10"><i class="fa-solid fa-xmark text-sm"></i></div>
                        
                        <div id="preview_img_wrap" class="w-full h-40 bg-slate-800 hidden">
                            <img id="preview_img_el" src="" class="w-full h-full object-cover">
                        </div>

                        <div class="p-5 text-center">
                            <h3 id="preview_title_el" class="text-xl font-bold text-white mb-2 break-words">Title</h3>
                            <p id="preview_desc_el" class="text-gray-400 mb-4 text-sm font-light break-words">Description</p>
                            <span id="preview_btn_el" class="inline-block px-6 py-2 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 text-white text-sm font-bold shadow-lg shadow-purple-500/30">Button</span>
                        </div>
                    </div>

                     <div id="preview_disabled" class="hidden absolute inset-0 bg-black/80 z-20 flex flex-col items-center justify-center text-slate-500">
                        <i class="fa-solid fa-eye-slash text-4xl mb-3"></i>
                        <span>ปิดการแสดงผล</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('popupModal');
        const modalPanel = document.getElementById('modalPanel');
        
        // Input Elements
        const inputs = {
            id: document.getElementById('input_id'),
            enable: document.getElementById('input_enable'),
            title: document.getElementById('input_title'),
            desc: document.getElementById('input_desc'),
            img: document.getElementById('input_img'),
            btn: document.getElementById('input_btn'),
            link: document.getElementById('input_link'),
        };

        // Preview Elements
        const previews = {
            title: document.getElementById('preview_title_el'),
            desc: document.getElementById('preview_desc_el'),
            imgWrap: document.getElementById('preview_img_wrap'),
            img: document.getElementById('preview_img_el'),
            btn: document.getElementById('preview_btn_el'),
            disabledMsg: document.getElementById('preview_disabled'),
        };

        function updatePreview() {
            previews.title.innerText = inputs.title.value || 'ตัวอย่างหัวข้อ';
            previews.desc.innerText = inputs.desc.value || 'รายละเอียดตัวอย่าง...';
            previews.btn.innerText = inputs.btn.value || 'ปุ่มกด';

            if (inputs.img.value && inputs.img.value.trim() !== "") {
                previews.img.src = inputs.img.value;
                previews.imgWrap.classList.remove('hidden');
            } else {
                previews.imgWrap.classList.add('hidden');
            }

            if(inputs.enable.checked) {
                previews.disabledMsg.classList.add('hidden');
            } else {
                previews.disabledMsg.classList.remove('hidden');
            }
        }

        // Attach Events for Live Preview
        ['input', 'change'].forEach(evt => {
            Object.values(inputs).forEach(el => {
                if(el) el.addEventListener(evt, updatePreview);
            });
        });

        function openModal() {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modalPanel.classList.remove('scale-95', 'opacity-0');
                modalPanel.classList.add('scale-100', 'opacity-100');
            }, 10);
            
            // Reset Form (Add Mode)
            document.getElementById('modalTitle').innerText = "เพิ่ม Popup ใหม่";
            inputs.id.value = "";
            inputs.title.value = "";
            inputs.desc.value = "";
            inputs.img.value = "";
            inputs.btn.value = "ดูรายละเอียด";
            inputs.link.value = "#";
            inputs.enable.checked = true;
            updatePreview();
        }

        function editPopup(data) {
            openModal();
            // Fill Data (Edit Mode)
            document.getElementById('modalTitle').innerText = "แก้ไข Popup";
            inputs.id.value = data.id;
            inputs.title.value = data.popup_title;
            inputs.desc.value = data.popup_desc;
            inputs.img.value = data.popup_img;
            inputs.btn.value = data.popup_btn_text;
            inputs.link.value = data.popup_link;
            inputs.enable.checked = (data.popup_enable == 1);
            updatePreview();
        }

        function closeModal() {
            modalPanel.classList.remove('scale-100', 'opacity-100');
            modalPanel.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    </script>
</body>
</html>
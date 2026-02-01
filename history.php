<?php include 'db.php'; ?>
<?php
require_once 'header.php';
checkLogin();

// --- Configuration ---
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';

// --- SQL Preparation ---
$sql = "SELECT * FROM orders WHERE user_id = ?";
$params = [$_SESSION['user_id']];

if ($filter_status != 'all') {
    $sql .= " AND status = ?";
    $params[] = $filter_status;
}

// Count Total
$countStmt = $pdo->prepare(str_replace("SELECT *", "SELECT COUNT(*)", $sql));
$countStmt->execute($params);
$total_items = $countStmt->fetchColumn();
$total_pages = ceil($total_items / $limit);

// Fetch Data
$sql .= " ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// --- Helper Function: Get Status Badge ---
function getStatusBadge($status) {
    $config = [
        'success' => ['bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400', 'border' => 'border-emerald-500/20', 'icon' => 'fa-check-circle', 'label' => 'เสร็จสิ้น'],
        'cancelled' => ['bg' => 'bg-red-500/10', 'text' => 'text-red-400', 'border' => 'border-red-500/20', 'icon' => 'fa-circle-xmark', 'label' => 'ยกเลิก'],
        'pending' => ['bg' => 'bg-amber-500/10', 'text' => 'text-amber-400', 'border' => 'border-amber-500/20', 'icon' => 'fa-clock', 'label' => 'รอตรวจสอบ']
    ];
    return $config[$status] ?? $config['pending'];
}
?>

<div class="container mx-auto py-8 px-4 md:px-6 min-h-screen">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2 flex items-center gap-3">
                <span class="bg-gradient-to-tr from-theme-main to-purple-600 p-2 rounded-xl shadow-lg shadow-purple-500/20">
                    <i class="fa-solid fa-clock-rotate-left text-xl text-white"></i>
                </span>
                ประวัติการสั่งซื้อ
            </h1>
            <p class="text-slate-400 text-sm pl-1">รายการทั้งหมด <span class="text-theme-main font-bold"><?php echo number_format($total_items); ?></span> รายการ</p>
        </div>

        <form method="GET" class="w-full md:w-auto">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <i class="fa-solid fa-filter"></i>
                </div>
                <select name="status" onchange="this.form.submit()" class="w-full md:w-48 bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-xl focus:ring-theme-main focus:border-theme-main block pl-10 p-2.5 cursor-pointer hover:bg-slate-750 transition">
                    <option value="all" <?php echo $filter_status == 'all' ? 'selected' : ''; ?>>ทั้งหมด</option>
                    <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>รอตรวจสอบ</option>
                    <option value="success" <?php echo $filter_status == 'success' ? 'selected' : ''; ?>>เสร็จสิ้น</option>
                    <option value="cancelled" <?php echo $filter_status == 'cancelled' ? 'selected' : ''; ?>>ยกเลิก</option>
                </select>
            </div>
        </form>
    </div>

    <?php if(count($orders) > 0): ?>
        
        <div class="grid grid-cols-1 gap-4 md:hidden mb-6">
            <?php foreach($orders as $order): $st = getStatusBadge($order->status); ?>
            <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-4 shadow-lg hover:border-theme-main/30 transition">
                <div class="flex justify-between items-start mb-3 pb-3 border-b border-slate-700/50">
                    <div>
                        <span class="text-xs text-slate-500 uppercase font-bold">Order ID</span>
                        <div class="font-mono text-white text-lg font-bold">#<?php echo str_pad($order->id, 6, '0', STR_PAD_LEFT); ?></div>
                    </div>
                    <div class="<?php echo "{$st['bg']} {$st['text']} {$st['border']}"; ?> px-2.5 py-1 rounded-lg text-xs font-bold border flex items-center gap-1.5">
                        <i class="fa-solid <?php echo $st['icon']; ?>"></i> <?php echo $st['label']; ?>
                    </div>
                </div>

                <div class="mb-3 space-y-1">
                    <div class="text-white font-bold text-lg leading-tight truncate"><?php echo htmlspecialchars($order->product_name); ?></div>
                    <div class="text-slate-400 text-sm flex items-center gap-2">
                        <i class="fa-regular fa-calendar-check text-slate-600"></i>
                        <?php echo date('d M Y, H:i', strtotime($order->purchased_at)); ?> น.
                    </div>
                </div>

                <div class="flex justify-between items-center pt-2">
                    <div class="text-theme-main text-xl font-bold">฿<?php echo number_format($order->price, 2); ?></div>
                    <button onclick='openDetails(<?php echo json_encode($order); ?>)' class="bg-slate-700 hover:bg-slate-600 text-white text-sm px-4 py-2 rounded-lg transition flex items-center gap-2">
                        <i class="fa-solid fa-magnifying-glass"></i> รายละเอียด
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="hidden md:block bg-slate-800/40 backdrop-blur rounded-2xl overflow-hidden border border-slate-700 shadow-xl mb-6">
            <table class="w-full text-left">
                <thead class="bg-slate-900/50 text-slate-400 uppercase text-xs font-bold tracking-wider border-b border-slate-700">
                    <tr>
                        <th class="px-6 py-5">Order ID</th>
                        <th class="px-6 py-5">สินค้า</th>
                        <th class="px-6 py-5 text-right">ราคา</th>
                        <th class="px-6 py-5 text-center">สถานะ</th>
                        <th class="px-6 py-5 text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    <?php foreach($orders as $order): $st = getStatusBadge($order->status); ?>
                    <tr class="hover:bg-slate-700/20 transition duration-200 group">
                        <td class="px-6 py-5 align-middle">
                            <div class="font-mono text-white font-bold group-hover:text-theme-main transition">#<?php echo str_pad($order->id, 6, '0', STR_PAD_LEFT); ?></div>
                            <div class="text-xs text-slate-500 mt-1"><?php echo date('d/m/y H:i', strtotime($order->purchased_at)); ?></div>
                        </td>
                        
                        <td class="px-6 py-5 align-middle">
                            <div class="font-bold text-white"><?php echo htmlspecialchars($order->product_name); ?></div>
                            <?php if($order->data_received && $order->data_received != '-'): ?>
                                <div class="text-xs text-green-400 mt-1 font-mono truncate max-w-[200px] opacity-70">
                                    <i class="fa-solid fa-check-circle mr-1"></i> ได้รับสินค้าแล้ว
                                </div>
                            <?php endif; ?>
                        </td>

                        <td class="px-6 py-5 align-middle text-right">
                            <span class="text-white font-bold">฿<?php echo number_format($order->price, 2); ?></span>
                        </td>

                        <td class="px-6 py-5 align-middle text-center">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border <?php echo "{$st['bg']} {$st['text']} {$st['border']}"; ?>">
                                <i class="fa-solid <?php echo $st['icon']; ?>"></i> <?php echo $st['label']; ?>
                            </div>
                        </td>

                        <td class="px-6 py-5 align-middle text-center">
                            <button onclick='openDetails(<?php echo json_encode($order); ?>)' class="text-slate-400 hover:text-white bg-slate-800 hover:bg-theme-main p-2 rounded-lg transition shadow-lg" title="ดูรายละเอียด">
                                <i class="fa-solid fa-magnifying-glass-plus"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <div class="flex flex-col items-center justify-center py-20 bg-slate-800/30 rounded-2xl border border-slate-700 border-dashed">
            <div class="bg-slate-800 p-4 rounded-full mb-4 shadow-lg">
                <i class="fa-solid fa-box-open text-4xl text-slate-600"></i>
            </div>
            <h3 class="text-xl font-bold text-white mb-1">ไม่พบข้อมูลคำสั่งซื้อ</h3>
            <p class="text-slate-400 text-sm">ยังไม่มีรายการสั่งซื้อตามเงื่อนไขที่เลือก</p>
            <?php if($filter_status != 'all'): ?>
                <a href="?status=all" class="mt-4 text-theme-main hover:underline text-sm font-bold">ล้างตัวกรอง</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if($total_pages > 1): ?>
    <div class="flex justify-center mt-8">
        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm bg-slate-800/50 backdrop-blur p-1 border border-slate-700">
            <?php for($i=1; $i<=$total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&status=<?php echo $filter_status; ?>" 
                   class="relative inline-flex items-center px-4 py-2 text-sm font-semibold rounded-md transition-all duration-200 <?php echo ($i == $page) ? 'z-10 bg-theme-main text-white shadow-lg shadow-purple-500/30 scale-105' : 'text-slate-400 hover:bg-slate-700 hover:text-white'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </nav>
    </div>
    <?php endif; ?>

</div>

<div id="orderModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/90 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-slate-800 border border-slate-700 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg opacity-0 translate-y-4 scale-95" id="modalPanel">
                
                <div class="bg-slate-900/50 px-4 py-4 sm:px-6 flex justify-between items-center border-b border-slate-700">
                    <h3 class="text-lg font-bold leading-6 text-white flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-theme-main"></i> รายละเอียดคำสั่งซื้อ <span id="m_id" class="font-mono text-slate-400">#000000</span>
                    </h3>
                    <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-white transition focus:outline-none">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <div class="px-4 py-5 sm:p-6 space-y-4">
                    
                    <div class="flex items-start gap-4">
                        <div class="shrink-0 w-12 h-12 rounded-lg bg-theme-main/10 flex items-center justify-center text-theme-main text-2xl">
                            <i class="fa-solid fa-cube"></i>
                        </div>
                        <div>
                            <div class="text-sm text-slate-400 mb-1">สินค้า</div>
                            <div id="m_product" class="text-white font-bold text-lg leading-tight">Product Name</div>
                            <div id="m_date" class="text-xs text-slate-500 mt-1">Date</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 bg-slate-900/50 p-4 rounded-xl border border-slate-700/50">
                        <div>
                            <div class="text-xs text-slate-500 mb-1">สถานะ</div>
                            <div id="m_status">Status Badge</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-slate-500 mb-1">ราคา</div>
                            <div id="m_price" class="text-xl font-bold text-white">฿0.00</div>
                        </div>
                    </div>

                    <div id="m_data_container" class="hidden">
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            <i class="fa-solid fa-key text-theme-main mr-1"></i> สินค้าที่ได้รับ / ข้อมูล
                        </label>
                        <div class="relative group">
                            <textarea id="m_data" readonly class="w-full bg-slate-900 border border-slate-700 rounded-xl p-3 text-green-400 font-mono text-sm focus:ring-1 focus:ring-theme-main focus:outline-none resize-none h-24 scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent"></textarea>
                            <button onclick="copyModalData()" class="absolute top-2 right-2 p-1.5 bg-slate-800 hover:bg-theme-main text-slate-400 hover:text-white rounded-lg transition shadow-lg border border-slate-700" title="Copy">
                                <i class="fa-regular fa-copy" id="m_copy_icon"></i>
                            </button>
                        </div>
                        <p class="text-xs text-slate-500 mt-2 text-center">สามารถนำข้อมูลนี้ไปใช้งานได้ทันที</p>
                    </div>

                    <div id="m_note_container" class="hidden bg-yellow-500/10 border border-yellow-500/20 p-3 rounded-lg">
                        <p class="text-xs text-yellow-500"><i class="fa-solid fa-circle-info mr-1"></i> <span id="m_note"></span></p>
                    </div>

                </div>

                <div class="bg-slate-900/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2 border-t border-slate-700">
                    <button type="button" onclick="closeModal()" class="mt-3 inline-flex w-full justify-center rounded-lg bg-slate-700 px-3 py-2 text-sm font-semibold text-white shadow-sm ring-1 ring-inset ring-slate-600 hover:bg-slate-600 sm:mt-0 sm:w-auto transition">
                        ปิดหน้าต่าง
                    </button>
                    <a href="contact.php" class="inline-flex w-full justify-center rounded-lg bg-transparent px-3 py-2 text-sm font-semibold text-slate-400 hover:text-white sm:w-auto transition">
                        แจ้งปัญหา
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// --- Modal Logic ---
const modal = document.getElementById('orderModal');
const backdrop = document.getElementById('modalBackdrop');
const panel = document.getElementById('modalPanel');

function openDetails(order) {
    // 1. Populate Data
    document.getElementById('m_id').innerText = '#' + String(order.id).padStart(6, '0');
    document.getElementById('m_product').innerText = order.product_name;
    document.getElementById('m_price').innerText = '฿' + parseFloat(order.price).toFixed(2);
    
    // Format Date
    const date = new Date(order.purchased_at);
    document.getElementById('m_date').innerText = date.toLocaleDateString('th-TH') + ' ' + date.toLocaleTimeString('th-TH', {hour: '2-digit', minute:'2-digit'}) + ' น.';

    // Status Badge Logic
    let statusHtml = '';
    if(order.status === 'success') {
        statusHtml = '<span class="inline-flex items-center gap-1 text-emerald-400 font-bold text-sm"><i class="fa-solid fa-check-circle"></i> เสร็จสิ้น</span>';
    } else if (order.status === 'cancelled') {
        statusHtml = '<span class="inline-flex items-center gap-1 text-red-400 font-bold text-sm"><i class="fa-solid fa-circle-xmark"></i> ยกเลิก</span>';
    } else {
        statusHtml = '<span class="inline-flex items-center gap-1 text-amber-400 font-bold text-sm"><i class="fa-solid fa-clock"></i> รอตรวจสอบ</span>';
    }
    document.getElementById('m_status').innerHTML = statusHtml;

    // Data Received
    const dataContainer = document.getElementById('m_data_container');
    const dataField = document.getElementById('m_data');
    if(order.data_received && order.data_received !== '-' && order.status === 'success') {
        dataContainer.classList.remove('hidden');
        dataField.value = order.data_received;
    } else {
        dataContainer.classList.add('hidden');
    }

    // Note
    const noteContainer = document.getElementById('m_note_container');
    if(order.note) {
        noteContainer.classList.remove('hidden');
        document.getElementById('m_note').innerText = order.note;
    } else {
        noteContainer.classList.add('hidden');
    }

    // 2. Show Modal (Animation)
    modal.classList.remove('hidden');
    // Trigger reflow
    void modal.offsetWidth;
    
    // Add animation classes
    backdrop.classList.remove('opacity-0');
    panel.classList.remove('opacity-0', 'translate-y-4', 'scale-95');
}

function closeModal() {
    // Remove animation classes
    backdrop.classList.add('opacity-0');
    panel.classList.add('opacity-0', 'translate-y-4', 'scale-95');

    // Wait for transition end then hide
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300); // Match transition duration
}

// Copy Logic inside Modal
function copyModalData() {
    const copyText = document.getElementById("m_data");
    const icon = document.getElementById("m_copy_icon");
    
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value).then(() => {
        // Change Icon
        icon.className = "fa-solid fa-check text-green-400";
        setTimeout(() => {
            icon.className = "fa-regular fa-copy";
        }, 2000);

        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                background: '#1e293b',
                color: '#fff'
            });
            Toast.fire({ icon: 'success', title: 'คัดลอกเรียบร้อย' });
        }
    });
}

// Close on backdrop click
modal.addEventListener('click', (e) => {
    if (e.target === backdrop || e.target.closest('#modalPanel') === null) {
        // Only close if clicking backdrop (checking logic slightly adjusted for safety)
        if(e.target === backdrop) closeModal();
    }
});
</script>

<?php require_once 'footer.php'; ?>
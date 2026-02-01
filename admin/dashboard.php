<?php include 'admin_auth.php'; ?>

<?php
require_once 'header.php';

// --- 1. จัดการ Logic วันที่และโหมด (คงเดิม 100%) ---
$mode = $_GET['mode'] ?? 'week';
$date_input = $_GET['date'] ?? date('Y-m-d');

$timestamp = strtotime($date_input);
$chart_labels = [];
$chart_data = [];
$title_chart = "";

switch ($mode) {
    case 'day':
        $title_chart = "ยอดขายรายชั่วโมง (" . date('d/m/Y', $timestamp) . ")";
        for ($i = 0; $i < 24; $i++) {
            $chart_labels[] = sprintf("%02d:00", $i);
            $stmt = $pdo->prepare("SELECT SUM(price) FROM orders WHERE DATE(purchased_at) = ? AND HOUR(purchased_at) = ?");
            $stmt->execute([$date_input, $i]);
            $chart_data[] = $stmt->fetchColumn() ?: 0;
        }
        break;

    case 'week':
        $start_date = date('Y-m-d', strtotime('-6 days', $timestamp));
        $end_date = date('Y-m-d', $timestamp);
        $period_text = date('d/m', strtotime($start_date)) . " - " . date('d/m/Y', strtotime($end_date));
        $title_chart = "ยอดขาย 7 วัน ($period_text)";

        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days", $timestamp));
            $chart_labels[] = date('D d', strtotime($d));
            $stmt = $pdo->prepare("SELECT SUM(price) FROM orders WHERE DATE(purchased_at) = ?");
            $stmt->execute([$d]);
            $chart_data[] = $stmt->fetchColumn() ?: 0;
        }
        break;

    case 'month':
        $title_chart = "ยอดขายรายวัน " . date('m/Y', $timestamp);
        $month = date('m', $timestamp);
        $year = date('Y', $timestamp);
        $days_in_month = date('t', $timestamp);

        for ($d = 1; $d <= $days_in_month; $d++) {
            $chart_labels[] = $d;
            $stmt = $pdo->prepare("SELECT SUM(price) FROM orders WHERE YEAR(purchased_at) = ? AND MONTH(purchased_at) = ? AND DAY(purchased_at) = ?");
            $stmt->execute([$year, $month, $d]);
            $chart_data[] = $stmt->fetchColumn() ?: 0;
        }
        break;

    case 'year':
        $title_chart = "ยอดขายรายเดือน ปี " . date('Y', $timestamp);
        $year = date('Y', $timestamp);
        $months_short = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

        for ($m = 1; $m <= 12; $m++) {
            $chart_labels[] = $months_short[$m-1];
            $stmt = $pdo->prepare("SELECT SUM(price) FROM orders WHERE YEAR(purchased_at) = ? AND MONTH(purchased_at) = ?");
            $stmt->execute([$year, $m]);
            $chart_data[] = $stmt->fetchColumn() ?: 0;
        }
        break;
}

// 2. ข้อมูล Stats รวม (คงเดิม)
$stats = [
    'income' => $pdo->query("SELECT SUM(price) FROM orders")->fetchColumn() ?: 0,
    'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'orders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'sold_items' => $pdo->query("SELECT COUNT(*) FROM stocks WHERE is_sold = 1")->fetchColumn(),
];
?>

<style>
    /* Gradient Background Animation */
    @keyframes gradient-xy {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }
    
    /* Fade In Up Animation */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translate3d(0, 20px, 0); }
        to { opacity: 1; transform: translate3d(0, 0, 0); }
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        opacity: 0; /* เริ่มต้นซ่อนไว้ */
    }
    
    .delay-100 { animation-delay: 0.1s; }
    .delay-200 { animation-delay: 0.2s; }
    .delay-300 { animation-delay: 0.3s; }

    /* Glass Effect */
    .glass-panel {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    }

    /* Custom Scrollbar */
    .modern-scrollbar::-webkit-scrollbar { width: 6px; }
    .modern-scrollbar::-webkit-scrollbar-track { background: rgba(0,0,0,0.2); border-radius: 10px; }
    .modern-scrollbar::-webkit-scrollbar-thumb { background: linear-gradient(to bottom, #8b5cf6, #6366f1); border-radius: 10px; }
    .modern-scrollbar::-webkit-scrollbar-thumb:hover { background: #8b5cf6; }

    /* Input Date Customization */
    input[type="date"]::-webkit-calendar-picker-indicator,
    input[type="month"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
        cursor: pointer;
    }
</style>

<div class="container mx-auto px-4 pb-12 pt-6 max-w-7xl">

    <div class="animate-fade-in-up mb-8">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-6">
            <div>
                <h2 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400 mb-2 drop-shadow-sm">
                    Dashboard Overview
                </h2>
                <p class="text-slate-400 text-lg flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    ระบบจัดการและดูสถิติร้านค้า Real-time
                </p>
            </div>

            <div class="w-full lg:w-auto glass-panel p-2 rounded-2xl flex flex-col md:flex-row gap-3 items-center shadow-xl">
                
                <div class="grid grid-cols-4 w-full md:w-auto bg-slate-900/50 rounded-xl p-1 gap-1">
                    <?php foreach(['day'=>'วัน', 'week'=>'สัปดาห์', 'month'=>'เดือน', 'year'=>'ปี'] as $m => $label): ?>
                    <a href="?mode=<?php echo $m; ?>&date=<?php echo $date_input; ?>" 
                       class="text-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-300 relative overflow-hidden group
                              <?php echo $mode==$m ? 'bg-gradient-to-r from-violet-600 to-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800'; ?>">
                       <?php echo $label; ?>
                    </a>
                    <?php endforeach; ?>
                </div>

                <div class="hidden md:block w-px h-8 bg-slate-700 mx-2"></div>

                <form action="" method="GET" class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-start">
                    <input type="hidden" name="mode" value="<?php echo $mode; ?>">
                    
                    <?php 
                        $prev_link = "";
                        $next_link = "";
                        if($mode == 'day') { $prev_link = '-1 day'; $next_link = '+1 day'; }
                        elseif($mode == 'week') { $prev_link = '-1 week'; $next_link = '+1 week'; }
                        elseif($mode == 'month') { $prev_link = '-1 month'; $next_link = '+1 month'; }
                        elseif($mode == 'year') { $prev_link = '-1 year'; $next_link = '+1 year'; }
                        
                        $prev_date = date('Y-m-d', strtotime($prev_link, $timestamp));
                        $next_date = date('Y-m-d', strtotime($next_link, $timestamp));
                    ?>
                    
                    <a href="?mode=<?php echo $mode; ?>&date=<?php echo $prev_date; ?>" 
                       class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-violet-600 text-white transition-all shadow-md hover:shadow-violet-500/50">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>

                    <div class="relative group flex-1 md:flex-none">
                        <?php if($mode == 'year'): ?>
                            <select name="date" onchange="this.form.submit()" 
                                    class="w-full bg-slate-900 text-white border border-slate-700 rounded-xl px-4 py-2 text-center focus:ring-2 focus:ring-violet-500 focus:border-transparent outline-none appearance-none cursor-pointer font-medium hover:bg-slate-800 transition">
                                <?php 
                                $curr_year = date('Y');
                                for($y = $curr_year; $y >= $curr_year - 5; $y--): ?>
                                    <option value="<?php echo $y; ?>-01-01" <?php echo date('Y', $timestamp) == $y ? 'selected' : ''; ?>>
                                        ปี <?php echo $y; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        <?php elseif($mode == 'month'): ?>
                            <input type="month" name="date" 
                                   value="<?php echo date('Y-m', $timestamp); ?>" 
                                   onchange="this.form.submit()"
                                   class="w-full bg-slate-900 text-white border border-slate-700 rounded-xl px-4 py-2 text-center focus:ring-2 focus:ring-violet-500 outline-none cursor-pointer font-medium hover:bg-slate-800 transition">
                        <?php else: ?>
                            <input type="date" name="date" 
                                   value="<?php echo date('Y-m-d', $timestamp); ?>" 
                                   onchange="this.form.submit()"
                                   class="w-full bg-slate-900 text-white border border-slate-700 rounded-xl px-4 py-2 text-center focus:ring-2 focus:ring-violet-500 outline-none cursor-pointer font-medium hover:bg-slate-800 transition">
                        <?php endif; ?>
                    </div>

                    <a href="?mode=<?php echo $mode; ?>&date=<?php echo $next_date; ?>" 
                       class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-violet-600 text-white transition-all shadow-md hover:shadow-violet-500/50">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8">
        
        <div class="animate-fade-in-up delay-100 glass-panel p-5 rounded-2xl relative overflow-hidden group hover:-translate-y-1 transition duration-300">
            <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fa-solid fa-sack-dollar text-6xl text-green-400"></i>
            </div>
            <p class="text-slate-400 text-sm font-medium mb-1">รายได้รวม</p>
            <h3 class="text-2xl lg:text-3xl font-bold text-white mb-2">฿<?php echo number_format($stats['income'], 2); ?></h3>
            <div class="h-1 w-full bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-green-500 w-[70%] rounded-full shadow-[0_0_10px_rgba(34,197,94,0.7)]"></div>
            </div>
        </div>

        <div class="animate-fade-in-up delay-200 glass-panel p-5 rounded-2xl relative overflow-hidden group hover:-translate-y-1 transition duration-300">
            <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fa-solid fa-users text-6xl text-blue-400"></i>
            </div>
            <p class="text-slate-400 text-sm font-medium mb-1">สมาชิก</p>
            <h3 class="text-2xl lg:text-3xl font-bold text-white mb-2"><?php echo number_format($stats['users']); ?></h3>
            <div class="h-1 w-full bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-blue-500 w-[45%] rounded-full shadow-[0_0_10px_rgba(59,130,246,0.7)]"></div>
            </div>
        </div>

        <div class="animate-fade-in-up delay-300 glass-panel p-5 rounded-2xl relative overflow-hidden group hover:-translate-y-1 transition duration-300">
            <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fa-solid fa-file-invoice text-6xl text-purple-400"></i>
            </div>
            <p class="text-slate-400 text-sm font-medium mb-1">ออเดอร์</p>
            <h3 class="text-2xl lg:text-3xl font-bold text-white mb-2"><?php echo number_format($stats['orders']); ?></h3>
            <div class="h-1 w-full bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-purple-500 w-[60%] rounded-full shadow-[0_0_10px_rgba(168,85,247,0.7)]"></div>
            </div>
        </div>

        <div class="animate-fade-in-up delay-100 glass-panel p-5 rounded-2xl relative overflow-hidden group hover:-translate-y-1 transition duration-300">
            <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fa-solid fa-box-open text-6xl text-orange-400"></i>
            </div>
            <p class="text-slate-400 text-sm font-medium mb-1">ขายแล้ว (ชิ้น)</p>
            <h3 class="text-2xl lg:text-3xl font-bold text-white mb-2"><?php echo number_format($stats['sold_items']); ?></h3>
            <div class="h-1 w-full bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-orange-500 w-[85%] rounded-full shadow-[0_0_10px_rgba(249,115,22,0.7)]"></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 animate-fade-in-up delay-200">
            <div class="glass-panel p-6 rounded-2xl h-full flex flex-col">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-white flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-violet-500/20 text-violet-400 flex items-center justify-center">
                            <i class="fa-solid fa-chart-line"></i>
                        </span>
                        <?php echo $title_chart; ?>
                    </h3>
                </div>
                <div class="relative w-full flex-1 min-h-[350px]">
                    <canvas id="mainChart"></canvas>
                </div>
            </div>
        </div>

        <div class="animate-fade-in-up delay-300">
            <div class="glass-panel p-6 rounded-2xl flex flex-col h-[500px]">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-white flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </span>
                        ออเดอร์ล่าสุด
                    </h3>
                    <span class="text-xs font-semibold bg-slate-700 text-slate-300 px-2.5 py-1 rounded-md border border-slate-600">Live</span>
                </div>
                
                <div class="flex-1 overflow-y-auto pr-2 modern-scrollbar">
                    <div class="flex flex-col gap-3">
                        <?php
                        $orders = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 50")->fetchAll();
                        if(count($orders) > 0):
                            foreach($orders as $o):
                        ?>
                        <div class="group flex items-center justify-between p-3.5 bg-slate-800/40 hover:bg-slate-700/60 rounded-xl transition-all duration-300 border border-transparent hover:border-slate-600">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-slate-400 group-hover:bg-violet-600 group-hover:text-white transition-colors duration-300 shadow-sm shrink-0">
                                    <i class="fa-solid fa-basket-shopping"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-200 truncate group-hover:text-violet-400 transition-colors">
                                        <?php echo htmlspecialchars($o->product_name); ?>
                                    </p>
                                    <p class="text-xs text-slate-500 flex items-center gap-1">
                                        <i class="fa-regular fa-clock text-[10px]"></i>
                                        <?php echo date('d/m/y H:i', strtotime($o->purchased_at)); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="block text-sm font-bold text-emerald-400 drop-shadow-[0_0_8px_rgba(52,211,153,0.3)]">
                                    +฿<?php echo number_format($o->price); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; else: ?>
                            <div class="h-full flex flex-col items-center justify-center text-slate-500 gap-3">
                                <i class="fa-solid fa-inbox text-4xl opacity-50"></i>
                                <span>ยังไม่มีรายการสั่งซื้อ</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('mainChart').getContext('2d');
    
    // Create Enhanced Gradient
    let gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(139, 92, 246, 0.6)'); // Violet start
    gradient.addColorStop(0.5, 'rgba(139, 92, 246, 0.1)'); // Fade middle
    gradient.addColorStop(1, 'rgba(139, 92, 246, 0.0)');  // Transparent end

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'ยอดขาย',
                data: <?php echo json_encode($chart_data); ?>,
                borderColor: '#8b5cf6', // Violet-500
                backgroundColor: gradient,
                borderWidth: 3,
                pointBackgroundColor: '#0f172a', // Slate-900
                pointBorderColor: '#a78bfa', // Violet-400
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointHoverBackgroundColor: '#8b5cf6',
                pointHoverBorderColor: '#fff',
                fill: true,
                tension: 0.4 // Smooth curve
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                y: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            },
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)', // Darker tooltip
                    titleColor: '#e2e8f0',
                    bodyColor: '#fff',
                    borderColor: 'rgba(139, 92, 246, 0.3)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: false,
                    titleFont: { size: 14, family: "'Inter', sans-serif" },
                    bodyFont: { size: 14, family: "'Inter', sans-serif", weight: 'bold' },
                    callbacks: {
                        label: function(context) {
                            return ' รายได้: ฿' + Number(context.parsed.y).toLocaleString(undefined, {minimumFractionDigits: 0});
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { color: '#94a3b8', font: { size: 12 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255,255,255,0.05)', borderDash: [6, 6] },
                    ticks: { 
                        color: '#94a3b8', 
                        font: { size: 12 },
                        callback: function(value){ return '฿' + value.toLocaleString(); } 
                    },
                    border: { display: false }
                }
            }
        }
    });
</script>

<?php 
// ปิด tag ให้ถูกต้อง (สมมติว่า header.php มีการเปิด body/main ไว้)
echo "</div>"; 
?>
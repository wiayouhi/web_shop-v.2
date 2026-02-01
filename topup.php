<?php include 'db.php'; ?>
<?php
checkLogin(); // บังคับล็อกอินก่อน
require_once 'header.php';
?>

<style>
    @keyframes float {
        0% { transform: translateY(0px) rotate(3deg); }
        50% { transform: translateY(-10px) rotate(0deg); }
        100% { transform: translateY(0px) rotate(3deg); }
    }
    .logo-float {
        animation: float 4s ease-in-out infinite;
    }
    .fade-up-element {
        animation: fadeUp 0.6s ease-out forwards;
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="container mx-auto py-12 px-4 min-h-[80vh] flex items-center justify-center">
    
    <div class="w-full max-w-lg fade-up-element">
        
        <div class="text-center mb-8 relative">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-40 h-40 bg-orange-500/30 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="relative z-10 mb-6 flex justify-center">
                <div class="w-24 h-24 bg-white rounded-3xl flex items-center justify-center shadow-2xl logo-float border-4 border-slate-800">
                    <img src="https://images.seeklogo.com/logo-png/36/1/truemoney-wallet-logo-png_seeklogo-367826.png" class="w-20 rounded-xl" alt="TrueMoney">
                </div>
            </div>
            
            <h1 class="text-3xl font-extrabold text-white mb-2 drop-shadow-md">เติมเงินเข้าระบบ</h1>
            <p class="text-gray-400">ระบบอัตโนมัติ 24 ชั่วโมง ผ่านซองของขวัญ TrueMoney</p>
        </div>

        <div class="bg-slate-800/40 backdrop-blur-md rounded-3xl p-8 border border-white/10 shadow-2xl relative overflow-hidden">
            
            <div class="bg-orange-500/10 border border-orange-500/20 rounded-2xl p-5 mb-8">
                <h3 class="text-orange-400 font-bold mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> ขั้นตอนการสร้างซอง
                </h3>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li class="flex items-start gap-2">
                        <span class="bg-orange-500/20 text-orange-400 rounded-full w-5 h-5 flex items-center justify-center text-xs mt-0.5 font-bold">1</span>
                        เลือกเมนู "ส่งซองของขวัญ" ในแอป TrueMoney
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="bg-orange-500/20 text-orange-400 rounded-full w-5 h-5 flex items-center justify-center text-xs mt-0.5 font-bold">2</span>
                        เลือกประเภท <span class="text-white font-bold underline decoration-orange-500">แบ่งจำนวนเงินเท่ากัน</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="bg-orange-500/20 text-orange-400 rounded-full w-5 h-5 flex items-center justify-center text-xs mt-0.5 font-bold">3</span>
                        กรอกจำนวนคนรับซอง <span class="text-white font-bold">1 คน</span> เท่านั้น
                    </li>
                </ul>
            </div>

            <div class="space-y-5">
                <div>
                    <label class="block text-gray-300 mb-2 text-sm font-medium ml-1">ลิงก์ซองของขวัญ</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-500 group-focus-within:text-orange-500 transition-colors">
                            <i class="fa-solid fa-link"></i>
                        </div>
                        <input type="text" id="angpao_link" 
                               class="w-full bg-slate-900/80 border border-slate-600 rounded-xl py-4 pl-12 pr-4 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 focus:bg-slate-900 focus:outline-none text-white transition-all placeholder-gray-600 shadow-inner" 
                               placeholder="https://gift.truemoney.com/campaign/...">
                    </div>
                </div>
                
                <button onclick="submitTopup()" class="w-full bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-400 hover:to-red-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-orange-500/30 transition-all duration-300 transform hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2 text-lg">
                    <i class="fa-regular fa-paper-plane"></i> ยืนยันการเติมเงิน
                </button>
            </div>
            
            <p class="text-center text-xs text-gray-500 mt-6">
                *หากเติมเงินไม่เข้าเกิน 5 นาที กรุณาติดต่อแอดมิน
            </p>

        </div>
    </div>
</div>

<script>
    function submitTopup() {
        // ดึงค่าลิงก์จากช่อง input
        let link = document.getElementById('angpao_link').value.trim();
        
        // เช็คว่าว่างไหม
        if(!link) {
            Swal.fire({
                icon: 'warning',
                title: 'กรุณากรอกข้อมูล',
                text: 'โปรดวางลิงก์ซองของขวัญก่อนกดยืนยัน',
                background: '#1e293b', color: '#fff',
                confirmButtonColor: '#f97316',
                confirmButtonText: 'ตกลง'
            });
            return;
        }

        // เช็ค Format ลิงก์เบื้องต้น (Optional)
        if(!link.includes('gift.truemoney.com')) {
            Swal.fire({
                icon: 'error',
                title: 'ลิงก์ไม่ถูกต้อง',
                text: 'กรุณาตรวจสอบลิงก์ซองของขวัญอีกครั้ง',
                background: '#1e293b', color: '#fff',
                confirmButtonColor: '#ef4444'
            });
            return;
        }

        // แสดง Loading
        Swal.fire({
            title: 'กำลังตรวจสอบ...',
            text: 'ระบบกำลังเช็คยอดเงิน กรุณารอสักครู่',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading() },
            background: '#1e293b', color: '#fff'
        });

        // เตรียมข้อมูลส่งไปหลังบ้าน
        const formData = new FormData();
        formData.append('link', link);

        // ยิงไปที่ API
        fetch('api/topup_angpao.php', { 
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                // สำเร็จ 
                Swal.fire({
                    icon: 'success',
                    title: 'เติมเงินสำเร็จ!',
                    html: `คุณได้รับเครดิต <span class="text-green-400 font-bold text-xl">${data.amount}</span> บาท`,
                    background: '#1e293b', color: '#fff',
                    confirmButtonColor: '#22c55e',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    window.location.reload(); // รีโหลดหน้าเพื่ออัปเดตยอดเงิน
                });
            } else {
                // ไม่สำเร็จ (เช่น ลิงก์ผิด, ลิงก์ใช้ไปแล้ว)
                Swal.fire({
                    icon: 'error',
                    title: 'เติมเงินไม่สำเร็จ',
                    text: data.message,
                    background: '#1e293b', color: '#fff',
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'ลองใหม่อีกครั้ง'
                });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'System Error',
                text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
                background: '#1e293b', color: '#fff'
            });
        });
    }
</script>

<?php require_once 'footer.php'; ?>
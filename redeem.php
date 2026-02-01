<?php require_once 'header.php'; ?>

<style>
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
        100% { transform: translateY(0px); }
    }
    .gift-float {
        animation: float 3s ease-in-out infinite;
    }
    .input-code {
        letter-spacing: 0.2em; /* เพิ่มระยะห่างตัวอักษรให้อ่านง่าย */
    }
</style>

<div class="container mx-auto py-12 px-4 min-h-[80vh] flex items-center justify-center relative">
    
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-purple-600/20 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="w-full max-w-md relative">
        
        <div class="absolute -top-12 left-1/2 -translate-x-1/2 z-20">
            <div class="w-24 h-24 bg-slate-800 rounded-2xl flex items-center justify-center shadow-2xl border-4 border-slate-700 gift-float">
                <i class="fa-solid fa-gift text-5xl text-transparent bg-clip-text bg-gradient-to-br from-purple-400 to-pink-600 drop-shadow-lg"></i>
            </div>
        </div>

        <div class="bg-slate-800/50 backdrop-blur-xl p-8 pt-16 rounded-3xl border border-white/10 shadow-2xl relative overflow-hidden">
            
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-purple-500 to-transparent opacity-80"></div>

            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">แลกโค้ดรางวัล</h1>
                <p class="text-gray-400 text-sm">กรอก Gift Code ที่ได้รับจากกิจกรรม<br>เพื่อรับเครดิตฟรีทันที</p>
            </div>

            <form id="redeemForm" class="space-y-6">
                <div class="relative group">
                    <label class="block text-purple-300 mb-2 text-xs font-bold uppercase tracking-wider text-center">Your Code</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-ticket text-gray-500 group-focus-within:text-purple-500 transition-colors"></i>
                        </div>
                        <input type="text" id="code" 
                               class="input-code w-full bg-slate-900/60 border border-slate-600 rounded-xl py-4 pl-12 pr-4 text-white text-center font-mono text-xl uppercase placeholder-gray-600 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:bg-slate-900 focus:outline-none transition-all shadow-inner" 
                               placeholder="CODE-XXXX" autocomplete="off">
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-purple-500/25 transition-all transform hover:-translate-y-1 active:scale-95 group">
                    <span class="group-hover:hidden"><i class="fa-solid fa-box-open mr-2"></i> แลกของรางวัล</span>
                    <span class="hidden group-hover:inline-block"><i class="fa-solid fa-check mr-2"></i> ยืนยันโค้ดนี้</span>
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="#" class="text-xs text-gray-500 hover:text-white transition-colors border-b border-gray-700 hover:border-white pb-0.5">
                    <i class="fa-brands fa-facebook-f mr-1"></i> ติดตามเพจเพื่อรับโค้ดฟรี
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Auto Uppercase Input
document.getElementById('code').addEventListener('input', function(e) {
    this.value = this.value.toUpperCase();
});

document.getElementById('redeemForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let code = document.getElementById('code').value.trim();
    
    // ตรวจสอบค่าว่าง
    if (!code) {
        Swal.fire({
            icon: 'warning',
            title: 'กรุณากรอกโค้ด',
            text: 'ช่องกรอกข้อมูลว่างเปล่า',
            background: '#1e293b', color: '#fff',
            confirmButtonColor: '#9333ea'
        });
        return;
    }

    Swal.fire({
        title: 'กำลังตรวจสอบ...',
        text: 'กรุณารอสักครู่ ระบบกำลังเช็คความถูกต้อง',
        didOpen: () => Swal.showLoading(),
        background: '#1e293b', color: '#fff',
        allowOutsideClick: false
    });

    const formData = new FormData();
    formData.append('code', code);

    fetch('api/redeem.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Success Animation
            Swal.fire({
                icon: 'success',
                title: 'แลกสำเร็จ!',
                html: `<div class="text-gray-300">คุณได้รับ</div><div class="text-3xl font-bold text-green-400 mt-2">${data.message}</div>`, // สมมติว่า message ส่งจำนวนเงินกลับมา หรือปรับข้อความตาม API
                background: '#1e293b', color: '#fff',
                confirmButtonColor: '#22c55e',
                confirmButtonText: 'ตกลง'
            }).then(() => {
                location.reload(); // รีโหลดเพื่ออัปเดตยอดเงิน
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'ไม่สามารถแลกได้',
                text: data.message, // "โค้ดหมดอายุ", "โค้ดผิด", "ใช้งานไปแล้ว"
                background: '#1e293b', color: '#fff',
                confirmButtonColor: '#ef4444'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'System Error',
            text: 'เกิดข้อผิดพลาดในการเชื่อมต่อ',
            background: '#1e293b', color: '#fff'
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>
<?php 
require_once 'header.php'; 
?>

<style>
    .glass-panel {
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .contact-card:hover {
        transform: translateY(-5px);
    }
    .faq-card {
        transition: all 0.3s ease;
    }
    .faq-card:hover {
        background: rgba(30, 41, 59, 0.8);
        border-color: rgba(99, 102, 241, 0.5); /* Indigo-500 */
    }
</style>

<div class="container mx-auto py-16 px-4 min-h-screen">
    
    <div class="text-center mb-16 relative">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-32 h-32 bg-theme-main/20 rounded-full blur-[80px] pointer-events-none"></div>
        
        <span class="inline-block py-1 px-3 rounded-full bg-slate-800 border border-slate-700 text-xs text-gray-400 mb-4">
            <i class="fa-solid fa-headset mr-2 text-theme-main"></i>Customer Support
        </span>
        <h1 class="text-4xl md:text-5xl font-bold text-white mb-4 drop-shadow-lg">
            ช่วยเหลือ & <span class="text-transparent bg-clip-text bg-gradient-to-r from-theme-main to-purple-400">ติดต่อเรา</span>
        </h1>
        <p class="text-slate-400 max-w-lg mx-auto text-lg">
            มีปัญหาในการเติมเงิน หรือต้องการสอบถามข้อมูล? <br class="hidden md:block">
            ทีมงานพร้อมดูแลตลอด 24 ชั่วโมง ผ่านช่องทางดังนี้
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto mb-20 relative z-10">
        <?php 
        $channels = [
            [
                'name' => 'Facebook Page',
                'desc' => 'ติดตามข่าวสาร โปรโมชั่น',
                'url' => $web_config->facebook_url ?? '#',
                'icon' => 'fa-facebook-f',
                'bg_icon' => 'bg-blue-600',
                'text_color' => 'text-blue-500',
                'border_hover' => 'group-hover:border-blue-500/50'
            ],
            [
                'name' => 'Line Official',
                'desc' => 'แจ้งปัญหา เติมเงินไม่เข้า',
                'url' => $web_config->line_url ?? '#',
                'icon' => 'fa-line',
                'bg_icon' => 'bg-green-500',
                'text_color' => 'text-green-500',
                'border_hover' => 'group-hover:border-green-500/50'
            ],
            [
                'name' => 'Discord Community',
                'desc' => 'พูดคุยกับเพื่อนในเกม',
                'url' => $web_config->discord_url ?? '#',
                'icon' => 'fa-discord',
                'bg_icon' => 'bg-indigo-500',
                'text_color' => 'text-indigo-500',
                'border_hover' => 'group-hover:border-indigo-500/50'
            ],
            [
                'name' => 'YouTube Channel',
                'desc' => 'ดูรีวิวและวิธีใช้งาน',
                'url' => $web_config->youtube_url ?? '#',
                'icon' => 'fa-youtube',
                'bg_icon' => 'bg-red-600',
                'text_color' => 'text-red-500',
                'border_hover' => 'group-hover:border-red-500/50'
            ],
            [
                'name' => 'TikTok',
                'desc' => 'ไฮไลท์เกมมันส์ๆ',
                'url' => $web_config->tiktok_url ?? '#',
                'icon' => 'fa-tiktok',
                'bg_icon' => 'bg-pink-600',
                'text_color' => 'text-pink-500',
                'border_hover' => 'group-hover:border-pink-500/50'
            ],
            [
                'name' => 'Instagram',
                'desc' => 'รูปภาพสินค้าสวยๆ',
                'url' => $web_config->instagram_url ?? '#',
                'icon' => 'fa-instagram',
                'bg_icon' => 'bg-purple-600',
                'text_color' => 'text-purple-500',
                'border_hover' => 'group-hover:border-purple-500/50'
            ]
        ];

        foreach($channels as $ch): 
            if(!empty($ch['url']) && $ch['url'] != '#'):
        ?>
            <a href="<?php echo $ch['url']; ?>" target="_blank" class="contact-card group glass-panel p-6 rounded-2xl flex items-center gap-5 transition-all duration-300 border border-slate-800 <?php echo $ch['border_hover']; ?>">
                <div class="w-14 h-14 rounded-xl <?php echo $ch['bg_icon']; ?> flex items-center justify-center text-white text-2xl shadow-lg shadow-black/30 group-hover:scale-110 transition-transform duration-300">
                    <i class="fa-brands <?php echo $ch['icon']; ?>"></i>
                </div>
                
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-200 group-hover:text-white transition-colors">
                        <?php echo $ch['name']; ?>
                    </h3>
                    <p class="text-sm text-gray-500 group-hover:text-gray-400 transition-colors">
                        <?php echo $ch['desc']; ?>
                    </p>
                </div>

                <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-gray-500 group-hover:bg-white group-hover:text-slate-900 transition-all duration-300 transform -translate-x-2 opacity-0 group-hover:translate-x-0 group-hover:opacity-100">
                    <i class="fa-solid fa-arrow-right text-sm"></i>
                </div>
            </a>
        <?php 
            endif;
        endforeach; 
        ?>
    </div>

    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-10">
            <h2 class="text-2xl font-bold text-white mb-2 flex items-center justify-center gap-2">
                <i class="fa-regular fa-circle-question text-theme-main"></i> คำถามที่พบบ่อย (FAQ)
            </h2>
            <div class="h-1 w-16 bg-theme-main mx-auto rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="faq-card bg-slate-900/50 p-6 rounded-xl border border-slate-700">
                <div class="flex items-start gap-3">
                    <div class="mt-1 w-6 h-6 rounded-full bg-green-500/20 text-green-500 flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-white mb-2">เติมเงินแล้วยอดไม่เข้า?</h4>
                        <p class="text-sm text-gray-400 leading-relaxed">
                            ปกติระบบจะเติมอัตโนมัติภายใน 1-3 นาที หากเกิน 5 นาที กรุณาเตรียม "ลิงก์ซองของขวัญ" แล้วแจ้งแอดมินทาง Line ได้เลยครับ
                        </p>
                    </div>
                </div>
            </div>

            <div class="faq-card bg-slate-900/50 p-6 rounded-xl border border-slate-700">
                <div class="flex items-start gap-3">
                    <div class="mt-1 w-6 h-6 rounded-full bg-blue-500/20 text-blue-500 flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-box"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-white mb-2">สินค้าจะเติมตอนไหน?</h4>
                        <p class="text-sm text-gray-400 leading-relaxed">
                            ทีมงานจะเติมสต็อกทุกวันในช่วงเย็น (17:00 - 20:00 น.) ติดตามประกาศแจ้งเตือนได้ที่หน้าเพจ Facebook ครับ
                        </p>
                    </div>
                </div>
            </div>

            <div class="faq-card bg-slate-900/50 p-6 rounded-xl border border-slate-700">
                <div class="flex items-start gap-3">
                    <div class="mt-1 w-6 h-6 rounded-full bg-red-500/20 text-red-500 flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-white mb-2">วิธีเปลี่ยนรหัสผ่าน?</h4>
                        <p class="text-sm text-gray-400 leading-relaxed">
                            เข้าสู่ระบบ > คลิกที่ชื่อมุมขวาบน > เลือกเมนู "ตั้งค่าบัญชี" > เลือกหัวข้อ "เปลี่ยนรหัสผ่าน"
                        </p>
                    </div>
                </div>
            </div>

            <div class="faq-card bg-slate-900/50 p-6 rounded-xl border border-slate-700">
                <div class="flex items-start gap-3">
                    <div class="mt-1 w-6 h-6 rounded-full bg-purple-500/20 text-purple-500 flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-gamepad"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-white mb-2">รับเติมเกมอื่นนอกเหนือจากนี้ไหม?</h4>
                        <p class="text-sm text-gray-400 leading-relaxed">
                            ปัจจุบันเรารับเฉพาะเกมที่มีในหน้าเว็บไซต์เท่านั้นครับ เพื่อความรวดเร็วและความปลอดภัยสูงสุด
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once 'footer.php'; ?>
</main> <footer class="relative bg-[#0b1120] text-slate-400 pt-20 pb-10 border-t border-slate-800/60 overflow-hidden mt-auto">
    
    <div class="absolute top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-purple-500/50 to-transparent"></div>
    <div class="absolute top-20 left-10 w-72 h-72 bg-blue-600/10 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-20 right-10 w-72 h-72 bg-purple-600/10 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="container mx-auto px-6 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8">
            
            <div class="lg:col-span-5 space-y-6">
                <div class="flex items-center gap-4">
                    <?php if(!empty($web_config->site_logo)): ?>
                        <div class="relative group">
                            <div class="absolute -inset-0.5 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full blur opacity-30 group-hover:opacity-70 transition duration-500"></div>
                            <img src="<?php echo $web_config->site_logo; ?>" class="relative h-14 w-14 rounded-full bg-slate-900 border-2 border-slate-800 object-cover shadow-xl">
                        </div>
                    <?php endif; ?>
                    <div>
                        <h3 class="text-2xl font-bold text-white tracking-wide font-sans"><?php echo $web_config->site_name; ?></h3>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                            <span class="text-xs text-green-400 font-medium tracking-wide uppercase">Online Store</span>
                        </div>
                    </div>
                </div>
                
                <p class="text-slate-400 leading-relaxed text-sm pr-4 lg:pr-10 border-l-2 border-slate-700 pl-4">
                    <?php 
                        echo !empty($web_config->site_about) 
                        ? nl2br(htmlspecialchars($web_config->site_about)) 
                        : 'ศูนย์รวมไอดีเกมและบริการเติมเกมครบวงจร ระบบอัตโนมัติ 24 ชั่วโมง มั่นใจปลอดภัย 100% พร้อมทีมงานซัพพอร์ตมืออาชีพ'; 
                    ?>
                </p>

                <div class="pt-4">
                    <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-3">Verified Payment</p>
                    <div class="flex gap-3">
                        <div class="bg-white/5 border border-white/10 p-2 rounded-lg hover:bg-white/10 transition duration-300 backdrop-blur-sm group cursor-pointer" title="TrueMoney Wallet">
                            <img src="https://images.seeklogo.com/logo-png/36/1/truemoney-wallet-logo-png_seeklogo-367826.png" class="h-6 w-auto opacity-80 group-hover:opacity-100 transition grayscale group-hover:grayscale-0">
                        </div>
                        </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <h4 class="text-white font-bold text-lg mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-link text-purple-500"></i> ติดตามเรา
                </h4>
                <ul class="space-y-3">
                    <?php 
                    $social_links = [
                        ['url' => $web_config->facebook_url ?? '#', 'icon' => 'fa-facebook-f', 'name' => 'Facebook Page', 'color' => 'hover:bg-[#1877F2] hover:border-[#1877F2]', 'text' => 'hover:text-white'],
                        ['url' => $web_config->line_url ?? '#', 'icon' => 'fa-line', 'name' => 'Line Official', 'color' => 'hover:bg-[#06C755] hover:border-[#06C755]', 'text' => 'hover:text-white'],
                        ['url' => $web_config->youtube_url ?? '#', 'icon' => 'fa-youtube', 'name' => 'YouTube', 'color' => 'hover:bg-[#FF0000] hover:border-[#FF0000]', 'text' => 'hover:text-white'],
                        ['url' => $web_config->tiktok_url ?? '#', 'icon' => 'fa-tiktok', 'name' => 'TikTok', 'color' => 'hover:bg-[#000000] hover:border-white', 'text' => 'hover:text-white'],
                        ['url' => $web_config->instagram_url ?? '#', 'icon' => 'fa-instagram', 'name' => 'Instagram', 'color' => 'hover:bg-gradient-to-tr hover:from-yellow-400 hover:via-red-500 hover:to-purple-500 hover:border-transparent', 'text' => 'hover:text-white'],
                    ];
                    
                    foreach($social_links as $link): 
                        if(!empty($link['url']) && $link['url'] != '#'):
                    ?>
                    <li>
                        <a href="<?php echo $link['url']; ?>" target="_blank" class="group flex items-center gap-3 p-2.5 rounded-xl border border-slate-700/50 bg-slate-800/30 transition-all duration-300 <?php echo $link['color']; ?>">
                            <span class="w-8 h-8 rounded-lg bg-slate-700/50 flex items-center justify-center text-slate-300 group-hover:text-white transition group-hover:bg-white/20">
                                <i class="fa-brands <?php echo $link['icon']; ?>"></i>
                            </span>
                            <span class="text-sm font-medium text-slate-400 <?php echo $link['text']; ?> transition-colors"><?php echo $link['name']; ?></span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-xs ml-auto opacity-0 group-hover:opacity-100 transition-opacity text-white"></i>
                        </a>
                    </li>
                    <?php endif; endforeach; ?>
                </ul>
            </div>

            <div class="lg:col-span-4">
                <?php if(!empty($web_config->discord_widget_id)): ?>
                    <div class="relative group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl blur opacity-20 group-hover:opacity-40 transition duration-500"></div>
                        
                        <div class="relative bg-[#1e2124] rounded-xl overflow-hidden border border-slate-700 shadow-2xl">
                            <div class="flex items-center justify-between px-4 py-2 bg-[#282b30] border-b border-slate-700 h-10 select-none">
                                <div class="flex items-center gap-2">
                                    <i class="fa-brands fa-discord text-[#5865F2]"></i>
                                    <span class="text-gray-300 text-xs font-bold uppercase tracking-wider">Discord Server</span>
                                </div>
                                <div class="flex gap-1.5">
                                    <div class="w-2.5 h-2.5 rounded-full bg-slate-600"></div>
                                    <div class="w-2.5 h-2.5 rounded-full bg-slate-600"></div>
                                </div>
                            </div>
                            
                            <iframe 
                                src="https://discord.com/widget?id=<?php echo $web_config->discord_widget_id; ?>&theme=dark" 
                                width="100%"  
                                height="280" 
                                allowtransparency="true" 
                                frameborder="0" 
                                sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"
                                class="w-full bg-[#1e2124]"
                            ></iframe>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="h-full min-h-[250px] flex flex-col items-center justify-center bg-slate-800/30 rounded-2xl border border-dashed border-slate-700 text-center p-6">
                        <i class="fa-brands fa-discord text-5xl text-slate-600 mb-4"></i>
                        <h5 class="text-slate-300 font-bold mb-1">Join Community</h5>
                        <p class="text-slate-500 text-sm mb-4">เข้าร่วมกลุ่มพูดคุยกับเพื่อนๆ</p>
                        <?php if(!empty($web_config->discord_url)): ?>
                            <a href="<?php echo $web_config->discord_url; ?>" target="_blank" class="px-6 py-2 bg-[#5865F2] hover:bg-[#4752c4] text-white rounded-lg transition shadow-lg shadow-indigo-500/20">
                                คลิกเพื่อเข้าร่วม
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <div class="border-t border-slate-800/60 mt-16 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-slate-500">
            <p>&copy; <?php echo date("Y"); ?> <span class="text-slate-300 font-bold"><?php echo $web_config->site_name; ?></span>. All Rights Reserved.</p>
            <div class="flex gap-6">
                <a href="#" class="hover:text-purple-400 transition">เงื่อนไขการให้บริการ</a>
                <a href="#" class="hover:text-purple-400 transition">นโยบายความเป็นส่วนตัว</a>
                <a href="#" class="hover:text-purple-400 transition">แจ้งชำระเงิน</a>
            </div>
        </div>
    </div>
</footer>

</body>
</html>
<x-layouts.app>
    <!-- Hero Section (2-Column Grid) -->
    <div class="relative bg-white pt-6 pb-16 lg:pt-10 lg:pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-12 lg:gap-12 items-center">
                
                <!-- Left Info Column -->
                <div class="lg:col-span-6 space-y-8">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-emerald-500/20 bg-emerald-50/50 text-emerald-700 text-xs font-bold uppercase tracking-wider font-sans">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        FOUNDING SUPPLIERS PROGRAM NOW OPEN
                    </div>
                    
                    <!-- Heading -->
                    <h1 class="text-4xl sm:text-5xl lg:text-[54px] font-extrabold font-display tracking-tight text-slate-900 leading-tight">
                        The Future of <br class="hidden sm:block">
                        <span class="text-emerald-500">Education</span> Starts with <br class="hidden sm:block">
                        the Right <span class="text-emerald-500">Suppliers.</span>
                    </h1>
                    
                    <!-- Description -->
                    <div class="space-y-4">
                        <p class="text-lg sm:text-xl text-slate-600 leading-relaxed font-semibold">
                            Edushopify is the global marketplace connecting education buyers with trusted suppliers of technology, equipment, and solutions.
                        </p>
                        <p class="text-sm sm:text-base text-slate-500 font-medium">
                            Pre-registration is free and takes less than 5 minutes.
                        </p>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-2">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-3.5 text-base font-semibold rounded-xl text-white bg-emerald-600 hover:bg-emerald-700 shadow-xl shadow-emerald-500/20 transform hover:-translate-y-0.5 transition-all duration-200">
                            Pre-Register Now
                            <svg class="ml-2.5 w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"></path></svg>
                        </a>
                        <a href="#process-section" class="inline-flex items-center justify-center px-8 py-3.5 text-base font-semibold rounded-xl text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 hover:border-slate-300 shadow-sm transform hover:-translate-y-0.5 transition-all duration-200 font-semibold font-display">
                            See How it Works
                        </a>
                    </div>
                </div>
                
                <!-- Right Mockups Column -->
                <div class="lg:col-span-6 mt-16 lg:mt-0 relative flex flex-col items-center">
                    <!-- Hero Mockup Image -->
                    <img src="{{ asset('images/herosection.png') }}" alt="Edushopify Dashboard Preview" class="w-full max-w-[580px] h-auto object-contain">
                    
                    <!-- Bottom Green Banner (stats) -->
                    <div class="w-full max-w-[540px] mt-8 bg-emerald-600 rounded-2xl p-4 text-white shadow-xl shadow-emerald-600/10 flex flex-wrap justify-around items-center text-center gap-4 border border-emerald-500/20 relative z-10">
                        <div>
                            <div class="text-xl sm:text-2xl font-bold font-display">24</div>
                            <div class="text-[10px] sm:text-xs text-emerald-100 uppercase font-semibold tracking-wider mt-0.5">RFQ Opportunities</div>
                        </div>
                        <div class="h-8 w-px bg-emerald-500/50 hidden sm:block"></div>
                        <div>
                            <div class="text-xl sm:text-2xl font-bold font-display">12</div>
                            <div class="text-[10px] sm:text-xs text-emerald-100 uppercase font-semibold tracking-wider mt-0.5">Categories</div>
                        </div>
                        <div class="h-8 w-px bg-emerald-500/50 hidden sm:block"></div>
                        <div>
                            <div class="text-xl sm:text-2xl font-bold font-display">4</div>
                            <div class="text-[10px] sm:text-xs text-emerald-100 uppercase font-semibold tracking-wider mt-0.5">Active Buyers</div>
                        </div>
                    </div>
                </div>
                
            </div>

            <!-- Mini Features Row (Full Width at bottom of Hero) -->
            <div class="mt-16 pt-8 border-t border-slate-100 grid grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Feature 1 -->
                <div class="space-y-2">
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 font-display">Global Reach</h4>
                        <p class="text-slate-500 text-xs mt-0.5 font-semibold">Across continents</p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="space-y-2">
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A12.018 12.018 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632v-.109A12.018 12.018 0 0 1 12 19.128Zm0 0-.002-.003M12 19.128c1.112 0 2.16-.285 3.07-.786a9.337 9.337 0 0 0-.952-4.122 4.125 4.125 0 0 0-2.493 7.533ZM9.75 9.75c0 1.242 1.008 2.25 2.25 2.25s2.25-1.008 2.25-2.25-1.008-2.25-2.25-2.25-2.25 1.008-2.25 2.25Z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 font-display">Verified Suppliers</h4>
                        <p class="text-slate-500 text-xs mt-0.5 font-semibold">Trusted & approved</p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="space-y-2">
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.875V11.25M9 10.125V15h.875c.621 0 1.125.504 1.125 1.125V18.75m0-12.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM18 7.875a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 font-display">Quality Scores</h4>
                        <p class="text-slate-500 text-xs mt-0.5 font-semibold">Data & analytics</p>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="space-y-2">
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18c-2.305 0-4.408.867-6 2.292m0-14.25v14.25"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 font-display">Real Opportunities</h4>
                        <p class="text-slate-500 text-xs mt-0.5 font-semibold">RFQs that convert</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Why Join as a Founding Supplier Section -->
    <div class="bg-slate-50 py-20 lg:py-28 border-t border-slate-200/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Headers -->
            <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                <p class="text-emerald-600 font-bold tracking-[0.2em] text-xs uppercase">Benefits</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 font-display">
                    Why Join as a <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-500 font-bold">Founding Supplier?</span>
                </h2>
            </div>
            
            <!-- Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="bg-white rounded-2xl p-8 border border-slate-200/60 shadow-sm hover:shadow-xl hover:border-emerald-500/30 hover:-translate-y-1 transition-all duration-300 group">
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl text-emerald-600 flex items-center justify-center mb-6 group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-300">
                        <!-- Shield Icon -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3 font-display">Premium Badge & Profile</h3>
                    <p class="text-slate-600 leading-relaxed text-sm">
                        Stand out with a verified founding member badge on your profile.
                    </p>
                </div>
                
                <!-- Card 2 -->
                <div class="bg-white rounded-2xl p-8 border border-slate-200/60 shadow-sm hover:shadow-xl hover:border-emerald-500/30 hover:-translate-y-1 transition-all duration-300 group">
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl text-emerald-600 flex items-center justify-center mb-6 group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-300">
                        <!-- Key icon (Early access) -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3 font-display">Early Access</h3>
                    <p class="text-slate-600 leading-relaxed text-sm">
                        Access high-value RFQs before the platform officially launches.
                    </p>
                </div>
                
                <!-- Card 3 -->
                <div class="bg-white rounded-2xl p-8 border border-slate-200/60 shadow-sm hover:shadow-xl hover:border-emerald-500/30 hover:-translate-y-1 transition-all duration-300 group">
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl text-emerald-600 flex items-center justify-center mb-6 group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-300">
                        <!-- Star Icon -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442a.563.563 0 0 1 .311.988l-4.112 3.562a.563.563 0 0 0-.17.523l1.157 5.351a.563.563 0 0 1-.82.593l-4.72-2.842a.563.563 0 0 0-.53 0l-4.72 2.842a.563.563 0 0 1-.82-.593l1.157-5.351a.563.563 0 0 0-.17-.523L2.908 10.93a.563.563 0 0 1 .311-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3 font-display">Priority Placement</h3>
                    <p class="text-slate-600 leading-relaxed text-sm">
                        Appear top in search results for your categories.
                    </p>
                </div>
                
                <!-- Card 4 -->
                <div class="bg-white rounded-2xl p-8 border border-slate-200/60 shadow-sm hover:shadow-xl hover:border-emerald-500/30 hover:-translate-y-1 transition-all duration-300 group">
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl text-emerald-600 flex items-center justify-center mb-6 group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-300">
                        <!-- Tag/Discount icon (Lower commission) -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581a2.25 2.25 0 0 0 3.181 0l5.178-5.178a2.25 2.25 0 0 0 0-3.181L12.019 3.659A2.25 2.25 0 0 0 10.428 3ZM6.75 6.75h.008v.008H6.75V6.75Z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3 font-display">Lower Commission</h3>
                    <p class="text-slate-600 leading-relaxed text-sm">
                        Enjoy special lifetime low transaction fees on orders.
                    </p>
                </div>
                
                <!-- Card 5 -->
                <div class="bg-white rounded-2xl p-8 border border-slate-200/60 shadow-sm hover:shadow-xl hover:border-emerald-500/30 hover:-translate-y-1 transition-all duration-300 group">
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl text-emerald-600 flex items-center justify-center mb-6 group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-300">
                        <!-- User support/headphones icon -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3 font-display">Dedicated Support</h3>
                    <p class="text-slate-600 leading-relaxed text-sm">
                        Get a dedicated account manager to assist your business.
                    </p>
                </div>
                
                <!-- Card 6 -->
                <div class="bg-white rounded-2xl p-8 border border-slate-200/60 shadow-sm hover:shadow-xl hover:border-emerald-500/30 hover:-translate-y-1 transition-all duration-300 group">
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl text-emerald-600 flex items-center justify-center mb-6 group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-300">
                        <!-- Megaphone icon -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.68-.69-1.25-2.08-1.53-3.21l-.01-.05M15 12.44v-4.88c0-.62-.5-1.12-1.125-1.125H11.25v7.13M12 18.75c-3.728 0-6.75-3.022-6.75-6.75s3.022-6.75 6.75-6.75 6.75 3.022 6.75 6.75-3.022 6.75-6.75 6.75Z" stroke="none"></path><path stroke-linecap="round" stroke-linejoin="round" d="M10.35 12.4c.54.49 1.13.9 1.15 1.83V6.23c-.02.93-.61 1.34-1.15 1.83m6.65 4.34c0 .621-.504 1.125-1.125 1.125H11.25v-4.5h4.625c.621 0 1.125.504 1.125 1.125v2.25ZM6.75 10.5H5.25a2.25 2.25 0 0 0-2.25 2.25v1.5a2.25 2.25 0 0 0 2.25 2.25h1.5M9.75 16.5v2.25A2.25 2.25 0 0 1 7.5 21"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3 font-display">Marketing Boost</h3>
                    <p class="text-slate-600 leading-relaxed text-sm">
                        Feature in our newsletters and marketing campaigns.
                    </p>
                </div>
            </div>
            
        </div>
    </div>

    <!-- How Edushopify Works Section -->
    <div id="process-section" class="bg-white py-20 lg:py-28 border-t border-slate-200/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Headers -->
            <div class="text-center max-w-3xl mx-auto mb-20 space-y-3">
                <p class="text-emerald-600 font-bold tracking-[0.2em] text-xs uppercase">The Process</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 font-display">
                    How Edushopify <span class="text-emerald-500 font-bold">Works</span>
                </h2>
            </div>
            
            <!-- Steps Stepper -->
            <div class="relative">
                <!-- Line connector behind steps -->
                <div class="absolute top-1/2 left-[10%] right-[10%] h-0.5 bg-slate-100 -translate-y-1/2 z-0 hidden lg:block"></div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-8 relative z-10">
                    <!-- Step 1 -->
                    <div class="flex flex-col items-center text-center space-y-4 group">
                        <div class="w-14 h-14 bg-white border-2 border-slate-200 text-slate-700 font-bold rounded-full flex items-center justify-center shadow-md group-hover:border-emerald-500 group-hover:text-emerald-600 transition-all duration-300 relative">
                            <span class="absolute -top-2 -left-2 w-6 h-6 bg-slate-800 text-white rounded-full text-xs flex items-center justify-center font-sans font-semibold">1</span>
                            <!-- User icon -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"></path></svg>
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-bold text-slate-950 font-display text-sm sm:text-base">Register Profile</h4>
                            <p class="text-slate-500 text-xs max-w-[180px] mx-auto">Create your account and complete baseline details.</p>
                        </div>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="flex flex-col items-center text-center space-y-4 group">
                        <div class="w-14 h-14 bg-white border-2 border-slate-200 text-slate-700 font-bold rounded-full flex items-center justify-center shadow-md group-hover:border-emerald-500 group-hover:text-emerald-600 transition-all duration-300 relative">
                            <span class="absolute -top-2 -left-2 w-6 h-6 bg-slate-800 text-white rounded-full text-xs flex items-center justify-center font-sans font-semibold">2</span>
                            <!-- Verify check icon -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path></svg>
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-bold text-slate-950 font-display text-sm sm:text-base">Verify Business</h4>
                            <p class="text-slate-500 text-xs max-w-[180px] mx-auto">Upload trade license and certification documents.</p>
                        </div>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="flex flex-col items-center text-center space-y-4 group">
                        <div class="w-14 h-14 bg-white border-2 border-slate-200 text-slate-700 font-bold rounded-full flex items-center justify-center shadow-md group-hover:border-emerald-500 group-hover:text-emerald-600 transition-all duration-300 relative">
                            <span class="absolute -top-2 -left-2 w-6 h-6 bg-slate-800 text-white rounded-full text-xs flex items-center justify-center font-sans font-semibold">3</span>
                            <!-- Box icon -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"></path></svg>
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-bold text-slate-950 font-display text-sm sm:text-base">Upload Catalog</h4>
                            <p class="text-slate-500 text-xs max-w-[180px] mx-auto">List your products and service catalog details.</p>
                        </div>
                    </div>
                    
                    <!-- Step 4 -->
                    <div class="flex flex-col items-center text-center space-y-4 group">
                        <div class="w-14 h-14 bg-white border-2 border-slate-200 text-slate-700 font-bold rounded-full flex items-center justify-center shadow-md group-hover:border-emerald-500 group-hover:text-emerald-600 transition-all duration-300 relative">
                            <span class="absolute -top-2 -left-2 w-6 h-6 bg-slate-800 text-white rounded-full text-xs flex items-center justify-center font-sans font-semibold">4</span>
                            <!-- Match Inbox icon -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.008 1.24l.885 1.77a2.25 2.25 0 0 0 2.007 1.24h1.98a2.25 2.25 0 0 0 2.007-1.24l.885-1.77a2.25 2.25 0 0 1 2.007-1.24h3.86M12 8.25v3.75m0 0-3-3m3 3 3-3"></path></svg>
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-bold text-slate-950 font-display text-sm sm:text-base">Match RFQs</h4>
                            <p class="text-slate-500 text-xs max-w-[180px] mx-auto">Get matched with buyers' direct quote requests.</p>
                        </div>
                    </div>
                    
                    <!-- Step 5 -->
                    <div class="flex flex-col items-center text-center space-y-4 group">
                        <div class="w-14 h-14 bg-white border-2 border-slate-200 text-slate-700 font-bold rounded-full flex items-center justify-center shadow-md group-hover:border-emerald-500 group-hover:text-emerald-600 transition-all duration-300 relative">
                            <span class="absolute -top-2 -left-2 w-6 h-6 bg-slate-800 text-white rounded-full text-xs flex items-center justify-center font-sans font-semibold">5</span>
                            <!-- Submit send icon -->
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"></path></svg>
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-bold text-slate-950 font-display text-sm sm:text-base">Submit & Win</h4>
                            <p class="text-slate-500 text-xs max-w-[180px] mx-auto">Submit competitive quotes and secure contracts.</p>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Our Launch Roadmap Timeline Section -->
    <div class="bg-slate-50 py-20 lg:py-28 border-t border-slate-200/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Headers -->
            <div class="text-center max-w-3xl mx-auto mb-20 space-y-3">
                <p class="text-emerald-600 font-bold tracking-[0.2em] text-xs uppercase">Timeline</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 font-display">
                    Our Launch <span class="text-emerald-500 font-bold">Roadmap</span>
                </h2>
                <p class="text-slate-500 text-sm max-w-xl mx-auto">Follow our planned milestones as we build the premier educational procurement hub.</p>
            </div>
            
            <!-- Timeline Steps -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 relative">
                <!-- Background horizontal bar (only desktop) -->
                <div class="absolute top-[28px] left-[5%] right-[5%] h-1 bg-slate-200 z-0 hidden lg:block"></div>
                
                <!-- Phase 1 (Active) -->
                <div class="relative z-10 bg-white rounded-2xl p-6 border-2 border-emerald-500 shadow-xl space-y-4 hover:scale-[1.03] hover:-translate-y-1 transition-all duration-300 transform group cursor-default">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-500 text-white rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/25 font-bold transition-transform duration-300 group-hover:scale-110">
                            ★
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-emerald-600 uppercase">Phase 1</div>
                            <h4 class="font-bold text-slate-950 font-display text-sm">Pre-Registration</h4>
                        </div>
                    </div>
                    <div class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg inline-block">June 2026</div>
                    <ul class="space-y-2.5 text-xs font-medium">
                        <li class="flex items-start gap-2 text-slate-700">
                            <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                            <span>Verified Supplier onboarding</span>
                        </li>
                        <li class="flex items-start gap-2 text-slate-700">
                            <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                            <span>Core features walkthrough</span>
                        </li>
                        <li class="flex items-start gap-2 text-slate-700">
                            <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                            <span>Exclusive founding perks</span>
                        </li>
                        <li class="flex items-start gap-2 text-slate-700">
                            <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                            <span>Pre-launch support team</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Phase 2 -->
                <div class="relative z-10 bg-white rounded-2xl p-6 border border-slate-200/60 shadow-sm space-y-4 hover:border-emerald-500 hover:shadow-xl hover:scale-[1.03] hover:-translate-y-1 transition-all duration-300 transform group cursor-default">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-slate-100 text-slate-500 rounded-full flex items-center justify-center transition-all duration-300 group-hover:bg-emerald-500 group-hover:text-white group-hover:shadow-lg group-hover:shadow-emerald-500/25 group-hover:scale-110">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path></svg>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-500 uppercase transition-colors duration-300 group-hover:text-emerald-600">Phase 2</div>
                            <h4 class="font-bold text-slate-950 font-display text-sm">Platform Preview</h4>
                        </div>
                    </div>
                    <div class="text-xs font-semibold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg inline-block transition-colors duration-300 group-hover:bg-emerald-50 group-hover:text-emerald-700">July 2026</div>
                    <ul class="space-y-2.5 text-xs font-medium">
                        <li class="flex items-start gap-2 text-slate-500 group-hover:text-slate-700 transition-colors duration-300">
                            <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-emerald-500 transition-colors duration-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                            <span>Preview matchmaking system</span>
                        </li>
                        <li class="flex items-start gap-2 text-slate-500 group-hover:text-slate-700 transition-colors duration-300">
                            <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-emerald-500 transition-colors duration-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                            <span>Import catalogs & services</span>
                        </li>
                        <li class="flex items-start gap-2 text-slate-500 group-hover:text-slate-700 transition-colors duration-300">
                            <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-emerald-500 transition-colors duration-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                            <span>Review incoming requests</span>
                        </li>
                        <li class="flex items-start gap-2 text-slate-500 group-hover:text-slate-700 transition-colors duration-300">
                            <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-emerald-500 transition-colors duration-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                            <span>API integrations sandbox</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Phase 3 -->
                <div class="relative z-10 bg-white rounded-2xl p-6 border border-slate-200/60 shadow-sm space-y-4 hover:border-emerald-500 hover:shadow-xl hover:scale-[1.03] hover:-translate-y-1 transition-all duration-300 transform group cursor-default">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-slate-100 text-slate-500 rounded-full flex items-center justify-center transition-all duration-300 group-hover:bg-emerald-500 group-hover:text-white group-hover:shadow-lg group-hover:shadow-emerald-500/25 group-hover:scale-110">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a.75.75 0 0 0 .937-.83 7.97 7.97 0 0 0-4.408-5.723M18.001 21h-12M6 18.72a.75.75 0 0 1-.937-.83 7.97 7.97 0 0 1 4.408-5.723M12 11.25a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z"></path></svg>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-500 transition-colors duration-300 group-hover:text-emerald-600">Phase 3</div>
                            <h4 class="font-bold text-slate-950 font-display text-sm">Buyer Onboarding</h4>
                        </div>
                    </div>
                    <div class="text-xs font-semibold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg inline-block transition-colors duration-300 group-hover:bg-emerald-50 group-hover:text-emerald-700">August 2026</div>
                    <ul class="space-y-2.5 text-xs font-medium">
                        <li class="flex items-start gap-2 text-slate-500 group-hover:text-slate-700 transition-colors duration-300">
                            <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-emerald-500 transition-colors duration-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                            <span>Buyer registrations</span>
                        </li>
                        <li class="flex items-start gap-2 text-slate-500 group-hover:text-slate-700 transition-colors duration-300">
                            <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-emerald-500 transition-colors duration-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                            <span>RFQ posting tools live</span>
                        </li>
                        <li class="flex items-start gap-2 text-slate-500 group-hover:text-slate-700 transition-colors duration-300">
                            <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-emerald-500 transition-colors duration-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                            <span>Custom request forms</span>
                        </li>
                        <li class="flex items-start gap-2 text-slate-500 group-hover:text-slate-700 transition-colors duration-300">
                            <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-emerald-500 transition-colors duration-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                            <span>Direct communication panel</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Phase 4 -->
                <div class="relative z-10 bg-white rounded-2xl p-6 border border-slate-200/60 shadow-sm space-y-4 hover:border-emerald-500 hover:shadow-xl hover:scale-[1.03] hover:-translate-y-1 transition-all duration-300 transform group cursor-default">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-slate-100 text-slate-500 rounded-full flex items-center justify-center transition-all duration-300 group-hover:bg-emerald-500 group-hover:text-white group-hover:shadow-lg group-hover:shadow-emerald-500/25 group-hover:scale-110">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.63 8.41a6 6 0 0 1 5.96 5.96Zm0 0L3 21t1.8-8.2"></path></svg>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-500 transition-colors duration-300 group-hover:text-emerald-600">Phase 4</div>
                            <h4 class="font-bold text-slate-950 font-display text-sm">Official Launch</h4>
                        </div>
                    </div>
                    <div class="text-xs font-semibold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg inline-block transition-colors duration-300 group-hover:bg-emerald-50 group-hover:text-emerald-700">September 2026</div>
                    <ul class="space-y-2.5 text-xs font-medium">
                        <li class="flex items-start gap-2 text-slate-500 group-hover:text-slate-700 transition-colors duration-300">
                            <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-emerald-500 transition-colors duration-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                            <span>Fully live B2B marketplace</span>
                        </li>
                        <li class="flex items-start gap-2 text-slate-500 group-hover:text-slate-700 transition-colors duration-300">
                            <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-emerald-500 transition-colors duration-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                            <span>Live transactions & payments</span>
                        </li>
                        <li class="flex items-start gap-2 text-slate-500 group-hover:text-slate-700 transition-colors duration-300">
                            <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-emerald-500 transition-colors duration-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                            <span>Global supplier search</span>
                        </li>
                        <li class="flex items-start gap-2 text-slate-500 group-hover:text-slate-700 transition-colors duration-300">
                            <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-emerald-500 transition-colors duration-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path></svg>
                            <span>Premium marketing active</span>
                        </li>
                    </ul>
            </div>
        </div>
    </div>
</div>>
        </div>
    </div>
</div>
          <!-- Simple & Transparent Pricing Section -->
    @php
        $homepagePlans   = \App\Models\SubscriptionPlan::active()->get();
        $homeFreePlans   = $homepagePlans->where('type', 'free')->values();
        $homeMonthlyPaid = $homepagePlans->where('type', 'monthly')->values();
        $homeYearlyPaid  = $homepagePlans->where('type', 'yearly')->values();

        // Merge Free plans + Monthly/Yearly plans, sorting by sort_order
        $monthlyViewPlans = $homepagePlans->filter(fn($p) => $p->billing_type === 'free' || $p->billing_type === 'monthly')->sortBy('sort_order')->values();
        $yearlyViewPlans  = $homepagePlans->filter(fn($p) => $p->billing_type === 'free' || $p->billing_type === 'yearly')->sortBy('sort_order')->values();

        $hasToggle       = $homeMonthlyPaid->isNotEmpty() && $homeYearlyPaid->isNotEmpty();
    @endphp
    <div id="pricing-section" class="bg-white py-20 lg:py-28 border-t border-slate-200/50"
         x-data="{ billing: '{{ $homeMonthlyPaid->isNotEmpty() ? 'monthly' : 'yearly' }}' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Headers -->
            <div class="text-center max-w-3xl mx-auto mb-10 space-y-3">
                <p class="text-emerald-600 font-bold tracking-[0.2em] text-xs uppercase">Pricing</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 font-display">
                    Simple &amp; <span class="text-emerald-500 font-bold">Transparent</span> Pricing
                </h2>
                <p class="text-slate-500 text-sm max-w-xl mx-auto">Choose the plan that fits your business needs, with zero hidden costs.</p>
            </div>

            <!-- Monthly / Yearly Toggle -->
            @if($hasToggle)
            <div class="flex justify-center mb-12">
                <div class="inline-flex items-center bg-slate-100 rounded-xl p-1 gap-1">
                    <button @click="billing = 'monthly'"
                            :class="billing === 'monthly' ? 'bg-white shadow text-slate-900' : 'text-slate-500 hover:text-slate-700'"
                            class="px-5 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200">
                        Monthly
                    </button>
                    <button @click="billing = 'yearly'"
                            :class="billing === 'yearly' ? 'bg-white shadow text-slate-900' : 'text-slate-500 hover:text-slate-700'"
                            class="px-5 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2">
                        Yearly
                        @php
                            $mp = $homeMonthlyPaid->first();
                            $yp = $homeYearlyPaid->first();
                            $homeSavePct = 0;
                            if ($mp && $yp && $mp->price > 0 && $yp->price > 0) {
                                $monthEq = $mp->price * 12;
                                $homeSavePct = $monthEq > $yp->price ? round((($monthEq - $yp->price) / $monthEq) * 100) : 0;
                            }
                        @endphp
                        @if($homeSavePct > 0)
                            <span class="bg-emerald-500 text-white text-[10px] px-2 py-0.5 rounded-full font-bold">Save {{ $homeSavePct }}%</span>
                        @endif
                    </button>
                </div>
            </div>
            @endif

            <!-- Monthly View (includes Free + Monthly) -->
            @if($monthlyViewPlans->isNotEmpty())
            <div x-show="billing === 'monthly'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="flex flex-col md:flex-row gap-8 items-stretch justify-center max-w-6xl mx-auto">
                @foreach($monthlyViewPlans as $plan)
                <div class="relative bg-white rounded-2xl p-8 border {{ $plan->is_featured ? 'border-2 border-emerald-500 shadow-xl scale-[1.02]' : 'border-slate-200 shadow-sm hover:shadow-lg hover:border-slate-300' }} w-full md:w-1/3 max-w-[380px] flex flex-col justify-between transition-all duration-300">
                    @if($plan->is_featured)
                    <span class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 bg-emerald-500 text-white text-[10px] font-bold rounded-full uppercase tracking-wider shadow font-sans">Best Value</span>
                    @endif
                    @if($plan->bonus_days > 0)
                    <div class="absolute {{ $plan->is_featured ? 'top-4 right-4' : '-top-3 left-1/2 -translate-x-1/2' }}">
                        <span class="bg-amber-400 text-amber-900 text-[10px] font-bold px-3 py-1 rounded-full shadow-sm whitespace-nowrap">+{{ $plan->bonus_days }} Bonus Days!</span>
                    </div>
                    @endif
                    <div class="space-y-6">
                        <div>
                            <span class="inline-flex px-2.5 py-0.5 {{ $plan->isFree() ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700' }} text-[10px] font-bold rounded-full uppercase tracking-wider mb-3">
                                {{ ucfirst($plan->billing_type) }}
                            </span>
                            <h3 class="text-lg font-bold text-slate-900 font-display">{{ $plan->name }}</h3>
                            
                            @if($plan->isFree())
                                <div class="mt-3 flex items-baseline">
                                    <span class="text-4xl font-extrabold text-slate-950 font-display">FREE</span>
                                </div>
                                <p class="mt-1.5 text-xs text-slate-500">
                                    {{ $plan->totalFreeDays() }} days free
                                    @if($plan->bonus_days > 0)<span class="text-amber-600 font-semibold"> (inc. +{{ $plan->bonus_days }}d bonus)</span>@endif
                                </p>
                            @else
                                <div class="mt-3 flex items-baseline gap-1">
                                    <span class="text-4xl font-extrabold text-slate-950 font-display">{{ $plan->formattedPrice() }}</span>
                                    <span class="text-slate-500 text-sm">/mo</span>
                                </div>
                                @if($plan->bonus_days > 0)
                                    <p class="mt-1 text-xs text-amber-600 font-semibold">+ {{ $plan->bonus_days }} bonus days</p>
                                @endif
                            @endif
                        </div>
                        @include('supplier._plan-features', ['plan' => $plan])
                    </div>
                    @if(auth()->check() && auth()->user()->isSupplier())
                        <a href="{{ route('supplier.pricing') }}" class="mt-8 block w-full text-center px-4 py-3 rounded-xl {{ $plan->is_featured ? 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-md shadow-emerald-500/10' : 'border border-slate-200 text-slate-700 hover:bg-slate-50' }} text-xs font-semibold transition-colors">
                            Choose {{ $plan->name }}
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="mt-8 block w-full text-center px-4 py-3 rounded-xl {{ $plan->is_featured ? 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-md shadow-emerald-500/10' : 'border border-slate-200 text-slate-700 hover:bg-slate-50' }} text-xs font-semibold transition-colors">
                            {{ $plan->isFree() ? 'Get Started Free' : 'Get Started' }}
                        </a>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            <!-- Yearly View (includes Free + Yearly) -->
            @if($yearlyViewPlans->isNotEmpty())
            <div x-show="billing === 'yearly'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="flex flex-col md:flex-row gap-8 items-stretch justify-center max-w-6xl mx-auto">
                @foreach($yearlyViewPlans as $plan)
                @php
                    $ymp = $homeMonthlyPaid->first();
                    $ySave = null;
                    if ($ymp && $ymp->price > 0 && $plan->price > 0) {
                        $yMonthEq = $ymp->price * 12;
                        $yAmt = $yMonthEq - $plan->price;
                        if ($yAmt > 0) $ySave = ['amount' => $ymp->effectiveCurrencySymbol().number_format($yAmt,0), 'pct' => round(($yAmt/$yMonthEq)*100)];
                    }
                @endphp
                <div class="relative bg-white rounded-2xl p-8 border {{ $plan->is_featured ? 'border-2 border-emerald-500 shadow-xl scale-[1.02]' : 'border-slate-200 shadow-sm hover:shadow-lg hover:border-slate-300' }} w-full md:w-1/3 max-w-[380px] flex flex-col justify-between transition-all duration-300">
                    @if($plan->is_featured)
                    <span class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 bg-emerald-500 text-white text-[10px] font-bold rounded-full uppercase tracking-wider shadow font-sans">Best Value</span>
                    @endif
                    @if($ySave)
                    <div class="absolute top-4 right-4">
                        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2.5 py-1 rounded-full">Save {{ $ySave['amount'] }}</span>
                    </div>
                    @endif
                    @if($plan->bonus_days > 0)
                    <div class="absolute {{ $plan->is_featured ? 'top-4 right-4' : '-top-3 left-1/2 -translate-x-1/2' }}">
                        <span class="bg-amber-400 text-amber-900 text-[10px] font-bold px-3 py-1 rounded-full shadow-sm whitespace-nowrap">+{{ $plan->bonus_days }} Bonus Days!</span>
                    </div>
                    @endif
                    <div class="space-y-6">
                        <div>
                            <span class="inline-flex px-2.5 py-0.5 {{ $plan->isFree() ? 'bg-emerald-100 text-emerald-700' : 'bg-purple-100 text-purple-700' }} text-[10px] font-bold rounded-full uppercase tracking-wider mb-3">
                                {{ ucfirst($plan->billing_type) }}
                            </span>
                            <h3 class="text-lg font-bold text-slate-900 font-display">{{ $plan->name }}</h3>
                            
                            @if($plan->isFree())
                                <div class="mt-3 flex items-baseline">
                                    <span class="text-4xl font-extrabold text-slate-950 font-display">FREE</span>
                                </div>
                                <p class="mt-1.5 text-xs text-slate-500">
                                    {{ $plan->totalFreeDays() }} days free
                                    @if($plan->bonus_days > 0)<span class="text-amber-600 font-semibold"> (inc. +{{ $plan->bonus_days }}d bonus)</span>@endif
                                </p>
                            @else
                                <div class="mt-3 flex items-baseline gap-1">
                                    <span class="text-4xl font-extrabold text-slate-950 font-display">{{ $plan->formattedPrice() }}</span>
                                    <span class="text-slate-500 text-sm">/yr</span>
                                </div>
                                @if($ySave)<p class="mt-1 text-xs text-emerald-600 font-semibold">Save {{ $ySave['amount'] }} vs monthly ({{ $ySave['pct'] }}% off)</p>@endif
                                @if($plan->bonus_days > 0)<p class="mt-1 text-xs text-amber-600 font-semibold">+ {{ $plan->bonus_days }} bonus days</p>@endif
                            @endif
                        </div>
                        @include('supplier._plan-features', ['plan' => $plan])
                    </div>
                    @if(auth()->check() && auth()->user()->isSupplier())
                        <a href="{{ route('supplier.pricing') }}" class="mt-8 block w-full text-center px-4 py-3 rounded-xl {{ $plan->is_featured ? 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-md shadow-emerald-500/10' : 'border border-slate-200 text-slate-700 hover:bg-slate-50' }} text-xs font-semibold transition-colors">
                            Choose {{ $plan->name }}
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="mt-8 block w-full text-center px-4 py-3 rounded-xl {{ $plan->is_featured ? 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-md shadow-emerald-500/10' : 'border border-slate-200 text-slate-700 hover:bg-slate-50' }} text-xs font-semibold transition-colors">
                            {{ $plan->isFree() ? 'Get Started Free' : 'Get Started' }}
                        </a>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

        </div>
    </div>


    <!-- Frequently Asked Questions Section -->
    <div class="bg-slate-50 py-20 lg:py-28 border-t border-slate-200/50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Headers -->
            <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                <p class="text-emerald-600 font-bold tracking-[0.2em] text-xs uppercase">FAQ</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 font-display">
                    Frequently Asked <span class="text-emerald-500 font-bold">Questions</span>
                </h2>
            </div>
            
            <!-- Accordions -->
            <div class="space-y-4">
                <!-- Q1 -->
                <details class="group bg-white rounded-xl border border-slate-200/60 overflow-hidden [&_summary::-webkit-details-marker]:hidden transition-all duration-305">
                    <summary class="flex justify-between items-center p-6 text-sm sm:text-base font-bold text-slate-905 cursor-pointer select-none">
                        <span>What is the Founding Supplier program?</span>
                        <span class="text-slate-400 group-open:rotate-180 transition-transform duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path></svg>
                        </span>
                    </summary>
                    <div class="px-6 pb-6 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-4 font-medium">
                        The Founding Supplier program is an exclusive pre-launch campaign offering premium benefits, profile badges, and fee exemptions for suppliers who join before our official launch.
                    </div>
                </details>
                
                <!-- Q2 -->
                <details class="group bg-white rounded-xl border border-slate-200/60 overflow-hidden [&_summary::-webkit-details-marker]:hidden transition-all duration-305">
                    <summary class="flex justify-between items-center p-6 text-sm sm:text-base font-bold text-slate-905 cursor-pointer select-none">
                        <span>How long is the pre-launch phase?</span>
                        <span class="text-slate-400 group-open:rotate-180 transition-transform duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path></svg>
                        </span>
                    </summary>
                    <div class="px-6 pb-6 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-4 font-medium">
                        The pre-registration phase runs from June 2026 to August 2026, followed by platform preview access, prior to our official public launch in September 2026.
                    </div>
                </details>
                
                <!-- Q3 -->
                <details class="group bg-white rounded-xl border border-slate-200/60 overflow-hidden [&_summary::-webkit-details-marker]:hidden transition-all duration-305">
                    <summary class="flex justify-between items-center p-6 text-sm sm:text-base font-bold text-slate-905 cursor-pointer select-none">
                        <span>What is the cost of registration?</span>
                        <span class="text-slate-400 group-open:rotate-180 transition-transform duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path></svg>
                        </span>
                    </summary>
                    <div class="px-6 pb-6 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-4 font-medium">
                        Basic registration is completely free. We also offer Professional and Enterprise plans with added tools, and pre-registering now gets you 3 free months of the Pro plan.
                    </div>
                </details>
                
                <!-- Q4 -->
                <details class="group bg-white rounded-xl border border-slate-200/60 overflow-hidden [&_summary::-webkit-details-marker]:hidden transition-all duration-305">
                    <summary class="flex justify-between items-center p-6 text-sm sm:text-base font-bold text-slate-905 cursor-pointer select-none">
                        <span>How do I verify my supplier account?</span>
                        <span class="text-slate-400 group-open:rotate-180 transition-transform duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path></svg>
                        </span>
                    </summary>
                    <div class="px-6 pb-6 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-4 font-medium">
                        Account verification requires uploading certified documents such as a valid company registration certificate, tax certificates, and authorization letters.
                    </div>
                </details>
                
                <!-- Q5 -->
                <details class="group bg-white rounded-xl border border-slate-200/60 overflow-hidden [&_summary::-webkit-details-marker]:hidden transition-all duration-305">
                    <summary class="flex justify-between items-center p-6 text-sm sm:text-base font-bold text-slate-905 cursor-pointer select-none">
                        <span>Can I change my plan later?</span>
                        <span class="text-slate-400 group-open:rotate-180 transition-transform duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path></svg>
                        </span>
                    </summary>
                    <div class="px-6 pb-6 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-4 font-medium">
                        Yes, you can upgrade, downgrade, or cancel your subscription plan at any time directly through your supplier dashboard settings page.
                    </div>
                </details>
                
                <!-- Q6 -->
                <details class="group bg-white rounded-xl border border-slate-200/60 overflow-hidden [&_summary::-webkit-details-marker]:hidden transition-all duration-305">
                    <summary class="flex justify-between items-center p-6 text-sm sm:text-base font-bold text-slate-905 cursor-pointer select-none">
                        <span>Who qualifies as a Founding Supplier?</span>
                        <span class="text-slate-400 group-open:rotate-180 transition-transform duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path></svg>
                        </span>
                    </summary>
                    <div class="px-6 pb-6 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-4 font-medium">
                        Any education equipment manufacturer, distributor, service provider, publisher, or system integrator who successfully registers and gets verified during our pre-launch phase.
                    </div>
                </details>
            </div>
            
        </div>
    </div>

    <!-- CTA Dark Banner Section -->
    <div class="bg-slate-900 py-16 sm:py-24 text-white relative overflow-hidden">
        <!-- Decorative background glow -->
        <div class="absolute -right-1/4 -bottom-1/2 w-[600px] h-[600px] bg-emerald-500/10 rounded-full blur-3xl"></div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-12 lg:gap-12 items-center">
                <!-- Left Info -->
                <div class="lg:col-span-7 space-y-6">
                    <span class="px-3 py-1 bg-emerald-500/20 text-emerald-400 text-xs font-bold rounded-full uppercase tracking-wider font-sans">
                        Limited Time Opportunity
                    </span>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight font-display text-white">
                        Don't Miss Your Chance to Join as a Founding Supplier
                    </h2>
                    <p class="text-slate-400 text-sm sm:text-base max-w-xl font-medium">
                        Final chance to register and secure your premium status and founding supplier benefits before the platform launch.
                    </p>
                    
                    <div class="flex flex-wrap gap-4 items-center pt-2">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold rounded-xl shadow-lg transition-all text-sm sm:text-base duration-200">
                            Pre-Register as a Founding Supplier
                        </a>
                    </div>
                </div>
                
                <!-- Right Timer -->
                <div class="lg:col-span-5 mt-12 lg:mt-0 flex flex-col items-center lg:items-end justify-center">
                    <div class="text-slate-400 text-xs uppercase font-bold tracking-widest mb-4">Registration Closes In</div>
                    <div class="flex gap-3 sm:gap-6 bg-slate-950/50 p-6 rounded-2xl border border-slate-800/80 backdrop-blur-sm shadow-2xl">
                        <!-- Days -->
                        <div class="flex flex-col items-center">
                            <span id="countdown-days" class="text-3xl sm:text-4xl font-extrabold font-display text-emerald-400 min-w-[3.5rem] text-center">00</span>
                            <span class="text-[9px] uppercase tracking-wider text-slate-500 font-semibold mt-1">Days</span>
                        </div>
                        <div class="text-xl sm:text-2xl text-slate-700 font-bold">:</div>
                        <!-- Hours -->
                        <div class="flex flex-col items-center">
                            <span id="countdown-hours" class="text-3xl sm:text-4xl font-extrabold font-display text-emerald-400 min-w-[3.5rem] text-center">00</span>
                            <span class="text-[9px] uppercase tracking-wider text-slate-500 font-semibold mt-1">Hours</span>
                        </div>
                        <div class="text-xl sm:text-2xl text-slate-700 font-bold">:</div>
                        <!-- Minutes -->
                        <div class="flex flex-col items-center">
                            <span id="countdown-mins" class="text-3xl sm:text-4xl font-extrabold font-display text-emerald-400 min-w-[3.5rem] text-center">00</span>
                            <span class="text-[9px] uppercase tracking-wider text-slate-500 font-semibold mt-1">Mins</span>
                        </div>
                        <div class="text-xl sm:text-2xl text-slate-700 font-bold">:</div>
                        <!-- Seconds -->
                        <div class="flex flex-col items-center">
                            <span id="countdown-secs" class="text-3xl sm:text-4xl font-extrabold font-display text-emerald-400 min-w-[3.5rem] text-center">00</span>
                            <span class="text-[9px] uppercase tracking-wider text-slate-500 font-semibold mt-1">Secs</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Don't Miss This Opportunity Card Banner -->
    <div class="bg-white py-16 sm:py-24 border-t border-slate-200/50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-tr from-emerald-600/5 via-teal-500/5 to-indigo-600/5 rounded-3xl border border-slate-100 p-8 sm:p-12 text-center space-y-6 shadow-sm">
                <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-display">
                    Don't Miss This <span class="text-emerald-500 font-bold">Opportunity</span>
                </h3>
                <p class="text-slate-600 text-sm sm:text-base max-w-xl mx-auto font-medium">
                    Secure your place as a founding member and gain priority access to institutional buyers globally.
                </p>
                <div class="pt-2">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-3.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl shadow-md transition-all">
                        Pre-Register Now
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Deadline Banner -->
    <div class="bg-emerald-50 border-t border-b border-emerald-100 py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3 text-emerald-900 font-semibold text-xs sm:text-sm">
                <span class="text-xl">⚡</span>
                <span>DEADLINE: Founding Registration Closes Aug 30, 2026. Register now to secure status.</span>
            </div>
            <a href="{{ route('register') }}" class="px-5 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-lg text-xs shadow-md transition-all shrink-0">
                Register Now →
            </a>
        </div>
    </div>

    <x-slot:scripts>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <script>
            // Timer target: August 30, 2026
            const targetDate = new Date("2026-08-30T00:00:00Z").getTime();

            const runCountdown = () => {
                const now = new Date().getTime();
                const gap = targetDate - now;

                if (gap <= 0) {
                    document.getElementById("countdown-days").innerText = "00";
                    document.getElementById("countdown-hours").innerText = "00";
                    document.getElementById("countdown-mins").innerText = "00";
                    document.getElementById("countdown-secs").innerText = "00";
                    return;
                }

                const d = Math.floor(gap / (1000 * 60 * 60 * 24));
                const h = Math.floor((gap % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const m = Math.floor((gap % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((gap % (1000 * 60)) / 1000);

                document.getElementById("countdown-days").innerText = String(d).padStart(2, '0');
                document.getElementById("countdown-hours").innerText = String(h).padStart(2, '0');
                document.getElementById("countdown-mins").innerText = String(m).padStart(2, '0');
                document.getElementById("countdown-secs").innerText = String(s).padStart(2, '0');
            };

            setInterval(runCountdown, 1000);
            runCountdown();
        </script>
    </x-slot:scripts>
</x-layouts.app>

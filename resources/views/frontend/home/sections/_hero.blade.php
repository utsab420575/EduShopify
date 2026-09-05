<section class="relative h-[420px] overflow-hidden">
    <img src="{{ asset('images/herosection.png') }}" class="absolute inset-0 w-full h-full object-cover" alt="EduShopify" />
    <div class="hero-overlay absolute inset-0"></div>
    <div class="relative fe-container h-full flex flex-col justify-center">
        <h1 class="text-white font-extrabold text-4xl lg:text-5xl leading-tight max-w-xl">
            Global Suppliers for<br>Education. All in One Place.
        </h1>
        <p class="text-white/80 mt-3 text-base max-w-sm">Connect with verified education suppliers worldwide</p>
        <div class="flex gap-3 mt-7 flex-wrap">
            <a href="{{ route('frontend.suppliers.index') }}" class="bg-white text-gray-900 font-semibold px-5 py-2.5 rounded-md text-sm hover:bg-gray-100">Find Suppliers</a>
            <a href="{{ route('frontend.handoff.post-rfq') }}" class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-5 py-2.5 rounded-md text-sm">Post an RFQ</a>
        </div>
    </div>
</section>

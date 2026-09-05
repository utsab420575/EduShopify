<section class="bg-gray-50 py-16 px-4 border-t border-gray-100">
  <div class="max-w-3xl mx-auto text-center mb-10">
    <p class="text-emerald-600 text-xs font-semibold uppercase tracking-widest mb-2">Our Advantage</p>
    <h2 class="text-3xl font-bold text-gray-900">Why Choose <span class="text-emerald-500">Edushopify</span></h2>
    <p class="text-gray-500 mt-3 text-sm leading-relaxed max-w-sm mx-auto">The trusted platform for education procurement — connecting buyers with verified suppliers worldwide.</p>
  </div>

  {{-- Skeleton shown immediately; JS reveals #why-choose-grid a short
       moment later — heading above is real from the start. --}}
  <div class="max-w-5xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6" data-skeleton-target="#why-choose-grid">
    <div class="skel-block" style="height:120px;"></div>
    <div class="skel-block" style="height:120px;"></div>
    <div class="skel-block" style="height:120px;"></div>
    <div class="skel-block" style="height:120px;"></div>
  </div>

  <div class="max-w-5xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 text-center cards-hidden" id="why-choose-grid">
    {{-- Item 1: Verified Suppliers --}}
    <div class="why-choose-card group flex flex-col items-center gap-3 card-fade-up" style="animation-delay: 0ms">
      <div class="why-choose-icon w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100/80 flex items-center justify-center transition-all duration-200">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
      </div>
      <div>
        <p class="why-choose-title font-semibold text-sm text-gray-900 transition-colors duration-200">Verified Suppliers</p>
        <p class="text-xs text-gray-500 mt-0.5">Trusted &amp; Verified</p>
      </div>
    </div>

    {{-- Item 2: Global Reach --}}
    <div class="why-choose-card group flex flex-col items-center gap-3 card-fade-up" style="animation-delay: 55ms">
      <div class="why-choose-icon w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100/80 flex items-center justify-center transition-all duration-200">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
      </div>
      <div>
        <p class="why-choose-title font-semibold text-sm text-gray-900 transition-colors duration-200">Global Reach</p>
        <p class="text-xs text-gray-500 mt-0.5">Suppliers in 50+ Countries</p>
      </div>
    </div>

    {{-- Item 3: RFQ Opportunities --}}
    <div class="why-choose-card group flex flex-col items-center gap-3 card-fade-up" style="animation-delay: 110ms">
      <div class="why-choose-icon w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100/80 flex items-center justify-center transition-all duration-200">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
      </div>
      <div>
        <p class="why-choose-title font-semibold text-sm text-gray-900 transition-colors duration-200">RFQ Opportunities</p>
        <p class="text-xs text-gray-500 mt-0.5">Get Relevant Business</p>
      </div>
    </div>

    {{-- Item 4: Secure & Reliable --}}
    <div class="why-choose-card group flex flex-col items-center gap-3 card-fade-up" style="animation-delay: 165ms">
      <div class="why-choose-icon w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100/80 flex items-center justify-center transition-all duration-200">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <div>
        <p class="why-choose-title font-semibold text-sm text-gray-900 transition-colors duration-200">Secure &amp; Reliable</p>
        <p class="text-xs text-gray-500 mt-0.5">Safe Communication</p>
      </div>
    </div>
  </div>
</section>

<style>
.why-choose-card {
  padding: 24px 16px;
  border-radius: 18px;
  background-color: transparent;
  border: 1px solid transparent;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  cursor: pointer;
}

.why-choose-card:hover {
  background-color: #ffffff;
  border-color: #e5e7eb;
  box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.06);
  transform: translateY(-2px);
}

.why-choose-card:hover .why-choose-icon {
  transform: scale(1.08);
  background-color: #ecfdf5;
  color: #059669;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
}

.why-choose-card:hover .why-choose-title {
  color: #059669;
}
</style>

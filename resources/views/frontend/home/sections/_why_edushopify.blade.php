<section class="py-12 lg:py-16 bg-white">
    <div class="fe-container">
        <x-frontend::common.section-heading
            eyebrow="Why EduShopify"
            title="Built for education procurement"
            align="center"
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach([
                ['icon' => 'fa-diagram-project', 'title' => 'Structured RFQ Procurement', 'text' => 'Post detailed requests and receive comparable, competitive quotations.'],
                ['icon' => 'fa-magnifying-glass', 'title' => 'Verified Supplier Discovery', 'text' => 'Find suppliers reviewed and approved for the education sector.'],
                ['icon' => 'fa-scale-balanced', 'title' => 'Competitive Quotation Workflow', 'text' => 'Compare, shortlist and request revisions before you award.'],
                ['icon' => 'fa-people-group', 'title' => 'Account-Based Collaboration', 'text' => 'Manage procurement as a team with roles and permissions.'],
            ] as $item)
                <div class="fe-card rounded-2xl p-6">
                    <span class="w-11 h-11 rounded-xl flex items-center justify-center mb-4" style="background:var(--fe-primary-soft);color:var(--fe-primary);">
                        <i class="fa-solid {{ $item['icon'] }}"></i>
                    </span>
                    <h3 class="text-sm font-semibold mb-1.5" style="color:var(--fe-text);">{{ $item['title'] }}</h3>
                    <p class="text-sm" style="color:var(--fe-text-muted);">{{ $item['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

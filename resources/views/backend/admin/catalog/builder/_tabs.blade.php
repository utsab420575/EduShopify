@php
    $tabs = [
        'categories'        => ['label' => 'Category', 'icon' => 'fa-sitemap', 'route' => 'admin.catalog.builder.categories'],
        'attribute-groups'  => ['label' => 'Attribute Group', 'icon' => 'fa-layer-group', 'route' => 'admin.catalog.builder.attribute-groups'],
        'attributes'        => ['label' => 'Attribute', 'icon' => 'fa-sliders', 'route' => 'admin.catalog.builder.attributes'],
        'assign'            => ['label' => 'Assign to Category', 'icon' => 'fa-link', 'route' => 'admin.catalog.builder.assign'],
    ];
    $tabKeys = array_keys($tabs);
@endphp

<div class="mb-6">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-900">Category Builder</h1>
        <p class="text-sm text-gray-500 mt-1">A guided workspace for setting up categories, attribute groups, attributes, and category assignments. Steps can be used in any order.</p>
    </div>

    <div class="border-b border-gray-200">
        <nav class="flex gap-6 -mb-px overflow-x-auto">
            @foreach($tabs as $key => $tab)
                <a href="{{ route($tab['route']) }}"
                   class="py-3 text-sm font-semibold border-b-2 whitespace-nowrap flex items-center gap-2 {{ $active === $key ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700' }}">
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold {{ $active === $key ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500' }}">
                        {{ array_search($key, $tabKeys) + 1 }}
                    </span>
                    <i class="fa-solid {{ $tab['icon'] }} text-xs"></i>
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</div>

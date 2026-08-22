@props(['name'])

<svg {{ $attributes->merge(['class' => 'w-5 h-5']) }} fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
    @switch($name)
        @case('home')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5L12 3l9 7.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 9.5V20a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V9.5" />
            @break

        @case('document-text')
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 3h7l5 5v12a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M14 3v5h5M9 13h6M9 17h6M9 9h2" />
            @break

        @case('inbox-arrow-down')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13h4.5l1.5 3h6l1.5-3H21" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13V7a2 2 0 012-2h10a2 2 0 012 2v6" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13v5a2 2 0 002 2h14a2 2 0 002-2v-5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v6m0 0l-2.5-2.5M12 10l2.5-2.5" />
            @break

        @case('scale')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M8 6h8M4 6l-2 6a3 3 0 006 0L6 6M20 6l-2 6a3 3 0 006 0L22 6" />
            @break

        @case('trophy')
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 4h8v6a4 4 0 01-8 0V4z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 5H5a2 2 0 002 4M16 5h3a2 2 0 01-2 4" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14v3m-3 3h6m-3-3v3" />
            @break

        @case('clipboard-document-list')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 4h6a1 1 0 011 1v1H8V5a1 1 0 011-1z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h12a1 1 0 011 1v13a1 1 0 01-1 1H6a1 1 0 01-1-1V7a1 1 0 011-1z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 12h7M8.5 15h7M8.5 9h3" />
            @break

        @case('magnifying-glass')
            <circle cx="10.5" cy="10.5" r="6.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 20l-4.8-4.8" />
            @break

        @case('heart')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 20s-7-4.35-9.5-9A5.5 5.5 0 0112 5.5 5.5 5.5 0 0121.5 11c-2.5 4.65-9.5 9-9.5 9z" />
            @break

        @case('bookmark')
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 4h12a1 1 0 011 1v16l-7-4-7 4V5a1 1 0 011-1z" />
            @break

        @case('chat-bubble-left-right')
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 4h11a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2z" />
            @break

        @case('user-circle')
            <circle cx="12" cy="9" r="3.25" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M5.5 19a6.5 6.5 0 0113 0" />
            <circle cx="12" cy="12" r="9" />
            @break

        @case('lifebuoy')
            <circle cx="12" cy="12" r="9" />
            <circle cx="12" cy="12" r="3.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.5 6.5l3 3m5 5l3 3m0-11l-3 3m-5 5l-3 3" />
            @break

        @case('bars-3')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            @break

        @case('x-mark')
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            @break

        @case('chevron-down')
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            @break

        @case('bell')
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9a6 6 0 1112 0c0 4 1.5 5.5 2 6H4c.5-.5 2-2 2-6z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19a2 2 0 004 0" />
            @break

        @case('logout')
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 21H6a2 2 0 01-2-2V5a2 2 0 012-2h3" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 16l4-4-4-4M19 12H9" />
            @break

        @case('plus')
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            @break

        @case('building-storefront')
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 10v9a1 1 0 001 1h14a1 1 0 001-1v-9" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l1.5-3h15L21 6M3 6l.5 2a2 2 0 004 .3A2 2 0 0011.5 10a2 2 0 004-.3A2 2 0 0020.5 8L21 6M3 6h18" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 20v-5h4v5" />
            @break

        @case('clock')
            <circle cx="12" cy="12" r="9" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3.5 2" />
            @break

        @default
            <circle cx="12" cy="12" r="9" />
    @endswitch
</svg>

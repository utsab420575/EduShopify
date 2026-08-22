@props(['role', 'title' => null])

@php
    $accent = match ($role) {
        'supplier' => 'teal',
        'admin'    => 'violet',
        default    => 'indigo',
    };

    $user = auth()->user();
    $accountName = $user?->account?->display_name;
    $initials = $user ? collect(explode(' ', trim($user->name)))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('') : '?';
@endphp

<header class="sticky top-0 z-30 h-16 bg-white/95 backdrop-blur border-b border-slate-200 flex items-center gap-4 px-4 sm:px-6 lg:px-8 shrink-0">

    <!-- Mobile menu toggle -->
    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-slate-500 hover:text-slate-800 -ml-1 p-1">
        <x-dashboard.icon name="bars-3" class="w-6 h-6" />
    </button>

    <h1 class="text-base sm:text-lg font-bold text-slate-900 font-display truncate">
        {{ $title ?? 'Dashboard' }}
    </h1>

    <div class="flex-1"></div>

    <!-- Notifications (static placeholder) -->
    <button class="relative text-slate-400 hover:text-slate-600 p-1.5 rounded-full hover:bg-slate-50" title="Notifications" disabled>
        <x-dashboard.icon name="bell" class="w-5 h-5" />
    </button>

    <!-- User menu -->
    <div class="relative" x-data="{ open: false }">
        <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-2.5 py-1 pl-1 pr-2 rounded-full hover:bg-slate-50">
            <span class="w-8 h-8 rounded-full bg-{{ $accent }}-100 text-{{ $accent }}-700 flex items-center justify-center text-xs font-bold shrink-0">
                {{ $initials }}
            </span>
            <span class="hidden sm:flex flex-col items-start leading-tight">
                <span class="text-sm font-semibold text-slate-800">{{ $user?->name }}</span>
                @if($accountName)
                    <span class="text-[11px] text-slate-400">{{ $accountName }}</span>
                @endif
            </span>
            <x-dashboard.icon name="chevron-down" class="hidden sm:block w-4 h-4 text-slate-400" />
        </button>

        <div x-show="open" x-cloak x-transition
             class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 py-1.5 z-40">
            <div class="px-3.5 py-2 border-b border-slate-100 sm:hidden">
                <p class="text-sm font-semibold text-slate-800">{{ $user?->name }}</p>
                @if($accountName)
                    <p class="text-xs text-slate-400">{{ $accountName }}</p>
                @endif
            </div>
            @php($profileRoute = $role . '.profile')
            @if(\Illuminate\Support\Facades\Route::has($profileRoute))
                <a href="{{ route($profileRoute) }}" class="flex items-center gap-2 px-3.5 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                    <x-dashboard.icon name="user-circle" class="w-4 h-4" />
                    My Profile
                </a>
            @else
                <span class="flex items-center gap-2 px-3.5 py-2 text-sm text-slate-400 cursor-not-allowed">
                    <x-dashboard.icon name="user-circle" class="w-4 h-4" />
                    My Profile
                </span>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 px-3.5 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                    <x-dashboard.icon name="logout" class="w-4 h-4" />
                    Log out
                </button>
            </form>
        </div>
    </div>
</header>

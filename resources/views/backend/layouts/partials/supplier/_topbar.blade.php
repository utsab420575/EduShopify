@php
    $unreadNotifications = $unreadNotifications ?? 0;
    $unreadMessages      = $unreadMessages ?? 0;
    // Offer the switch as soon as both capabilities are more than a bare,
    // never-submitted draft — while this Supplier profile is itself still
    // pending admin review, the Buyer side (which auto-activates the moment
    // its own wizard is submitted) is still a real profile worth switching to.
    $bothCapabilities    = $account->hasCapability('supplier')
        && $account->capabilityStatus('supplier') !== 'draft'
        && $account->hasCapability('buyer')
        && $account->capabilityStatus('buyer') !== 'draft';
@endphp

<header class="h-20 shrink-0 bg-white border-b flex items-center justify-between px-4 lg:px-6" style="border-color:var(--topbar-border)">
    <div class="flex items-center gap-3 min-w-0">
        <button @click="mobileSidebar = true" class="lg:hidden text-gray-500 p-2 -ml-2">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="hidden sm:block min-w-0">
            <p class="text-xs text-gray-400 truncate">
                <a href="{{ route('supplier.dashboard') }}" class="hover:text-gray-600">Supplier Dashboard</a>
                @hasSection('breadcrumb')
                    <i class="fa-solid fa-chevron-right text-[8px] mx-1.5 text-gray-300"></i>
                    <span class="text-gray-600">@yield('breadcrumb')</span>
                @endif
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2">
        {{-- Switch to Buyer if dual-capability --}}
        @if($bothCapabilities)
            <a href="{{ route('buyer.dashboard') }}" class="hidden sm:inline-flex items-center gap-1.5 text-xs font-medium px-3 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">
                <i class="fa-solid fa-right-left"></i> Switch to Buyer
            </a>
        @endif

        {{-- Notifications --}}
        <div class="relative">
            <button @click="notifOpen = !notifOpen; profileOpen=false" class="relative p-2.5 rounded-xl text-gray-500 hover:bg-gray-100">
                <i class="fa-regular fa-bell"></i>
                @if($unreadNotifications > 0)
                    <span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] font-semibold rounded-full min-w-4 h-4 px-1 flex items-center justify-center">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
                @endif
            </button>
            <div x-show="notifOpen" @click.outside="notifOpen=false" x-transition x-cloak
                 class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-white rounded-xl border border-gray-200 shadow-lg z-50 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <p class="text-sm font-semibold text-gray-900">Notifications</p>
                    <a href="{{ route('supplier.notifications.index') }}" class="text-xs font-medium" style="color:var(--theme-primary)">View all</a>
                </div>
                <div class="max-h-72 overflow-y-auto divide-y divide-gray-100">
                    @forelse(($topbarNotifications ?? []) as $notification)
                        <a href="{{ route('supplier.notifications.index') }}" class="flex gap-3 px-4 py-3 hover:bg-gray-50">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" style="background:var(--theme-primary-soft)">
                                <i class="fa-solid fa-bell text-xs" style="color:var(--theme-primary)"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm text-gray-800 line-clamp-2">{{ $notification->data['message'] ?? 'Notification' }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                        </a>
                    @empty
                        <p class="px-4 py-6 text-center text-sm text-gray-400">You're all caught up.</p>
                    @endforelse
                </div>
                <a href="{{ route('supplier.notifications.index') }}" class="block text-center text-xs font-medium py-2.5 border-t border-gray-100" style="color:var(--theme-primary)">View all notifications</a>
            </div>
        </div>

        {{-- Profile dropdown --}}
        <div class="relative">
            <button @click="profileOpen = !profileOpen; notifOpen=false" class="flex items-center gap-2 pl-2 pr-1 py-1 rounded-xl hover:bg-gray-100">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=4f46e5&color=fff" class="w-8 h-8 rounded-full" alt="">
                <span class="hidden sm:block text-left leading-tight">
                    <span class="block text-xs font-semibold text-gray-900">{{ $user->name }}</span>
                    <span class="block text-[10px] text-gray-400">{{ $account->display_name }}</span>
                </span>
                <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 hidden sm:block"></i>
            </button>
            <div x-show="profileOpen" @click.outside="profileOpen=false" x-transition x-cloak
                 class="absolute right-0 mt-2 w-48 max-w-[calc(100vw-2rem)] bg-white rounded-xl border border-gray-200 shadow-lg z-50 py-1">
                <a href="{{ route('supplier.company.profile') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fa-regular fa-building w-4 text-gray-400"></i>Company Profile</a>
                <a href="{{ route('supplier.settings.security') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fa-solid fa-gear w-4 text-gray-400"></i>Settings</a>
                <div class="border-t border-gray-100 my-1"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50"><i class="fa-solid fa-arrow-right-from-bracket w-4"></i>Logout</button>
                </form>
            </div>
        </div>
    </div>
</header>

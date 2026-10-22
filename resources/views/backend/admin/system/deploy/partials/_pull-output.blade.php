@if(session('pullOutput') || session('pullError'))
    <div class="bg-gray-900 rounded-xl overflow-hidden mb-6">
        <div class="flex items-center justify-between px-4 py-2.5 border-b border-gray-800">
            <p class="text-xs font-semibold text-gray-300 flex items-center gap-2">
                <i class="fa-solid fa-terminal"></i> Last Pull Output
            </p>
            @if(session('success'))
                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-green-500/15 text-green-400">Success</span>
            @else
                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-red-500/15 text-red-400">Failed</span>
            @endif
        </div>
        <div class="p-4 max-h-72 overflow-auto">
            @if(session('pullOutput'))
                <pre class="text-xs font-mono text-green-400 whitespace-pre-wrap">{{ session('pullOutput') }}</pre>
            @endif
            @if(session('pullError'))
                {{-- git writes progress to stderr too, so this isn't necessarily an error on success. --}}
                <pre class="text-xs font-mono whitespace-pre-wrap {{ session('success') ? 'text-gray-400' : 'text-red-400' }} {{ session('pullOutput') ? 'mt-2' : '' }}">{{ session('pullError') }}</pre>
            @endif
        </div>
    </div>
@endif

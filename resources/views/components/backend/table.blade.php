@props(['head' => null])

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    @isset($toolbar)
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-4 border-b border-gray-100">
            {{ $toolbar }}
        </div>
    @endisset

    @if($slot->isEmpty() || trim($slot->toHtml()) === '')
        {{ $empty ?? '' }}
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                @isset($head)
                    <thead class="bg-gray-50">
                        {{ $head }}
                    </thead>
                @endisset
                <tbody class="divide-y divide-gray-100">
                    {{ $slot }}
                </tbody>
            </table>
        </div>
    @endif

    @isset($pagination)
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $pagination }}
        </div>
    @endisset
</div>

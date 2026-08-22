@if(session('success'))
    <div class="flex items-start gap-3 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-6" role="alert">
        <i class="fa-solid fa-circle-check mt-0.5"></i>
        <p class="text-sm">{{ session('success') }}</p>
    </div>
@endif

@if(session('error'))
    <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-6" role="alert">
        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
        <p class="text-sm">{{ session('error') }}</p>
    </div>
@endif

@if(session('warning'))
    <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 mb-6" role="alert">
        <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
        <p class="text-sm">{{ session('warning') }}</p>
    </div>
@endif

@if($errors->any())
    <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-6" role="alert">
        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
        <div class="text-sm">
            <p class="font-medium">Please fix the following:</p>
            <ul class="list-disc list-inside mt-1 space-y-0.5">
                @foreach($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

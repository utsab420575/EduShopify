@extends('backend.layouts.supplier')

@section('title', 'Gallery & Media')
@section('breadcrumb', 'Business Profile / Gallery & Media')

@section('body')

    <x-backend.page-header title="Gallery & Media" subtitle="Showcase your factory, warehouse, team, products, and showcase videos." />

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        {{-- Upload form --}}
        <div class="xl:col-span-4 space-y-6">
            <x-backend.form-card title="Add Media Item">
                <form method="POST" action="{{ route('supplier.company.gallery.store') }}" enctype="multipart/form-data"
                      x-data="{ mediaType: 'image' }" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Media Type</label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center gap-1.5 text-sm cursor-pointer">
                                <input type="radio" name="type" value="image" x-model="mediaType" checked style="accent-color:var(--theme-primary)">
                                Photo
                            </label>
                            <label class="inline-flex items-center gap-1.5 text-sm cursor-pointer">
                                <input type="radio" name="type" value="video" x-model="mediaType" style="accent-color:var(--theme-primary)">
                                Video Link
                            </label>
                        </div>
                    </div>

                    <template x-if="mediaType === 'image'">
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Image File <span class="text-red-500">*</span></label>
                                <input type="file" name="image" accept="image/*" class="w-full text-xs text-gray-500 border border-gray-200 rounded-lg p-2 bg-gray-50">
                                <p class="text-[11px] text-gray-400 mt-1">JPG, PNG or WEBP up to 5MB.</p>
                            </div>
                            <x-backend.input name="caption" label="Caption (optional)" placeholder="e.g. Main production line" />
                            <x-backend.input name="alt_text" label="Alt Text (optional)" />
                        </div>
                    </template>

                    <template x-if="mediaType === 'video'">
                        <div class="space-y-3">
                            <x-backend.input name="title" label="Video Title" required placeholder="e.g. Factory Tour" />
                            <x-backend.input name="video_url" label="Video URL" required placeholder="https://www.youtube.com/watch?v=..." />
                            <x-backend.input name="caption" label="Caption (optional)" />
                        </div>
                    </template>

                    <button type="submit" class="btn-primary w-full text-sm font-medium py-2.5 rounded-lg flex items-center justify-center gap-2 mt-4">
                        <i class="fa-solid fa-plus"></i> Add to Gallery
                    </button>
                </form>
            </x-backend.form-card>
        </div>

        {{-- Gallery Grid --}}
        <div class="xl:col-span-8 space-y-6">
            <x-backend.form-card title="Photos">
                @if($gallery->isEmpty())
                    <x-backend.empty-state icon="fa-images" title="No photos added yet" description="Upload high quality photos of your facilities and products." />
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        @foreach($gallery as $item)
                            <div class="relative group rounded-xl overflow-hidden border border-gray-200 bg-gray-50">
                                <img src="{{ asset('storage/'.$item->image_path) }}" class="w-full h-36 object-cover" alt="{{ $item->alt_text }}">
                                @if($item->caption)
                                    <p class="text-xs text-gray-700 px-2 py-1.5 truncate">{{ $item->caption }}</p>
                                @endif
                                <div class="absolute top-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <form method="POST" action="{{ route('supplier.company.gallery.destroy', $item) }}" onsubmit="return confirm('Delete this image?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-7 h-7 bg-red-600 text-white rounded-full flex items-center justify-center text-xs shadow hover:bg-red-700">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-backend.form-card>

            {{-- Videos list --}}
            @if($videos->isNotEmpty())
                <x-backend.form-card title="Videos">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($videos as $video)
                            <div class="p-3 border border-gray-200 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-lg bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-play"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $video->title }}</p>
                                        <a href="{{ $video->video_url }}" target="_blank" class="text-xs text-indigo-600 hover:underline truncate block">{{ $video->video_url }}</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-backend.form-card>
            @endif
        </div>

    </div>

@endsection

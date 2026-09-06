@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css">
@endpush

<div x-data="{ categoryId: '{{ old('category_id', $post->category_id) }}' }">

    <x-backend.form-card title="Post Details">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <x-backend.input name="title" label="Title" required :value="old('title', $post->title)" placeholder="e.g. 5 Tips for Streamlining School Procurement" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                <div class="flex items-center gap-2">
                    <select name="category_id" x-model="categoryId" id="category-select" class="focus-accent flex-1 text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                        <option value="">Uncategorized</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" @click="$dispatch('open-modal-quick-add-category')" title="Add new category"
                            class="w-10 h-10 shrink-0 rounded-lg border border-gray-300 text-gray-500 hover:bg-gray-50 flex items-center justify-center">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
                @error('category_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                <select name="status" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                    <option value="draft" @selected(old('status', $post->status ?: 'draft') === 'draft')>Draft</option>
                    <option value="approved" @selected(old('status', $post->status) === 'approved')>Published</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <x-backend.textarea name="excerpt" label="Excerpt" :value="old('excerpt', $post->excerpt)" placeholder="A short summary shown on listing cards (optional)" />
            </div>

            <div class="sm:col-span-2">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="featured" value="1" @checked(old('featured', $post->featured)) style="accent-color:var(--theme-primary)">
                    Feature this post
                </label>
            </div>
        </div>
    </x-backend.form-card>

    <x-backend.form-card title="Cover Image">
        <div class="flex items-center gap-4">
            <img id="cover-preview"
                 src="{{ $post->cover_image ? (str_starts_with($post->cover_image, 'http') || str_starts_with($post->cover_image, '/') ? $post->cover_image : \Illuminate\Support\Facades\Storage::url($post->cover_image)) : '' }}"
                 class="w-28 h-20 rounded-lg object-cover border border-gray-200 bg-gray-50" style="{{ $post->cover_image ? '' : 'display:none' }}" alt="">
            <div class="flex-1">
                <input type="file" name="cover_image" accept="image/*"
                       onchange="if(this.files[0]){var p=document.getElementById('cover-preview');p.src=URL.createObjectURL(this.files[0]);p.style.display='block';}"
                       class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="text-xs text-gray-400 mt-1">JPG, PNG or WEBP, up to 4MB. Shown on listing cards and at the top of the post.</p>
                @error('cover_image') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </x-backend.form-card>

    <x-backend.form-card title="Content">
        <textarea name="content" id="blog-content-editor">{{ old('content', $post->content) }}</textarea>
        @error('content') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
    </x-backend.form-card>

    <x-backend.form-card title="Tags">
        <x-backend.input name="tags" label="Tags" :value="old('tags', $tagNames)" placeholder="e.g. Procurement, EdTech, STEM (comma separated)" />
        <p class="text-xs text-gray-400 mt-1.5">Separate multiple tags with a comma. New tags are created automatically.</p>
    </x-backend.form-card>

    <div x-data="{ open: false }">
        <button type="button" @click="open = !open" class="flex items-center gap-2 text-sm font-medium mb-3" style="color:var(--theme-primary)">
            <i class="fa-solid fa-chevron-right text-xs transition-transform" :class="open && 'rotate-90'"></i> SEO (optional)
        </button>
        <div x-show="open" x-transition x-cloak>
            <x-backend.form-card title="Search Engine Optimization">
                <x-backend.input name="meta_title" label="Meta Title" :value="old('meta_title', $post->meta_title)" />
                <div class="mt-4">
                    <x-backend.textarea name="meta_description" label="Meta Description" :value="old('meta_description', $post->meta_description)" />
                </div>
                <div class="mt-4">
                    <x-backend.input name="meta_keywords" label="Meta Keywords" :value="old('meta_keywords', $post->meta_keywords)" placeholder="Comma separated keywords" />
                </div>
            </x-backend.form-card>
        </div>
    </div>
</div>

<x-backend.modal id="quick-add-category" title="New Blog Category" width="max-w-sm">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Category Name</label>
        <input type="text" id="quick-add-category-name" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5" placeholder="e.g. Sustainability">
        <p id="quick-add-category-error" class="text-xs text-red-600 mt-1 hidden"></p>
    </div>
    <div class="flex justify-end gap-2 mt-4">
        <button type="button" @click="open = false" class="text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
        <button type="button" onclick="quickAddBlogCategory()" class="btn-primary text-sm font-medium px-4 py-2 rounded-lg">Add Category</button>
    </div>
</x-backend.modal>

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
    <script>
        $(function () {
            $('#blog-content-editor').summernote({
                height: 420,
                placeholder: 'Write the blog post content here...',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']],
                ],
                callbacks: {
                    onImageUpload: function (files) {
                        for (let i = 0; i < files.length; i++) {
                            uploadBlogContentImage(files[i]);
                        }
                    },
                },
            });
        });

        function uploadBlogContentImage(file) {
            const data = new FormData();
            data.append('file', file);
            data.append('_token', '{{ csrf_token() }}');

            fetch('{{ route('admin.blog.posts.upload-image') }}', {
                method: 'POST',
                body: data,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            })
                .then(async (r) => {
                    let body = null;
                    try { body = await r.json(); } catch (e) { /* non-JSON response, e.g. an HTML error page */ }

                    if (!r.ok) {
                        console.error('Blog image upload failed', r.status, body);

                        if (r.status === 419) {
                            throw new Error('Your session has expired. Please refresh the page and try again.');
                        }
                        if (r.status === 422 && body?.errors?.file) {
                            throw new Error(body.errors.file[0]);
                        }
                        throw new Error(body?.message || ('Upload failed (HTTP ' + r.status + ').'));
                    }

                    return body;
                })
                .then((res) => $('#blog-content-editor').summernote('insertImage', res.url))
                .catch((e) => Swal.fire('Image upload failed', e.message, 'error'));
        }

        function quickAddBlogCategory() {
            const input = document.getElementById('quick-add-category-name');
            const errorEl = document.getElementById('quick-add-category-error');
            errorEl.classList.add('hidden');

            fetch('{{ route('admin.blog.categories.quick-add') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ name: input.value }),
            })
                .then(async (r) => {
                    const body = await r.json();
                    if (!r.ok) throw new Error(body.errors?.name?.[0] || 'Could not add category.');
                    return body;
                })
                .then((category) => {
                    const select = document.getElementById('category-select');
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    option.selected = true;
                    select.appendChild(option);
                    select.dispatchEvent(new Event('change'));
                    input.value = '';
                    window.dispatchEvent(new CustomEvent('close-modal-quick-add-category'));
                })
                .catch((e) => {
                    errorEl.textContent = e.message;
                    errorEl.classList.remove('hidden');
                });
        }
    </script>
@endpush

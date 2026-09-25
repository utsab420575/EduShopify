@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css">
@endpush

<div class="space-y-6">

    {{-- Status Alerts --}}
    @if($post->status === 'rejected' && $post->rejection_reason)
        <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-900 leading-relaxed shadow-xs">
            <div class="flex items-center gap-2 font-semibold text-red-800 mb-1">
                <i class="fa-solid fa-circle-exclamation text-red-600"></i>
                Post Needs Revision
            </div>
            <p class="text-xs text-red-700 whitespace-pre-line mb-2">
                <strong>Administrator Feedback:</strong> {{ $post->rejection_reason }}
            </p>
            <p class="text-xs text-red-600">
                Please make the required changes below and click <strong>Submit for Approval</strong> to send it back to the admin team for review.
            </p>
        </div>
    @elseif($post->status === 'pending')
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-900 leading-relaxed shadow-xs flex items-start gap-3">
            <i class="fa-solid fa-clock text-amber-500 mt-0.5"></i>
            <div>
                <p class="font-semibold text-amber-800">In Review</p>
                <p class="text-xs text-amber-700 mt-0.5">
                    This post has been submitted for administrator approval. You may continue to update it; re-submitting will update your pending submission.
                </p>
            </div>
        </div>
    @elseif($post->status === 'approved')
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-900 leading-relaxed shadow-xs flex items-start gap-3">
            <i class="fa-solid fa-circle-check text-emerald-600 mt-0.5"></i>
            <div>
                <p class="font-semibold text-emerald-800">Published on Platform</p>
                <p class="text-xs text-emerald-700 mt-0.5">
                    This article is live on EduShopify. Submitting new edits will send it for re-approval to ensure content quality.
                </p>
            </div>
        </div>
    @endif

    {{-- Post Details --}}
    <x-backend.form-card title="Post Details">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <x-backend.input name="title" label="Title" required :value="old('title', $post->title)" placeholder="e.g. 5 Practical Ways Educational Institutions Optimize Their Supply Chain" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                <select name="category_id" id="category-select" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white">
                    <option value="">Select a Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $post->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Submission Action</label>
                <select name="status" class="focus-accent w-full text-sm rounded-lg border border-gray-300 px-3 py-2.5 bg-white font-medium">
                    <option value="draft" @selected(old('status', $post->status ?: 'draft') === 'draft')>Save as Draft (Private)</option>
                    <option value="pending" @selected(old('status', $post->status) === 'pending' || old('status') === 'pending')>Submit for Approval (Send to Admin)</option>
                </select>
                <p class="text-[11px] text-gray-400 mt-1">Select "Submit for Approval" when ready for platform admins to review and publish your post.</p>
                @error('status') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <x-backend.textarea name="excerpt" label="Summary / Excerpt" :value="old('excerpt', $post->excerpt)" placeholder="A concise 1-2 sentence overview shown on blog listings and search results (optional)" />
            </div>
        </div>
    </x-backend.form-card>

    {{-- Cover Image --}}
    <x-backend.form-card title="Cover Image">
        <div class="flex items-center gap-4">
            <img id="cover-preview"
                 src="{{ $post->cover_image ? $post->coverImageUrl() : '' }}"
                 class="w-32 h-20 rounded-lg object-cover border border-gray-200 bg-gray-50" style="{{ $post->cover_image ? '' : 'display:none' }}" alt="">
            <div class="flex-1">
                <input type="file" name="cover_image" accept="image/*" onchange="previewCoverImage(this)"
                       class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                <p class="text-xs text-gray-400 mt-1">High-quality JPG, PNG or WEBP, up to 10MB. Displayed at the top of the article and on article cards.</p>
                @error('cover_image') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </x-backend.form-card>

    {{-- Content --}}
    <x-backend.form-card title="Article Content">
        <textarea name="content" id="blog-content-editor">{{ old('content', $post->content) }}</textarea>
        <input type="file" id="blog-image-file-input" accept="image/*" multiple class="hidden">
        @error('content') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
    </x-backend.form-card>

    {{-- Tags --}}
    <x-backend.form-card title="Tags &amp; Keywords">
        <x-backend.input name="tags" label="Tags" :value="old('tags', $tagNames)" placeholder="e.g. EdTech, Procurement, Laboratory Equipment, Smart Classrooms" />
        <p class="text-xs text-gray-400 mt-1.5">Separate tags with commas. Tags help institutional buyers find your articles.</p>
    </x-backend.form-card>

    {{-- SEO (Optional) --}}
    <div x-data="{ open: false }">
        <button type="button" @click="open = !open" class="flex items-center gap-2 text-sm font-medium mb-3 text-emerald-600 hover:text-emerald-700">
            <i class="fa-solid fa-chevron-right text-xs transition-transform" :class="open && 'rotate-90'"></i> SEO Optimization (optional)
        </button>
        <div x-show="open" x-transition x-cloak>
            <x-backend.form-card title="Search Engine Optimization (SEO)">
                <x-backend.input name="meta_title" label="Meta Title" :value="old('meta_title', $post->meta_title)" placeholder="Custom title for Google search results" />
                <div class="mt-4">
                    <x-backend.textarea name="meta_description" label="Meta Description" :value="old('meta_description', $post->meta_description)" placeholder="Brief description for search engines" />
                </div>
                <div class="mt-4">
                    <x-backend.input name="meta_keywords" label="Meta Keywords" :value="old('meta_keywords', $post->meta_keywords)" placeholder="Comma separated keywords" />
                </div>
            </x-backend.form-card>
        </div>
    </div>

</div>

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
    <script>
        $(function () {
            // Custom summernote image button to avoid dialog freezing issues
            $.extend($.summernote.plugins, {
                blogImageButton: function (context) {
                    var ui = $.summernote.ui;
                    context.memo('button.blogImage', function () {
                        var button = ui.button({
                            contents: '<i class="note-icon-picture"></i>',
                            tooltip: 'Insert Image',
                            click: function () {
                                document.getElementById('blog-image-file-input').click();
                            },
                        });
                        return button.render();
                    });
                },
            });

            $('#blog-content-editor').summernote({
                height: 400,
                placeholder: 'Write your post content here with rich formatting, headings, bullet lists, and illustrations...',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'blogImage', 'video']],
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

            document.getElementById('blog-image-file-input').addEventListener('change', function () {
                Array.from(this.files || []).forEach(uploadBlogContentImage);
                this.value = '';
            });
        });

        function previewCoverImage(input) {
            const file = input.files && input.files[0];
            if (!file) return;

            try {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const preview = document.getElementById('cover-preview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } catch (e) {
                console.warn('Cover preview failed', e);
            }
        }

        function uploadBlogContentImage(file) {
            const data = new FormData();
            data.append('file', file);
            data.append('_token', '{{ csrf_token() }}');

            fetch('{{ route('supplier.blog.posts.upload-image') }}', {
                method: 'POST',
                body: data,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            })
                .then(async (r) => {
                    let body = null;
                    try { body = await r.json(); } catch (e) {}

                    if (!r.ok) {
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
                .catch((e) => {
                    if (window.Swal) {
                        Swal.fire('Image upload failed', e.message, 'error');
                    } else {
                        alert('Image upload failed: ' + e.message);
                    }
                });
        }
    </script>
@endpush

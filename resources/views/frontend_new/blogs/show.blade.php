@extends('frontend_new.layouts.app')

@section('title', $post->meta_title ?? $post->title . ' – Edushopify Blog')

@section('body_class', 'bg-gray-50')

@section('content')
@php
  $authorName = $post->account?->display_name
      ?? $post->account?->supplierProfile?->display_name
      ?? $post->account?->buyerProfile?->display_name
      ?? 'EduShopify Member';
  $authorInitial = strtoupper(substr($authorName, 0, 1));
  $cover = $post->coverImageUrl();

  $currentAuthorName = auth()->user()
      ? ($currentAccount?->display_name 
         ?? $currentAccount?->supplierProfile?->display_name 
         ?? $currentAccount?->buyerProfile?->display_name 
         ?? auth()->user()->name)
      : null;
  $currentAuthorInitial = $currentAuthorName ? strtoupper(substr($currentAuthorName, 0, 1)) : '';
@endphp

{{--
  Tailwind is loaded here via the CDN Play script (no build step), which
  compiles utility classes by scanning the page's actual HTML at load
  time. The like buttons/icons below only ever render ONE of their two
  color variants server-side (Blade's ternary picks a branch before the
  page ever reaches the browser) — the "liked" rose classes are then
  applied later purely via JS once someone actually likes something, so
  the CDN script never saw them in its initial scan and renders them
  unstyled (default black) instead of rose. This hidden block's only job
  is to mention every class both like buttons ever switch to/from, so
  the CDN compiles all of them up front regardless of which branch was
  used for the actual visible buttons.
--}}
<div class="hidden post-like-trigger flex items-center gap-1.5 gap-2 px-3 px-5 py-1.5 py-2.5 rounded-full rounded-xl border font-semibold font-bold text-sm cursor-pointer transition-all shadow-sm
            bg-rose-50 border-rose-200 text-rose-600 bg-rose-500 text-white shadow-rose-200 hover:bg-rose-600
            bg-gray-50 border-gray-200 text-gray-600 bg-white text-gray-700 hover:border-rose-300 hover:text-rose-600 hover:bg-rose-50/50
            bg-white/20 bg-gray-100
            post-like-icon w-4 h-4 w-5 h-5 transition-transform text-rose-500 fill-rose-500 text-white fill-white text-gray-400 fill-none
            post-likes-count-label px-2 py-0.5 rounded-full text-xs
            bg-gray-50 text-gray-500"></div>

{{-- Breadcrumbs --}}
<div class="bg-white border-b border-gray-200">
  <div class="max-w-7xl mx-auto px-4 py-3 text-xs text-gray-500 flex items-center gap-2 overflow-x-auto whitespace-nowrap">
    <a href="{{ route('v2.home') }}" class="hover:text-emerald-600 transition-colors">Home</a>
    <span>/</span>
    <a href="{{ route('v2.blogs.index') }}" class="hover:text-emerald-600 transition-colors">Blog</a>
    @if($post->category)
      <span>/</span>
      <a href="{{ route('v2.blogs.index', ['category' => $post->category->slug]) }}" class="hover:text-emerald-600 transition-colors">
        {{ $post->category->name }}
      </a>
    @endif
    <span>/</span>
    <span class="text-gray-900 font-medium truncate max-w-xs">{{ $post->title }}</span>
  </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-10">
  <div class="flex flex-col lg:flex-row gap-10">

    {{-- Main Article Content Column --}}
    <main class="w-full lg:w-2/3 xl:w-3/4">
      <article class="bg-white rounded-3xl border border-gray-200/90 shadow-sm overflow-hidden p-6 sm:p-10">
        
        {{-- Header Meta --}}
        <div class="flex flex-wrap items-center gap-3 mb-4">
          @if($post->category)
            <a href="{{ route('v2.blogs.index', ['category' => $post->category->slug]) }}"
               class="bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-semibold px-3 py-1 rounded-full transition-colors">
              {{ $post->category->name }}
            </a>
          @endif

          <span class="text-xs text-gray-400">•</span>
          <span class="text-xs text-gray-500">{{ $post->published_at?->format('F d, Y') }}</span>
          <span class="text-xs text-gray-400">•</span>
          <span class="text-xs text-gray-500 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            {{ $post->reading_time_minutes ?? 5 }} min read
          </span>
        </div>

        {{-- Title --}}
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-gray-900 leading-tight mb-6">
          {{ $post->title }}
        </h1>

        {{-- Author Info Bar with Interactive Metrics --}}
        <div class="flex flex-wrap items-center justify-between gap-4 py-4 border-y border-gray-100 mb-8">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
              {{ $authorInitial }}
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">{{ $authorName }}</p>
              <p class="text-xs text-gray-400">Verified Marketplace Contributor</p>
            </div>
          </div>

          <div class="flex items-center gap-2 sm:gap-3 text-xs">
            {{-- Views Count --}}
            <span class="flex items-center gap-1.5 text-gray-500 bg-gray-50 px-3 py-1.5 rounded-full border border-gray-100" title="Views">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
              <span>{{ $post->views_count }}</span>
            </span>

            {{-- Comments Link --}}
            <a href="#comments-section" class="flex items-center gap-1.5 text-gray-500 bg-gray-50 hover:bg-emerald-50 hover:text-emerald-700 px-3 py-1.5 rounded-full border border-gray-100 transition-colors" title="Jump to Comments">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
              </svg>
              <span class="post-comments-count-label">{{ $post->comments_count }}</span>
            </a>

            {{-- Interactive Post Like Button --}}
            <button type="button"
                    onclick="handleTogglePostLike()"
                    id="top-like-btn"
                    class="post-like-trigger flex items-center gap-1.5 px-3 py-1.5 rounded-full border transition-all cursor-pointer font-semibold {{ $hasLikedPost ? 'bg-rose-50 border-rose-200 text-rose-600 shadow-sm' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-rose-50 hover:border-rose-200 hover:text-rose-600' }}"
                    title="{{ $hasLikedPost ? 'Unlike article' : 'Like article' }}">
              <svg class="post-like-icon w-4 h-4 transition-transform {{ $hasLikedPost ? 'text-rose-500' : 'text-gray-400' }}" fill="{{ $hasLikedPost ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
              </svg>
              <span class="post-likes-count-label">{{ $post->likes_count }}</span>
              <span class="hidden sm:inline">Likes</span>
            </button>
          </div>
        </div>

        {{-- Cover Image --}}
        <div class="rounded-2xl overflow-hidden mb-8 shadow-sm">
          <img src="{{ $cover }}" alt="{{ $post->title }}" class="w-full h-auto max-h-[460px] object-cover" />
        </div>

        {{-- Excerpt Highlight --}}
        @if($post->excerpt)
          <div class="p-5 bg-emerald-50/70 border-l-4 border-emerald-500 rounded-r-xl text-gray-700 text-base leading-relaxed italic mb-8">
            {{ $post->excerpt }}
          </div>
        @endif

        {{-- Body Content --}}
        <div class="prose prose-lg max-w-none text-gray-800 leading-relaxed space-y-5 text-[15px]">
          {!! $post->content !!}
        </div>

        {{-- Additional Images / Gallery --}}
        @if($post->images->isNotEmpty())
          <div class="mt-10 pt-8 border-t border-gray-100">
            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">Article Gallery</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              @foreach($post->images as $img)
                <div class="rounded-xl overflow-hidden border border-gray-100 bg-gray-50">
                  <img src="{{ $img->url() }}" alt="{{ $img->caption ?? 'Gallery Image' }}" class="w-full h-48 object-cover" />
                  @if($img->caption)
                    <p class="p-2.5 text-xs text-gray-500 italic bg-white">{{ $img->caption }}</p>
                  @endif
                </div>
              @endforeach
            </div>
          </div>
        @endif

        {{-- Engagement Banner (Like, Discuss, Share) --}}
        <div class="mt-10 p-5 sm:p-6 bg-gradient-to-r from-emerald-50/90 via-teal-50/50 to-gray-50 rounded-2xl border border-emerald-100 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div class="flex items-center gap-3 flex-wrap">
            {{-- Big Like Button --}}
            <button type="button"
                    onclick="handleTogglePostLike()"
                    id="banner-like-btn"
                    class="post-like-trigger flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition-all shadow-sm cursor-pointer {{ $hasLikedPost ? 'bg-rose-500 text-white shadow-rose-200 hover:bg-rose-600' : 'bg-white text-gray-700 border border-gray-200 hover:border-rose-300 hover:text-rose-600 hover:bg-rose-50/50' }}">
              <svg class="post-like-icon w-5 h-5 transition-transform {{ $hasLikedPost ? 'text-white' : 'text-gray-400' }}" fill="{{ $hasLikedPost ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
              </svg>
              <span class="post-like-text">{{ $hasLikedPost ? 'Liked' : 'Like Article' }}</span>
              <span class="post-likes-count-label {{ $hasLikedPost ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }} px-2 py-0.5 rounded-full text-xs font-bold">{{ $post->likes_count }}</span>
            </button>

            {{-- Jump to Comments Button --}}
            <a href="#comments-section"
               class="flex items-center gap-2 px-4 py-2.5 bg-white text-gray-700 border border-gray-200 rounded-xl font-semibold text-sm hover:border-emerald-300 hover:text-emerald-700 hover:bg-emerald-50/50 transition-all shadow-sm">
              <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
              </svg>
              <span>Discussion</span>
              <span class="post-comments-count-label bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs font-bold">{{ $post->comments_count }}</span>
            </a>
          </div>

          {{-- Share Button --}}
          <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <button type="button"
                    onclick="copyArticleLink()"
                    id="copy-link-btn"
                    class="flex items-center gap-2 px-4 py-2.5 bg-white text-gray-600 border border-gray-200 rounded-xl text-xs font-semibold hover:border-gray-300 hover:text-gray-900 transition-all shadow-sm cursor-pointer">
              <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
              </svg>
              <span id="copy-btn-text">Share Article</span>
            </button>
          </div>
        </div>

        {{-- Tags List --}}
        @if($post->tags->isNotEmpty())
          <div class="mt-8 pt-6 border-t border-gray-100 flex flex-wrap items-center gap-2">
            <span class="text-xs font-semibold text-gray-400 mr-2">TAGS:</span>
            @foreach($post->tags as $tag)
              <a href="{{ route('v2.blogs.index', ['tag' => $tag->slug]) }}"
                 class="px-3 py-1 bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 text-xs font-medium text-gray-600 rounded-full transition-colors">
                #{{ $tag->name }}
              </a>
            @endforeach
          </div>
        @endif

      </article>

      {{-- Comments & Discussion Section --}}
      <section id="comments-section" class="mt-10 bg-white rounded-3xl border border-gray-200/90 shadow-sm p-6 sm:p-10 scroll-mt-6">
        
        {{-- Section Header --}}
        <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-100">
          <div>
            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
              <span>Discussion & Comments</span>
              <span class="text-sm font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-100">
                <span class="post-comments-count-label">{{ $post->comments_count }}</span>
              </span>
            </h2>
            <p class="text-xs text-gray-500 mt-1">Join the dialogue with school administrators, buyers, and educational equipment suppliers.</p>
          </div>
        </div>

        {{-- Post a New Comment Box --}}
        @auth
          <div class="mb-10 p-5 sm:p-6 bg-gray-50/90 rounded-2xl border border-gray-200">
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-xs">
                  {{ $currentAuthorInitial }}
                </div>
                <div>
                  <span class="text-xs font-bold text-gray-900">{{ $currentAuthorName }}</span>
                  <span class="text-[11px] text-gray-400 block sm:inline sm:ml-1">• Verified Account</span>
                </div>
              </div>
              <span class="text-[11px] text-gray-400 hidden sm:inline">Be constructive and courteous</span>
            </div>

            <form id="main-comment-form" onsubmit="handleMainCommentSubmit(event)" class="space-y-3">
              @csrf
              <div class="relative">
                <textarea id="main-comment-content"
                          rows="3"
                          required
                          minlength="3"
                          maxlength="2000"
                          oninput="updateCharCounter(this, 'main-comment-char-count')"
                          placeholder="What are your thoughts on this article? Share your questions, experience, or procurement tips..."
                          class="w-full text-sm text-gray-800 bg-white border border-gray-200 rounded-xl p-3.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all placeholder:text-gray-400 resize-y"></textarea>
              </div>
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                <span class="text-[11px] text-gray-400" id="main-comment-char-count">2000 characters remaining</span>
                <button type="submit"
                        id="main-comment-submit-btn"
                        class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm hover:shadow transition-all flex items-center justify-center gap-2 cursor-pointer self-end sm:self-auto">
                  <span>Post Comment</span>
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                  </svg>
                </button>
              </div>
            </form>
          </div>
        @else
          {{-- Guest Sign In Call To Action --}}
          <div class="mb-10 p-6 bg-gradient-to-br from-emerald-50/60 via-gray-50 to-blue-50/40 rounded-2xl border border-emerald-100/90 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3.5 text-left">
              <div class="w-11 h-11 rounded-2xl bg-white border border-emerald-200 text-emerald-600 flex items-center justify-center shadow-xs shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
              </div>
              <div>
                <h4 class="text-sm font-bold text-gray-900">Join the discussion</h4>
                <p class="text-xs text-gray-500 mt-0.5">Sign in to leave a comment, reply to members, and like insights.</p>
              </div>
            </div>
            <div class="flex items-center gap-2.5 shrink-0 w-full sm:w-auto justify-end">
              <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all shadow-xs text-center">
                Sign In to Comment
              </a>
              <a href="{{ route('register', ['redirect' => url()->current()]) }}" class="px-4 py-2.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 text-xs font-bold rounded-xl transition-all text-center">
                Register
              </a>
            </div>
          </div>
        @endauth

        {{-- Comments List Container --}}
        <div id="comments-container" class="space-y-6">
          @forelse($post->approvedComments as $comment)
            @php
              $commenterName = $comment->account?->display_name
                  ?? $comment->account?->supplierProfile?->display_name
                  ?? $comment->account?->buyerProfile?->display_name
                  ?? $comment->authorUser?->name
                  ?? 'Community Member';
              $isCommentLiked = in_array($comment->id, $likedCommentIds);
            @endphp

            <div id="comment-card-{{ $comment->id }}" class="p-5 rounded-2xl bg-gray-50/80 border border-gray-100 hover:border-gray-200 transition-all">
              {{-- Comment Header --}}
              <div class="flex items-start justify-between gap-3 mb-2">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-xs">
                    {{ strtoupper(substr($commenterName, 0, 1)) }}
                  </div>
                  <div>
                    <div class="flex items-center gap-1.5 flex-wrap">
                      <p class="text-xs font-bold text-gray-900">{{ $commenterName }}</p>
                      @if($comment->account_id === $post->account_id)
                        <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800">Author</span>
                      @endif
                    </div>
                    <p class="text-[10px] text-gray-400">{{ $comment->created_at->diffForHumans() }}</p>
                  </div>
                </div>

                {{-- Comment Like Button --}}
                <button type="button"
                        onclick="handleToggleCommentLike({{ $comment->id }}, this)"
                        data-comment-id="{{ $comment->id }}"
                        class="comment-like-btn flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-lg border transition-all cursor-pointer {{ $isCommentLiked ? 'bg-rose-50 border-rose-200 text-rose-600' : 'bg-white border-gray-200 text-gray-600 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200' }}"
                        title="{{ $isCommentLiked ? 'Unlike comment' : 'Like comment' }}">
                  <svg class="w-3.5 h-3.5 {{ $isCommentLiked ? 'text-rose-500' : 'text-gray-400' }}" fill="{{ $isCommentLiked ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                  </svg>
                  <span class="comment-likes-count">{{ $comment->likes_count }}</span>
                </button>
              </div>

              {{-- Comment Text --}}
              <p class="text-sm text-gray-700 leading-relaxed pl-10 whitespace-pre-line">
                {{ $comment->content }}
              </p>

              {{-- Actions Bar --}}
              <div class="mt-3 pl-10 flex items-center gap-4">
                <button type="button"
                        onclick="toggleReplyBox({{ $comment->id }})"
                        class="text-xs font-semibold text-gray-500 hover:text-emerald-700 flex items-center gap-1 transition-colors cursor-pointer">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 10h10a5 5 0 0 1 5 5v2"/>
                    <path d="M7 6L3 10l4 4"/>
                  </svg>
                  <span>Reply</span>
                </button>
              </div>

              {{-- Inline Reply Box --}}
              <div id="reply-box-{{ $comment->id }}" class="hidden mt-4 pl-6 sm:pl-10">
                @auth
                  <form onsubmit="handleReplySubmit(event, {{ $comment->id }})" class="p-3.5 bg-white rounded-xl border border-gray-200 space-y-2.5 shadow-xs">
                    @csrf
                    <div class="flex items-center justify-between text-[11px] text-gray-500">
                      <span>Replying to <strong class="text-gray-800">{{ $commenterName }}</strong></span>
                      <button type="button" onclick="toggleReplyBox({{ $comment->id }})" class="text-gray-400 hover:text-gray-600 cursor-pointer">✕ Cancel</button>
                    </div>
                    <textarea id="reply-input-{{ $comment->id }}"
                              rows="2"
                              required
                              minlength="3"
                              maxlength="2000"
                              placeholder="Write a constructive reply..."
                              class="w-full text-xs text-gray-800 bg-gray-50 border border-gray-200 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 placeholder:text-gray-400 resize-y"></textarea>
                    <div class="flex justify-end gap-2">
                      <button type="button" onclick="toggleReplyBox({{ $comment->id }})" class="px-3 py-1 text-xs text-gray-500 hover:text-gray-700 rounded-lg cursor-pointer">Cancel</button>
                      <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                        <span>Send Reply</span>
                      </button>
                    </div>
                  </form>
                @else
                  <div class="p-3 bg-white rounded-xl border border-gray-200 text-xs text-gray-500 flex items-center justify-between">
                    <span>Please <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="text-emerald-600 font-bold hover:underline">sign in</a> to reply to this comment.</span>
                    <button type="button" onclick="toggleReplyBox({{ $comment->id }})" class="text-gray-400 hover:text-gray-600 cursor-pointer">✕</button>
                  </div>
                @endauth
              </div>

              {{-- Nested Replies Container --}}
              <div id="replies-list-{{ $comment->id }}" class="mt-4 pl-6 sm:pl-10 space-y-3 border-l-2 border-emerald-100 {{ $comment->approvedReplies->isEmpty() ? 'hidden' : '' }}">
                @foreach($comment->approvedReplies as $reply)
                  @php
                    $replyAuthor = $reply->account?->display_name
                        ?? $reply->account?->supplierProfile?->display_name
                        ?? $reply->account?->buyerProfile?->display_name
                        ?? $reply->authorUser?->name
                        ?? 'Community Member';
                    $isReplyLiked = in_array($reply->id, $likedCommentIds);
                  @endphp
                  <div id="reply-card-{{ $reply->id }}" class="p-3 bg-white rounded-xl border border-gray-200/80 hover:border-gray-300 transition-all">
                    <div class="flex items-start justify-between gap-2 mb-1.5">
                      <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 font-bold text-[10px] flex items-center justify-center shrink-0">
                          {{ strtoupper(substr($replyAuthor, 0, 1)) }}
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                          <span class="text-xs font-bold text-gray-900">{{ $replyAuthor }}</span>
                          @if($reply->account_id === $post->account_id)
                            <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800">Author</span>
                          @endif
                          <span class="text-[10px] text-gray-400">• {{ $reply->created_at->diffForHumans() }}</span>
                        </div>
                      </div>

                      {{-- Reply Like Button --}}
                      <button type="button"
                              onclick="handleToggleCommentLike({{ $reply->id }}, this)"
                              data-comment-id="{{ $reply->id }}"
                              class="comment-like-btn flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded border transition-all cursor-pointer {{ $isReplyLiked ? 'bg-rose-50 border-rose-200 text-rose-600' : 'bg-gray-50 border-gray-200 text-gray-500 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200' }}"
                              title="{{ $isReplyLiked ? 'Unlike reply' : 'Like reply' }}">
                        <svg class="w-3 h-3 {{ $isReplyLiked ? 'text-rose-500' : 'text-gray-400' }}" fill="{{ $isReplyLiked ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                        <span class="comment-likes-count">{{ $reply->likes_count }}</span>
                      </button>
                    </div>
                    <p class="text-xs text-gray-700 leading-relaxed pl-8 whitespace-pre-line">
                      {{ $reply->content }}
                    </p>
                  </div>
                @endforeach
              </div>

            </div>
          @empty
            <div id="no-comments-placeholder" class="text-center py-10 px-4 bg-gray-50/50 rounded-2xl border border-dashed border-gray-200">
              <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                  <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                </svg>
              </div>
              <h4 class="text-sm font-bold text-gray-800 mb-1">No comments yet</h4>
              <p class="text-xs text-gray-500 max-w-sm mx-auto">Be the first to share your thoughts, question the author, or exchange insights with fellow educators.</p>
            </div>
          @endforelse
        </div>
      </section>
    </main>

    {{-- Sidebar Column --}}
    <aside class="w-full lg:w-1/3 xl:w-1/4 flex flex-col gap-6">

      {{-- Author Card --}}
      <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Published By</h3>
        <div class="flex items-center gap-3 mb-3">
          <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 font-extrabold text-base flex items-center justify-center shrink-0">
            {{ $authorInitial }}
          </div>
          <div class="min-w-0">
            <h4 class="text-sm font-bold text-gray-900 truncate">{{ $authorName }}</h4>
            <p class="text-xs text-emerald-600 font-medium">Verified Partner</p>
          </div>
        </div>
        <p class="text-xs text-gray-500 leading-relaxed mb-4">
          Sharing educational equipment best practices, tender guidance, and product innovations on the EduShopify global marketplace.
        </p>
        <a href="{{ route('v2.blogs.index') }}" class="block text-center text-xs font-semibold text-emerald-600 hover:text-emerald-700 hover:underline">
          View All Partner Posts →
        </a>
      </div>

      {{-- Top Categories --}}
      <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Categories</h3>
        <ul class="space-y-2 text-sm">
          @foreach($allCategories as $cat)
            <li>
              <a href="{{ route('v2.blogs.index', ['category' => $cat->slug]) }}"
                 class="flex items-center justify-between py-1.5 px-2 rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors {{ $post->category_id == $cat->id ? 'font-bold text-emerald-700 bg-emerald-50/70' : '' }}">
                <span>{{ $cat->name }}</span>
                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-medium">{{ $cat->posts_count }}</span>
              </a>
            </li>
          @endforeach
        </ul>
      </div>

      {{-- Popular Tags --}}
      @if($popularTags->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
          <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Popular Tags</h3>
          <div class="flex flex-wrap gap-2">
            @foreach($popularTags as $tag)
              <a href="{{ route('v2.blogs.index', ['tag' => $tag->slug]) }}"
                 class="px-2.5 py-1 bg-gray-100 hover:bg-emerald-100 hover:text-emerald-800 text-xs text-gray-600 rounded-lg transition-colors font-medium">
                #{{ $tag->name }}
              </a>
            @endforeach
          </div>
        </div>
      @endif

      {{-- Related Articles Widget --}}
      @if($relatedPosts->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
          <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Related Articles</h3>
          <div class="space-y-4">
            @foreach($relatedPosts as $rel)
              @php
                $relCover = $rel->coverImageUrl();
              @endphp
              <a href="{{ route('v2.blogs.show', $rel->slug) }}" class="group flex gap-3 items-center">
                <img src="{{ $relCover }}" alt="{{ $rel->title }}" class="w-16 h-16 rounded-xl object-cover shrink-0 group-hover:scale-105 transition-transform" />
                <div class="min-w-0 flex-1">
                  <h5 class="text-xs font-bold text-gray-900 group-hover:text-emerald-600 transition-colors line-clamp-2 leading-snug">
                    {{ $rel->title }}
                  </h5>
                  <p class="text-[10px] text-gray-400 mt-1">{{ $rel->published_at?->format('M d, Y') }}</p>
                </div>
              </a>
            @endforeach
          </div>
        </div>
      @endif

    </aside>

  </div>
</div>

{{-- Dynamic Toast Notification Container --}}
<div id="toast-container" class="fixed bottom-6 right-6 z-50 flex flex-col gap-2 pointer-events-none"></div>

@endsection

@push('scripts')
<script>
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const POST_LIKE_URL = "{{ route('v2.blogs.like', $post->slug) }}";
  const POST_COMMENT_URL = "{{ route('v2.blogs.comment', $post->slug) }}";
  const COMMENT_LIKE_BASE_URL = "{{ url('v2/blog/comment') }}";
  const LOGIN_URL = "{{ route('login', ['redirect' => url()->current()]) }}";
  const IS_LOGGED_IN = {{ auth()->check() ? 'true' : 'false' }};

  // Toast notification helper
  function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    const isSuccess = type === 'success';
    toast.className = `px-4 py-3 rounded-xl shadow-lg text-xs font-semibold flex items-center gap-2 pointer-events-auto transition-all duration-300 transform translate-y-2 opacity-0 ${
      isSuccess ? 'bg-gray-900 text-white border border-gray-700' : 'bg-rose-600 text-white'
    }`;
    toast.innerHTML = `
      <span>${isSuccess ? '✓' : '⚠️'}</span>
      <span>${message}</span>
    `;

    container.appendChild(toast);

    // Animate in
    requestAnimationFrame(() => {
      toast.classList.remove('translate-y-2', 'opacity-0');
      toast.classList.add('translate-y-0', 'opacity-100');
    });

    // Auto dismiss
    setTimeout(() => {
      toast.classList.add('opacity-0', 'translate-y-2');
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  // Character counter helper
  function updateCharCounter(el, targetId) {
    const remaining = 2000 - el.value.length;
    const counter = document.getElementById(targetId);
    if (counter) {
      counter.textContent = `${remaining} characters remaining`;
    }
  }

  // Copy share URL
  function copyArticleLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
      const btnText = document.getElementById('copy-btn-text');
      if (btnText) btnText.textContent = 'Link Copied!';
      showToast('Article link copied to clipboard!');
      setTimeout(() => {
        if (btnText) btnText.textContent = 'Share Article';
      }, 2500);
    }).catch(() => {
      showToast('Could not copy link', 'error');
    });
  }

  // Toggle Post Like
  let isTogglingPostLike = false;
  async function handleTogglePostLike() {
    if (!IS_LOGGED_IN) {
      showToast('Please sign in to like this article.', 'error');
      setTimeout(() => { window.location.href = LOGIN_URL; }, 1000);
      return;
    }

    if (isTogglingPostLike) return;
    isTogglingPostLike = true;

    try {
      const response = await fetch(POST_LIKE_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN
        }
      });

      if (response.status === 401) {
        showToast('Please sign in to like this article.', 'error');
        window.location.href = LOGIN_URL;
        return;
      }

      const data = await response.json();
      if (data.success) {
        // Update all post like triggers on the page
        const isLiked = data.liked;
        const count = data.likes_count;

        // Top button
        const topBtn = document.getElementById('top-like-btn');
        if (topBtn) {
          if (isLiked) {
            topBtn.className = 'post-like-trigger flex items-center gap-1.5 px-3 py-1.5 rounded-full border transition-all cursor-pointer font-semibold bg-rose-50 border-rose-200 text-rose-600 shadow-sm';
            topBtn.title = 'Unlike article';
            const icon = topBtn.querySelector('svg');
            if (icon) { icon.setAttribute('class', 'post-like-icon w-4 h-4 transition-transform text-rose-500'); icon.setAttribute('fill', 'currentColor'); }
          } else {
            topBtn.className = 'post-like-trigger flex items-center gap-1.5 px-3 py-1.5 rounded-full border transition-all cursor-pointer font-semibold bg-gray-50 border-gray-200 text-gray-600 hover:bg-rose-50 hover:border-rose-200 hover:text-rose-600';
            topBtn.title = 'Like article';
            const icon = topBtn.querySelector('svg');
            if (icon) { icon.setAttribute('class', 'post-like-icon w-4 h-4 transition-transform text-gray-400'); icon.setAttribute('fill', 'none'); }
          }
        }

        // Banner button
        const bannerBtn = document.getElementById('banner-like-btn');
        if (bannerBtn) {
          const bannerText = bannerBtn.querySelector('.post-like-text');
          const bannerIcon = bannerBtn.querySelector('.post-like-icon');
          const bannerCountBadge = bannerBtn.querySelector('.post-likes-count-label');

          if (isLiked) {
            bannerBtn.className = 'post-like-trigger flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition-all shadow-sm cursor-pointer bg-rose-500 text-white shadow-rose-200 hover:bg-rose-600';
            if (bannerText) bannerText.textContent = 'Liked';
            if (bannerIcon) { bannerIcon.setAttribute('class', 'post-like-icon w-5 h-5 transition-transform text-white'); bannerIcon.setAttribute('fill', 'currentColor'); }
            if (bannerCountBadge) bannerCountBadge.className = 'post-likes-count-label bg-white/20 text-white px-2 py-0.5 rounded-full text-xs font-bold';
          } else {
            bannerBtn.className = 'post-like-trigger flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition-all shadow-sm cursor-pointer bg-white text-gray-700 border border-gray-200 hover:border-rose-300 hover:text-rose-600 hover:bg-rose-50/50';
            if (bannerText) bannerText.textContent = 'Like Article';
            if (bannerIcon) { bannerIcon.setAttribute('class', 'post-like-icon w-5 h-5 transition-transform text-gray-400'); bannerIcon.setAttribute('fill', 'none'); }
            if (bannerCountBadge) bannerCountBadge.className = 'post-likes-count-label bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full text-xs font-bold';
          }
        }

        // Update all post like count labels
        document.querySelectorAll('.post-likes-count-label').forEach(el => {
          el.textContent = count;
        });

        showToast(data.message || (isLiked ? 'Article liked!' : 'Like removed.'));
      } else {
        showToast(data.message || 'Something went wrong', 'error');
      }
    } catch (err) {
      console.error(err);
      showToast('Network error while toggling like.', 'error');
    } finally {
      isTogglingPostLike = false;
    }
  }

  // Toggle Comment Like
  async function handleToggleCommentLike(commentId, buttonEl) {
    if (!IS_LOGGED_IN) {
      showToast('Please sign in to like comments.', 'error');
      setTimeout(() => { window.location.href = LOGIN_URL; }, 1000);
      return;
    }

    buttonEl.disabled = true;

    try {
      const response = await fetch(`${COMMENT_LIKE_BASE_URL}/${commentId}/like`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN
        }
      });

      if (response.status === 401) {
        showToast('Please sign in to like this comment.', 'error');
        window.location.href = LOGIN_URL;
        return;
      }

      const data = await response.json();
      if (data.success) {
        const isLiked = data.liked;
        const count = data.likes_count;

        const countEl = buttonEl.querySelector('.comment-likes-count');
        if (countEl) countEl.textContent = count;

        const svg = buttonEl.querySelector('svg');
        if (isLiked) {
          buttonEl.classList.remove('bg-white', 'border-gray-200', 'text-gray-600', 'bg-gray-50', 'text-gray-500');
          buttonEl.classList.add('bg-rose-50', 'border-rose-200', 'text-rose-600');
          buttonEl.title = 'Unlike comment';
          if (svg) { svg.classList.remove('text-gray-400'); svg.classList.add('text-rose-500'); svg.setAttribute('fill', 'currentColor'); }
        } else {
          buttonEl.classList.remove('bg-rose-50', 'border-rose-200', 'text-rose-600');
          buttonEl.classList.add('bg-white', 'border-gray-200', 'text-gray-600');
          buttonEl.title = 'Like comment';
          if (svg) { svg.classList.remove('text-rose-500'); svg.classList.add('text-gray-400'); svg.setAttribute('fill', 'none'); }
        }
      } else {
        showToast(data.message || 'Error updating like', 'error');
      }
    } catch (err) {
      console.error(err);
      showToast('Network error while toggling comment like', 'error');
    } finally {
      buttonEl.disabled = false;
    }
  }

  // Toggle Reply Box visibility
  function toggleReplyBox(commentId) {
    const box = document.getElementById(`reply-box-${commentId}`);
    if (!box) return;
    const isHidden = box.classList.contains('hidden');
    if (isHidden) {
      box.classList.remove('hidden');
      const input = document.getElementById(`reply-input-${commentId}`);
      if (input) input.focus();
    } else {
      box.classList.add('hidden');
    }
  }

  // Submit Main Comment
  async function handleMainCommentSubmit(event) {
    event.preventDefault();
    if (!IS_LOGGED_IN) {
      showToast('Please sign in to post a comment.', 'error');
      window.location.href = LOGIN_URL;
      return;
    }

    const contentInput = document.getElementById('main-comment-content');
    const submitBtn = document.getElementById('main-comment-submit-btn');
    const content = contentInput.value.trim();

    if (content.length < 3) {
      showToast('Comment must be at least 3 characters.', 'error');
      return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = `
      <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <span>Posting...</span>
    `;

    try {
      const response = await fetch(POST_COMMENT_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN
        },
        body: JSON.stringify({ content: content })
      });

      if (response.status === 401) {
        showToast('Session expired. Please sign in.', 'error');
        window.location.href = LOGIN_URL;
        return;
      }

      const data = await response.json();
      if (data.success) {
        // Reset form
        contentInput.value = '';
        updateCharCounter(contentInput, 'main-comment-char-count');

        // Hide empty state if present
        const noComments = document.getElementById('no-comments-placeholder');
        if (noComments) noComments.remove();

        // Render new comment
        renderNewComment(data.comment);

        // Update comment counts everywhere
        document.querySelectorAll('.post-comments-count-label').forEach(el => {
          el.textContent = data.total_comments;
        });

        showToast('Comment posted successfully!');
      } else {
        showToast(data.message || 'Error posting comment.', 'error');
      }
    } catch (err) {
      console.error(err);
      showToast('Network error while posting comment.', 'error');
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = `
        <span>Post Comment</span>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
        </svg>
      `;
    }
  }

  // Submit Inline Reply
  async function handleReplySubmit(event, parentId) {
    event.preventDefault();
    if (!IS_LOGGED_IN) {
      showToast('Please sign in to reply.', 'error');
      window.location.href = LOGIN_URL;
      return;
    }

    const input = document.getElementById(`reply-input-${parentId}`);
    const content = input.value.trim();

    if (content.length < 3) {
      showToast('Reply must be at least 3 characters.', 'error');
      return;
    }

    const submitBtn = event.target.querySelector('button[type="submit"]');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Sending...';
    }

    try {
      const response = await fetch(POST_COMMENT_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN
        },
        body: JSON.stringify({
          content: content,
          parent_id: parentId
        })
      });

      if (response.status === 401) {
        showToast('Session expired. Please sign in.', 'error');
        window.location.href = LOGIN_URL;
        return;
      }

      const data = await response.json();
      if (data.success) {
        input.value = '';
        toggleReplyBox(parentId);

        // Render reply inside parent comment's replies container
        renderNewReply(parentId, data.comment);

        // Update comment counts everywhere
        document.querySelectorAll('.post-comments-count-label').forEach(el => {
          el.textContent = data.total_comments;
        });

        showToast('Reply posted successfully!');
      } else {
        showToast(data.message || 'Error posting reply.', 'error');
      }
    } catch (err) {
      console.error(err);
      showToast('Network error while posting reply.', 'error');
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send Reply';
      }
    }
  }

  // Helper: inject newly created comment into DOM
  function renderNewComment(c) {
    const container = document.getElementById('comments-container');
    if (!container) return;

    const div = document.createElement('div');
    div.id = `comment-card-${c.id}`;
    div.className = 'p-5 rounded-2xl bg-emerald-50/40 border border-emerald-200 transition-all';
    div.innerHTML = `
      <div class="flex items-start justify-between gap-3 mb-2">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-xs">
            ${c.author_initial}
          </div>
          <div>
            <div class="flex items-center gap-1.5 flex-wrap">
              <p class="text-xs font-bold text-gray-900">${escapeHtml(c.author)}</p>
              <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800">You</span>
            </div>
            <p class="text-[10px] text-gray-400">${c.date}</p>
          </div>
        </div>

        <button type="button"
                onclick="handleToggleCommentLike(${c.id}, this)"
                data-comment-id="${c.id}"
                class="comment-like-btn flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-lg border transition-all cursor-pointer bg-white border-gray-200 text-gray-600 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200"
                title="Like comment">
          <svg class="w-3.5 h-3.5 text-gray-400 fill-none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
          </svg>
          <span class="comment-likes-count">0</span>
        </button>
      </div>

      <p class="text-sm text-gray-700 leading-relaxed pl-10 whitespace-pre-line">${escapeHtml(c.content)}</p>

      <div class="mt-3 pl-10 flex items-center gap-4">
        <button type="button"
                onclick="toggleReplyBox(${c.id})"
                class="text-xs font-semibold text-gray-500 hover:text-emerald-700 flex items-center gap-1 transition-colors cursor-pointer">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M3 10h10a5 5 0 0 1 5 5v2"/>
            <path d="M7 6L3 10l4 4"/>
          </svg>
          <span>Reply</span>
        </button>
      </div>

      <div id="reply-box-${c.id}" class="hidden mt-4 pl-6 sm:pl-10">
        <form onsubmit="handleReplySubmit(event, ${c.id})" class="p-3.5 bg-white rounded-xl border border-gray-200 space-y-2.5 shadow-xs">
          <div class="flex items-center justify-between text-[11px] text-gray-500">
            <span>Replying to <strong class="text-gray-800">${escapeHtml(c.author)}</strong></span>
            <button type="button" onclick="toggleReplyBox(${c.id})" class="text-gray-400 hover:text-gray-600 cursor-pointer">✕ Cancel</button>
          </div>
          <textarea id="reply-input-${c.id}"
                    rows="2"
                    required
                    minlength="3"
                    maxlength="2000"
                    placeholder="Write a constructive reply..."
                    class="w-full text-xs text-gray-800 bg-gray-50 border border-gray-200 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 placeholder:text-gray-400 resize-y"></textarea>
          <div class="flex justify-end gap-2">
            <button type="button" onclick="toggleReplyBox(${c.id})" class="px-3 py-1 text-xs text-gray-500 hover:text-gray-700 rounded-lg cursor-pointer">Cancel</button>
            <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
              <span>Send Reply</span>
            </button>
          </div>
        </form>
      </div>

      <div id="replies-list-${c.id}" class="mt-4 pl-6 sm:pl-10 space-y-3 border-l-2 border-emerald-100 hidden"></div>
    `;

    container.insertBefore(div, container.firstChild);
  }

  // Helper: inject newly created reply into DOM
  function renderNewReply(parentId, r) {
    const list = document.getElementById(`replies-list-${parentId}`);
    if (!list) return;

    list.classList.remove('hidden');

    const card = document.createElement('div');
    card.id = `reply-card-${r.id}`;
    card.className = 'p-3 bg-emerald-50/30 rounded-xl border border-emerald-200/90 hover:border-emerald-300 transition-all';
    card.innerHTML = `
      <div class="flex items-start justify-between gap-2 mb-1.5">
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 font-bold text-[10px] flex items-center justify-center shrink-0">
            ${r.author_initial}
          </div>
          <div class="flex items-center gap-1.5 flex-wrap">
            <span class="text-xs font-bold text-gray-900">${escapeHtml(r.author)}</span>
            <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800">You</span>
            <span class="text-[10px] text-gray-400">• ${r.date}</span>
          </div>
        </div>

        <button type="button"
                onclick="handleToggleCommentLike(${r.id}, this)"
                data-comment-id="${r.id}"
                class="comment-like-btn flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded border transition-all cursor-pointer bg-gray-50 border-gray-200 text-gray-500 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200"
                title="Like reply">
          <svg class="w-3 h-3 text-gray-400 fill-none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
          </svg>
          <span class="comment-likes-count">0</span>
        </button>
      </div>
      <p class="text-xs text-gray-700 leading-relaxed pl-8 whitespace-pre-line">${escapeHtml(r.content)}</p>
    `;

    list.appendChild(card);
  }

  // Safe HTML escape helper
  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }
</script>
@endpush

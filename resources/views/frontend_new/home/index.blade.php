@extends('frontend_new.layouts.app')

@section('title', 'Edushopify – Global Suppliers for Education')

@section('content')

    {{-- Hero Section — renders immediately, no skeleton; its own
         heading/subtext/buttons fade-up comes from .hero-fade-up inside
         the partial itself (plain CSS, no JS dependency). --}}
    @include('frontend_new.home.partial._hero')

    {{--
        Skeleton loading screen, then fade-up reveal, for everything below
        the hero. #page-skeleton shows immediately (no JS needed);
        #page-content starts hidden via .home-content-hidden.
        frontend_new.js removes that class once the page is ready and adds
        .is-visible to each .reveal-up section in a short stagger. If JS
        never runs, the <noscript> block below shows the real content and
        drops the skeleton so the page is never stuck.
    --}}
    <div id="page-skeleton" aria-hidden="true">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="skel-block" style="height:220px;"></div>
                <div class="skel-block" style="height:220px;"></div>
                <div class="skel-block" style="height:220px;"></div>
                <div class="skel-block" style="height:220px;"></div>
            </div>
        </div>
    </div>

    <div id="page-content" class="home-content-hidden">

        {{-- Featured Suppliers Section --}}
        <div class="reveal-up" data-reveal>
            @include('frontend_new.home.partial._featured_suppliers')
        </div>

        {{-- All Suppliers Section --}}
        <div class="reveal-up" data-reveal>
            @include('frontend_new.home.partial._all_suppliers')
        </div>

        {{-- Why Choose Section --}}
        <div class="reveal-up" data-reveal>
            @include('frontend_new.home.partial._why_choose')
        </div>

        {{-- Events Section --}}
        <div class="reveal-up" data-reveal>
            @include('frontend_new.home.partial._events')
        </div>

    </div>

    <noscript>
        <style>
            #page-skeleton { display: none !important; }
            #page-content.home-content-hidden { display: block !important; }
            .reveal-up { opacity: 1 !important; transform: none !important; }
        </style>
    </noscript>

@endsection

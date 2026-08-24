@extends('backend.layouts.admin')

@section('title', $capability->account?->display_name)
@section('breadcrumb', 'Users & Accounts / Capabilities / ' . $capability->account?->display_name)

@section('body')
    @include('backend.admin.capabilities._panel', ['capability' => $capability, 'documents' => $documents])
@endsection

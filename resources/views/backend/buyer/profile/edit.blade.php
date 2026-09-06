@extends('backend.layouts.buyer')

@section('title', 'My Profile')
@section('breadcrumb', 'Buyer / My Profile')

@section('body')
    @livewire('buyer.buyer-profile-manager')
@endsection

@extends('backend.layouts.supplier')

@section('title', 'Edit Draft — ' . $quotation->quotation_number)
@section('breadcrumb', 'Quotations / ' . $quotation->quotation_number . ' / Edit Draft')

@section('body')

    <x-backend.page-header title="Edit Draft Quotation" subtitle="RFQ: {{ $rfq->rfq_number }} — {{ $rfq->title }}" />

    @include('backend.supplier.procurement.quotations.partials._form')

@endsection

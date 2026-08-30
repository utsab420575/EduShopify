@extends('backend.layouts.supplier')

@section('title', 'Revise Quotation — ' . $quotation->quotation_number)
@section('breadcrumb', 'Quotations / ' . $quotation->quotation_number . ' / Revise')

@section('body')

    <x-backend.page-header title="Revise Quotation" subtitle="RFQ: {{ $rfq->rfq_number }} — {{ $rfq->title }}" />

    @php($isRevision = true)
    @include('backend.supplier.procurement.quotations.partials._form')

@endsection

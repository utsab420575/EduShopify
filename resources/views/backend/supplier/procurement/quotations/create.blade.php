@extends('backend.layouts.supplier')

@section('title', 'Submit Quotation — ' . $rfq->title)
@section('breadcrumb', 'Quotations / Submit Quotation')

@section('body')

    <x-backend.page-header title="Submit Quotation" subtitle="RFQ: {{ $rfq->rfq_number }} — {{ $rfq->title }}" />

    @include('backend.supplier.procurement.quotations.partials._form')

@endsection

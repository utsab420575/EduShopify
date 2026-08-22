@extends('backend.layouts.buyer')

@section('title', 'Edit RFQ')
@section('breadcrumb', 'Procurement / RFQs / Edit')

@section('body')

    <x-backend.page-header title="Edit RFQ" subtitle="{{ $rfq->rfq_number }}" />

    @include('backend.buyer.procurement.rfqs.partials._form')

@endsection

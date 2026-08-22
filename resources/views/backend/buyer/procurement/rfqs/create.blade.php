@extends('backend.layouts.buyer')

@section('title', 'Create RFQ')
@section('breadcrumb', 'Procurement / RFQs / Create')

@section('body')

    <x-backend.page-header title="Create RFQ" subtitle="Describe what you need and let suppliers send you quotations." />

    @include('backend.buyer.procurement.rfqs.partials._form')

@endsection

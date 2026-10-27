@extends('frontend_new.layouts.app')

@section('title', 'Terms of Service | Edushopify')

@section('content')

  @include('frontend_new.pages.partials._hero', [
    'label' => 'Legal',
    'title' => 'Terms of Service',
    'subtitle' => 'The rules for using the Edushopify marketplace as a buyer, supplier or visitor.',
  ])

  @include('frontend_new.pages.partials._legal', [
    'updated' => 'October 27, 2026',
    'intro' => 'These Terms of Service govern access to and use of the Edushopify marketplace. By creating an account or using the platform, you agree to these terms.',
    'sections' => [
      'The Marketplace' => 'Edushopify is a B2B education procurement marketplace connecting institutional Buyers with verified Suppliers through a structured Request for Quotation (RFQ) process. Edushopify facilitates discovery, sourcing and communication between Buyers and Suppliers.',
      'Accounts' => 'You must provide accurate information when registering. Buyer and Supplier capabilities are subject to eligibility, approval and ongoing compliance with these terms. You are responsible for activity carried out under your account and its team members.',
      'RFQs, Quotations and Purchase Orders' => 'An RFQ posted by a Buyer is a structured sourcing request. A guest inquiry submitted through the public marketplace is not an RFQ, quotation, award or purchase order. Official procurement actions require an authenticated, eligible account.',
      'Subscriptions' => 'Suppliers access marketplace features through a subscription plan. Plan limits, pricing and billing periods are shown on the Pricing page and in the supplier dashboard at the time of purchase.',
      'Fulfilment & Payment' => 'Payment and fulfilment for products and services currently take place outside Edushopify, directly between the Buyer and Supplier. Edushopify processes Supplier subscription payments only.',
      'Conduct' => 'Users must not misuse the platform, submit false information, or attempt to access data or accounts they are not authorized to access. We may suspend accounts that breach these terms.',
      'Changes' => 'We may update these terms from time to time. Continued use of the platform after changes are published constitutes acceptance of the revised terms.',
    ],
  ])

@endsection

@extends('frontend_new.layouts.app')

@section('title', 'Privacy Policy | Edushopify')

@section('content')

  @include('frontend_new.pages.partials._hero', [
    'label' => 'Legal',
    'title' => 'Privacy Policy',
    'subtitle' => 'What information we collect, how we use it, and the choices you have.',
  ])

  @include('frontend_new.pages.partials._legal', [
    'updated' => 'October 27, 2026',
    'intro' => 'This Privacy Policy explains how Edushopify collects, uses and protects information when you use the marketplace.',
    'sections' => [
      'Information We Collect' => 'We collect account information you provide during registration, profile and company details, RFQ and quotation content, and inquiry or contact form submissions. We also collect standard technical data such as IP address and browser information for security purposes.',
      'How We Use Information' => 'Information is used to operate the marketplace, facilitate RFQ and quotation workflows, verify Supplier eligibility, respond to inquiries, and maintain platform security.',
      'Public Information' => 'Certain information — such as published listings, Supplier storefront details, published reviews and global RFQ summaries — is intentionally public. Private information, including RFQ buyer contact details, selected-Supplier RFQ data and Supplier verification documents, is not exposed publicly.',
      'Guest Inquiries' => 'When you submit a Contact Supplier or Contact Us form, we store your submitted details and route them to the relevant Supplier or our support team as a lead — not as an RFQ or official procurement request.',
      'Data Sharing' => 'We do not sell personal information. Information is shared with other users only as needed for marketplace functionality, for example sharing your inquiry with the Supplier you contacted.',
      'Your Choices' => 'You may update your account information at any time from your dashboard, or contact us to request assistance with your data.',
    ],
  ])

@endsection

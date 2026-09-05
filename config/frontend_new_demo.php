<?php

/*
|--------------------------------------------------------------------------
| Frontend V2 demo/placeholder data
|--------------------------------------------------------------------------
|
| Holds content for static-HTML sections that have no backing model or
| schema field yet. Per explicit direction: no migrations for this content
| — config data only, until each concept gets a real backend source. Do
| not add facts here that claim something real about a specific business
| identity; this is cosmetic/demo content only.
|
*/

return [

    /*
    | "Founding Supplier" / "ISE Exhibitor" / "BETT Exhibitor" badges — no
    | such column or table exists on supplier_profiles. Selected
    | deterministically by supplier ID (via App\Support\FrontendNewDemo) so
    | the same real supplier shows the same badges everywhere they appear —
    | never a factual claim about a specific real supplier.
    */
    'supplier_badge_pattern' => [
        ['founding' => false, 'ise' => false, 'bett' => false],
        ['founding' => true,  'ise' => true,  'bett' => true],
        ['founding' => false, 'ise' => true,  'bett' => false],
        ['founding' => true,  'ise' => true,  'bett' => false],
    ],

    /*
    | "Our Services" (About tab + Services tab) — generic support-service
    | offerings shown identically on every supplier profile. There is no
    | per-supplier "services offered" model (SupplierServiceArea is
    | geographic coverage, not this) — this is presentational filler
    | content, not a claim about what any specific supplier actually offers.
    */
    'supplier_services' => [
        ['icon' => 'wrench', 'title' => 'Installation & Setup', 'text' => 'Professional on-site installation and commissioning of all equipment, ensuring everything is configured and ready to use from day one.', 'tags' => ['On-site', 'Hardware', 'Software']],
        ['icon' => 'cap', 'title' => 'Teacher Training', 'text' => 'Comprehensive hands-on training programs for educators covering product usage, curriculum integration, and best practices.', 'tags' => ['Workshops', 'Certification', 'Online']],
        ['icon' => 'headset', 'title' => 'Technical Support', 'text' => '24/7 technical assistance via phone, email, and live chat. Dedicated account managers for premium clients.', 'tags' => ['24/7', 'Remote', 'On-site']],
        ['icon' => 'book', 'title' => 'Curriculum Integration', 'text' => "Expert STEM curriculum consulting to seamlessly integrate our products into your existing teaching frameworks and lesson plans.", 'tags' => ['STEM', 'K-12', 'Higher Ed']],
        ['icon' => 'wrench', 'title' => 'Maintenance & Repair', 'text' => 'Regular preventive maintenance schedules and fast-turnaround repair services to minimize downtime in your labs.', 'tags' => ['Annual', 'Parts & Labour', 'Warranty']],
        ['icon' => 'target', 'title' => 'Custom Solutions', 'text' => "Tailored product bundles and bespoke solutions designed around your school's specific curriculum goals and budget.", 'tags' => ['Bespoke', 'Budget-friendly', 'Scalable']],
    ],

    /*
    | "Certifications & Accreditations" / "Industry Partnerships" — no
    | Certification/Partnership model exists. Generic filler shown
    | identically on every supplier profile, not a claim about any
    | specific supplier's actual certifications.
    */
    'supplier_certifications' => [
        ['name' => 'ISO 9001:2015', 'issuer' => 'International Organization for Standardization', 'since' => 2019, 'text' => 'Quality Management Systems certification ensuring consistent product and service quality.'],
        ['name' => 'CE Marking', 'issuer' => 'European Conformity', 'since' => 2020, 'text' => 'Compliance with EU safety, health, and environmental protection standards.'],
        ['name' => 'Google for Education Partner', 'issuer' => 'Google LLC', 'since' => 2021, 'text' => 'Certified partner for integrating Google Workspace tools into educational environments.'],
        ['name' => 'Microsoft Education Partner', 'issuer' => 'Microsoft Corporation', 'since' => 2021, 'text' => 'Authorized partner for deploying Microsoft 365 Education and Azure solutions in schools.'],
    ],

    'supplier_industry_partnerships' => ['GESS Partner', 'BETT Exhibitor', 'ISE Exhibitor', 'STEM.org Certified', 'UNESCO Partner', 'UNICEF Supplier'],

    /*
    | Homepage "Events" section — no Event model/table exists yet.
    */
    'events' => [
        ['title' => 'BETT 2026', 'category' => 'EdTech', 'badge_class' => 'bg-blue-600', 'overlay_class' => 'from-black/70 via-black/20 to-transparent', 'date' => 'Jan 22–24, 2026', 'location' => 'London, UK'],
        ['title' => 'STEM Education Summit', 'category' => 'STEM', 'badge_class' => 'bg-emerald-600', 'overlay_class' => 'from-black/70 via-black/20 to-transparent', 'date' => 'Mar 10–12, 2026', 'location' => 'Dubai, UAE'],
        ['title' => 'World Robot Olympiad', 'category' => 'Robotics', 'badge_class' => 'bg-purple-600', 'overlay_class' => 'from-purple-900/60 via-purple-700/30 to-transparent', 'date' => 'Apr 5–7, 2026', 'location' => 'Singapore'],
        ['title' => 'ISE 2026', 'category' => 'AV & Display', 'badge_class' => 'bg-orange-600', 'overlay_class' => 'from-orange-900/70 via-orange-700/20 to-transparent', 'date' => 'Feb 3–6, 2026', 'location' => 'Barcelona, Spain'],
    ],

];

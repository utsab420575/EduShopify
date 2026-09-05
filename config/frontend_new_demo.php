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
    | "Founding Supplier" / "ISE Exhibitor" badges — no such column or table
    | exists on supplier_profiles. Applied positionally (by list index, not
    | by supplier identity) purely to reproduce the static reference's
    | visual variety — never a factual claim about a specific real supplier.
    */
    'supplier_badge_pattern' => [
        ['founding' => false, 'ise' => false],
        ['founding' => true,  'ise' => true],
        ['founding' => false, 'ise' => true],
        ['founding' => true,  'ise' => true],
    ],

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

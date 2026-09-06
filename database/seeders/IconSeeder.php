<?php

namespace Database\Seeders;

use App\Models\Icon;
use App\Models\IconLibrary;
use Illuminate\Database\Seeder;

class IconSeeder extends Seeder
{
    public function run(): void
    {
        // 1. FontAwesome 6 Library
        $fa = IconLibrary::updateOrCreate(
            ['slug' => 'fontawesome'],
            [
                'name' => 'FontAwesome 6',
                'type' => 'CDN',
                'cdn_url' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
                'is_active' => true,
            ]
        );

        $faIcons = [
            ['name' => 'Fast Delivery & Logistics', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-truck'],
            ['name' => 'Assembly & Installation', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-screwdriver-wrench'],
            ['name' => 'Custom Manufacturing & OEM', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-gear'],
            ['name' => 'Packaging & Crating', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-boxes-packing'],
            ['name' => 'Warranty & Protection', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-shield-halved'],
            ['name' => 'Quality Certification', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-certificate'],
            ['name' => 'Award Winning Quality', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-award'],
            ['name' => '24/7 Customer Support', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-headset'],
            ['name' => 'Software & LMS Integration', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-laptop-code'],
            ['name' => 'Teacher Training & Workshops', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-chalkboard-user'],
            ['name' => 'Consulting & Procurement', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-handshake'],
            ['name' => 'Laboratory Setup & Testing', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-flask'],
            ['name' => 'Scientific Equipment Calibration', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-microscope'],
            ['name' => 'Design & Prototyping', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-palette'],
            ['name' => 'Bulk Warehousing', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-warehouse'],
            ['name' => 'Maintenance & Repairs', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-rotate'],
            ['name' => 'Curriculum Planning', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-book-open'],
            ['name' => 'International Export', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-globe'],
            ['name' => 'Classroom Architecture & Space Planning', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-compass-drafting'],
            ['name' => 'Robotics & STEM Lab Solutions', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-robot'],
            ['name' => 'Safety & Security Audits', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-user-shield'],
            ['name' => 'Recycling & Eco-Disposal', 'icon_type' => 'fontawesome', 'icon_value' => 'fa-solid fa-recycle'],
        ];

        foreach ($faIcons as $item) {
            Icon::updateOrCreate(
                ['library_id' => $fa->id, 'name' => $item['name']],
                [
                    'icon_type' => $item['icon_type'],
                    'icon_value' => $item['icon_value'],
                    'is_active' => true,
                ]
            );
        }

        // 2. Bootstrap Icons Library
        $bi = IconLibrary::updateOrCreate(
            ['slug' => 'bootstrap-icons'],
            [
                'name' => 'Bootstrap Icons',
                'type' => 'CDN',
                'cdn_url' => 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
                'is_active' => true,
            ]
        );

        $biIcons = [
            ['name' => 'BI Shipping Truck', 'icon_type' => 'fontawesome', 'icon_value' => 'bi bi-truck'],
            ['name' => 'BI Tools & Maintenance', 'icon_type' => 'fontawesome', 'icon_value' => 'bi bi-tools'],
            ['name' => 'BI Gear Settings', 'icon_type' => 'fontawesome', 'icon_value' => 'bi bi-gear-fill'],
            ['name' => 'BI Shield Verified', 'icon_type' => 'fontawesome', 'icon_value' => 'bi bi-shield-check'],
            ['name' => 'BI Headset Support', 'icon_type' => 'fontawesome', 'icon_value' => 'bi bi-headset'],
            ['name' => 'BI Award Quality', 'icon_type' => 'fontawesome', 'icon_value' => 'bi bi-award'],
        ];

        foreach ($biIcons as $item) {
            Icon::updateOrCreate(
                ['library_id' => $bi->id, 'name' => $item['name']],
                [
                    'icon_type' => $item['icon_type'],
                    'icon_value' => $item['icon_value'],
                    'is_active' => true,
                ]
            );
        }

        // 3. Custom SVG Library
        $svgLib = IconLibrary::updateOrCreate(
            ['slug' => 'custom-svg'],
            [
                'name' => 'Custom SVG Library',
                'type' => 'Custom',
                'cdn_url' => null,
                'is_active' => true,
            ]
        );

        $svgIcons = [
            [
                'name' => 'SVG Delivery Van',
                'icon_type' => 'svg',
                'icon_value' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>',
            ],
            [
                'name' => 'SVG Quality Verified',
                'icon_type' => 'svg',
                'icon_value' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" /></svg>',
            ],
        ];

        foreach ($svgIcons as $item) {
            Icon::updateOrCreate(
                ['library_id' => $svgLib->id, 'name' => $item['name']],
                [
                    'icon_type' => $item['icon_type'],
                    'icon_value' => $item['icon_value'],
                    'is_active' => true,
                ]
            );
        }
    }
}

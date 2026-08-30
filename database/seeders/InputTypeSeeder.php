<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\InputType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InputTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'code'        => 'text',
                'name'        => 'Single-line Text',
                'description' => 'Short freeform text input (e.g. Model number, Serial code, Part number)',
                'has_options' => false,
                'is_multiple' => false,
                'is_active'   => true,
                'sort_order'  => 1,
            ],
            [
                'code'        => 'textarea',
                'name'        => 'Multi-line Textarea',
                'description' => 'Longer multi-line text input (e.g. Care instructions, Detailed notes)',
                'has_options' => false,
                'is_multiple' => false,
                'is_active'   => true,
                'sort_order'  => 2,
            ],
            [
                'code'        => 'number',
                'name'        => 'Number / Decimal',
                'description' => 'Numeric measurement value with optional unit (e.g. Wattage, Weight, Voltage)',
                'has_options' => false,
                'is_multiple' => false,
                'is_active'   => true,
                'sort_order'  => 3,
            ],
            [
                'code'        => 'select',
                'name'        => 'Single Select (Dropdown)',
                'description' => 'Choose one option from a predefined list (e.g. Material, Storage, Gender)',
                'has_options' => true,
                'is_multiple' => false,
                'is_active'   => true,
                'sort_order'  => 4,
            ],
            [
                'code'        => 'multi_select',
                'name'        => 'Multiple Select (Checkboxes)',
                'description' => 'Choose one or more options from a predefined list (e.g. Certifications, Compatible OS)',
                'has_options' => true,
                'is_multiple' => true,
                'is_active'   => true,
                'sort_order'  => 5,
            ],
            [
                'code'        => 'boolean',
                'name'        => 'Yes / No (Toggle)',
                'description' => 'Binary toggle choice (e.g. Is Waterproof, Rechargeable, Assembly Required)',
                'has_options' => false,
                'is_multiple' => false,
                'is_active'   => true,
                'sort_order'  => 6,
            ],
            [
                'code'        => 'date',
                'name'        => 'Date',
                'description' => 'Calendar date picker (e.g. Release date, Expiry date, Manufacture date)',
                'has_options' => false,
                'is_multiple' => false,
                'is_active'   => true,
                'sort_order'  => 7,
            ],
            [
                'code'        => 'color',
                'name'        => 'Color Swatch (Hex)',
                'description' => 'Color selection with name and hex color preview swatch (e.g. Midnight Black #000000)',
                'has_options' => true,
                'is_multiple' => false,
                'is_active'   => true,
                'sort_order'  => 8,
            ],
        ];

        foreach ($types as $typeData) {
            InputType::updateOrCreate(
                ['code' => $typeData['code']],
                $typeData
            );
        }

        // Map existing attributes in database to their corresponding input_type_id
        $inputTypesByCode = InputType::all()->keyBy('code');

        foreach ($inputTypesByCode as $code => $inputType) {
            DB::table('attributes')
                ->where('input_type', $code)
                ->whereNull('input_type_id')
                ->update(['input_type_id' => $inputType->id]);
        }
    }
}

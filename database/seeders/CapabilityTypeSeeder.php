<?php

namespace Database\Seeders;

use App\Models\CapabilityType;
use Illuminate\Database\Seeder;

class CapabilityTypeSeeder extends Seeder
{
    public function run(): void
    {
        CapabilityType::firstOrCreate(
            ['code' => 'buyer'],
            ['name' => 'Buyer', 'description' => 'Buyer capability to submit RFQs and view products']
        );

        CapabilityType::firstOrCreate(
            ['code' => 'supplier'],
            ['name' => 'Supplier', 'description' => 'Supplier capability to upload documents, receive RFQs and submit quotes']
        );
    }
}

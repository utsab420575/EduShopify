<?php

namespace App\Filament\Admin\Resources\BuyerApplicationResource\Pages;

use App\Filament\Admin\Resources\BuyerApplicationResource;
use Filament\Resources\Pages\ListRecords;

class ListBuyerApplications extends ListRecords
{
    protected static string $resource = BuyerApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

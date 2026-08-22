<?php

namespace App\Filament\Admin\Resources\AttributeResource\Pages;

use App\Filament\Admin\Resources\AttributeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAttribute extends EditRecord
{
    protected static string $resource = AttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}

<?php

namespace App\Filament\Admin\Resources\SettingResource\Pages;

use App\Filament\Admin\Resources\SettingResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['payload'] = ['value' => SettingResource::coerce($data['value'] ?? null)];
        unset($data['value']);

        return $data;
    }

    protected function afterSave(): void
    {
        Cache::forget("setting:{$this->record->group_name}:{$this->record->name}");
    }
}

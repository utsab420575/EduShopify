<?php

namespace App\Filament\Admin\Resources\ContactInquiryResource\Pages;

use App\Filament\Admin\Resources\ContactInquiryResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewContactInquiry extends ViewRecord
{
    protected static string $resource = ContactInquiryResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Inquiry')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email')->copyable(),
                        TextEntry::make('phone')->placeholder('—'),
                        TextEntry::make('organization')->placeholder('—'),
                        TextEntry::make('supplierAccount.supplierProfile.display_name')->label('About supplier')->placeholder('General inquiry'),
                        TextEntry::make('status')->badge(),
                    ]),
                    TextEntry::make('subject')->placeholder('—'),
                    TextEntry::make('message')->prose(),
                ]),
        ]);
    }
}

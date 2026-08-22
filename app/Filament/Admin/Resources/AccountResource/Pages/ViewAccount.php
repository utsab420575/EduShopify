<?php

namespace App\Filament\Admin\Resources\AccountResource\Pages;

use App\Filament\Admin\Resources\AccountResource;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewAccount extends ViewRecord
{
    protected static string $resource = AccountResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Account')
                ->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('account_number'),
                        TextEntry::make('display_name'),
                        TextEntry::make('account_type')->badge(),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('primaryOwner.email')->label('Primary owner')->placeholder('—'),
                        TextEntry::make('created_at')->dateTime(),
                    ]),
                ]),

            Section::make('Capabilities')
                ->schema([
                    RepeatableEntry::make('capabilities')
                        ->label('')
                        ->schema([
                            Grid::make(3)->schema([
                                TextEntry::make('capabilityType.name')->label('Capability'),
                                TextEntry::make('status')->badge(),
                                TextEntry::make('activated_at')->dateTime()->placeholder('—'),
                            ]),
                        ]),
                ]),

            Section::make('Members')
                ->schema([
                    RepeatableEntry::make('members')
                        ->label('')
                        ->schema([
                            Grid::make(4)->schema([
                                TextEntry::make('user.name')->label('Name'),
                                TextEntry::make('user.email')->label('Email'),
                                TextEntry::make('member_type'),
                                TextEntry::make('status')->badge(),
                            ]),
                        ]),
                ]),

            Section::make('Moderation')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('suspension_reason')->placeholder('—'),
                        TextEntry::make('suspended_at')->dateTime()->placeholder('—'),
                    ]),
                ]),
        ]);
    }
}

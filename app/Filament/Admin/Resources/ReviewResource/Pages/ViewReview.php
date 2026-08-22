<?php

namespace App\Filament\Admin\Resources\ReviewResource\Pages;

use App\Filament\Admin\Resources\ReviewResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewReview extends ViewRecord
{
    protected static string $resource = ReviewResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Review')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('buyerAccount.buyerProfile.display_name')->label('Buyer'),
                        TextEntry::make('supplierAccount.supplierProfile.display_name')->label('Supplier'),
                        TextEntry::make('rating')->formatStateUsing(fn (int $state) => str_repeat('★', $state) . str_repeat('☆', 5 - $state)),
                        TextEntry::make('review_context')->badge(),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('created_at')->dateTime(),
                    ]),
                    TextEntry::make('title')->placeholder('—'),
                    TextEntry::make('comment')->placeholder('—')->prose(),
                ]),

            Section::make('Moderation')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('moderatedBy.name')->label('Moderated by')->placeholder('—'),
                        TextEntry::make('moderation_reason')->label('Reason')->placeholder('—'),
                    ]),
                ]),

            Section::make('Supplier reply')
                ->schema([
                    TextEntry::make('reply.reply')->label('Reply')->placeholder('No reply yet'),
                ]),
        ]);
    }
}

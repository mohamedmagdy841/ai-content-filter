<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FilterLogResource\Pages;
use App\Filament\Resources\FilterLogResource\RelationManagers;
use App\Models\FilterLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FilterLogResource extends Resource
{
    protected static ?string $model = FilterLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('content_type'),
                Tables\Columns\TextColumn::make('reason')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->reason),
                Tables\Columns\TextColumn::make('flagged_by'),
                Tables\Columns\TextColumn::make('confidence')->numeric(decimalPlaces: 2),
                Tables\Columns\TextColumn::make('content.content')->label('Related Post/Comment')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->content),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFilterLogs::route('/'),
            'create' => Pages\CreateFilterLog::route('/create'),
            'edit' => Pages\EditFilterLog::route('/{record}/edit'),
        ];
    }
}

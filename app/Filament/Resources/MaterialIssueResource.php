<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialIssueResource\Pages;
use App\Filament\Resources\MaterialIssueResource\RelationManagers;
use App\Models\MaterialIssue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MaterialIssueResource extends Resource
{
    protected static ?string $model = MaterialIssue::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('manufacturing_order_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('product_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('quantity_issued')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('quantity_consumed')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('quantity_wasted')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('wastage_reason'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('manufacturing_order_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_issued')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_consumed')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_wasted')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('wastage_reason')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListMaterialIssues::route('/'),
            'create' => Pages\CreateMaterialIssue::route('/create'),
            'edit' => Pages\EditMaterialIssue::route('/{record}/edit'),
        ];
    }
}

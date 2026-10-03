<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialIssueResource\Pages;
use App\Models\MaterialIssue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MaterialIssueResource extends Resource
{
    protected static ?string $model = MaterialIssue::class;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationGroup = 'Manufacturing';
    protected static ?string $navigationLabel = 'Material Issues';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'id';


    public static function canAccess(): bool
    {
        return !auth()->user()->hasRole('Seller');
    }
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Issue Details')
                ->schema([
                    Forms\Components\Select::make('manufacturing_order_id')
                        ->label('Manufacturing Order')
                        ->relationship('manufacturingOrder', 'reference')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('product_id')
                        ->label('Raw Material')
                        ->relationship('product', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                ])->columns(2),

            Forms\Components\Section::make('Quantities')
                ->schema([
                    Forms\Components\TextInput::make('quantity_issued')
                        ->label('Issued')
                        ->required()->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('quantity_consumed')
                        ->label('Consumed')
                        ->required()->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('quantity_wasted')
                        ->label('Wasted')
                        ->required()->numeric()->minValue(0)->default(0),
                    Forms\Components\TextInput::make('wastage_reason')
                        ->label('Wastage Reason')
                        ->maxLength(255),
                ])->columns(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('manufacturingOrder.reference')
                    ->label('Production Order')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Raw Material')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity_issued')
                    ->label('Issued')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_consumed')
                    ->label('Consumed')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_wasted')
                    ->label('Wasted')
                    ->numeric()
                    ->sortable()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('wastage_reason')
                    ->label('Wastage Reason')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('manufacturingOrder')
                    ->relationship('manufacturingOrder', 'reference')
                    ->label('Production Order'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMaterialIssues::route('/'),
            'create' => Pages\CreateMaterialIssue::route('/create'),
            'edit'   => Pages\EditMaterialIssue::route('/{record}/edit'),
        ];
    }
}

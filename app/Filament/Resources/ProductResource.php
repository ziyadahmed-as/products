<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Catalog';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Basic Information')
                ->schema([
                    Forms\Components\Select::make('type')
                        ->options([
                            'raw_material'        => 'Raw Material',
                            'manufactured_product'=> 'Manufactured Product',
                            'resale_product'      => 'Resale Product',
                            'consumable'          => 'Consumable',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('name')->required(),
                    Forms\Components\TextInput::make('sku')->label('SKU')->required(),
                    Forms\Components\Select::make('category_id')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('unit_of_measure_id')
                        ->relationship('unit', 'name')
                        ->searchable()
                        ->preload(),
                    Forms\Components\TextInput::make('package_size'),
                ])->columns(2),

            Forms\Components\Section::make('Pricing & Stock')
                ->schema([
                    Forms\Components\TextInput::make('purchase_cost')->numeric()->prefix('$'),
                    Forms\Components\TextInput::make('selling_price')->numeric()->prefix('$'),
                    Forms\Components\TextInput::make('minimum_stock_level')
                        ->required()->numeric()->default(0),
                ])->columns(3),

            Forms\Components\Section::make('Description & Image')
                ->schema([
                    Forms\Components\Textarea::make('specification')->columnSpanFull()->rows(3),
                    Forms\Components\FileUpload::make('image')
                        ->image()
                        ->directory('products')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Public Display')
                ->schema([
                    Forms\Components\TextInput::make('rating')
                        ->numeric()->step('0.1')->default(0.0)->minValue(0)->maxValue(5.0),
                    Forms\Components\TextInput::make('reviews_count')
                        ->numeric()->default(0),
                    Forms\Components\Toggle::make('is_featured')->default(false),
                    Forms\Components\Toggle::make('is_active')->default(true)->required(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->circular()->size(40),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('sku')->label('SKU')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'manufactured_product' => 'success',
                        'resale_product'       => 'info',
                        'raw_material'         => 'gray',
                        'consumable'           => 'warning',
                        default                => 'gray',
                    }),
                Tables\Columns\TextColumn::make('category.name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('selling_price')
                    ->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('rating')
                    ->numeric(1)->sortable(),
                Tables\Columns\IconColumn::make('is_featured')->boolean()->label('Featured'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'raw_material'        => 'Raw Material',
                        'manufactured_product'=> 'Manufactured Product',
                        'resale_product'      => 'Resale Product',
                        'consumable'          => 'Consumable',
                    ]),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Featured'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}

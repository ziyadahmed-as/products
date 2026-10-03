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
    protected static ?string $recordTitleAttribute = 'name';


    public static function canAccess(): bool
    {
        // Allowed for sellers to view, restricted to their branches below.
        return true;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user->hasRole('Seller')) {
            $branchIds = $user->branches()->pluck('branches.id');
            // Only products that belong to the seller's branch
            $query->whereHas('branches', fn ($q) => $q->whereIn('branches.id', $branchIds))
                  ->whereIn('type', ['resale_product', 'manufactured_product']);
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        return !auth()->user()->hasRole('Seller');
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return !auth()->user()->hasRole('Seller');
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return !auth()->user()->hasRole('Seller');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Product Identity')
                ->schema([
                    Forms\Components\Select::make('type')
                        ->options([
                            'manufactured_product' => '🏭 Manufactured Product',
                            'resale_product'       => '🛒 Resale Product',
                            'raw_material'         => '⚙️ Raw Material',
                            'consumable'           => '🔧 Consumable',
                        ])
                        ->required()
                        ->live()
                        ->default('resale_product')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('sku')
                        ->label('SKU / Item Code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->default(fn () => 'SKU-' . strtoupper(substr(uniqid(), -6)))
                        ->maxLength(100),
                    Forms\Components\Select::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required(),
                        ]),
                    Forms\Components\Select::make('unit_of_measure_id')
                        ->label('Unit of Measure')
                        ->relationship('unit', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required(),
                            Forms\Components\TextInput::make('abbreviation')->required(),
                        ]),
                    Forms\Components\TextInput::make('package_size')
                        ->placeholder('e.g. 50kg bag, 1L bottle')
                        ->maxLength(100),
                ])->columns(2),

            Forms\Components\Section::make('Pricing & Stock Control')
                ->schema([
                    Forms\Components\TextInput::make('purchase_cost')
                        ->label('Purchase / Production Cost')
                        ->numeric()
                        ->prefix('$')
                        ->minValue(0),
                    Forms\Components\TextInput::make('selling_price')
                        ->numeric()
                        ->prefix('$')
                        ->minValue(0)
                        ->visible(fn (Forms\Get $get) => in_array($get('type'), [
                            'manufactured_product', 'resale_product',
                        ])),
                    Forms\Components\TextInput::make('minimum_stock_level')
                        ->label('Minimum Stock Level (Reorder Point)')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->default(0),
                ])->columns(3),

            Forms\Components\Section::make('Branch Allocation')
                ->schema([
                    Forms\Components\Select::make('branches')
                        ->label('Assigned Branches')
                        ->relationship('branches', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->helperText('Branches where this product is managed / sold.'),
                ])
                ->visible(fn (Forms\Get $get) => in_array($get('type'), [
                    'manufactured_product', 'resale_product',
                ])),

            Forms\Components\Section::make('Initial Stock')
                ->description('Set the opening stock quantity for this product at a specific storage location.')
                ->schema([
                    Forms\Components\Select::make('initial_location_id')
                        ->label('Storage Location')
                        ->options(fn () => \App\Models\StorageLocation::where('is_active', true)->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->helperText('Leave blank to skip setting initial stock.'),
                    Forms\Components\TextInput::make('initial_quantity')
                        ->label('Opening Quantity')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText('Stock will be added to inventory balance on save.'),
                ])
                ->columns(2)
                ->visible(fn (Forms\Get $get) => in_array($get('type'), [
                    'manufactured_product', 'resale_product', 'raw_material', 'consumable',
                ])),

            Forms\Components\Section::make('Description & Image')
                ->schema([
                    Forms\Components\Textarea::make('specification')
                        ->label('Specification / Description')
                        ->rows(4)
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('image')
                        ->image()
                        ->directory('products')
                        ->maxSize(2048)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Storefront Settings')
                ->schema([
                    Forms\Components\TextInput::make('rating')
                        ->numeric()
                        ->step('0.1')
                        ->default(0.0)
                        ->minValue(0)
                        ->maxValue(5.0)
                        ->label('Rating (0–5)'),
                    Forms\Components\TextInput::make('reviews_count')
                        ->label('Review Count')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),
                    Forms\Components\Toggle::make('is_featured')
                        ->label('Featured on Homepage')
                        ->default(false),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Active / Visible in Store')
                        ->default(true)
                        ->required(),
                ])
                ->columns(2)
                ->visible(fn (Forms\Get $get) => in_array($get('type'), [
                    'manufactured_product', 'resale_product',
                ])),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'manufactured_product' => 'success',
                        'resale_product'       => 'info',
                        'raw_material'         => 'gray',
                        'consumable'           => 'warning',
                        default                => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'manufactured_product' => 'Manufactured',
                        'resale_product'       => 'Resale',
                        'raw_material'         => 'Raw Material',
                        'consumable'           => 'Consumable',
                        default                => $state,
                    }),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('selling_price')
                    ->money('USD')
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('purchase_cost')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('minimum_stock_level')
                    ->label('Min Stock')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('rating')
                    ->numeric(1)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(function () {
                        $user = auth()->user();
                        if ($user && $user->hasRole('Seller')) {
                            return [
                                'manufactured_product' => 'Manufactured Product',
                                'resale_product'       => 'Resale Product',
                            ];
                        }
                        return [
                            'manufactured_product' => 'Manufactured Product',
                            'resale_product'       => 'Resale Product',
                            'raw_material'         => 'Raw Material',
                            'consumable'           => 'Consumable',
                        ];
                    }),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Featured'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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

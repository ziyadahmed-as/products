<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'name';


    public static function canAccess(): bool
    {
        return !auth()->user()->hasRole('Seller');
    }
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Branch Details')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50),
                    Forms\Components\Select::make('type')
                        ->options([
                            'main'       => 'Main Branch',
                            'warehouse'  => 'Warehouse',
                            'retail'     => 'Retail Shop',
                            'production' => 'Production Facility',
                        ])
                        ->required(),
                    Forms\Components\Select::make('parent_branch_id')
                        ->label('Parent Branch')
                        ->relationship('parent', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Forms\Components\Toggle::make('is_active')
                        ->default(true)
                        ->required(),
                ])->columns(2),

            Forms\Components\Section::make('Assigned Users')
                ->schema([
                    Forms\Components\Select::make('users')
                        ->relationship('users', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->label('Branch Users'),
                ]),

            Forms\Components\Section::make('Available Products')
                ->schema([
                    Forms\Components\Select::make('products')
                        ->relationship('products', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->label('Products in Branch'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->copyable()
                    ->badge(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'main'       => 'success',
                        'warehouse'  => 'info',
                        'retail'     => 'warning',
                        'production' => 'danger',
                        default      => 'gray',
                    }),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent Branch')
                    ->placeholder('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('storageLocations_count')
                    ->counts('storageLocations')
                    ->label('Storage Locations')
                    ->badge(),
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
                    ->options([
                        'main'       => 'Main Branch',
                        'warehouse'  => 'Warehouse',
                        'retail'     => 'Retail Shop',
                        'production' => 'Production Facility',
                    ]),
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
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit'   => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}

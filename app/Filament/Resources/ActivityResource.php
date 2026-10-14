<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use Spatie\Activitylog\Models\Activity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?string $navigationLabel = 'Audit Logs';
    protected static ?int $navigationSort = 100;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole('Super Admin') || $user->hasRole('Auditor'));
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('log_name')->label('Module'),
                Forms\Components\TextInput::make('event')->label('Action'),
                Forms\Components\TextInput::make('description')->label('Description')->columnSpanFull(),
                Forms\Components\TextInput::make('causer.name')->label('Causer'),
                Forms\Components\TextInput::make('created_at')->label('Timestamp'),
                Forms\Components\KeyValue::make('properties')->label('Properties / Payload')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label('User')
                    ->searchable()
                    ->default(fn ($record) => $record->properties['username'] ?? 'System')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('properties.role')
                    ->label('Role')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('log_name')
                    ->label('Module')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                Tables\Columns\TextColumn::make('event')
                    ->label('Action')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'login', 'register', 'create', 'complete', 'approve' => 'success',
                        'failed_login', 'delete', 'cancel'                   => 'danger',
                        'logout', 'update'                                  => 'warning',
                        default                                             => 'gray',
                    }),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->description),

                Tables\Columns\TextColumn::make('properties.reference')
                    ->label('Reference')
                    ->searchable()
                    ->fontFamily('mono')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('properties.outcome')
                    ->label('Outcome')
                    ->badge()
                    ->color(fn ($state) => $state === 'failed' ? 'danger' : 'success')
                    ->default('success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')
                    ->label('Module')
                    ->options([
                        'Auth'          => 'Auth',
                        'Sales'         => 'Sales',
                        'Inventory'     => 'Inventory',
                        'Manufacturing' => 'Manufacturing',
                        'Users'         => 'Users',
                        'Transfers'     => 'Transfers',
                        'Reviews'       => 'Reviews',
                        'Reports'       => 'Reports',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Immutable: No delete bulk action
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
        ];
    }
}

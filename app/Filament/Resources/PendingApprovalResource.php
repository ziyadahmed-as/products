<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PendingApprovalResource\Pages;
use App\Filament\Resources\PendingApprovalResource\RelationManagers;
use App\Models\PendingApproval;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PendingApprovalResource extends Resource
{
    protected static ?string $model = PendingApproval::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('type')
                    ->required(),
                Forms\Components\TextInput::make('approvable_type')
                    ->required(),
                Forms\Components\TextInput::make('approvable_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('requested_by')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('approved_by')
                    ->numeric(),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\Textarea::make('requester_note')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('reviewer_note')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('decided_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('approvable_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('approvable_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('requested_by')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approved_by')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('decided_at')
                    ->dateTime()
                    ->sortable(),
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
            'index' => Pages\ListPendingApprovals::route('/'),
            'create' => Pages\CreatePendingApproval::route('/create'),
            'edit' => Pages\EditPendingApproval::route('/{record}/edit'),
        ];
    }
}

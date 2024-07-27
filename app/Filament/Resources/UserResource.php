<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $tenantOwnershipRelationshipName = 'teams';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(30),
                Forms\Components\TextInput::make('email')
                    ->required()
                    ->email()
                    ->maxLength(50),
                Forms\Components\Select::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'seller' => 'Seller',
                        'vendor' => 'Vendor'
                    ])
                    ->native(false)
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->required()
                    ->hidden(fn ( string $operation ): bool => $operation !== 'create')
                    ->maxLength(30),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ->modifyQueryUsing(fn (Model $query) => $query->)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->sortable()
                    ->badge()
                    ->color('badge'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Date added'))
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}

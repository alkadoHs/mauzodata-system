<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic informations')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('unit')
                            ->required()
                            ->maxLength(10),
                        TextInput::make('buy_price')
                            ->label(__('Buying price'))
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->required(),
                        TextInput::make('stock')
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->required(),
                        TextInput::make('sale_price')
                            ->label(__('Selling price'))
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(','),
                    ]),
                    Section::make('Addional information')
                        ->columns(2)
                        ->collapsed()
                        ->schema([
                            TextInput::make('discount_stock')
                                ->helperText('The stock amount you sell by discount.')
                                ->numeric()
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(','),
                            TextInput::make('discount_price')
                                ->numeric()
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(','),
                            TextInput::make('product_id')
                                ->maxLength(100)
                                ->default(Filament::getTenant()->id . date('dhmi')),
                            TextInput::make('expire_date')
                                ->mask('9999/99/99')
                                ->placeholder('YYYY/MM/DD'),
                        ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('TITLE'))
                    ->searchable(),
                TextColumn::make('stock')
                    ->label(__('STOCK'))
                    ->sortable()
                    ->numeric()
                    ->toggleable(),
                TextColumn::make('buy_price')
                    ->label('B.PRICE')
                    ->tooltip('buying price')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('sale_price')
                    ->label('S.PRICE')
                    ->tooltip('Selling price')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('discount_stock')
                    ->label(__('D.STOCK'))
                    ->tooltip('Discount stock')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('discount_price')
                    ->label(__('D.PRICE'))
                    ->tooltip('Discount price')
                    ->numeric(1)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('expire_date')
                    ->date('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('product_id')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
            ])
            ->filters([
                //
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProducts::route('/'),
        ];
    }
}

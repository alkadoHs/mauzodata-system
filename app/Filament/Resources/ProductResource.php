<?php

namespace App\Filament\Resources;

use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
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
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

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
                        TextInput::make('sale_price')
                            ->label(__('Selling price'))
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->required(),
                        TextInput::make('stock')
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->required(),
                        TextInput::make('stock_alert')
                            ->label(__('Stock Alert'))
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->required(),
                    ]),
                    Section::make('Addional information')
                        ->columns(2)
                        ->collapsed()
                        ->schema([
                            TextInput::make('discount_stock')
                                ->helperText('The stock amount you sell by discount.')
                                ->numeric()
                                ->default(0.00)
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->required(),
                            TextInput::make('discount_price')
                                ->numeric()
                                ->default(0.00)
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->required(),
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
            ->headerActions([
                ExportAction::make()
                    ->exporter(ProductExporter::class),
                ImportAction::make()
                    ->importer(ProductImporter::class)
            ])
            ->paginated([10,25,50,100])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('title')
                    ->label(__('TITLE'))
                    ->searchable(),
                TextColumn::make('stock')
                    ->label(__('STOCK'))
                    ->sortable()
                    ->numeric()
                    ->toggleable()
                    ->badge()
                    ->color( function (int|float $state, Product $record ): string {
                        if($record->stock == 0)
                           return 'danger';
                        elseif($record->stock < $record->stock_alert && $record->stock != 0)
                           return 'warning';
                        else
                            return 'success';
                     })
                    ->summarize([
                        Sum::make()
                            ->label('Total')
                    ]),
                TextColumn::make('buy_price')
                    ->label('B.PRICE')
                    ->tooltip('buying price')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->visible(auth()->user()->role === 'admin')
                    ->summarize([
                        Sum::make()
                    ]),
                TextColumn::make('sale_price')
                    ->label('S.PRICE')
                    ->tooltip('Selling price')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->summarize([
                        Sum::make()
                    ]),
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
                    ->toggleable(),
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
                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(ProductExporter::class)
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

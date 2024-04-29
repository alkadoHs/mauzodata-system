<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreditSaleResource\Pages;
use App\Filament\Resources\CreditSaleResource\RelationManagers;
use App\Models\CreditSale;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CreditSaleResource extends Resource
{
    protected static ?string $model = CreditSale::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             //
    //         ]);
    // }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.invoice_number')
                    ->searchable(),
                TextColumn::make('total_order')
                    ->state(
                        fn (CreditSale $creditSale) => $creditSale->order->orderItems->reduce(
                            fn ($acc, $item) => $acc + $item->price * $item->quantity, 0)
                        )
                        ->numeric(),
                        
                TextColumn::make('Paid')
                    ->state(
                        fn (CreditSale $creditSale) => $creditSale->creditSalePayments->reduce(
                            fn ($acc, $item) => $acc + $item->paid, 0)
                        )
                        ->numeric(),

                TextColumn::make('Dept')
                    ->state(fn (CreditSale $creditSale) => 
                            $creditSale->order->orderItems->reduce(
                                fn ($acc, $item) => $acc + ($item->price * $item->quantity), 0
                            ) 
                            - 
                            $creditSale->creditSalePayments->reduce(
                                fn ($acc, $item) => $acc + $item->paid, 0
                            )

                        )
                        ->numeric(),

                TextColumn::make('order.customer.name')
                    ->searchable()
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            RelationManagers\CreditSalePaymentsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCreditSales::route('/'),
            // 'create' => Pages\CreateCreditSale::route('/create'),
            'view' => Pages\ViewCreditSale::route('/{record}'),
            'edit' => Pages\EditCreditSale::route('/{record}/edit'),
        ];
    }
}

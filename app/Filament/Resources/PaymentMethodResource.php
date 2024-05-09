<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Filament\Resources\PaymentMethodResource\RelationManagers;
use App\Models\CreditSalePayment;
use App\Models\Expense;
use App\Models\Order;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(50),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('Date added'))
                    ->toggleable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(
                        function (PaymentMethod $record, DeleteAction $action) {
                            $ordersAssociated = Order::where('payment_method_id', $record->id)->get();
                            $expenseAssociated = Expense::where('payment_method_id', $record->id)->get();
                            $creditSalepaymentsAssociated = CreditSalePayment::where('payment_method_id', $record->id)->get();

                            if(count($ordersAssociated)) {
                                Notification::make()
                                    ->body('You can not delete this payment method because orders table depends on it.')
                                    ->color('danger')
                                    ->duration(10000)
                                    ->send();
                                return $action->halt(); //prevent from deleting item
                            } elseif(count($expenseAssociated)) {
                                Notification::make()
                                    ->body('You can not delete this payment method because expenses table depends on it.')
                                    ->color('danger')
                                    ->duration(10000)
                                    ->send();
                                return $action->halt(); //prevent from deleting item
                            } elseif(count($creditSalepaymentsAssociated)) {
                                Notification::make()
                                    ->body('You can not delete this payment method because credit sale payments table depends on it.')
                                    ->color('danger')
                                    ->duration(10000)
                                    ->send();
                                return $action->halt(); //prevent from deleting item
                            }

                            return $record;
                        }
                    ),
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
            'index' => Pages\ManagePaymentMethods::route('/'),
        ];
    }
}

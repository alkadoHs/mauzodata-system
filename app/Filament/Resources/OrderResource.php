<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $recordTitleAttribute = 'invoice_number';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columns(2)
                    ->schema([
                        Forms\Components\Hidden::make('user_id')
                            ->default(auth()->id())
                            ->required(),
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name', fn (Builder $query) => $query->where('team_id', Filament::getTenant()->id))
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(30),
                                Forms\Components\TextInput::make('conatct')
                                    ->label('Label(phone,email,address etc'),
                                Forms\Components\Hidden::make('team_id')
                                    ->default(Filament::getTenant()->id)
                                    ->required(),
                            ])
                            ->searchable(),
                        Forms\Components\TextInput::make('invoice_number')
                            ->required()
                            ->default(auth()->id() . date('dhi'))
                            ->maxLength(255),
                        ]),

                Repeater::make('orderItems')
                        ->relationship()
                        ->columns(3)
                        ->columnSpanFull()
                        ->addActionLabel('Add Item')
                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data) {
                            Product::find($data['product_id'])->decrement('stock', $data['quantity']);
                            return $data;
                        })
                        ->schema([
                            Select::make('product_id')
                                ->label(__('Product'))
                                ->placeholder('Select product')
                                ->relationship('product', 'title', fn (Builder $query) => $query->where([['team_id',Filament::getTenant()->id]]))
                                ->searchable()
                                ->preload()
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                ->afterStateUpdated(fn (Set $set, int $state) => $set('price', number_format(Product::find($state)?->sale_price ?? 0)))
                                ->suffix(fn (?int $state): string => Product::find($state)?->unit ?? 'each')
                                ->required(),
                            TextInput::make('quantity')
                                ->label('Qty')
                                ->live(onBlur: true)
                                ->default(1)
                                ->suffix(fn (Get $get): string => Product::find($get('product_id'))?->unit ?? 'each')
                                ->mask(RawJs::make('$money($input)'))
                                ->afterStateUpdated(function (?string $state, Get $get, Set $set) {
                                    $product = Product::find($get('product_id'));
                                    $quantity = (float) str_replace(',', '', $state);

                                    if($product && $product->stock < $quantity) {
                                        $set('quantity', number_format($product->stock));
                                        Notification::make()
                                            ->body("Stock is not enough! The available stock for <b>$product->title</b> is -> " . number_format($product->stock))
                                            ->color('danger')
                                            ->send();
                                    }

                                })
                                ->stripCharacters(',')
                                ->required(),
                            TextInput::make('price')
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->readOnly()
                                ->required()
                        ])
                        ->live()
                        ->afterStateUpdated(
                                fn (Get $get, Set $set) => static::updateTotals($get, $set)
                            )
                        ->deleteAction(
                            fn (Get $get, Set $set) => static::updateTotals($get, $set)
                        ),


                Section::make()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->readOnly(),
                        Forms\Components\Select::make('payment_method_id')
                            ->relationship('paymentMethod', 'name', fn (Builder $query) => $query->where('team_id', Filament::getTenant()->id))
                            ->native(false),
                        Forms\Components\Select::make('status')
                            ->options([
                                'paid' => 'Paid',
                                'credit' => 'Credit'
                            ])
                            ->default('paid')
                            ->native(false)
                            ->required(),
                    ])
            ]);
    }


    public static function updateTotals(Get $get, Set $set): void
    {
        $selectedProducts = collect($get('orderItems'))->filter(fn($item) => !empty($item['product_id'] && !empty($item['quantity'])));

        $subtotal = $selectedProducts->reduce(
            fn ($subtotal, $product) => $subtotal + ((float) str_replace(',', '', $product['quantity'] ?? 0) * (float) str_replace(',', '', $product['price']))
            , 0);

        $set('subtotal' , number_format($subtotal));
        $set('paid', $subtotal);
        // $set('total', number_format($subtotal + ($subtotal * ($get('taxes') / 100))));

    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where([['user_id', auth()->id()]])->latest())
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->numeric()
                    ->state(fn (Order $order) => $order->orderItems->reduce(fn ($acc, $item) => $acc + $item->price * $item->quantity, 0)),
                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:m')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:m')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}

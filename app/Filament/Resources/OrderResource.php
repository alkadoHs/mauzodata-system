<?php

namespace App\Filament\Resources;

use App\Filament\Exports\OrderExporter;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\Widgets\OrderStats;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\CreateAction;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $recordTitleAttribute = 'invoice_number';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 1;

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
                                    ->label('Contact(phone,email,address etc'),
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
                            $product = Product::find($data['product_id']);
                            if($product->stock < $data['quantity']) {
                                // $action->halt();

                                Notification::make()
                                    ->title("$product->title can not be sold because the stock is not enough, the available stock for this product is <b>$product->stock</b> but you are trying to sell <b>". $data['quantity'] . "</b>")
                                    ->danger()
                                    ->persistent()
                                    ->send();
                                return;
                            } else {
                                $product->decrement('stock', $data['quantity']);
                            }
                            
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

                                    if($product && $product->discount_price > 0 && $product->discount_stock > 0 && $quantity >= $product->discount_stock) {
                                        $set('price', number_format($product->discount_price));
                                    } elseif($product) {
                                        $set('price', number_format($product->sale_price));
                                    }

                                    if($product && $product->stock < $quantity) {
                                        $set('quantity', number_format($product->stock));
                                        Notification::make()
                                            ->body("Stock is not enough! The available stock for <b>$product->title</b> is -> " . number_format($product->stock))
                                            ->color('danger')
                                            ->duration(10000)
                                            ->send();
                                    }

                                })
                                ->stripCharacters(',')
                                ->required(),
                            TextInput::make('price')
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->readOnly(auth()->user()->role !== 'admin')
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
                            ->native(false)
                            ->required(),
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


    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query) => auth()->user()->role != 'admin' ?
                    $query->where('user_id', auth()->id())->whereDate('created_at', now())->latest(): 
                    $query->whereYear('created_at', now())->latest()
                )
            ->groups([
                'user.name',
                'status',
                'paymentMethod.name'
            ])
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->extremePaginationLinks()
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('Seller'))
                    ->numeric()
                    ->visible(auth()->user()->role === 'admin')
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
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): ?string => match ($state) {
                        'paid' => 'success',
                        'credit' => 'warning'
                    })
                    ->icon(fn (?string $state) => match ($state) {
                        'paid' => 'heroicon-m-check-circle',
                        'credit' => 'heroicon-m-clock',
                    }),
                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:m')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Date Updated')
                    ->dateTime('d/m/Y H:m')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Filter by seller')
                    ->visible(auth()->user()->role === 'admin')
                    ->options(fn () => Filament::getTenant()->users()->get()->pluck('name', 'id'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('status')
                        ->options([
                            'paid' => 'Paid sales',
                            'credit' => 'Credit sales',
                        ])
                        ->native(false),
                Tables\Filters\SelectFilter::make('payment_method_id')
                    ->label('Payment method')
                    ->options(fn () => Filament::getTenant()->paymentMethods()->get()->pluck('name', 'id'))
                    ->searchable(),
                // Tables\Filters\TrashedFilter::make(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->native(false)
                            ->placeholder(fn ($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('created_until')
                            ->native(false)
                            ->placeholder(fn ($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Order from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Order until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    })
                    ->visible(auth()->user()->role == 'admin'),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(auth()->user()->role === 'admin'),
                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(OrderExporter::class)
                ]),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            OrderStats::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['invoice_number', 'customer.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Order $record */

        return [
            'Customer' => optional($record->customer)->name,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['customer', 'orderItems']);
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

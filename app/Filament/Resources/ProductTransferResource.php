<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductTransferResource\Pages;
use App\Filament\Resources\ProductTransferResource\RelationManagers;
use App\Models\Product;
use App\Models\ProductTransfer;
use App\Models\Team;
use App\Models\User;
use App\Models\VendorProduct;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductTransferResource extends Resource
{
    protected static ?string $model = ProductTransfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('to')
                    ->options([
                        'branch' => 'Branch',
                        'vendor' => 'Vendor',
                    ])
                    ->live()
                    ->native(false)
                    ->required(),
                Forms\Components\Select::make('to_team_id')
                    ->relationship(
                        name:'toTeam',
                        titleAttribute:'name',
                        modifyQueryUsing: fn (Builder $query) => $query->where('id','!=', Filament::getTenant()->id)
                    )
                    ->label(__('Select Branch'))
                    ->native(false)
                    ->visible(fn (Get $get) => $get('to') === 'branch')
                    ->required(fn (Get $get) => $get('to') === 'branch'),
                Forms\Components\Select::make('to_user_id')
                    ->relationship(
                        name:'toUser',
                        titleAttribute:'name',
                        modifyQueryUsing: fn (Builder $query) => $query->where('id', '!=', Filament::auth()->id())
                    )
                    ->native(false)
                    ->label('Select Vendor')
                    ->visible(fn (Get $get) => $get('to') === 'vendor')
                    ->required(fn (Get $get) => $get('to') === 'vendor'),
                Forms\Components\Select::make('product_id')
                    ->relationship(
                        name:'product',
                        titleAttribute:'title',
                        modifyQueryUsing: fn (Builder $query) => $query->where('team_id', Filament::getTenant()->id)
                        )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),

                Forms\Components\TextInput::make('stock')
                    ->required()
                    ->numeric()
                    ->placeholder(fn (Get $get): ?string => "available stock: ". number_format(Product::find($get('product_id'))?->stock) ?? "---"),

                // Forms\Components\TextInput::make('status')
                //     ->required()
                //     ->maxLength(255)
                //     ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(ProductTransfer::query())
            ->paginated([25, 50, 100])
            ->columns([
                Tables\Columns\TextColumn::make('team.name')
                    ->label('From')
                    ->sortable(),

                Tables\Columns\TextColumn::make('to')
                    ->sortable()
                    ->badge()
                    ->color(fn (?string $state): ?string => match ($state) {
                        'branch' => 'success',
                        'vendor' => 'info'
                    }),
                Tables\Columns\TextColumn::make('Name')
                    ->state(fn (ProductTransfer $record):?string => $record->to_user_id ? $record->toUser?->name: $record->toTeam?->name),
                Tables\Columns\TextColumn::make('product.title')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approve',
                        'rejected' => 'Reject'
                    ])
                    ->selectablePlaceholder(false)
                    ->disableOptionWhen(fn (?string $state) => $state !== 'pending')
                    ->beforeStateUpdated(function ($record, $state) {
                        $product = Product::find($record['product_id']);
                        if($state === 'approved' && $record['stock'] > $product->stock) {
                            Notification::make()
                                ->danger()
                                ->title("The sender do not have enough stock.")
                                ->send();
                            return;
                        } elseif($state === 'approved' && $record['stock'] <= $product->stock) {
                            $product->decrement('stock', $record['stock']);
                        }

                        
                        if($record['to'] === 'branch') {
                            //check if product exists
                            $branchProduct = Product::where([
                                ['team_id', '=', $record['to_team_id']],
                                ['product_id', '=', $product->product_id]
                                ])->first();
                            if($branchProduct && $state === 'approved') {
                                $branchProduct->increment('stock', $record['stock']);
                            } elseif(!$branchProduct && $state === 'approved') {
                                Product::create([
                                    'team_id' => $record['to_team_id'],
                                    'title' => $product->title,
                                    'unit' => $product->unit,
                                    'product_id' => $product->product_id,
                                    'buy_price' => $product->buy_price,
                                    'stock' => $record['stock'],
                                    'stock_alert' => $product->stock_alert,
                                    'sale_price' => $product->sale_price,
                                    'discount_stock' => $product->discount_stock,
                                    'discount_price' => $product->discount_price,
                                    'expire_date' => $product->expire_date,
                                ]);
                            }
                            Notification::make()
                                ->success()
                                ->title("Transfered successfully")
                                ->send();
                        } elseif($record['to'] === 'vendor') {
                            //check if product exists to vendor
                            $vendorProduct = VendorProduct::where('product_id', $record['product_id'])->orWhereRelation('product', 'product_id', $product->product_id)->first();

                            if($vendorProduct && $state === 'approved') {
                                $vendorProduct->increment('stock', $record['stock']);
                            } elseif(!$vendorProduct && $state === 'approved') {
                                VendorProduct::create([
                                    'team_id' => User::find($record['to_user_id'])->teams()->first()->id,
                                    'user_id' => $record['to_user_id'],
                                    'product_id' => $record['product_id'],
                                    'stock' => $record['stock'],
                                ]);
                            }
                            Notification::make()
                                ->success()
                                ->title("Transfered to vendor successfully")
                                ->send();
                        }
                    }),
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
            'index' => Pages\ListProductTransfers::route('/'),
            'create' => Pages\CreateProductTransfer::route('/create'),
            'edit' => Pages\EditProductTransfer::route('/{record}/edit'),
        ];
    }
}

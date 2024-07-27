<?php

namespace App\Filament\Resources;

use App\Filament\Exports\ExpenseExporter;
use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('user_id')
                    ->default(auth()->id())
                    ->required(),
                Select::make('payment_method_id')
                    ->relationship(
                        name: 'paymentMethod',
                        titleAttribute: 'name',
                    )
                    ->native(false)
                    ->required(),
                Repeater::make('expenseItems')
                    ->relationship()
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('item')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('cost')
                            ->required()
                            ->maxLength(10)
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->minLength(3),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query) => auth()->user()->role !== 'admin' ?
                    $query->where('user_id', auth()->id())->whereDate('created_at', now())->latest() :
                    $query->latest()
            )
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->columns([
                TextColumn::make('user.name')
                    ->toggleable()
                    ->visible(auth()->user()->role === 'admin'),
                TextColumn::make('expenses')
                    ->state(
                        fn (Expense $expense) => $expense?->expenseItems->reduce(
                            fn ($acc, $item) => $acc + $item->cost, 0
                            ) 
                        )
                    ->numeric(),
                TextColumn::make('paymentMethod.name')
                    ->label('Account')
                    ->placeholder('--##--'),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:m')
                    ->toggleable()
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Filter by seller')
                    ->visible(auth()->user()->role === 'admin')
                    ->options(fn () => User::get()->pluck('name', 'id'))
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
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(auth()->user()->role === 'admin'),
                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(ExpenseExporter::class)
                ]),
            ]);
    }

    public static function getWidgets(): array
    {
        return [
            // ExpenseStats::class,
        ];
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'view' => Pages\ViewExpense::route('/{record}'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}

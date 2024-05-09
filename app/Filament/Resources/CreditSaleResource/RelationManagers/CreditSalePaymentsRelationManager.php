<?php

namespace App\Filament\Resources\CreditSaleResource\RelationManagers;

use App\Filament\Exports\CreditSalePaymentExporter;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CreditSalePaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'creditSalePayments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),
                Forms\Components\TextInput::make('paid')
                    ->label(__('Amount Paid'))
                    ->required()
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(','),
                Forms\Components\Select::make('payment_method_id')
                    ->relationship(
                         name: 'paymentMethod',
                         titleAttribute: 'name',
                         modifyQueryUsing: fn (Builder $query) => $query->where('team_id', Filament::getTenant()->id)
                        )
                    ->native(false)
                    ->required()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->headerActions([
                ExportAction::make()
                    ->exporter(CreditSalePaymentExporter::class)
            ])
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Paid to'),
                Tables\Columns\TextColumn::make('paid')
                    ->numeric(),
                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->placeholder('--##--'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:m'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(CreditSalePaymentExporter::class)
                ]),
            ]);
    }
}

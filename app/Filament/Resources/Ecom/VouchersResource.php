<?php

namespace App\Filament\Resources\Ecom;

use App\Filament\Resources\Ecom\VouchersResource\Pages;
use App\Filament\Resources\Ecom\VouchersResource\RelationManagers;
use App\Models\Ecom\Vouchers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VouchersResource extends Resource
{
    protected static ?string $model = Vouchers::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Ecommerce';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Voucher Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\TextInput::make('code')
                            ->label('Voucher Code')
                            ->required(),
                        Forms\Components\DateTimePicker::make('valid_to')
                            ->required(),
                        Forms\Components\Select::make('discount_type')
                            ->required()
                            ->options([
                                1 => 'Fixed Discount',
                                2 => 'Percentage Discount'
                            ]),
                        Forms\Components\TextInput::make('discount_value')
                            ->numeric()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\TextColumn::make('valid_to')
                    ->dateTime()
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
            'index' => Pages\ListVouchers::route('/'),
            'create' => Pages\CreateVouchers::route('/create'),
            'edit' => Pages\EditVouchers::route('/{record}/edit'),
        ];
    }
}

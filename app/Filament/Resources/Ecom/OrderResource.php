<?php

namespace App\Filament\Resources\Ecom;

use App\Filament\Resources\Ecom\OrderResource\Pages;
use App\Filament\Resources\Ecom\OrderResource\RelationManagers;
use App\Models\Ecom\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Ecommerce';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('full_name')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->required(),
                        Forms\Components\TextInput::make('telephone'),
                    ]),
                Forms\Components\Tabs::make('')
                    ->columnSpan(2)
                    ->tabs([
                        Tab::make('Shipping Address')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('shipping_address_line_one')
                                    ->required(),
                                Forms\Components\TextInput::make('shipping_address_line_two'),
                                Forms\Components\TextInput::make('shipping_town_city')
                                    ->required(),
                                Forms\Components\TextInput::make('shipping_county')
                                    ->required(),
                                Forms\Components\TextInput::make('shipping_postcode')
                                    ->required(),
                            ]),
                        Tab::make('Billing Address')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('billing_address_line_one'),
                                Forms\Components\TextInput::make('billing_address_line_two'),
                                Forms\Components\TextInput::make('billing_town_city'),
                                Forms\Components\TextInput::make('billing_county'),
                                Forms\Components\TextInput::make('billing_postcode'),
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#'),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Customer Name'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Customer Email'),
                Tables\Columns\TextColumn::make('shippingAddress.postcode')
                    ->label('Postcode'),
                Tables\Columns\TextColumn::make('value')
                    ->label('Order Value')
                    ->prefix('£'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
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
            RelationManagers\LineItemsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}

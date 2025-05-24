<?php

namespace App\Filament\Resources\Product;

use App\Filament\Resources\Product\ProductCollectionsResource\Pages;
use App\Filament\Resources\Product\ProductCollectionsResource\RelationManagers;
use App\Models\Ecom\ProductCollections;
use App\Models\Product\Product;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductCollectionsResource extends Resource {

    protected static ?string $model = ProductCollections::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Products';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Collection Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\TextInput::make('description'),
                        Forms\Components\FileUpload::make('image')
                            ->directory('product_collections')
                            ->image(),
                        Forms\Components\Select::make('suppliers')
                            ->required()
                            ->options(Supplier::query()->pluck('name', 'id'))
                            ->searchable()
                            ->reactive()
                            ->multiple(),
                        Forms\Components\Select::make('products')
                            ->required()
                            ->options(function (Forms\Get $get) {
                                $suppliers = $get('suppliers');

                                if (empty($suppliers)) {
                                    return [
                                        'Select A Supplier To Select Products'
                                    ];
                                }

                                return Product::query()->whereIn('brand', $suppliers)->pluck('name', 'id');
                            })
                            ->searchable()
                            ->multiple()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('name'),
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
            'index' => Pages\ListProductCollections::route('/'),
            'create' => Pages\CreateProductCollections::route('/create'),
            'edit' => Pages\EditProductCollections::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\Product;

use App\Filament\Resources\Product\CustomPropertiesResource\Pages;
use App\Filament\Resources\Product\CustomPropertiesResource\RelationManagers;
use App\Models\Product\CustomProperties;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomPropertiesResource extends Resource
{
    protected static ?string $model = CustomProperties::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Products';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Property Options')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name'),
                        Forms\Components\Toggle::make('display_in_filters')
                            ->inline(false),
                        Forms\Components\Toggle::make('display_on_product_page')
                            ->inline(false),
                        Forms\Components\Toggle::make('display_in_nav_menu')
                            ->inline(false),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('name')
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
            RelationManagers\PropertiesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomProperties::route('/'),
            'create' => Pages\CreateCustomProperties::route('/create'),
            'edit' => Pages\EditCustomProperties::route('/{record}/edit'),
        ];
    }
}

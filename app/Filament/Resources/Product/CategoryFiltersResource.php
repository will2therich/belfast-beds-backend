<?php

namespace App\Filament\Resources\Product;

use App\Filament\Resources\Product\CategoryFiltersResource\Pages;
use App\Filament\Resources\Product\CategoryFiltersResource\RelationManagers;
use App\Helper\StringHelper;
use App\Models\Product\CategoryFilters;
use App\Models\Product\PriceGroup;
use App\Models\Product\ProductCategory;
use App\Models\Product\Properties;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryFiltersResource extends Resource
{
    protected static ?string $model = CategoryFilters::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Products';

    public static function form(Form $form): Form
    {
        $availableOptions = [];

        $priceGroups = PriceGroup::query()->select('name')->groupBy('name')->get();
        $properties = Properties::query()->select('name')->groupBy('name')->get();

        foreach ($priceGroups as $priceGroup) {
            $availableOptions['price_group_' . StringHelper::generateSlug($priceGroup->name)] = $priceGroup->name;
        }

        foreach ($properties as $property) {
            $availableOptions['property_' . StringHelper::generateSlug($property->name)] = $property->name;
        }


        return $form
            ->schema([
                Forms\Components\Section::make('Filter Settings')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name'),
                        Forms\Components\Select::make('categories')
                            ->label('Category')
                            ->hint('Parent Categories Only')
                            ->multiple()
                            ->options(ProductCategory::whereNull('parent_category_id')->get()->pluck('name', 'id'))
                            ->searchable(),
                        Forms\Components\Select::make('option_name')
                            ->label('Filter On')
                            ->options($availableOptions)
                            ->searchable(),
                        Forms\Components\Repeater::make('options')
                            ->columns(2)
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\TextInput::make('label')
                                    ->hint('The name shown on the filter list, add {{ category }} to add category name to label'),
                                Forms\Components\TextInput::make('search')
                                    ->hint('The name of the option, partial names accepted')
                            ])
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategoryFilters::route('/'),
            'create' => Pages\CreateCategoryFilters::route('/create'),
            'edit' => Pages\EditCategoryFilters::route('/{record}/edit'),
        ];
    }
}

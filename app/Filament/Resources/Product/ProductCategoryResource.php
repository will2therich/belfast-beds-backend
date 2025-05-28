<?php

namespace App\Filament\Resources\Product;

use App\Filament\Resources\Product\ProductCategoryResource\Pages;
use App\Filament\Resources\Product\ProductCategoryResource\RelationManagers;
use App\Models\Product\ProductCategory;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductCategoryResource extends Resource
{

    protected static ?string $model = ProductCategory::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Products';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Category Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name'),
                        Forms\Components\TextInput::make('slug'),
                        Forms\Components\Toggle::make('enabled')
                            ->inline(false),
                        FileUpload::make('image')
                            ->image()
                            ->directory('product_categories')
                            ->imageEditor(),
                    ]),
                Forms\Components\Section::make('Featured Sections')
                    ->schema([
                        Forms\Components\Repeater::make('featured_sections')
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\TextInput::make('description')
                                    ->required(),
                                Forms\Components\TextInput::make('url')
                                    ->label('path')
                                    ->required(),
                                Forms\Components\ColorPicker::make('backgroundColor')
                                    ->hint('Requires either background colour OR background image'),
                                FileUpload::make('backgroundImage')
                                    ->image(),
                                Forms\Components\ColorPicker::make('textColor')
                                    ->default('#FFF'),
                                Forms\Components\ColorPicker::make('buttonBackgroundColor')
                                    ->default('#FFF'),
                                Forms\Components\ColorPicker::make('buttonTextColor')
                                    ->default('#092540'),
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rs_id')
                    ->label('ID'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('parentCategory.name'),
                Tables\Columns\TextColumn::make('enabled')
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
            'index' => Pages\ListProductCategories::route('/'),
            'create' => Pages\CreateProductCategory::route('/create'),
            'edit' => Pages\EditProductCategory::route('/{record}/edit'),
        ];
    }
}

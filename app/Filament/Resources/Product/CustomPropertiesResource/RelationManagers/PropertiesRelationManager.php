<?php

namespace App\Filament\Resources\Product\CustomPropertiesResource\RelationManagers;

use App\Helper\StringHelper;
use App\Models\Product\CustomPropertiesOptions;
use App\Models\Product\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use TomatoPHP\FilamentIcons\Components\IconPicker;

class PropertiesRelationManager extends RelationManager
{
    protected static string $relationship = 'options';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->required(),
                IconPicker::make('icon'),
                Forms\Components\Textarea::make('description')
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function ($data) {
                        $data['slug'] = StringHelper::generateSlug($data['name']);

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('Assign To Products')
                    ->form([
                        Forms\Components\Select::make('products')
                            ->options(fn (Model $record) => Product::query()->take(10)->pluck('name', 'id'))
                            ->getSearchResultsUsing(fn (string $search, Model $record) => Product::query()->where('name', 'like', "%{$search}%")->take(50)->pluck('name', 'id'))
                            ->getOptionLabelUsing(fn ($value): ?string => Product::find($value)?->name)
                            ->multiple()
                    ])
                    ->action(function (CustomPropertiesOptions $record, $data) {

                        if (isset($data['products'])) {
                            $productObjs = Product::whereIn('id', $data['products'])->get();

                            foreach ($productObjs as $productObj) {
                                $productObj->customProperties()->syncWithoutDetaching($record);
                            }
                            dd($productObjs);
                        }
                        dd($record, $data);
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

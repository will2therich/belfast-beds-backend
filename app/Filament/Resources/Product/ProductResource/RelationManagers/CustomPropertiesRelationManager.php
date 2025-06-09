<?php

namespace App\Filament\Resources\Product\ProductResource\RelationManagers;

use App\Models\Product\CustomProperties;
use App\Models\Product\CustomPropertiesOptions;
use App\Models\Product\Product;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomPropertiesRelationManager extends RelationManager
{
    protected static string $relationship = 'customProperties';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('customProperty.name')
                    ->label('Property'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Value'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('Link Property')
                    ->form([
                        Forms\Components\Select::make('custom_property')
                            ->options(CustomProperties::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('custom_property_option')
                            ->options(function (Forms\Get $get) {
                                if (empty($get('custom_property'))) {
                                    return [
                                        'Select a Property First'
                                    ];
                                } else {
                                    return CustomPropertiesOptions::where('custom_property_id', $get('custom_property'))->get()->pluck('name', 'id');
                                }
                            })
                            ->required()
                            ->searchable()
                    ])
                    ->action(function ($data) {

                        $record = $this->getOwnerRecord();

                        try {
                            $customPropertyOption = CustomPropertiesOptions::find($data['custom_property_option']);

                            if ($customPropertyOption instanceof CustomPropertiesOptions) {
                                $record->customProperties()->attach($customPropertyOption);
                                Notification::make('Property Linked');
                            }


                        } catch (\Exception $e) {
                            Notification::make('Failed To Link Property');
                        }
                    })
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

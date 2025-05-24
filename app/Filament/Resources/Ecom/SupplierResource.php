<?php

namespace App\Filament\Resources\Ecom;

use App\Filament\Resources\Ecom;
use App\Filament\Resources\Ecom\SupplierResource\Pages;
use App\Filament\Resources\Ecom\SupplierResource\RelationManagers;
use App\Models\Supplier;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationGroup = 'Ecommerce';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Supplier Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name'),
                        Placeholder::make('rs_id')
                            ->label('Retail System ID')
                            ->content(fn (Supplier $record): string => $record->rs_id),
                        FileUpload::make('image')
                            ->image()
                            ->directory('suppliers')
                            ->imageEditor(),
                        FileUpload::make('banner_image')
                            ->image()
                            ->directory('suppliers')
                            ->imageEditor(),
                        RichEditor::make('description')
                            ->hint('Shows on the brand page as a description about the brand')
                            ->columnSpan(2)
                            ->fileAttachmentsDirectory('suppliers/descriptions')
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rs_id')
                    ->label('#'),
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
            'index' => Ecom\SupplierResource\Pages\ListSuppliers::route('/'),
            'create' => Ecom\SupplierResource\Pages\CreateSupplier::route('/create'),
            'edit' => Ecom\SupplierResource\Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}

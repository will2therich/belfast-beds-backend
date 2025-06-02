<?php

namespace App\Filament\Resources\Core;

use App\Filament\Resources\Core\PagesResource\Pages;
use App\Filament\Resources\Core\PagesResource\RelationManagers;
use App\Models\Core\Pages as PageModel;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PagesResource extends Resource
{
    protected static ?string $model = PageModel::class;

    protected static ?string $navigationGroup = 'Core';
    protected static ?string $navigationIcon = 'iconoir-page';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Page Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\Placeholder::make('slug')
                            ->content(fn (PageModel $record): string => $record->slug),
                        Forms\Components\Toggle::make('enabled'),
                        Forms\Components\Toggle::make('show_in_footer'),
                        Forms\Components\RichEditor::make('content')
                            ->columnSpan(2)
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('slug'),
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePages::route('/create'),
            'edit' => Pages\EditPages::route('/{record}/edit'),
        ];
    }
}

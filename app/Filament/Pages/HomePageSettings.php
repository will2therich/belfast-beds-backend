<?php

namespace App\Filament\Pages;

use App\Helper\StringHelper;
use App\Models\Settings;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Guava\FilamentIconPicker\Forms\IconPicker;
use RalphJSmit\Filament\MediaLibrary\Forms\Components\MediaPicker;

class HomePageSettings extends Page
{

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.settings-page';
    protected static ?string $navigationGroup = 'Settings';

    public $data;

    public function mount() {
        $settings = Settings::get();
        $fillArr = [];

        foreach ($settings as $setting) {
            $value = $setting->value;
            if (StringHelper::isJson($value)) $value = json_decode($value, 1);

            $fillArr[$setting->key] = $value;
        }


        $this->settingsForm->fill($fillArr);
    }


    protected function getForms(): array
    {
        return array_merge(parent::getForms(), [
            "settingsForm" => $this->makeForm()->schema($this->getFormSchemaFn())->statePath('data')
        ]);
    }

    private function getFormSchemaFn() {

        return [
            Section::make('Home Page Details')
                ->schema([
                    Repeater::make('homeHeroSlides')
                        ->label('Hero Slides')
                        ->collapsible()
                        ->columns(3)
                        ->schema([
                            TextInput::make('title')
                                ->required(),
                            TextInput::make('description')
                                ->required(),
                            MediaPicker::make('image')
                                ->grow(),
                            TextInput::make('buttonText')
                                ->required(),
                            TextInput::make('buttonUrl')
                                ->required()
                        ]),
                    Repeater::make('features')
                        ->columns(3)
                        ->collapsible()
                        ->grid()
                        ->schema([
                            TextInput::make('title')
                                ->required(),
                            TextInput::make('description')
                                ->required(),
                            IconPicker::make('icon')
                        ]),
                    Repeater::make('promoBlocks')
                        ->label('Promotional Blocks')
                        ->addActionLabel('Add Promo Block')
                        ->collapsible()
                        ->reorderableWithButtons()
                        ->schema([
                            Select::make('type')
                                ->options([
                                    'imageWithGradientText' => 'Image with Gradient Text',
                                    'solidColorBanner' => 'Solid Color Banner',
                                    'imageWithBadge' => 'Image with Badge',
                                ])
                                ->required()
                                ->live() // This is key for dynamic fields
                                ->afterStateUpdated(fn (callable $set) => $set('badge', null)), // Reset badge state when type changes

                            // Common Fields
                            TextInput::make('url')
                                ->label('URL (path)')
                                ->helperText('Enter the path to redirect too.'),

                            //== Fields for 'imageWithGradientText' ==
                            Grid::make(2)
                                ->visible(fn (Get $get) => $get('type') === 'imageWithGradientText')
                                ->schema([
                                    MediaPicker::make('imageUrl')
                                        ->label('Background Image')
                                        ->required(),
                                    TextInput::make('altText')
                                        ->label('Image Alt Text')
                                        ->required(),
                                    Textarea::make('title')
                                        ->label('Title')
                                        ->helperText('Use <br> for line breaks.')
                                        ->required(),
                                    ColorPicker::make('gradientColor')
                                        ->label('Gradient Start Color')
                                        ->required(),
                                    ColorPicker::make('textColor')
                                        ->label('Text Color')
                                        ->required(),
                                ]),

                            //== Fields for 'solidColorBanner' ==
                            Grid::make(2)
                                ->visible(fn (Get $get) => $get('type') === 'solidColorBanner')
                                ->schema([
                                    TextInput::make('title')
                                        ->label('Title')
                                        ->required(),
                                    TextInput::make('subtitle')
                                        ->label('Subtitle'),
                                    ColorPicker::make('backgroundColor')
                                        ->label('Background Color')
                                        ->required(),
                                    ColorPicker::make('textColor')
                                        ->label('Text Color')
                                        ->required(),
                                ]),

                            //== Fields for 'imageWithBadge' ==
                            Grid::make(2)
                                ->visible(fn (Get $get) => $get('type') === 'imageWithBadge')
                                ->schema([
                                    MediaPicker::make('imageUrl')
                                        ->label('Background Image')
                                        ->required(),
                                    TextInput::make('altText')
                                        ->label('Image Alt Text')
                                        ->required(),
                                ]),

                            //== Nested Repeater for 'badge' fields ==
                            Grid::make(1)
                                ->visible(fn (Get $get) => $get('type') === 'imageWithBadge')
                                ->schema([
                                    Grid::make(3)
                                        ->schema([
                                            TextInput::make('badge_line1')
                                                ->label('Badge Line 1'),
                                            TextInput::make('badge_mainText')
                                                ->label('Badge Main Text')
                                                ->required(),
                                            TextInput::make('badge_line2')
                                                ->label('Badge Line 2'),
                                        ]),
                                    Grid::make(2)
                                        ->schema([
                                            ColorPicker::make('badge_backgroundColor')
                                                ->label('Badge Background Color')
                                                ->required(),
                                            ColorPicker::make('badge_textColor')
                                                ->label('Badge Text Color')
                                                ->required(),
                                        ]),
                                ]),
                        ])
                        ->columnSpanFull(),
                    Section::make('Promotional Banner')
                        ->schema([
                            Toggle::make('promotional_active')
                                ->label('Active')
                                ->inline(false),
                            TextInput::make('promotional_title')
                                ->label('Title'),
                            TextInput::make('promotional_text')
                                ->label('Text'),
                            ColorPicker::make('promotional_backgroundColour')
                                ->label('Background Colour'),
                            DateTimePicker::make('promotional_endDate')
                                ->label('End Date'),
                        ])
                ]),

        ];
    }

    public function saveForm() {
        $this->settingsForm->validate();
        Cache::forget('home-data');

        foreach ($this->data as $key => $value) {

            $setting = Settings::where('key', $key)->first();

            if (!$setting instanceof Settings) {
                $setting = new Settings;
                $setting->key = $key;
            }

            if (is_array($value)) $value = json_encode($value);
            $setting->value = $value;
            $setting->save();
        }

        return true;
    }

}

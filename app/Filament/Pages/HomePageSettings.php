<?php

namespace App\Filament\Pages;

use App\Helper\StringHelper;
use App\Models\Settings;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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

            if (StringHelper::isJson($value)) {
                $value = json_decode($value, 1);

                foreach ($value as &$option) {
                    if (isset($option['image']) && is_array($option['image'])) {
                        $option['image'] = $option['imageUrl'];
                    }
                }
            }

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
                        ->columns(3)
                        ->schema([
                            TextInput::make('title')
                                ->required(),
                            TextInput::make('description')
                                ->required(),
                            FileUpload::make('image')
                                ->directory('hero_slides')
                                ->image()
                                ->imageEditor(),
                            TextInput::make('buttonText')
                                ->required(),
                            TextInput::make('buttonUrl')
                                ->required()
                        ])
                ]),

        ];
    }

    public function saveForm() {
        foreach ($this->data as $key => $value) {
            if ($key == 'homeHeroSlides') {
                foreach ($value as $slideKey =>  $value2) {
                    foreach ($value2 as $key => $value3) {
                        if ($key == 'image') {
                            foreach ($value3 as $key => $image) {
                                if ($image instanceof TemporaryUploadedFile) {

                                    /** @var TemporaryUploadedFile $image */
                                    Storage::put('public/hero_image/' . $image->getFilename(), $image->getContent());
                                    $updatedValue = '/hero_image/' . $image->getFilename();
                                    $this->data['homeHeroSlides'][$slideKey]['imageUrl'] = $updatedValue;
                                }
                            }
                        }
                    }
                }
            }
        }

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

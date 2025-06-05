<?php

namespace App\Helper;

use Illuminate\Support\Facades\Cache;
use RalphJSmit\Filament\MediaLibrary\Media\Models\MediaLibraryItem;

class ImageHelper
{


    public static function getImageUrl($id)
    {
        return Cache::remember('media-id-' . $id, now()->addDay(), function () use ($id) {
            $mediaLibraryItem = MediaLibraryItem::find($id);

            $spatieMediaModel = $mediaLibraryItem->getItem();
            $path = $spatieMediaModel->getUrl();

            return $path;
        });
    }
}

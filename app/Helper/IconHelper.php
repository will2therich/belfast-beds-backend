<?php

namespace App\Helper;

use BladeUI\Icons\Factory;

class IconHelper
{

    public static function generateSvgIcon($iconName) {
        if (empty($iconName)) return '';
        $iconFactory = app(Factory::class);
        try {
            return $iconFactory->svg($iconName)->toHtml();
        } catch (\Exception $e) {
            // Handle the case where the icon does not exist
            return null;
        }
    }
}

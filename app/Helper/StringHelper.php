<?php

namespace App\Helper;

class StringHelper
{


    public static function generateSlug($string)
    {
        return str_replace(['/', ' '], '_',strtolower(trim($string)));
    }
}

<?php

namespace App\Helper;

class StringHelper
{


    public static function generateSlug($string)
    {
        return str_replace(['/', ' '], '_',strtolower(trim($string)));
    }

    /**
     * Determines if a string is valid JSON.
     *
     * @param string $string The string to check.
     * @return bool True if the string is valid JSON, false otherwise.
     */
    public static function isJson($string) {
        if (is_null($string) || !is_string($string)) {
            return false;
        }

        // Decode the string using json_decode
        $decoded = json_decode($string);

        // Check if decoding was successful and if the result is not null (empty JSON is still valid)
        return (json_last_error() === JSON_ERROR_NONE) && (!is_null($decoded));
    }
}

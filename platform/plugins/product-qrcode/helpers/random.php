<?php
namespace Botble\ProductQrcode\Helpers;

use Botble\ProductQrcode\Models\ProductQrcode;

class random
{
    public static function generateUniqueIdentifier()
    {
        $numbers = mt_rand(10, 99);

        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        $randomCharacters = '';
        for ($i = 0; $i < 6; $i++) {
            $index = mt_rand(0, strlen($characters) - 1);
            $randomCharacters .= $characters[$index];
        }

        $uniqueIdentifier = $numbers . $randomCharacters;

        return $uniqueIdentifier;
    }
}

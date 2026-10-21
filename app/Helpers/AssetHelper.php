<?php


namespace App\Helpers;

function public_asset($path, $secure = null)
{
    // Generate the asset URL using the built-in asset() function
    $assetUrl = asset($path, $secure);

    // Replace '/public/' in the asset URL with an empty string to remove it
    $assetUrlWithoutPublic = str_replace('public/', 'storage/', $assetUrl);

    return $assetUrlWithoutPublic;
}

function image_url($path)
{
    return env('APP_URL') . '/storage/' . $path;
}

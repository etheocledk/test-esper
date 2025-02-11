<?php

use Illuminate\Support\Str;

if (!function_exists('folderOpen')) {
    function folderOpen($folderPath, $permissions = 0777)
    {
        if (!file_exists($folderPath)) {
            mkdir($folderPath, $permissions, true);
        }
    }
}

if (!function_exists('uploadImage')) {
    function uploadImage($img, $name, $path)
    {
        $extension = $img->getClientOriginalExtension();

        //$folderName = time() . '-' . Str::slug($name);

        $uniqueName = time() . '-' . uniqid() . '-' . Str::slug($name);

        if (in_array($extension, ['pdf', 'svg', 'webp', 'jiff'])) { // Process based on file extension
            $img->move(public_path($path), $uniqueName . '.' . $extension);

            $imgurl = $path . $uniqueName . '.' . $extension;
        } else {
            $img = Image::make($img);

            $img->resize(640, 735, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $img->encode('webp', 75)->save($path . $uniqueName . '.webp');

            $imgurl = $path . $uniqueName . '.webp';
        }
        return $imgurl;
    }
}

if (!function_exists('deleteFile')) {
    function deleteFile($filePath)
    {
        if (file_exists($filePath)) {
            if (!empty($filePath)) {
                unlink($filePath);
            }
        }
    }
}

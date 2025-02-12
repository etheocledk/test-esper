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
    function uploadImage($file, $name, $path)
    {
        // Assurer que le chemin a un "/" à la fin
        $path = rtrim($path, '/') . '/';

        // Récupérer l'extension et le MIME type
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();

        // Générer un nom de fichier unique
        $uniqueName = time() . '-' . uniqid() . '-' . Str::slug($name) . '.' . $extension;

        if (str_starts_with($mimeType, 'image/')) { // Process based on file extension
            folderOpen($path);

            $img = Image::make($file);

            $img->resize(640, 735, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $img->encode('webp', 75)->save($path . $uniqueName . '.webp');

            $imgurl = $path . $uniqueName . '.webp';
            return $imgurl;
        } elseif (str_starts_with($mimeType, 'video/')) {
            folderOpen($path);

            $file->move(public_path($path), $uniqueName);
            return $path . $uniqueName;
        } elseif (in_array($extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'])) {

            folderOpen($path);

            $file->move(public_path($path), $uniqueName);
            return $path . $uniqueName;
        }

        return null;
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

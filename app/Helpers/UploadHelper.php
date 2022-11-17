<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Class UploadFile
 * @author Huy Developer
 */

class UploadHelper
{

    public static function createFile($content, $path, $fileName): ?string
    {
        try {
            Storage::put($fullPath =  $path . '/' . $fileName, $content);
            return $fullPath;
        } catch (\Throwable $th) {
            Log::error("UploadHelper ::: createFile", [
                'message'   => $th->getMessage()
            ]);
        }

        return null;
    }

    /**
     * upload file to server
     * @param $file
     * @param $path
     * @param $name
     * @return string
     */
    public static function uploadFile($file, $path, $old_url = null, $fileName = null): string
    {
        try {
            if (isset($file) && !empty($file)) {
                if (!is_null($old_url)) static::removeFileUrl($old_url);
                $fileName = $fileName != null ? $fileName : static::renameFile($file);
                $fileName = $fileName . '.' . $file->getClientOriginalExtension();
                $file->move($path, $fileName);
                return $path . '/' . $fileName;
            }
            return "";
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * remove file from server if exists
     * @param $file_url
     *  @return void
     */
    public static function removeFileUrl($file_url): void
    {
        if (file_exists($file_url)) {
            unlink($file_url);
        }
    }

    /**
     *  rename file to unique names
     * @param $file
     * @return string
     */
    public static function renameFile($file): string
    {
        $fileName = date('YmdHis') . '_' . $file->getClientOriginalName() . '.' . $file->getClientOriginalExtension();
        return $fileName;
    }

    /**
     *  check is file
     * @param $file
     * @return bool
     */
    public static function isFile($file): bool
    {
        return isset($file) && $file != null && $file->isValid();
    }

    // /**
    //  * getFileName
    //  *
    //  * @param  mixed $file
    //  * @return string
    //  */
    // public static function getFileName($file): string
    // {
    //     $fileNameArr = ArrayUtils::stringToArray($file->getClientOriginalName(), ".");
    //     return count($fileNameArr) > 0 ? $fileNameArr[0] : "";
    // }

    /**
     * getNameUnique
     *
     * @param  string $fileName
     * @return string
     */
    public static function getNameUnique($fileName): string
    {
        return $fileName . "_" . date('YmdHis');
    }
}

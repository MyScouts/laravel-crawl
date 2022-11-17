<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadFileController extends Controller
{
    public function download(Request $request)
    {

        if ($filePath = $request->query('file')) {
            if (Storage::exists($filePath)) {
                return Storage::download($filePath);
            }
        }

        return back()->with(['message' => 'File is not found!']);
    }
}

<?php

use App\Http\Controllers\CrawlController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UploadFileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [CrawlController::class, 'onCrawl']);

Auth::routes();
Route::redirect('', 'home');

Route::group([
    'middleware' => 'auth'
], function () {
    Route::get('/home',         [HomeController::class, 'index'])->name('home');
    Route::get('/crawl-data',   [CrawlController::class, 'onCrawl'])->name('crawlData');
    Route::get('/download',     [UploadFileController::class, 'download'])->name('downloadFile');
});

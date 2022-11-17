<?php

namespace App\Http\Controllers;

use App\Models\CrawlHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $lastCrawl = CrawlHistory::select('finished_date', 'file')->latest()->first();

        $crawls = CrawlHistory::orderBy('started_date', 'DESC')
            ->paginate(10);

        return view('home', compact('lastCrawl', 'crawls'));
    }
}

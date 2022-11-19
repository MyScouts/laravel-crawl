<?php

namespace App\Http\Controllers;

use App\Models\CrawlHistory;
use App\Models\JobBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        $job = DB::table("job_batches")->latest()->first();
        try {
            $bus = Bus::findBatch($job->id);
        } catch (\Throwable $th) {
            $bus = null;
        }
        $lastCrawl = CrawlHistory::select('finished_date', 'file')
            ->whereNotNull('finished_date')
            ->latest()
            ->first();
        $crawls = CrawlHistory::whereNotNull('finished_date')
            ->orderBy('started_date', 'DESC')
            ->paginate(10);
        return view('home', compact('lastCrawl', 'crawls', 'job', 'bus'));
    }

    public function getStatus()
    {
        $job = DB::table("job_batches")->latest()->first();
        try {
            $bus = Bus::findBatch($job->id);
            return response()->json(['data' => $bus]);
        } catch (\Throwable $th) {
            return response()->json(['data' => null]);
        }
    }
}

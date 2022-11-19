<?php

namespace App\Console\Commands;

use App\Events\CallCrawlEvent;
use App\Exports\CrawlExport;
use App\Jobs\CrawlUrlJob;
use App\Jobs\VerifyErrorUrlJob;
use App\Models\CrawlHistory;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CrawlDaily extends Command
{
    const SETTING_KEY = 'crawl_daily';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:crawl-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        event(new CallCrawlEvent());
    }
}

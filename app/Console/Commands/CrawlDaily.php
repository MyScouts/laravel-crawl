<?php

namespace App\Console\Commands;

use App\Exports\CrawlExport;
use App\Jobs\CrawlUrlJob;
use App\Jobs\VerifyErrorUrlJob;
use App\Models\CrawlHistory;
use Carbon\Carbon;
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
        $startTask = Carbon::now();
        $urlCrawl = $this->initUrl();
        $urlCrawl = array_slice($urlCrawl, 0, 100);
        $history = CrawlHistory::create([
            'total_task'    => count($urlCrawl),
            'started_date'  => $startTask
        ]);
        if (count($urlCrawl) > 0) {
            foreach ($urlCrawl as $url) {
                $url = substr($url, strpos($url, ';') + 1);
                $jobs[] = new CrawlUrlJob($url, $history->id);
            }

            Bus::batch($jobs)
                ->name('Processing crawl data form url')
                ->finally(function () use ($history) {
                    $historyId = $history->id;
                    $crawlHistory = CrawlHistory::find($historyId);
                    // processing job is completed
                    if ($crawlHistory->task_done == $crawlHistory->total_task) {
                        return $this->handleCompletedJob($historyId);
                    }
                    return $this->handleHasErrorJobs($historyId);
                })
                ->dispatch();
        }
    }

    /**
     * handleHasErrorJobs
     *
     * @param  mixed $historyId
     * @return void
     */
    private function handleHasErrorJobs($historyId)
    {
        // Processing error urls
        $errorFile = CrawlHistory::ERROR_FILE_PATH . "/$historyId.txt";
        if (!Storage::exists($errorFile)) return;
        $fileContent = Storage::get($errorFile);
        $errorUrls = explode("\n", $fileContent);
        foreach ($errorUrls as $key => $url) {
            $jobs[] = (new VerifyErrorUrlJob($url, $historyId, $key))->delay(Carbon::now()->addMinutes(30));
        }

        if (count($jobs) > 0) {
            Bus::batch($jobs)
                ->name("Verify error crawl url job")
                ->finally(function () use ($historyId) {
                    return $this->handleCompletedJob($historyId);
                })->dispatch();
            return;
        }

        return $this->handleCompletedJob($historyId);
    }

    /**
     * handleCompletedJob
     *
     * @param  mixed $historyId
     * @param  mixed $tempFile
     * @return void
     */
    private function handleCompletedJob($historyId)
    {
        Log::info("handleCompletedJob");
        $tempFile = CrawlHistory::TEMP_FILE_PATH . '/' . $historyId . '.txt';
        $dataUpdate = ['finished_date' => Carbon::now()];

        if (Storage::exists($tempFile)) {
            $content = json_decode(Storage::get($tempFile));
            $export = new CrawlExport($content);
            $filePath =  CrawlHistory::FILE_PATH . "/" . date('Ymdhis') . "/" . CrawlHistory::FILE_NAME;
            CrawlHistory::where('id', $historyId)->update(['file' => $filePath]);
            Excel::store($export, $filePath);
            Storage::delete($tempFile);
            $dataUpdate['file'] = $filePath;
        }

        $errorFilePath = CrawlHistory::ERROR_FILE_PATH . "/" . "$historyId.txt";

        if (Storage::exists($errorFilePath)) {
            $newErrorFile = CrawlHistory::ERROR_FILE_PATH . "/" . date("Ymdhis") . ".txt";
            Storage::move($errorFilePath, $newErrorFile);
            $dataUpdate['file_error'] = $newErrorFile;
        }

        CrawlHistory::where('id', $historyId)->update($dataUpdate);
    }
}
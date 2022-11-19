<?php

namespace App\Http\Controllers;

use App\Exports\CrawlExport;
use App\Jobs\CrawlUrlJob;
use App\Jobs\VerifyErrorUrlJob;
use App\Models\CrawlHistory;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class CrawlController extends Controller
{


    private function initUrl()
    {
        $dataUrl = "https://www.leasinger.de/todolist/1";

        $client = new Client(['allow_redirects' => ['track_redirects' => true], 'verify' => false]);

        $request = new Request('GET', $dataUrl);
        $res = $client->sendAsync($request)->wait();

        $htmlStr = strip_tags($res->getBody()->getContents());
        return explode("\n", $htmlStr);
    }

    /**
     * onCrawl
     *
     * @return void
     */
    public function onCrawl()
    {
        try {
            $startTask = Carbon::now();
            $urlCrawl = $this->initUrl();
            $urlCrawl = array_slice($urlCrawl, 0, 500);
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
                return back()->with(['message' => 'Crawl is processing!']);
            }

            return back()->with(['message' => 'Not found urls for crawl!']);
        } catch (\Throwable $th) {
            return back()->with(['message' => $th->getMessage()]);
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
            $jobs[] = new VerifyErrorUrlJob($url, $historyId, $key);
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

    /**
     * onCrawlUrls
     *
     * @return void
     */
    public function onCrawlUrls()
    {
        $startTask = Carbon::now();

        $urlCrawl = $this->initUrl();
        $urlCrawl = array_slice($urlCrawl, 0, 5000);
        $history = CrawlHistory::create([
            'total_task'    => count($urlCrawl),
            'started_date'  => $startTask
        ]);
        if (count($urlCrawl) > 0) {
            foreach (array_chunk($urlCrawl, 10) as $urls) {
                $jobs[] = new CrawlUrlJob($urls, $history->id);
            }

            Bus::batch($jobs)
                ->name('Processing crawl data form url')
                ->dispatch();
            return back()->with(['message' => 'Crawl is processing!']);
        }
        return back()->with(['message' => 'Not found urls for crawl!']);
    }
}

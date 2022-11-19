<?php

namespace App\Jobs;

use App\Helpers\CrawlHelper;
use App\Models\CrawlHistory;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VerifyErrorUrlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    private string $url;
    private int $historyId;
    private int $index;

    // /**
    //  * The number of times the job may be attempted.
    //  *
    //  * @var int
    //  */
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $url, int $historyId, int $index)
    {
        $this->url = $url;
        $this->index = $index;
        $this->historyId = $historyId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = CrawlHelper::processingCrawl($this->url);
        // remove error url form file
        $errorFilePath = CrawlHistory::ERROR_FILE_PATH . "/" . "{$this->historyId}.txt";
        if (Storage::exists($errorFilePath)) {
            $content = json_decode(Storage::get($errorFilePath));
            $errorUrls = explode("\n", $content);
            if (isset($errorUrls[$this->index])) {
                unset($errorUrls[$this->index]);
                Storage::put($errorFilePath, implode("\n", $errorUrls));
            }
        }

        // Save data to file
        $fileUrl = CrawlHistory::TEMP_FILE_PATH . '/' . $this->historyId . '.txt';
        $content = [];
        if (Storage::exists($fileUrl)) {
            $content = json_decode(Storage::get($fileUrl));
        }
        $content = array_merge($content, $data);
        Storage::put($fileUrl, json_encode($content));
        $crawlHistory = CrawlHistory::find($this->historyId);
        CrawlHistory::where('id', $this->historyId)
            ->update([
                'task_fail' => $crawlHistory->task_fail - 1,
                'task_done' => $crawlHistory->task_done + 1
            ]);
    }

    /**
     * failed
     *
     * @param  mixed $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        Log::error("CrawlController ::: callAPI", [
            'url'       => $this->url,
            'message'   => $exception->getMessage(),
            'historyId' => $this->historyId
        ]);
    }
}

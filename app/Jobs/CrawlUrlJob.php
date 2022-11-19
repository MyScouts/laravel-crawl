<?php

namespace App\Jobs;

use App\Exports\CrawlExport;
use App\Helpers\CrawlHelper;
use App\Helpers\UploadHelper;
use App\Models\CrawlHistory;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CrawlUrlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    const STATUS_FAIL = 'success';
    const STATUS_SUCCESS = 'fail';

    private string $url;
    private int $historyId;


    public $timeout = 12000;

    // /**
    //  * The number of times the job may be attempted.
    //  *
    //  * @var int
    //  */
    // public $tries = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $url, string $historyId)
    {
        $this->url = $url;
        $this->historyId = $historyId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $result = [];
        $data = CrawlHelper::processingCrawl($this->url);
        if ($data != null) $result[] = $data;
        $this->updateHistory(CrawlUrlJob::STATUS_SUCCESS, $result);
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
        $this->updateHistory(CrawlUrlJob::STATUS_FAIL);
        UploadHelper::appendContent(CrawlHistory::ERROR_FILE_PATH . "/" . "{$this->historyId}.txt", $this->url);
    }

    /**
     * updateHistory
     *
     * @param  mixed $status
     * @param  mixed $data
     * @return void
     */
    private function updateHistory($status, $data = null)
    {
        $crawlHistory = CrawlHistory::find($this->historyId);
        $dataUpdate = [];

        // Update info
        $dataUpdate['task_done'] = $status == CrawlUrlJob::STATUS_SUCCESS ? $crawlHistory->task_done + 1 : $crawlHistory->task_done;
        $dataUpdate['task_fail'] = $status == CrawlUrlJob::STATUS_FAIL ? $crawlHistory->task_fail + 1 : $crawlHistory->task_fail;
        CrawlHistory::where('id', $this->historyId)->update($dataUpdate);

        //
        if ($status == CrawlUrlJob::STATUS_SUCCESS) {
            $fileUrl = CrawlHistory::TEMP_FILE_PATH . '/' . $this->historyId . '.txt';
            $content = [];
            if (Storage::exists($fileUrl)) {
                $content = json_decode(Storage::get($fileUrl));
            }
            $content =  array_merge($content, $data);
            Storage::put($fileUrl, json_encode($content));
            return;
        }
    }
}

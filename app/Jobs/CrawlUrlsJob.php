<?php

namespace App\Jobs;

use App\Exports\CrawlExport;
use App\Helpers\CrawlHelper;
use App\Models\CrawlHistory;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CrawlUrlsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    const STATUS_FAIL = 'success';
    const STATUS_SUCCESS = 'fail';

    private array $urls;
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
    public function __construct(array $urls, string $historyId)
    {
        $this->urls = $urls;
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
        foreach ($this->urls as $url) {
            $url = substr($url, strpos($url, ';') + 1);
            $data = CrawlHelper::processingCrawl($url);
            if ($data != null) $result[] = $data;
        }
        $this->updateHistory(CrawlUrlJob::STATUS_SUCCESS, $result);
    }

    public function failed(Exception $exception)
    {
        // Create log file
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
        $dataUpdate['task_done'] = $crawlHistory->task_done + count($data);
        $dataUpdate['task_fail'] = $crawlHistory->task_fail + (count($this->urls) - count($data));

        $currentTask = $dataUpdate['task_done']  + $dataUpdate['task_fail'];

        $isDone = ($currentTask == $crawlHistory->total_task);

        if ($isDone) $dataUpdate['finished_date'] = Carbon::now();

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

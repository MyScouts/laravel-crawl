<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrawlHistory extends Model
{
    use HasFactory;

    const FILE_PATH = 'crawls/histories';
    const FILE_NAME = 'leasing-data-crawling-availability-description.xlsx';

    const TEMP_FILE_PATH = 'crawls/temps';
    const ERROR_FILE_PATH = 'crawls/errors';

    protected $table = "crawl_histories";

    protected $fillable = [
        'id',
        'file',
        'name',
        'total_task',
        'task_done',
        'task_fail',
        'finished_date',
        'started_date',
        'file_error'
    ];
}

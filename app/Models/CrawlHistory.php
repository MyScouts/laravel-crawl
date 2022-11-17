<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrawlHistory extends Model
{
    use HasFactory;

    const FILE_PATH = 'crawls';
    const FILE_NAME = 'leasing-data-crawling-availability-description.xlsx';

    protected $table = "crawl_histories";

    protected $fillable = [
        'id',
        'file',
        'name',
        'total_task',
        'task_done',
        'finished_date',
        'started_date'
    ];
}

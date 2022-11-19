<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crawl_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('file')->nullable();
            $table->text('file_error')->nullable();
            $table->string('name')->nullable();
            $table->bigInteger('total_task')->default(0);
            $table->bigInteger('task_done')->default(0);
            $table->bigInteger('task_fail')->default(0);
            $table->dateTime('finished_date')->nullable();
            $table->dateTime('started_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crawl_histories');
    }
};

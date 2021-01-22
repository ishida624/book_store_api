<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BookStoreOpenTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('bookStoreOpenTime', function (Blueprint $table) {
            $table->id();
            $table->string('storeName');
            $table->time('openTimeMon')->nullable();
            $table->time('closeTimeMon')->nullable();
            $table->time('openTimeTues')->nullable();
            $table->time('closeTimeTues')->nullable();
            $table->time('openTimeWed')->nullable();
            $table->time('closeTimeWed')->nullable();
            $table->time('openTimeThurs')->nullable();
            $table->time('closeTimeThurs')->nullable();
            $table->time('openTimeFri')->nullable();
            $table->time('closeTimeFri')->nullable();
            $table->time('openTimeSat')->nullable();
            $table->time('closeTimeSat')->nullable();
            $table->time('openTimeSun')->nullable();
            $table->time('closeTimeSun')->nullable();
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
        //
    }
}

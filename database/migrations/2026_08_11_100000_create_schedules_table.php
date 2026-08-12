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
        Schema::create('schedules', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('title');
            $table->date('date');
            $table->time('start_time');
            $table->time('lunch_start_time');
            $table->unsignedInteger('lunch_duration_minutes');
            $table->time('end_time');
            $table->timestamps();
            $table->softDeletes();

            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedules');
    }
};

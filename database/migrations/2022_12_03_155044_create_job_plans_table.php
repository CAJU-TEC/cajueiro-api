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
        Schema::create('job_plans', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('description')->nullable();
            $table->decimal('value')->nullable();
            $table->string('time')->nullable();
            $table->text('note')->nullable();
            $table->text('color')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_plans');
    }
};

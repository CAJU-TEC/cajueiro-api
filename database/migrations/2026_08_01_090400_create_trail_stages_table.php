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
        Schema::create('trail_stages', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('trail_id');
            $table->uuid('job_plan_id')->nullable();
            $table->string('description');
            $table->text('note')->nullable();
            $table->integer('position')->default(0);
            $table->integer('required_count')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['trail_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trail_stages');
    }
};

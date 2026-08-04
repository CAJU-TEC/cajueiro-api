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
        Schema::create('trail_stage_collaborator', function (Blueprint $table) {
            $table->uuid('trail_stage_id');
            $table->uuid('collaborator_id');
            $table->uuid('job_plan_id')->nullable();
            $table->uuid('completed_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('certificate_code')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['trail_stage_id', 'collaborator_id']);
            $table->index('certificate_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trail_stage_collaborator');
    }
};

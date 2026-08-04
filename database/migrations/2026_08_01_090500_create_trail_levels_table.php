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
        Schema::create('trail_levels', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('trail_stage_id');
            $table->string('description');
            $table->text('note')->nullable();
            $table->enum('type', [
                'task',
                'course',
                'platform',
                'technical_test',
                'other',
            ])->default('task');
            $table->integer('position')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['trail_stage_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trail_levels');
    }
};

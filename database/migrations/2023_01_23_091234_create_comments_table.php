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
        Schema::create('comments', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('collaborator_id')->nullable();
            $table->uuid('commentable_id')->nullable();
            $table->string('commentable_type')->nullable();
            $table->text('description');
            $table->enum('status', [
                'backlog',
                'todo',
                'analyze',
                'development',
                'test',
                'pending',
                'done',
            ])->default('backlog');
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
        Schema::dropIfExists('comments');
    }
};

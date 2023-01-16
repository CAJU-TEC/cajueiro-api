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
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('client_id');
            $table->foreignUuid('collaborator_id')->nullable();
            $table->foreignUuid('impact_id');
            $table->increments('code');
            $table->enum('priority', ['no', 'yes'])->default('no');
            $table->string('subject');
            $table->text('message');
            $table->enum('status', [
                'backlog',
                'todo',
                'analyze',
                'development',
                'test',
                'pending',
                'done',
            ])->default('backlog');
            $table->dateTime('date_attribute_ticket')->nullable();
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
        Schema::dropIfExists('tickets');
    }
};

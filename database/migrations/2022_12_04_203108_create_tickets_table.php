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
            $table->foreignUuid('collaborator_id');
            $table->foreignUuid('impact_id');
            $table->increments('code');
            $table->enum('priority', ['no', 'yes'])->default('no');
            $table->string('subject');
            $table->text('message');
            $table->enum('status', ['aberto', 'pendente', 'fechado'])->default('aberto');
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
        Schema::dropIfExists('tickets');
    }
};

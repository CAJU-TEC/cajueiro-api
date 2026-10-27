<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dumps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // sem FK em corporate_id e user_id: corporates.id e users.id não têm primary key neste schema
            $table->uuid('corporate_id')->index();
            $table->uuid('user_id')->index();
            $table->string('destino', 3); // dev | hmg
            $table->string('status', 20)->default('pendente'); // pendente | executando | concluido | falha
            $table->string('progresso')->nullable();
            $table->unsignedTinyInteger('percentual')->nullable();
            $table->unsignedBigInteger('bytes_copiados')->nullable();
            $table->unsignedBigInteger('bytes_estimados')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->longText('log')->nullable();
            $table->text('erro')->nullable();
            $table->timestamps();

            $table->index(['corporate_id', 'destino', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('dumps');
    }
};

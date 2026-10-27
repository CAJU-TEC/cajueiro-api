<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hosts e bancos de cada ambiente para a atualização DEV/HMG a partir de PROD.
     * "ativo" vale por enquanto só para esse módulo: só organizações ativas aparecem nele.
     * Começa false para todas; CorporatesBancosSeeder ativa e preenche os municípios.
     */
    public function up()
    {
        Schema::table('corporates', function (Blueprint $table) {
            $table->boolean('ativo')->default(false);
            $table->string('host_prod')->nullable();
            $table->string('banco_prod')->nullable();
            $table->string('host_dev')->nullable();
            $table->string('banco_dev')->nullable();
            $table->string('host_hmg')->nullable();
            $table->string('banco_hmg')->nullable();
        });
    }

    public function down()
    {
        Schema::table('corporates', function (Blueprint $table) {
            $table->dropColumn(['ativo', 'host_prod', 'banco_prod', 'host_dev', 'banco_dev', 'host_hmg', 'banco_hmg']);
        });
    }
};

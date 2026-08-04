<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Separa os níveis em soft skill e hard skill.
 *
 * Dimensão nova, e não reaproveitamento do `type`: aquele diz a natureza do
 * item (tarefa, curso, plataforma, teste técnico) e é ortogonal a isto — um
 * curso pode ser de comunicação (soft) ou de Docker (hard).
 *
 * Padrão `hard` porque os níveis já cadastrados são tarefas técnicas; marcar
 * tudo como soft exigiria revisão manual de cada um.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('trail_levels', function (Blueprint $table) {
            $table->enum('skill', ['soft', 'hard'])->default('hard')->after('type');
        });
    }

    public function down()
    {
        Schema::table('trail_levels', function (Blueprint $table) {
            $table->dropColumn('skill');
        });
    }
};

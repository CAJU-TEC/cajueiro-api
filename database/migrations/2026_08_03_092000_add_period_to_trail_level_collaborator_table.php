<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Prazo do nível, por matrícula.
 *
 * As datas ficam no pivô nível × colaborador de propósito: o mesmo nível tem
 * prazo diferente para cada colaborador matriculado, então guardar em
 * `trail_levels` valeria para todo mundo.
 *
 * Isso muda o sentido da linha do pivô: antes ela só existia para registrar
 * conclusão, agora também guarda o prazo de um nível que ainda não foi
 * concluído. Quem lê conclusão já filtra por `completed_at`, então essas
 * linhas "só com prazo" não contam como concluídas.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('trail_level_collaborator', function (Blueprint $table) {
            $table->date('starts_at')->nullable()->after('collaborator_id');
            $table->date('ends_at')->nullable()->after('starts_at');
        });
    }

    public function down()
    {
        Schema::table('trail_level_collaborator', function (Blueprint $table) {
            $table->dropColumn(['starts_at', 'ends_at']);
        });
    }
};

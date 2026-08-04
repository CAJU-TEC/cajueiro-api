<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Avaliação do nível (R9) e envio pelo colaborador (R10).
 *
 * `cut_score` fica no nível porque a nota de corte é da atividade, igual para
 * todo mundo. `score` fica no pivô porque a nota é de cada colaborador.
 *
 * Nota abaixo do corte reprova o NÍVEL, mas o nível continua contando para o
 * quórum da etapa: senão uma nota ruim travaria a etapa inteira, que é
 * justamente o que não se quer. O reflexo da nota é na porcentagem de
 * avaliação da etapa, não na de conclusão.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('trail_levels', function (Blueprint $table) {
            $table->unsignedTinyInteger('cut_score')->default(70)->after('skill');
        });

        Schema::table('trail_level_collaborator', function (Blueprint $table) {
            // Envio do colaborador: ele manda o nível (com certificado, se for
            // curso) e fica aguardando a avaliação do líder.
            $table->timestamp('submitted_at')->nullable()->after('ends_at');
            $table->uuid('submitted_by')->nullable()->after('submitted_at');
            $table->string('certificate_uri')->nullable()->after('submitted_by');

            // Nota de 0 a 100 dada pelo líder ao concluir. Nula = concluído sem
            // avaliação, que é o caminho antigo e continua valendo.
            $table->unsignedTinyInteger('score')->nullable()->after('certificate_uri');
        });
    }

    public function down()
    {
        Schema::table('trail_levels', function (Blueprint $table) {
            $table->dropColumn('cut_score');
        });

        Schema::table('trail_level_collaborator', function (Blueprint $table) {
            $table->dropColumn(['submitted_at', 'submitted_by', 'certificate_uri', 'score']);
        });
    }
};

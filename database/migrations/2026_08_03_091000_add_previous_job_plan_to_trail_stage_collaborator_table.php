<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Guarda o cargo que o colaborador tinha ANTES de a etapa promovê-lo.
 *
 * Sem isso o desfazer tentava adivinhar o cargo anterior olhando a última
 * etapa concluída da mesma trilha — e zerava o cargo de quem não tinha etapa
 * anterior, inclusive o cargo definido na contratação, que a trilha nunca deu.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('trail_stage_collaborator', function (Blueprint $table) {
            $table->uuid('previous_job_plan_id')->nullable()->after('job_plan_id');
        });

        $this->backfill();
    }

    /**
     * Para as etapas já concluídas, deduz o cargo anterior da própria história:
     * a última etapa concluída antes desta, em qualquer trilha.
     *
     * Cruzar as trilhas é o ponto. Um colaborador em duas trilhas pode ter sido
     * promovido pela outra, e derivar só da trilha atual é justamente o que
     * fazia o desfazer zerar o cargo.
     *
     * Onde não existe etapa anterior o campo fica nulo: o cargo veio de antes
     * da trilha (contratação, edição do RH) e essa informação não está mais em
     * lugar nenhum. Nesse caso o desfazer devolve "sem cargo", que é o melhor
     * que se pode afirmar.
     */
    private function backfill(): void
    {
        $completed = DB::table('trail_stage_collaborator')
            ->whereNull('deleted_at')
            ->whereNotNull('completed_at')
            ->whereNotNull('job_plan_id')
            ->orderBy('completed_at')
            ->get(['trail_stage_id', 'collaborator_id', 'completed_at']);

        foreach ($completed as $row) {
            $previous = DB::table('trail_stage_collaborator')
                ->where('collaborator_id', $row->collaborator_id)
                ->whereNull('deleted_at')
                ->whereNotNull('completed_at')
                ->whereNotNull('job_plan_id')
                ->where('completed_at', '<', $row->completed_at)
                ->orderByDesc('completed_at')
                ->value('job_plan_id');

            if (!$previous) {
                continue;
            }

            DB::table('trail_stage_collaborator')
                ->where('trail_stage_id', $row->trail_stage_id)
                ->where('collaborator_id', $row->collaborator_id)
                ->update(['previous_job_plan_id' => $previous]);
        }
    }

    public function down()
    {
        Schema::table('trail_stage_collaborator', function (Blueprint $table) {
            $table->dropColumn('previous_job_plan_id');
        });
    }
};

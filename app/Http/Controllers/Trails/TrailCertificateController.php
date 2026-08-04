<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use App\Models\TrailStage;
use App\Services\Trails\TrailProgressService;
use Exception;
use PDF;

class TrailCertificateController extends Controller
{
    private $pdf;

    public function __construct(private TrailProgressService $progress)
    {
        $this->pdf = PDF::loadHTML('');
    }

    public function __invoke($stageId, $collaboratorId)
    {
        $stage = TrailStage::with(['trail.team', 'jobPlan'])->findOrFail($stageId);
        $collaborator = Collaborator::findOrFail($collaboratorId);

        $completion = $this->progress->stageCompletion($stage, $collaborator);

        if (!$completion) {
            return response()->json('Etapa não concluída.', 422);
        }

        try {
            $this->pdf->loadView('reports.certificate', [
                'payload' => [
                    'collaborator' => $collaborator,
                    'stage' => $stage,
                    'trail' => $stage->trail,
                    'team' => $stage->trail?->team,
                    'jobPlan' => $stage->jobPlan,
                    'completion' => $completion,
                ]
            ]);
            $this->pdf->setOptions([
                'page-size' => 'a4',
                'margin-top' => 0,
                'margin-bottom' => 0,
                'margin-left' => 0,
                'margin-right' => 0,
                'orientation' => 'landscape'
            ]);

            return $this->pdf->stream();
        } catch (Exception $e) {
            return response()->json($e->getMessage());
        }
    }
}

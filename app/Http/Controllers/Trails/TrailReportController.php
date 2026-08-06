<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use App\Models\JobPlans;
use App\Models\Trail;
use App\Services\Trails\TrailProgressService;
use Exception;
use PDF;

/**
 * Relatório da trilha em PDF, para o líder (R11).
 *
 * Dois usos, um controller e um blade só: sem colaborador na rota sai o geral,
 * com todos os matriculados; com colaborador sai o individual, que é o que o
 * líder leva para a conversa de 1:1.
 *
 * O blade recebe sempre uma lista — de um item ou de vários. Duplicar a view
 * para o geral significaria manter dois lugares em sincronia toda vez que um
 * campo mudar, e eles mudaram bastante nas últimas semanas.
 *
 * O certificado anexado aparece como "sim" ou "não", nunca embutido. Juntar o
 * arquivo tornaria o PDF imprevisível de tamanho e obrigaria a lidar com PDF
 * dentro de PDF; quem quer ver o documento abre pela tela.
 */
class TrailReportController extends Controller
{
    private $pdf;

    public function __construct(private TrailProgressService $progress)
    {
        $this->pdf = PDF::loadHTML('');
    }

    public function __invoke($trailId, $collaboratorId = null)
    {
        $trail = Trail::with('team')->findOrFail($trailId);

        $collaborators = $collaboratorId
            ? collect([Collaborator::findOrFail($collaboratorId)])
            : $trail->collaborators()->orderBy('first_name')->get();

        $reports = $collaborators->map(fn ($collaborator) => $this->reportFor($trail, $collaborator));

        try {
            $this->pdf->loadView('reports.trail', [
                'reports' => $reports->all(),
                'titulo' => $collaboratorId
                    ? 'Relatório individual'
                    : 'Relatório geral da trilha',
                'trilha' => $trail->description
                    . ($trail->team ? ' · time ' . $trail->team->name : ''),
            ]);
            $this->pdf->setOptions([
                'page-size' => 'a4',
                'margin-top' => 14,
                'margin-bottom' => 14,
                'margin-left' => 12,
                'margin-right' => 12,
            ]);

            return $this->pdf->stream();
        } catch (Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    private function reportFor(Trail $trail, Collaborator $collaborator): array
    {
        $payload = $this->progress->progressFor($trail, $collaborator);

        return [
            'payload' => $payload,
            'jobPlan' => $collaborator->jobplan_id ? JobPlans::find($collaborator->jobplan_id) : null,
            'totals' => $this->totals($payload),
        ];
    }

    /**
     * Números do rodapé, calculados aqui e não no blade: view com laço de conta
     * é o tipo de coisa que passa a divergir da tela sem ninguém perceber.
     *
     * A média geral usa os níveis avaliados de toda a trilha, e não a média das
     * médias por etapa — etapa com um nível avaliado pesaria igual a uma com
     * cinco.
     */
    private function totals(array $payload): array
    {
        $levels = collect($payload['stages'])->flatMap(fn ($stage) => $stage['levels']);
        $scored = $levels->whereNotNull('score');

        return [
            'levels' => $levels->count(),
            'completed' => $levels->where('completed', true)->count(),
            'submitted' => $levels->where('level_state', TrailProgressService::LEVEL_SUBMITTED)->count(),
            'reproved' => $levels->where('reproved', true)->count(),
            'late' => $levels
                ->where('period_state', TrailProgressService::PERIOD_LATE)
                ->count(),
            'certificates' => $levels->whereNotNull('certificate_uri')->count(),
            'evaluation' => $scored->count() > 0 ? (int) round($scored->avg('score')) : null,
        ];
    }
}

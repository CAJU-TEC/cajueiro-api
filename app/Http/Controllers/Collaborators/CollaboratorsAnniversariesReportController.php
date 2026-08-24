<?php

namespace App\Http\Controllers\Collaborators;

use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use Carbon\Carbon;
use Exception;
use PDF;

class CollaboratorsAnniversariesReportController extends Controller
{
    private $pdf;

    private const MONTHS = [
        1 => 'JANEIRO',
        2 => 'FEVEREIRO',
        3 => 'MARÇO',
        4 => 'ABRIL',
        5 => 'MAIO',
        6 => 'JUNHO',
        7 => 'JULHO',
        8 => 'AGOSTO',
        9 => 'SETEMBRO',
        10 => 'OUTUBRO',
        11 => 'NOVEMBRO',
        12 => 'DEZEMBRO',
    ];

    public function __construct(private Collaborator $collaborators)
    {
        $this->pdf = PDF::loadHTML('');
    }

    public function __invoke()
    {
        try {
            $year = (int) Carbon::now()->format('Y');

            $this->pdf->loadView('reports.anniversaries', [
                'payload' => [
                    'year' => $year,
                    'months' => self::MONTHS,
                    'anniversaries' => $this->groupByMonth($year),
                    'brand' => $this->embed('CAJU.png'),
                    'tree' => $this->embed('cajueiro.png'),
                ]
            ]);
            $this->pdf->setOptions([
                'page-size' => 'a4',
                'margin-top' => 5,
                'margin-bottom' => 5,
                'margin-left' => 5,
                'margin-right' => 5,
                'orientation' => 'portrait'
            ]);
            return $this->pdf->download('aniversarios-de-casa-caju.pdf');
        } catch (Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    // O wkhtmltopdf não resolve caminho relativo do HTML temporário: as artes vão embutidas.
    private function embed(string $file): ?string
    {
        $path = public_path("images/{$file}");

        if (!file_exists($path)) {
            return null;
        }

        return 'data:image/png;base64,' . base64_encode(file_get_contents($path));
    }

    private function groupByMonth(int $year): array
    {
        $grouped = array_fill_keys(array_keys(self::MONTHS), []);

        $collaborators = $this->collaborators
            ->whereNotNull('entrance')
            ->whereNull('egress')
            ->get();

        foreach ($collaborators as $collaborator) {
            // O accessor de entrance devolve d/m/Y; aqui é preciso o valor cru para montar a data.
            $entrance = Carbon::parse($collaborator->getRawOriginal('entrance'));

            $years = $year - (int) $entrance->format('Y');

            // Quem entrou no próprio ano do cartaz ainda não completa tempo de casa.
            if ($years < 1) {
                continue;
            }

            // Só o primeiro nome: first_name às vezes vem composto e quebraria a linha do cartaz.
            $name = explode(' ', trim($collaborator->first_name))[0];

            $grouped[(int) $entrance->format('n')][] = [
                'name' => mb_strtoupper($name),
                'day' => $entrance->format('d.m'),
                'years' => $years,
                'label' => $years === 1 ? '1 ANO' : "{$years} ANOS",
                'sort' => (int) $entrance->format('j'),
            ];
        }

        foreach ($grouped as $month => $items) {
            usort($items, fn($a, $b) => [$a['sort'], -$a['years']] <=> [$b['sort'], -$b['years']]);
            $grouped[$month] = $items;
        }

        return $grouped;
    }
}

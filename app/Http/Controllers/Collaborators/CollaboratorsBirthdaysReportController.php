<?php

namespace App\Http\Controllers\Collaborators;

use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use Carbon\Carbon;
use Exception;
use PDF;

class CollaboratorsBirthdaysReportController extends Controller
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
            $this->pdf->loadView('reports.birthdays', [
                'payload' => [
                    'months' => self::MONTHS,
                    'birthdays' => $this->groupByMonth(),
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
            return $this->pdf->download('aniversarios-caju.pdf');
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

    private function groupByMonth(): array
    {
        $grouped = array_fill_keys(array_keys(self::MONTHS), []);

        $collaborators = $this->collaborators
            ->whereNotNull('birth')
            ->whereNull('egress')
            ->get();

        foreach ($collaborators as $collaborator) {
            // O accessor de birth devolve d/m/Y; aqui é preciso o valor cru para montar a data.
            $birth = Carbon::parse($collaborator->getRawOriginal('birth'));

            // Só o primeiro nome: first_name às vezes vem composto e quebraria a linha do cartaz.
            $name = explode(' ', trim($collaborator->first_name))[0];

            $grouped[(int) $birth->format('n')][] = [
                'name' => mb_strtoupper($name),
                'day' => $birth->format('d.m'),
                'sort' => (int) $birth->format('j'),
            ];
        }

        foreach ($grouped as $month => $items) {
            usort($items, fn($a, $b) => $a['sort'] <=> $b['sort']);
            $grouped[$month] = $items;
        }

        return $grouped;
    }
}

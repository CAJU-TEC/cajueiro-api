<?php

namespace Database\Seeders;

use App\Models\JobPlans;
use App\Models\Team;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobPlansSeeder extends Seeder
{
    const LADDER = [
        [
            'key' => 'estagio',
            'description' => 'Estágio',
            'time' => '1 SEMANA',
            'note' => 'Conhecendo o sistema e apresentando o que sabe sobre as tecnologias aplicadas na CAJU Tec.',
            'color' => '#66b3ff',
        ],
        [
            'key' => 'trainee',
            'description' => 'Trainne',
            'time' => '1 MÊS',
            'note' => 'Executando protocolos de demandas em projetos relacionados aos nossos produtos.',
            'color' => '#0080ff',
        ],
        [
            'key' => 'jr1',
            'description' => 'JÚNIOR (Caju-manso) - Nível 1 Crescimento',
            'time' => 'INDEFINIDO',
            'note' => '',
            'color' => '#00f500',
        ],
        [
            'key' => 'jr2',
            'description' => 'JÚNIOR (Caju-manso) - Nível 2 Maturação',
            'time' => 'INDEFINIDO',
            'note' => '',
            'color' => '#00d600',
        ],
        [
            'key' => 'jr3',
            'description' => 'JÚNIOR (Caju-manso) - Nível 3 Amadurecimento (Líder)',
            'time' => 'INDEFINIDO',
            'note' => '',
            'color' => '#00a300',
        ],
        [
            'key' => 'pl1',
            'description' => 'PLENO (Caju-manteiga) - Nível 1 Crescimento',
            'time' => 'INDEFINIDO',
            'note' => '',
            'color' => '#f57b00',
        ],
        [
            'key' => 'pl2',
            'description' => 'PLENO (Caju-manteiga) - Nível 2 Maturação',
            'time' => 'INDEFINIDO',
            'note' => '',
            'color' => '#d66c00',
        ],
        [
            'key' => 'pl3',
            'description' => 'PLENO (Caju-manteiga) - Nível 3 Amadurecimento (Líder)',
            'time' => 'INDEFINIDO',
            'note' => '',
            'color' => '#a35200',
        ],
        [
            'key' => 'sr1',
            'description' => 'SÊNIOR (Cajueiro) - Nível 1 Crescimento',
            'time' => 'INDEFINIDO',
            'note' => '',
            'color' => '#f50000',
        ],
        [
            'key' => 'sr2',
            'description' => 'SÊNIOR (Cajueiro) - Nível 2 Maturação',
            'time' => 'INDEFINIDO',
            'note' => '',
            'color' => '#d60000',
        ],
        [
            'key' => 'sr3',
            'description' => 'SÊNIOR (Cajueiro) - Nível 3 Amadurecimento (Líder)',
            'time' => 'INDEFINIDO',
            'note' => '',
            'color' => '#a30000',
        ],
    ];

    const TEAMS = [
        [
            'name' => 'Dev',
            'badge_icon' => 'code',
            'skip' => [],
            'adopt_orphans' => true,
            'values' => [
                'estagio' => 0.0,
                'trainee' => 500,
                'jr1' => 1500,
                'jr2' => 1750,
                'jr3' => 2000,
                'pl1' => 2250,
                'pl2' => 2500,
                'pl3' => 2750,
                'sr1' => 3000,
                'sr2' => 3250,
                'sr3' => 3500,
            ],
        ],
        [
            'name' => 'QA',
            'badge_icon' => 'bug_report',
            'skip' => ['trainee'],
            'adopt_orphans' => false,
            'values' => [],
        ],
        [
            'name' => 'Suporte',
            'badge_icon' => 'support_agent',
            'skip' => ['trainee'],
            'adopt_orphans' => false,
            'values' => [],
        ],
    ];

    public function run()
    {
        foreach (self::TEAMS as $config) {
            $team = Team::where('name', $config['name'])->first();

            if (!$team) {
                $this->command?->warn("Time {$config['name']} não encontrado. Planos ignorados.");
                continue;
            }

            $position = 0;

            foreach (self::LADDER as $step) {
                if (in_array($step['key'], $config['skip'])) {
                    continue;
                }

                $position++;

                $attributes = [
                    'team_id' => $team->id,
                    'description' => $step['description'],
                    'time' => $step['time'],
                    'note' => $step['note'],
                    'color' => $step['color'],
                    'badge_icon' => $config['badge_icon'],
                    'badge_color' => $step['color'],
                    'position' => $position,
                ];

                $plan = $this->findPlan($team, $step['description'], $config['adopt_orphans']);

                if ($plan) {
                    $plan->update($attributes);
                    continue;
                }

                JobPlans::create($attributes + [
                    'value' => $config['values'][$step['key']] ?? 0.0,
                ]);
            }
        }
    }

    /**
     * O valor de cada plano é ajustado pela tela, então só entra na criação — nunca na atualização.
     * Planos do Dev nasceram sem team_id (seed anterior à coluna) e são adotados pela descrição.
     */
    private function findPlan(Team $team, string $description, bool $adoptOrphans): ?JobPlans
    {
        $plan = JobPlans::where('team_id', $team->id)
            ->where('description', $description)
            ->first();

        if ($plan || !$adoptOrphans) {
            return $plan;
        }

        return JobPlans::whereNull('team_id')
            ->where('description', $description)
            ->first();
    }
}

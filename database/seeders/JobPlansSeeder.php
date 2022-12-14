<?php

namespace Database\Seeders;

use App\Models\JobPlans;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobPlansSeeder extends Seeder
{
    const JOB = [
        [
            'description' => 'Estágio',
            'value' => 0.0,
            'time' => '1 SEMANA',
            'note' => 'Conhecendo o sistema e apresentando o que sabe sobre as tecnologias aplicadas na CAJU Tec.',
            'color' => '#66b3ff',
        ],
        [
            'description' => 'Trainne',
            'value' => 500,
            'time' => '1 MÊS',
            'note' => 'Executando protocolos de demandas em projetos relacionados aos nossos produtos.',
            'color' => '#0080ff',
        ],
        [
            'description' => 'JÚNIOR (Caju-manso) - Nível 1 Crescimento',
            'value' => 1500,
            'time' => '6 MESES',
            'note' => '',
            'color' => '#00f500',
        ],
        [
            'description' => 'JÚNIOR (Caju-manso) - Nível 2 Maturação',
            'value' => 1750,
            'time' => '6 MESES',
            'note' => '',
            'color' => '#00d600',
        ],
        [
            'description' => 'JÚNIOR (Caju-manso) - Nível 3 Amadurecimento (Líder)',
            'value' => 2000,
            'time' => '6 MESES',
            'note' => '',
            'color' => '#00a300',
        ],
        [
            'description' => 'PLENO (Caju-manteiga) - Nível 1 Crescimento',
            'value' => 2250,
            'time' => '6 MESES',
            'note' => '',
            'color' => '#f57b00',
        ],
        [
            'description' => 'PLENO (Caju-manteiga) - Nível 2 Maturação',
            'value' => 2500,
            'time' => '6 MESES',
            'note' => '',
            'color' => '#d66c00',
        ],
        [
            'description' => 'PLENO (Caju-manteiga) - Nível 3 Amadurecimento (Líder)',
            'value' => 2750,
            'time' => '6 MESES',
            'note' => '',
            'color' => '#a35200',
        ],
        [
            'description' => 'SÊNIOR (Cajueiro) - Nível 1 Crescimento',
            'value' => 3000,
            'time' => '6 MESES',
            'note' => '',
            'color' => '#f50000',
        ],
        [
            'description' => 'SÊNIOR (Cajueiro) - Nível 2 Maturação',
            'value' => 3250,
            'time' => '6 MESES',
            'note' => '',
            'color' => '#d60000',
        ],
        [
            'description' => 'SÊNIOR (Cajueiro) - Nível 3 Amadurecimento (Líder)',
            'value' => 3500,
            'time' => '6 MESES',
            'note' => '',
            'color' => '#a30000',
        ],
    ];

    public function run()
    {
        //
        foreach (self::JOB as $job) {
            JobPlans::updateOrCreate([
                'description' => $job['description']
            ], [
                'description' => $job['description'],
                'value' => $job['value'],
                'time' => $job['time'],
                'note' => $job['note'],
                'color' => $job['color'],
            ]);
        }
    }
}

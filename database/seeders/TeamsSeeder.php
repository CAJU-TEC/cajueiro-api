<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamsSeeder extends Seeder
{
    const TEAMS = [
        [
            'name' => 'Dev',
            'description' => 'Desenvolvimento dos produtos e sustentação do sistema.',
            'color' => '#d54400',
        ],
        [
            'name' => 'QA',
            'description' => 'Qualidade: testes, homologação e validação das entregas.',
            'color' => '#1976d2',
        ],
        [
            'name' => 'Suporte',
            'description' => 'Atendimento aos clientes e triagem dos protocolos.',
            'color' => '#8a4a00',
        ],
        [
            'name' => 'Financeiro',
            'description' => 'Faturamento, cobrança e controle financeiro.',
            'color' => '#7b1fa2',
        ],
        [
            'name' => 'Onboarding',
            'description' => 'Integração e treinamento inicial dos novos clientes.',
            'color' => '#2e7d32',
        ],
    ];

    public function run()
    {
        foreach (self::TEAMS as $team) {
            Team::updateOrCreate(
                ['name' => $team['name']],
                [
                    'description' => $team['description'],
                    'color' => $team['color'],
                ]
            );
        }
    }
}

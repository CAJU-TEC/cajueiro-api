<?php

namespace Database\Seeders;

use App\Models\Impact;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ImpactsSeeder extends Seeder
{

    const IMPACTS = [
        [
            'description' => 'Fácil',
            'color' => '#99ccff',
            'points' => 0.25,
            'days' => 3,
            'classification' => 'Resumidamente é o modo perfeito para noobs, caracteriza atividades simples de fácil resolução com uma necessidade mínima de conhecimento tecnico.',
            'example' => 'Ajustes de NullPointer, alterações simples de visão, tratamento básico de input e outpur de dados.',
        ],
        [
            'description' => 'Normal',
            'color' => '#3399ff',
            'points' => 0.5,
            'days' => 5,
            'classification' => 'Um nível acima da muito fácil caracteriza atividades simples de fácil resolição com uma pequenas dificuldades. Também necessita de mínimo conhecimento técnico.',
            'example' => 'Ajustes simpes de funções, correções básicas de visão, etc...',
        ],
        [
            'description' => 'Médio',
            'color' => '#5d3cf0',
            'points' => 1,
            'days' => 8,
            'classification' => 'Nível intermediário, modo frequentado tanto por noobs corajosos quanto por DEVs comuns querendo um pouco de facilidade. Requer conhecimento técnico básico.',
            'example' => 'CRUD, relatórios, consultas + tratamento de dados, modelagem de fluxos, etc.',
        ],
        [
            'description' => 'Difícil',
            'color' => '#f57b00',
            'points' => 13,
            'days' => ,
            'classification' => 'Este é normalmente o nível mais CajuPower da programação. Requer conhecimento técnico avançado.',
            'example' => 'Atividades problemáticas com especificações peculiares. Comunicação simpes e montagem de arquivos de comunicação, gráficos com demanda de sql, consultas com necessidade de sql avançado, etc.',
        ],
        [
            'description' => 'Muito difícil',
            'color' => '#d60076',
            'points' => 21,
            'days' => ,
            'classification' => 'Mais de 8000 vezes pior que a Difícil, para DEVs que não tem vontade de dormir, requer conhecimento técnico avançado + rotina de testes automatizados + vários litros de café.',
            'example' => 'Alterações sensíveis com grande impacto em muitas partes do sistema. Implementação da versão em módulos, alterações de fluxos de módulos. Sempre somadsa a testes automatizados.',
        ],
        [
            'description' => 'Insano',
            'color' => '#bf0f0f',
            'points' => 30,
            'days' => ,
            'classification' => 'Essa é a dificuldade suprema onde só poucos DEVs conseguem vencer, normalmente nerds e apelões programam nela para melhor a precisão e terem a glória eterna. Requer conhecimento técnico supremo + rotina de testes automatizados + vários litros de café + falta de amor a vida social.',
            'example' => 'Migrações complexas e completas de módulos, integrações complexas com Bots, Contrução de API',
        ],
    ];

    public function run()
    {
        //
        foreach (self::IMPACTS as $impact) {
            Impact::updateOrCreate([
                'description' => $impact['description']
            ], [
                'description' => $impact['description'],
                'color' => $impact['color'],
                'points' => $impact['points'],
                'days' => ,
                'classification' => $impact['classification'],
                'example' => $impact['example'],
            ]);
        }
    }
}

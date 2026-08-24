<?php

namespace Database\Seeders;

use App\Models\JobPlans;
use App\Models\Team;
use App\Models\Trail;
use App\Models\TrailLevel;
use App\Models\TrailStage;
use Illuminate\Database\Seeder;

class TrailSupportSeeder extends Seeder
{
    const TEAM = 'Suporte';

    const CUT_SCORE = 70;

    const TRAIL = [
        'description' => 'Analise e Suporte',
        'note' => 'Trilha estruturada para desenvolver conhecimento técnico, atendimento, autonomia, análise de problemas e liderança, seguindo progressão por etapas de carreira.',
        'color' => '#E29127',
    ];

    const STAGES = [
        [
            'description' => 'Suporte I',
            'job_plan' => 'Estágio',
            'required_count' => 8,
            'note' => 'Marco de entrada na carreira de suporte. O colaborador desenvolve fundamentos de atendimento, sistema e organização.',
            'levels' => [
                [
                    'description' => 'Atendimento básico ao cliente',
                    'note' => 'Demonstrar comunicação clara, objetiva e profissional durante o atendimento.',
                    'skill' => 'soft',
                    'type' => 'communication',
                ],
                [
                    'description' => 'Empatia no atendimento',
                    'note' => 'Demonstrar capacidade de compreender a necessidade do cliente e conduzir o atendimento com empatia.',
                    'skill' => 'soft',
                    'type' => 'empathy',
                ],
                [
                    'description' => 'Conhecimento dos principais módulos do sistema',
                    'note' => 'Concluir treinamento sobre os principais módulos e funcionalidades.',
                    'skill' => 'hard',
                    'type' => 'course',
                ],
                [
                    'description' => 'Registro correto de chamados',
                    'note' => 'Registrar corretamente contexto, evidências, procedimentos realizados e encaminhamentos.',
                    'skill' => 'hard',
                    'type' => 'task',
                ],
                [
                    'description' => 'Resolução de chamados simples',
                    'note' => 'Resolver solicitações de baixa complexidade seguindo os procedimentos disponíveis.',
                    'skill' => 'hard',
                    'type' => 'task',
                ],
                [
                    'description' => 'Organização da rotina de atendimento',
                    'note' => 'Demonstrar organização no acompanhamento de chamados, prazos e pendências.',
                    'skill' => 'soft',
                    'type' => 'organization',
                ],
                [
                    'description' => 'Escrita de cenários em BDD/Gherkin',
                    'note' => 'Descrever comportamento esperado e ocorrido no padrão Dado / Quando / Então, em português, ao registrar chamado.',
                    'skill' => 'hard',
                    'type' => 'course',
                ],
                [
                    'description' => 'Avaliação do cliente no atendimento',
                    'note' => 'Atingir a meta de avaliação dada pelo cliente no avaliador do sistema de atendimento, no período considerado. A nota vem da avaliação do cliente, não da percepção do líder.',
                    'skill' => 'hard',
                    'type' => 'platform',
                ],
            ],
        ],
        [
            'description' => 'Suporte II',
            'job_plan' => 'JÚNIOR (Caju-manso) - Nível 1 Crescimento',
            'required_count' => 7,
            'note' => 'Marco de evolução para atuação com maior autonomia e resolução de problemas de média complexidade.',
            'levels' => [
                [
                    'description' => 'Investigação de problemas',
                    'note' => 'Investigar o problema antes de encaminhá-lo, coletando informações e evidências.',
                    'skill' => 'hard',
                    'type' => 'task',
                ],
                [
                    'description' => 'Testes e reprodução de erros',
                    'note' => 'Reproduzir problemas relatados e documentar os resultados dos testes.',
                    'skill' => 'hard',
                    'type' => 'technical_test',
                ],
                [
                    'description' => 'Atendimento de média complexidade',
                    'note' => 'Resolver chamados que exigem análise além dos procedimentos básicos.',
                    'skill' => 'hard',
                    'type' => 'task',
                ],
                [
                    'description' => 'Autonomia na resolução',
                    'note' => 'Demonstrar iniciativa para buscar a solução antes de solicitar auxílio.',
                    'skill' => 'soft',
                    'type' => 'proactivity',
                ],
                [
                    'description' => 'Comunicação de incidentes',
                    'note' => 'Comunicar claramente causa, impacto, testes realizados e próximos passos.',
                    'skill' => 'soft',
                    'type' => 'communication',
                ],
                [
                    'description' => 'Acompanhamento do chamado até a solução',
                    'note' => 'Assumir responsabilidade pelo chamado e acompanhar sua resolução até a conclusão.',
                    'skill' => 'hard',
                    'type' => 'task',
                ],
                [
                    'description' => 'Avaliação do cliente em chamados de média complexidade',
                    'note' => 'Manter a meta de avaliação dada pelo cliente no avaliador do sistema de atendimento nos chamados que exigem análise. A nota vem da avaliação do cliente, não da percepção do líder.',
                    'skill' => 'hard',
                    'type' => 'platform',
                ],
            ],
        ],
        [
            'description' => 'Suporte III',
            'job_plan' => 'PLENO (Caju-manteiga) - Nível 1 Crescimento',
            'required_count' => 10,
            'note' => 'Marco de atuação como referência técnica, com capacidade de tratar problemas complexos e apoiar outros profissionais.',
            'levels' => [
                [
                    'description' => 'Análise de problemas complexos',
                    'note' => 'Analisar problemas de alta complexidade e identificar possíveis causas.',
                    'skill' => 'hard',
                    'type' => 'technical_test',
                ],
                [
                    'description' => 'Análise de causa-raiz',
                    'note' => 'Investigar a origem de problemas recorrentes e documentar a causa identificada.',
                    'skill' => 'hard',
                    'type' => 'task',
                ],
                [
                    'description' => 'Integração com equipes técnicas',
                    'note' => 'Trabalhar com desenvolvimento, produto e tecnologia na resolução de incidentes.',
                    'skill' => 'soft',
                    'type' => 'collaboration',
                ],
                [
                    'description' => 'Apoio aos Suporte I e II',
                    'note' => 'Apoiar os colaboradores das etapas Suporte I e Suporte II na análise e resolução de chamados, acompanhando a evolução deles.',
                    'skill' => 'soft',
                    'type' => 'leadership',
                ],
                [
                    'description' => 'Documentação de soluções',
                    'note' => 'Criar ou atualizar documentação para problemas e soluções recorrentes.',
                    'skill' => 'hard',
                    'type' => 'task',
                ],
                [
                    'description' => 'Gestão de incidentes críticos',
                    'note' => 'Participar da condução de incidentes críticos, mantendo organização e comunicação adequada.',
                    'skill' => 'hard',
                    'type' => 'task',
                ],
                [
                    'description' => 'Domínio pleno dos produtos da empresa',
                    'note' => 'Conhecer todos os produtos da empresa, e não apenas os módulos do dia a dia, a ponto de atender qualquer frente.',
                    'skill' => 'hard',
                    'type' => 'course',
                ],
                [
                    'description' => 'Condução de reuniões e treinamentos',
                    'note' => 'Conduzir reuniões e treinamentos com cliente e com a equipe, online e presencialmente.',
                    'skill' => 'soft',
                    'type' => 'leadership',
                ],
                [
                    'description' => 'Oratória para apresentações',
                    'note' => 'Apresentar em público com clareza e postura, evoluindo a partir do coaching recebido.',
                    'skill' => 'soft',
                    'type' => 'communication',
                ],
                [
                    'description' => 'Avaliação do cliente em incidentes complexos e críticos',
                    'note' => 'Manter a meta de avaliação dada pelo cliente no avaliador do sistema de atendimento em incidentes de alta complexidade e críticos. A nota vem da avaliação do cliente, não da percepção do líder.',
                    'skill' => 'hard',
                    'type' => 'platform',
                ],
            ],
        ],
        [
            'description' => 'Analista de Suporte',
            'job_plan' => 'SÊNIOR (Cajueiro) - Nível 1 Crescimento',
            'required_count' => 8,
            'note' => 'Marco de especialização técnica e atuação na melhoria dos processos e indicadores do suporte.',
            'levels' => [
                [
                    'description' => 'Análise de indicadores de suporte',
                    'note' => 'Utilizar as ferramentas disponíveis para analisar volume, SLA, backlog e reincidência.',
                    'skill' => 'hard',
                    'type' => 'platform',
                ],
                [
                    'description' => 'Identificação de problemas recorrentes',
                    'note' => 'Identificar padrões nos chamados e oportunidades de melhoria.',
                    'skill' => 'hard',
                    'type' => 'task',
                ],
                [
                    'description' => 'Proposta de melhoria de processo',
                    'note' => 'Propor melhorias que aumentem eficiência, qualidade ou velocidade do atendimento.',
                    'skill' => 'soft',
                    'type' => 'proactivity',
                ],
                [
                    'description' => 'Análise de causa-raiz avançada',
                    'note' => 'Realizar investigação estruturada de problemas complexos e recorrentes.',
                    'skill' => 'hard',
                    'type' => 'technical_test',
                ],
                [
                    'description' => 'Treinamento da equipe',
                    'note' => 'Preparar e conduzir treinamentos técnicos ou operacionais para o time.',
                    'skill' => 'soft',
                    'type' => 'leadership',
                ],
                [
                    'description' => 'Interface com áreas técnicas',
                    'note' => 'Atuar como referência entre suporte e áreas como desenvolvimento, produto e tecnologia.',
                    'skill' => 'soft',
                    'type' => 'collaboration',
                ],
                [
                    'description' => 'Análise de requisitos de novas funcionalidades',
                    'note' => 'Levantar e detalhar o requisito de uma funcionalidade nova a partir da necessidade do cliente, em formato que o desenvolvimento consiga executar.',
                    'skill' => 'hard',
                    'type' => 'task',
                ],
                [
                    'description' => 'Pós-venda ativo',
                    'note' => 'Procurar o cliente antes de ele pedir ajuda, acompanhando uso, dificuldades e pendências.',
                    'skill' => 'soft',
                    'type' => 'proactivity',
                ],
            ],
        ],
        [
            'description' => 'Líder de Suporte',
            'job_plan' => 'SÊNIOR (Cajueiro) - Nível 3 Amadurecimento (Líder)',
            'required_count' => 9,
            'note' => 'Marco de liderança responsável por pessoas, operação, indicadores, qualidade e evolução do time.',
            'levels' => [
                [
                    'description' => 'Gestão de indicadores',
                    'note' => 'Acompanhar e interpretar os principais indicadores da operação.',
                    'skill' => 'hard',
                    'type' => 'platform',
                ],
                [
                    'description' => 'Gestão de SLA e backlog',
                    'note' => 'Controlar prioridades, prazos e volume de chamados.',
                    'skill' => 'hard',
                    'type' => 'task',
                ],
                [
                    'description' => 'Feedback e desenvolvimento de pessoas',
                    'note' => 'Realizar feedbacks e acompanhar a evolução individual dos colaboradores.',
                    'skill' => 'soft',
                    'type' => 'leadership',
                ],
                [
                    'description' => 'Gestão de conflitos',
                    'note' => 'Conduzir conflitos de forma equilibrada e orientada à solução.',
                    'skill' => 'soft',
                    'type' => 'emotional_intelligence',
                ],
                [
                    'description' => 'Priorização e tomada de decisão',
                    'note' => 'Tomar decisões considerando impacto no cliente, equipe e negócio.',
                    'skill' => 'soft',
                    'type' => 'leadership',
                ],
                [
                    'description' => 'Melhoria contínua da operação',
                    'note' => 'Identificar oportunidades e implementar melhorias no processo de suporte.',
                    'skill' => 'soft',
                    'type' => 'proactivity',
                ],
                [
                    'description' => 'Gestão e planejamento da equipe',
                    'note' => 'Organizar capacidade, distribuição de demandas, prioridades e desenvolvimento do time.',
                    'skill' => 'hard',
                    'type' => 'task',
                ],
                [
                    'description' => 'Decisão junto às lideranças de outros setores',
                    'note' => 'Participar das decisões internas da empresa junto aos líderes dos demais setores, levando a visão do suporte.',
                    'skill' => 'soft',
                    'type' => 'collaboration',
                ],
                [
                    'description' => 'Relatório de satisfação do cliente',
                    'note' => 'Produzir e apresentar o relatório das métricas de satisfação do cliente, a partir do avaliador do sistema de atendimento.',
                    'skill' => 'hard',
                    'type' => 'task',
                ],
            ],
        ],
    ];

    public function run()
    {
        $team = Team::where('name', self::TEAM)->first();

        if (!$team) {
            $this->command?->warn('Time '.self::TEAM.' não encontrado. Trilha ignorada.');

            return;
        }

        $trail = Trail::updateOrCreate(
            [
                'team_id' => $team->id,
                'description' => self::TRAIL['description'],
            ],
            [
                'note' => self::TRAIL['note'],
                'color' => self::TRAIL['color'],
                'active' => true,
            ]
        );

        foreach (self::STAGES as $index => $data) {
            $stage = TrailStage::updateOrCreate(
                [
                    'trail_id' => $trail->id,
                    'position' => $index + 1,
                ],
                [
                    'description' => $data['description'],
                    'note' => $data['note'],
                    'job_plan_id' => $this->jobPlanId($team, $data['job_plan']),
                    'required_count' => $data['required_count'],
                ]
            );

            foreach ($data['levels'] as $position => $item) {
                TrailLevel::updateOrCreate(
                    [
                        'trail_stage_id' => $stage->id,
                        'position' => $position + 1,
                    ],
                    [
                        'description' => $item['description'],
                        'note' => $item['note'],
                        'skill' => $item['skill'],
                        'type' => $item['type'],
                        'cut_score' => self::CUT_SCORE,
                    ]
                );
            }
        }
    }

    /**
     * O plano é achado pela descrição, que precisa bater com a do JobPlansSeeder.
     */
    private function jobPlanId(Team $team, string $description): ?string
    {
        $plan = JobPlans::where('team_id', $team->id)
            ->where('description', $description)
            ->first();

        if (!$plan) {
            $this->command?->warn("Plano \"{$description}\" não encontrado no time {$team->name}. Etapa fica sem plano-alvo.");
        }

        return $plan?->id;
    }
}

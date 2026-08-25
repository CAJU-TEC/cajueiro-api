<?php

namespace Database\Seeders;

use App\Models\JobPlans;
use App\Models\Team;
use App\Models\Trail;
use App\Models\TrailLevel;
use App\Models\TrailStage;
use Illuminate\Database\Seeder;

/**
 * Trilha "Programação WEB" do time Dev.
 *
 * Uma etapa por degrau do plano de carreira (plano_carreira_caju_tec_atualizado-1.pdf):
 * concluir a etapa promove ao cargo que ela aponta (R3). A ordem é a da carreira,
 * porque R1 só libera uma etapa depois da anterior.
 *
 * São 11 etapas: as 10 do QA mais a de Trainne, que o QA não tem por não ter o
 * cargo. Os nove degraus compartilhados usam os mesmos nomes nas duas trilhas.
 *
 * A ordem aqui é Trainne (1 mês) e depois Estágio (8 meses), por decisão do
 * time. Ela contraria a linha de vida do PDF e o `job_plans.position`, onde
 * Estágio é 1 e Trainne é 2 — se a posição dos cargos não for invertida lá, a
 * escada de cargos e a trilha contam histórias diferentes. Note também que a
 * promoção passa a ser para Trainne (R$ 500,00) antes de Estágio (R$ 0,00).
 *
 * Com essa ordem, o Trainne é ambientação e o Estágio carrega a formação: é ele
 * que hospeda o Plano de treinamento de Estagiários, que é literalmente o
 * documento dessa fase e ocupa os 8 meses.
 *
 * Os níveis de stack (Vue 2, Vuetify, Vuex, Sanctum, Snappy, cPanel) vêm do
 * levantamento feito no santana-api/santana-web (projeto STM), não de suposição
 * sobre o que um Laravel costuma usar — o E-Public não usa Quasar nem Spatie.
 *
 * Os demais níveis saem da coluna "Observação" do PDF: cada oração dela vira um nível,
 * e é isso que mantém a trilha colada no plano em vez de virar invenção. As duas
 * primeiras etapas recebem também o conteúdo do plano de treinamento de
 * estagiários (cajueiro-web/docs).
 *
 * Time e cargos são resolvidos por descrição, não por UUID: os ids de produção
 * não são os do banco local.
 *
 * Materiais de apoio ainda não entram aqui: `url` é obrigatório na API e os
 * links internos não existem. Quando existirem, vale um seeder próprio — links
 * mudam mais do que degraus de carreira.
 */
class TrailsDevSeeder extends Seeder
{
    private const TEAM = 'Dev';
    private const TRAIL = 'Programação WEB';

    /**
     * `job_plan` é a descrição do cargo concedido ao concluir a etapa.
     * `required_count` é o quórum: quantos níveis fecham a etapa (R2).
     *
     * Da etapa 3 em diante o quórum é igual ao total de níveis — as competências
     * de um degrau de carreira não são um cardápio. Quórum menor só na etapa 1,
     * onde os níveis são alternativas: o que o candidato apresenta varia com o
     * que ele já traz.
     *
     * Cada nível é [descrição, type, skill, observação]. O vocabulário de `type`
     * está em TrailLevelStoreRequest::TYPES.
     *
     * A observação diz o que precisa ser demonstrado para o nível fechar. Ela
     * existe porque concluir é avaliar (R9): sem um critério escrito, a nota de
     * 0 a 100 vira impressão do avaliador do dia.
     */
    private const STAGES = [
        [
            'description' => 'Enraizamento (Trainne)',
            'job_plan' => 'Trainne',
            'required_count' => 5,
            'note' => '1 mês de ambientação: conhecer o portfólio de produtos, o fluxo de protocolo e o ambiente, antes de começar a formação técnica. Quórum menor que o total porque os três sistemas são alternativas — cobre-se primeiro o que a pessoa vai atender.',
            'levels' => [
                ['Conhecer os sistemas E-Public e o domínio escolar', 'task', 'hard', 'Sabe o que o sistema faz e explica os termos do dia a dia da escola: matrícula, turma, série, etapa e BNCC.'],
                ['Conhecer os sistemas E-Institucional', 'task', 'hard', 'Sabe que necessidade o E-Institucional atende e em que ele se diferencia do E-Public.'],
                ['Conhecer os sistemas legados', 'task', 'hard', 'Reconhece o que ainda roda fora da stack atual, quem depende disso e o cuidado que exige ao ser tocado.'],
                ['Conhecer o fluxo de protocolo, da abertura à entrega', 'task', 'hard', 'Acompanha um protocolo do início ao fim e sabe em que situação ele está em cada momento.'],
                ['Usar o ambiente de desenvolvimento e o fluxo de homologação', 'task', 'hard', 'Sobe o projeto na própria máquina e sabe a diferença entre local, homologação e produção.'],
                ['Clareza na comunicação técnica', 'communication', 'soft', 'Relata o que fez e onde travou de forma que outra pessoa entenda sem precisar perguntar de novo.'],
                ['Organização e registro do próprio trabalho', 'organization', 'soft', 'Mantém o protocolo atualizado: o que já fez, o que falta e o que está bloqueando.'],
            ],
        ],
        [
            'description' => 'Germinação e Muda (Estágio)',
            'job_plan' => 'Estágio',
            'required_count' => 10,
            'note' => '8 meses. É aqui que mora o Plano de treinamento de Estagiários: dos fundamentos da web até a stack da casa, fechando com a avaliação prática por protocolos.',
            'levels' => [
                ['Linux: comandos básicos', 'course', 'hard', 'Navega entre diretórios, edita arquivo de texto e cria atalho pelo terminal, sem depender de interface gráfica.'],
                ['Lógica de programação', 'course', 'hard', 'Resolve exercícios com tipos primitivos, condição, laço e função, explicando o raciocínio que usou.'],
                ['HTML, CSS e JavaScript básico', 'course', 'hard', 'Monta uma página com formulário e tabela, posiciona os elementos com flexbox e trata um evento em JavaScript.'],
                ['HTTP, request/response, JSON e conceitos de API', 'course', 'hard', 'Explica o que acontece entre o clique e a resposta: método, rota, corpo, código de status e JSON de retorno.'],
                ['PHP: sintaxe, arrays, classes, namespaces e Composer', 'course', 'hard', 'Escreve PHP fora do framework: manipula arrays, cria classe com métodos e instala um pacote pelo Composer.'],
                ['Banco de dados: SELECT, JOIN, INSERT, UPDATE, DELETE e chaves', 'course', 'hard', 'Cria as tabelas de um cadastro com chave primária e estrangeira, e escreve as consultas de leitura e de escrita.'],
                ['Git e GitHub: fluxo de branches, homologação e Pull Request', 'course', 'hard', 'Abre branch a partir da correta, resolve um conflito e leva a alteração até o Pull Request revisado.'],
                ['O modelo MVC com Laravel', 'course', 'hard', 'Entrega um CRUD completo — migration, model, controller, rota e tela — sabendo por onde a requisição passa.'],
                ['Vue 2 e Vuetify: componentes e reatividade', 'course', 'hard', 'Cria um componente com props e evento, consome a API e trata carregamento e erro na tela.'],
                ['Ler código existente e seguir o fluxo de uma requisição', 'task', 'hard', 'Recebe uma funcionalidade que não escreveu e explica o caminho dela, da rota até o banco.'],
                ['Debugging: do erro ao log, do stack trace à correção', 'task', 'hard', 'Parte de um erro relatado, encontra a causa no log e no stack trace e corrige, sem tentativa e erro.'],
                ['Por que testar: noções de teste automatizado', 'course', 'hard', 'Explica o que um teste protege, roda a suíte do projeto e sabe ler o que falhou.'],
                ['Avaliação final: resolver protocolos do treinamento', 'technical_test', 'hard', 'Resolve o conjunto de protocolos do treinamento, exercitando todas as habilidades da etapa.'],
            ],
        ],
        [
            'description' => 'Crescimento e Entrada em Produção (Júnior Nvl 1)',
            'job_plan' => 'JÚNIOR (Caju-manso) - Nível 1 Crescimento',
            'required_count' => 7,
            'note' => 'Ganha autonomia em tarefas simples, entra na fila real de protocolos e aprende as peculiaridades do E-Public que aparecem todo dia.',
            'levels' => [
                ['Autonomia em tarefas técnicas simples', 'task', 'hard', 'Recebe uma tarefa conhecida e entrega sem precisar de acompanhamento passo a passo.'],
                ['Executar protocolos de demanda em produto real', 'task', 'hard', 'Assume protocolos da fila em sistema de cliente, com a responsabilidade que isso implica.'],
                ['Padrões de código e boas práticas do time', 'task', 'hard', 'O código entregue passa na revisão sem apontamento de padrão: nomes, organização e estrutura como o time faz.'],
                ['Vuex: estado compartilhado no front', 'course', 'hard', 'Sabe quando um dado pertence ao componente e quando pertence à store, e mexe na store sem quebrar outras telas.'],
                ['Autorização por abilities de token do Sanctum', 'task', 'hard', 'Entende que a permissão vem da ability do token, e não de tabela de papel, e protege uma rota nova corretamente.'],
                ['Geração de relatórios em PDF com Snappy e wkhtmltopdf', 'task', 'hard', 'Entrega um relatório novo, do controller à view, ciente do limite de tempo de execução do servidor.'],
                ['Revisão do próprio trabalho antes do Pull Request', 'collaboration', 'soft', 'Relê o próprio diff antes de abrir o PR e encontra os próprios erros antes que o revisor encontre.'],
            ],
        ],
        [
            'description' => 'Cajueiro Anão-Precoce (Júnior Nvl 2)',
            'job_plan' => 'JÚNIOR (Caju-manso) - Nível 2 Maturação',
            'required_count' => 6,
            'note' => 'Complexidade média com supervisão pontual, e passa a devolver qualidade ao que já existe: teste, documentação e entendimento de como o código chega em produção.',
            'levels' => [
                ['Resolver demandas de complexidade média', 'task', 'hard', 'Resolve demanda que toca mais de um módulo, com supervisão só nos pontos de decisão.'],
                ['Escrever testes automatizados', 'course', 'hard', 'Escreve teste de feature e de unidade para o que entrega, usando factory e sabendo o que vale testar.'],
                ['Entender o deploy: pipeline no GitHub Actions e ambiente cPanel', 'task', 'hard', 'Sabe o que o workflow faz em cada ambiente e o que o deploy.sh executa no servidor.'],
                ['Contribuir com melhorias no código', 'task', 'hard', 'Deixa o código que tocou melhor do que encontrou, sem transformar a entrega em refatoração ampla.'],
                ['Contribuir com a documentação', 'other', 'hard', 'Registra o que fez de forma que a próxima pessoa não precise perguntar.'],
                ['Trabalhar com supervisão apenas pontual', 'proactivity', 'soft', 'Procura o líder nas decisões, não na execução.'],
            ],
        ],
        [
            'description' => 'Cajueiro Comum (Júnior Nvl 3)',
            'job_plan' => 'JÚNIOR (Caju-manso) - Nível 3 Amadurecimento (Líder)',
            'required_count' => 4,
            'note' => 'Primeira liderança. Apoia os outros juniores e ganha a chave da produção.',
            'levels' => [
                ['Apoiar outros juniores no dia a dia', 'leadership', 'soft', 'É procurado pelos colegas e desbloqueia sem assumir a tarefa no lugar deles.'],
                ['Participar de decisões técnicas simples', 'collaboration', 'soft', 'Opina com argumento nas escolhas do time e aceita quando outro caminho vence.'],
                ['Propor solução para um problema recorrente', 'task', 'hard', 'Identifica algo que se repete na fila e propõe a correção de raiz, não o remendo.'],
                ['Conduzir um deploy em produção', 'task', 'hard', 'Conduz um deploy do início ao fim, sabendo o que fazer se der errado.'],
            ],
        ],
        [
            'description' => 'Floração e Fecundação (Pleno Nvl 1)',
            'job_plan' => 'PLENO (Caju-manteiga) - Nível 1 Crescimento',
            'required_count' => 4,
            'note' => 'Autonomia em média/alta complexidade e entrada no planejamento técnico. Passa a decidir desenho, não só a executar.',
            'levels' => [
                ['Autonomia em demandas de média/alta complexidade', 'task', 'hard', 'Assume demanda de escopo amplo e a divide em entregas que fazem sentido isoladamente.'],
                ['Desenho de API e contrato entre front e back', 'task', 'hard', 'Define o contrato antes de codar, e o contrato sobrevive à implementação.'],
                ['Diagnóstico de performance: consultas e N+1', 'technical_test', 'hard', 'Identifica consulta lenta ou N+1 com evidência de medição, não por suspeita.'],
                ['Participar do planejamento técnico das entregas', 'collaboration', 'soft', 'Participa da estimativa e aponta risco técnico antes de a entrega começar.'],
            ],
        ],
        [
            'description' => 'Inflorescência (Pleno Nvl 2)',
            'job_plan' => 'PLENO (Caju-manteiga) - Nível 2 Maturação',
            'required_count' => 3,
            'note' => 'Conduz projeto inteiro, revisa o trabalho dos outros e propõe arquitetura.',
            'levels' => [
                ['Conduzir um projeto de ponta a ponta', 'task', 'hard', 'Conduz um projeto do levantamento à entrega, respondendo por ele.'],
                ['Revisar código de outros desenvolvedores', 'collaboration', 'soft', 'Faz revisão que ensina: aponta o problema e explica o porquê.'],
                ['Sugerir melhorias de arquitetura', 'task', 'hard', 'Propõe mudança estrutural com o custo e o ganho declarados.'],
            ],
        ],
        [
            'description' => 'Polinização (Pleno Nvl 3)',
            'job_plan' => 'PLENO (Caju-manteiga) - Nível 3 Amadurecimento (Líder)',
            'required_count' => 3,
            'note' => 'Referência técnica do time, orientando juniores e plenos.',
            'levels' => [
                ['Ser referência técnica do time', 'leadership', 'soft', 'É a pessoa a quem o time recorre nas dúvidas difíceis do produto.'],
                ['Orientar juniores e plenos', 'leadership', 'soft', 'Acompanha o desenvolvimento de outras pessoas de forma contínua, não pontual.'],
                ['Participar de decisões de arquitetura com a liderança', 'task', 'hard', 'Participa das decisões trazendo alternativa e o trade-off de cada uma.'],
            ],
        ],
        [
            'description' => 'Frutificação e Maturação (Sênior Nvl 1)',
            'job_plan' => 'SÊNIOR (Cajueiro) - Nível 1 Crescimento',
            'required_count' => 3,
            'note' => 'Projetos críticos, com leitura do risco de cada entrega.',
            'levels' => [
                ['Atuar em projeto crítico', 'task', 'hard', 'Responde por entrega em que a falha tem custo alto para o cliente.'],
                ['Visão ampla do produto e do risco de cada release', 'other', 'hard', 'Sabe dizer o que cada release coloca em risco antes de ela subir.'],
                ['Influenciar decisões técnicas da empresa', 'leadership', 'soft', 'Muda decisão técnica da empresa com argumento, não por cargo.'],
            ],
        ],
        [
            'description' => 'Crescimento da Castanha (Sênior Nvl 2)',
            'job_plan' => 'SÊNIOR (Cajueiro) - Nível 2 Maturação',
            'required_count' => 3,
            'note' => 'Define o padrão técnico da casa e lidera fora do próprio time.',
            'levels' => [
                ['Definir padrões técnicos e arquiteturais', 'task', 'hard', 'Escreve o padrão que os outros passam a seguir.'],
                ['Mentorar plenos', 'leadership', 'soft', 'Desenvolve quem já é autônomo, elevando o teto e não o piso.'],
                ['Liderar uma iniciativa multi-time', 'leadership', 'soft', 'Conduz trabalho que atravessa mais de um time até o fim.'],
            ],
        ],
        [
            'description' => 'Desenvolvimento do pedúnculo (Sênior Nvl 3)',
            'job_plan' => 'SÊNIOR (Cajueiro) - Nível 3 Amadurecimento (Líder)',
            'required_count' => 3,
            'note' => 'Liderança consolidada, com peso estratégico e representação externa.',
            'levels' => [
                ['Liderança técnica consolidada', 'leadership', 'soft', 'A liderança é reconhecida dentro e fora do time, sem precisar de mandato.'],
                ['Influência estratégica na empresa', 'leadership', 'soft', 'Participa de decisões que definem o rumo técnico da empresa.'],
                ['Representação técnica externa', 'communication', 'soft', 'Representa a CAJU Tec tecnicamente diante de cliente, parceiro ou comunidade.'],
            ],
        ],
    ];

    public function run()
    {
        $team = Team::where('name', self::TEAM)->whereNull('deleted_at')->first();

        if (!$team) {
            $this->command?->error('Time "' . self::TEAM . '" não encontrado. Rode o TeamsSeeder antes.');

            return;
        }

        // Cargos do time, por descrição. Só os deste time: as descrições se
        // repetem entre Dev, QA e Suporte, e pegar o cargo errado promoveria
        // o colaborador para outro setor.
        $plans = JobPlans::where('team_id', $team->id)
            ->whereNull('deleted_at')
            ->pluck('id', 'description');

        $missing = collect(self::STAGES)
            ->pluck('job_plan')
            ->reject(fn ($description) => $plans->has($description));

        if ($missing->isNotEmpty()) {
            $this->command?->error('Cargos não encontrados no time ' . self::TEAM . ': ' . $missing->implode(', '));

            return;
        }

        // R2 - quórum não pode exceder os níveis cadastrados. A validação da API
        // só roda na edição da etapa; aqui o seeder escreve direto pelo model,
        // então a conferência tem de ser nossa.
        $overQuorum = collect(self::STAGES)
            ->filter(fn ($stage) => $stage['required_count'] > count($stage['levels']))
            ->pluck('description');

        if ($overQuorum->isNotEmpty()) {
            $this->command?->error('Quórum maior que o número de níveis em: ' . $overQuorum->implode(', '));

            return;
        }

        // Idempotência: a trilha é recriada inteira. O delete do model já
        // arrasta etapas, níveis, materiais e matrículas (Trail::boot).
        Trail::where('team_id', $team->id)
            ->where('description', self::TRAIL)
            ->get()
            ->each->delete();

        $trail = Trail::create([
            'team_id' => $team->id,
            'description' => self::TRAIL,
            'note' => 'Caminho de evolução do setor de Desenvolvimento Web. Cada etapa espelha um degrau do plano de carreira da CAJU Tec e promove ao cargo seguinte ao ser concluída.',
            'color' => '#0080ff',
            'active' => true,
        ]);

        foreach (self::STAGES as $position => $stage) {
            $this->createStage($trail, $plans, $stage, $position + 1);
        }

        $levels = collect(self::STAGES)->sum(fn ($stage) => count($stage['levels']));

        $this->command?->info(
            'Trilha "' . self::TRAIL . '" criada com ' . count(self::STAGES) . ' etapas e ' . $levels . ' níveis.'
        );
    }

    private function createStage(Trail $trail, $plans, array $stage, int $position): void
    {
        $created = TrailStage::create([
            'trail_id' => $trail->id,
            'job_plan_id' => $plans[$stage['job_plan']],
            'description' => $stage['description'],
            'note' => $stage['note'],
            'position' => $position,
            'required_count' => $stage['required_count'],
        ]);

        foreach ($stage['levels'] as $index => [$description, $type, $skill, $note]) {
            TrailLevel::create([
                'trail_stage_id' => $created->id,
                'description' => $description,
                'note' => $note,
                'type' => $type,
                'skill' => $skill,
                'cut_score' => 70,
                'position' => $index + 1,
            ]);
        }
    }
}

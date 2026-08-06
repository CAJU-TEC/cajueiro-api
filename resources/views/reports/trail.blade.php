@php
    /**
     * Relatório da trilha (R11), individual ou de todos os matriculados.
     *
     * Recebe sempre uma lista: com um item sai o individual, com vários sai o
     * geral, que ganha um resumo no topo e uma página por colaborador.
     *
     * Layout todo em tabela, sem flex e sem grid: o wkhtmltopdf do projeto roda
     * num QtWebKit antigo — o mesmo aviso está em reports/birthdays.blade.php.
     */
    $tipos = [
        'task' => 'Tarefa',
        'course' => 'Curso',
        'platform' => 'Plataforma',
        'technical_test' => 'Teste técnico',
        'communication' => 'Comunicação',
        'empathy' => 'Empatia',
        'emotional_intelligence' => 'Inteligência emocional',
        'collaboration' => 'Colaboração',
        'proactivity' => 'Proatividade',
        'organization' => 'Organização',
        'leadership' => 'Liderança',
        'other' => 'Outro',
    ];

    $data = fn ($valor) => $valor ? \Illuminate\Support\Carbon::parse($valor)->format('d/m/Y') : '—';

    // Situação do nível numa palavra: é o que o líder lê na diagonal.
    $situacao = function (array $nivel) {
        if ($nivel['completed']) {
            return $nivel['reproved'] ? ['Reprovado', 'ruim'] : ['Aprovado', 'bom'];
        }

        if ($nivel['level_state'] === 'submitted') {
            return ['Aguardando', 'espera'];
        }

        return $nivel['period_state'] === 'late' ? ['Atrasado', 'ruim'] : ['A fazer', 'neutro'];
    };
@endphp
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            color: #37474f;
            font-size: 11px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .title {
            border-bottom: 2px solid #eceff1;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .title .eyebrow {
            font-size: 9px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #78909c;
        }

        .title .doc {
            font-size: 24px;
            font-weight: bold;
            color: #1565c0;
        }

        .title .meta {
            font-size: 11px;
            color: #607d8b;
        }

        .title .right {
            text-align: right;
            vertical-align: bottom;
        }

        .summary {
            margin-bottom: 20px;
            border: 1px solid #dbe6f3;
        }

        .summary th {
            background: #f3f7fc;
            padding: 6px 8px;
            text-align: left;
            font-size: 9px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #78909c;
            border-bottom: 1px solid #dbe6f3;
        }

        .summary td {
            padding: 6px 8px;
            border-bottom: 1px solid #eceff1;
        }

        .summary .center {
            text-align: center;
        }

        /* Um colaborador por página no relatório geral: o líder imprime e
           entrega a folha de cada um. */
        .break {
            page-break-before: always;
        }

        .head {
            border-bottom: 3px solid #1976d2;
            padding-bottom: 8px;
        }

        .head .eyebrow {
            font-size: 9px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #78909c;
        }

        .head .name {
            font-size: 21px;
            font-weight: bold;
            color: #1565c0;
        }

        .head .meta {
            font-size: 11px;
            color: #607d8b;
        }

        .head .right {
            text-align: right;
            vertical-align: bottom;
        }

        .pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 9px;
            color: #ffffff;
            font-weight: bold;
            font-size: 10px;
        }

        .totals {
            margin-top: 12px;
            background: #f3f7fc;
            border: 1px solid #dbe6f3;
        }

        .totals td {
            padding: 7px 10px;
            text-align: center;
            border-right: 1px solid #dbe6f3;
        }

        .totals td:last-child {
            border-right: 0;
        }

        .totals .value {
            font-size: 17px;
            font-weight: bold;
            color: #1565c0;
        }

        .totals .label {
            font-size: 8px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #78909c;
        }

        .stage {
            margin-top: 16px;
            /* Evita a etapa quebrar no meio entre duas páginas. */
            page-break-inside: avoid;
        }

        .stage-head td {
            background: #eceff1;
            padding: 6px 9px;
            font-weight: bold;
            font-size: 12px;
            border-top: 2px solid #b0bec5;
        }

        .stage-head .numbers {
            text-align: right;
            font-weight: normal;
            font-size: 10px;
            color: #546e7a;
        }

        .levels th {
            background: #fafafa;
            border-bottom: 1px solid #cfd8dc;
            padding: 5px 8px;
            text-align: left;
            font-size: 9px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #78909c;
        }

        .levels td {
            border-bottom: 1px solid #eceff1;
            padding: 6px 8px;
            vertical-align: top;
        }

        .levels .center {
            text-align: center;
        }

        .bom {
            /* Verde aqui é status, não marca: faz par com o vermelho de
               "Reprovado" e o laranja de "Aguardando". Trocar por azul apagaria
               a leitura imediata da coluna. */
            color: #2e7d32;
            font-weight: bold;
        }

        .ruim {
            color: #c62828;
            font-weight: bold;
        }

        .espera {
            color: #ef6c00;
            font-weight: bold;
        }

        .neutro {
            color: #90a4ae;
        }

        .answer {
            color: #607d8b;
            font-style: italic;
            padding-top: 2px;
        }

        .empty {
            padding: 10px;
            color: #90a4ae;
            text-align: center;
        }

        .foot {
            margin-top: 18px;
            border-top: 1px solid #cfd8dc;
            padding-top: 6px;
            font-size: 9px;
            color: #90a4ae;
        }
    </style>
</head>

<body>
    <table class="title">
        <tr>
            <td>
                <div class="eyebrow">Trilha de aprendizado</div>
                <div class="doc">{{ $titulo }}</div>
                <div class="meta">{{ $trilha }}</div>
            </td>
            <td class="right">
                <div class="meta">Emitido em {{ now()->format('d/m/Y \à\s H:i') }}</div>
                <div class="meta">{{ count($reports) }} matriculado(s)</div>
            </td>
        </tr>
    </table>

    {{-- Resumo só faz sentido no relatório de vários: com um matriculado ele
         repetiria os números do bloco logo abaixo. --}}
    @if (count($reports) > 1)
        <table class="summary">
            <tr>
                <th>Colaborador</th>
                <th>Cargo</th>
                <th class="center">Etapas</th>
                <th class="center">Níveis</th>
                <th class="center">Média</th>
                <th class="center">Aguard.</th>
                <th class="center">Atrasados</th>
            </tr>
            @foreach ($reports as $report)
                <tr>
                    <td>{{ $report['payload']['collaborator']['full_name'] }}</td>
                    <td>{{ $report['jobPlan']?->description ?? '—' }}</td>
                    <td class="center">
                        {{ $report['payload']['completed_stages_count'] }}/{{ $report['payload']['stages_count'] }}
                    </td>
                    <td class="center">
                        {{ $report['totals']['completed'] }}/{{ $report['totals']['levels'] }}
                    </td>
                    <td class="center">
                        {{ is_null($report['totals']['evaluation']) ? '—' : $report['totals']['evaluation'] . '%' }}
                    </td>
                    <td class="center">{{ $report['totals']['submitted'] }}</td>
                    <td class="center {{ $report['totals']['late'] ? 'ruim' : '' }}">
                        {{ $report['totals']['late'] }}
                    </td>
                </tr>
            @endforeach
        </table>
    @endif

    @foreach ($reports as $indice => $report)
        @php($payload = $report['payload'])
        @php($jobPlan = $report['jobPlan'])
        @php($totals = $report['totals'])

    <table class="head {{ $indice > 0 ? 'break' : '' }}">
        <tr>
            <td>
                <div class="eyebrow">Trilha de aprendizado</div>
                <div class="name">{{ $payload['collaborator']['full_name'] }}</div>
                <div class="meta">
                    {{ $payload['trail']['description'] }}
                    @if ($payload['team'])
                        &middot; time {{ $payload['team']->name }}
                    @endif
                </div>
            </td>
            <td class="right">
                @if ($jobPlan)
                    <span class="pill" style="background: {{ $jobPlan->badge_color ?: '#1976d2' }}">
                        {{ $jobPlan->description }}
                    </span>
                @endif
                <div class="meta" style="padding-top: 4px">
                    {{ $payload['completed_stages_count'] }} de {{ $payload['stages_count'] }} etapas
                </div>
            </td>
        </tr>
    </table>

    <table class="totals">
        <tr>
            <td>
                <div class="value">{{ $totals['completed'] }}/{{ $totals['levels'] }}</div>
                <div class="label">Níveis concluídos</div>
            </td>
            <td>
                <div class="value">{{ is_null($totals['evaluation']) ? '—' : $totals['evaluation'] . '%' }}</div>
                <div class="label">Média das notas</div>
            </td>
            <td>
                <div class="value">{{ $totals['reproved'] }}</div>
                <div class="label">Reprovados</div>
            </td>
            <td>
                <div class="value">{{ $totals['submitted'] }}</div>
                <div class="label">Aguardando</div>
            </td>
            <td>
                <div class="value">{{ $totals['late'] }}</div>
                <div class="label">Atrasados</div>
            </td>
            <td>
                <div class="value">{{ $totals['certificates'] }}</div>
                <div class="label">Certificados</div>
            </td>
        </tr>
    </table>

    @foreach ($payload['stages'] as $stage)
        <table class="stage">
            <tr class="stage-head">
                <td>{{ $stage['position'] }}. {{ $stage['description'] }}</td>
                <td class="numbers">
                    {{ $stage['completed_levels_count'] }}/{{ $stage['required_count'] }} níveis
                    @if (!is_null($stage['evaluation_percent']))
                        &middot; nota {{ $stage['evaluation_percent'] }}%
                    @endif
                    @if ($stage['job_plan'])
                        &middot; {{ $stage['job_plan']->description }}
                    @endif
                </td>
            </tr>
        </table>

        <table class="levels">
            @if (count($stage['levels']))
                <tr>
                    <th style="width: 38%">Nível</th>
                    <th style="width: 12%">Competência</th>
                    <th style="width: 15%">Tema</th>
                    <th style="width: 15%">Prazo</th>
                    <th class="center" style="width: 8%">Nota</th>
                    <th class="center" style="width: 12%">Situação</th>
                </tr>
                @foreach ($stage['levels'] as $level)
                    @php([$rotulo, $classe] = $situacao($level))
                    <tr>
                        <td>
                            {{ $level['description'] }}
                            @if ($level['evaluation_note'])
                                <div class="answer">&ldquo;{{ $level['evaluation_note'] }}&rdquo;</div>
                            @endif
                        </td>
                        <td>{{ $level['skill'] === 'soft' ? 'Soft skill' : 'Hard skill' }}</td>
                        <td>{{ $tipos[$level['type']] ?? $level['type'] }}</td>
                        <td>
                            @if ($level['ends_at'])
                                {{ $data($level['starts_at']) }} a {{ $data($level['ends_at']) }}
                            @else
                                <span class="neutro">sem prazo</span>
                            @endif
                        </td>
                        <td class="center">
                            @if (is_null($level['score']))
                                <span class="neutro">—</span>
                            @else
                                <span class="{{ $level['reproved'] ? 'ruim' : 'bom' }}">
                                    {{ $level['score'] }}%
                                </span>
                                <div class="label" style="font-size: 8px; color: #b0bec5">
                                    corte {{ $level['cut_score'] }}%
                                </div>
                            @endif
                        </td>
                        <td class="center">
                            <span class="{{ $classe }}">{{ $rotulo }}</span>
                            {{-- Só se o certificado existe, nunca o arquivo. --}}
                            <div style="font-size: 8px; color: #90a4ae">
                                certificado: {{ $level['certificate_uri'] ? 'sim' : 'não' }}
                            </div>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td class="empty">Nenhum nível cadastrado nesta etapa.</td>
                </tr>
            @endif
        </table>
    @endforeach

    @if (!count($payload['stages']))
        <table>
            <tr>
                <td class="empty">Esta trilha ainda não tem etapas cadastradas.</td>
            </tr>
        </table>
    @endif

    @endforeach

    <table class="foot">
        <tr>
            <td>{{ $trilha }}</td>
            <td style="text-align: right">CAJU Tec &middot; Trilha de aprendizado</td>
        </tr>
    </table>
</body>

</html>

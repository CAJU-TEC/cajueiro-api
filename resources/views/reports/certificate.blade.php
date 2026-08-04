<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Certificado - {{ $payload['stage']->description }}</title>
    <style>
        /* wkhtmltopdf do projeto usa QtWebKit antigo: sem flex/grid, layout todo em tabela. */
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            color: #3f4254;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .sheet {
            width: 100%;
            height: 560px;
            border-top: 18px solid #2a3ec8;
            border-bottom: 18px solid #f4614e;
        }

        .sheet td {
            vertical-align: middle;
            text-align: center;
            padding: 0 60px;
        }

        .eyebrow {
            font-size: 13px;
            letter-spacing: 6px;
            text-transform: uppercase;
            color: #8c93a8;
            padding-bottom: 6px;
        }

        .title {
            font-size: 44px;
            font-weight: bold;
            letter-spacing: 3px;
            color: #2a3ec8;
            text-transform: uppercase;
            padding-bottom: 26px;
        }

        .lead {
            font-size: 15px;
            color: #6b7186;
            padding-bottom: 8px;
        }

        .name {
            font-size: 40px;
            font-weight: bold;
            color: #1b1f33;
            padding-bottom: 4px;
        }

        .rule {
            border-bottom: 2px solid #e4e6ef;
            width: 60%;
            margin: 10px auto 22px auto;
        }

        .body-text {
            font-size: 16px;
            line-height: 1.7em;
            color: #3f4254;
            padding-bottom: 30px;
        }

        .highlight {
            font-weight: bold;
            color: #1b1f33;
        }

        .plan {
            display: inline-block;
            font-size: 15px;
            font-weight: bold;
            color: #ffffff;
            background-color: {{ $payload['jobPlan']->badge_color ?? '#2a3ec8' }};
            padding: 6px 18px;
        }

        .footer {
            width: 100%;
            font-size: 12px;
            color: #8c93a8;
        }

        .footer td {
            padding: 0 60px;
            vertical-align: top;
        }

        .footer-label {
            text-transform: uppercase;
            letter-spacing: 2px;
            padding-bottom: 4px;
        }

        .footer-value {
            font-size: 14px;
            font-weight: bold;
            color: #3f4254;
        }
    </style>
</head>

<body>
    <table class="sheet">
        <tr>
            <td>
                <div class="eyebrow">Cajueiro &middot; Trilha de aprendizado</div>
                <div class="title">Certificado</div>

                <div class="lead">Certificamos que</div>
                <div class="name">{{ $payload['collaborator']->full_name }}</div>
                <div class="rule"></div>

                <div class="body-text">
                    concluiu a etapa <span class="highlight">{{ $payload['stage']->description }}</span>
                    da trilha <span class="highlight">{{ $payload['trail']->description ?? '-' }}</span>
                    @if ($payload['team'])
                        do time <span class="highlight">{{ $payload['team']->name }}</span>
                    @endif
                    .
                </div>

                @if ($payload['jobPlan'])
                    <div class="plan">{{ $payload['jobPlan']->description }}</div>
                @endif
            </td>
        </tr>
    </table>

    <table class="footer">
        <tr>
            <td align="left" width="50%">
                <div class="footer-label">Concluído em</div>
                <div class="footer-value">
                    {{ \Carbon\Carbon::parse($payload['completion']->completed_at)->format('d/m/Y') }}
                </div>
            </td>
            <td align="right" width="50%">
                <div class="footer-label">Código de validação</div>
                <div class="footer-value">{{ $payload['completion']->certificate_code ?? '-' }}</div>
            </td>
        </tr>
    </table>
</body>

</html>

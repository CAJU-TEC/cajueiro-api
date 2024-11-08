<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        table {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
            text-align: center;
        }

        th,
        td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 12px;
        }

        th {
            background-color: #cecccc;
        }

        /* Estilos para as células de cabeçalho */
        th {
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Estilos para células de dados */
        td {
            font-size: 10px;
        }

        tr td:first-child {
            text-align: left;
        }

        tr td div {
            text-align: left;
            display: block;
        }

        tfoot tr td {
            text-align: left;
            font-size: 10px;
        }

        .font-format::before {
            content: "•";
            font-size: 1.5em;
        }

        .green {
            color: rgb(167, 190, 167);
        }

        .yellow {
            color: rgb(212, 212, 177);
        }

        .gray {
            color: rgb(202, 202, 202);
        }

        .blue {
            color: rgb(163, 163, 216);
        }

        .purple {
            color: rgb(223, 144, 223);
        }

        .orange {
            color: rgb(240, 210, 156);
        }

        .darkgreen {
            color: rgb(135, 167, 135);
        }

        .teal {
            color: rgb(153, 206, 206);
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="8">Ficheiro de controle de protocolos</th>
            </tr>
            <tr>
                <th>Código: {{ $payload['checkList']->code }}</th>
                <th>Descrição: {{ $payload['checkList']->description ?? 'Sem nomeclatura' }}</th>
                <th>Responsáve(is):
                    {{ $payload['checkList']->collaborators->map(function ($r) {return $r->fullname;})->implode(', ') }}
                </th>
                <th style="background-color: {{ $payload['checkList']->status['color'] }}">Situação:
                    {{ $payload['checkList']->status['description'] }}</th>
                <th>Período: {{ $payload['checkList']->started }} à {{ $payload['checkList']->delivered }}</th>
            </tr>
        </thead>
    </table>
    <br />
    <table>
        <thead>
            <th width="40%">PROTOCOLO</th>
            <th width="10%">DEV</th>
            <th width="10%">STATUS</th>
            <th width="10%">TEMPO DE EXECUÇÃO</th>
            <th width="10%">INÍCIO(h)</th>
            <th width="10%">FIM(h)</th>
            <th width="10%">QUANTO TEMPO LEVOU</th>
        </thead>
        <tbody>
            @forelse($payload['checkList']->tickets as $ticket)
                <tr>
                    <td style="text-align: left;">
                        #{{ $ticket->code }} - <strong>{{ $ticket->subject }}</strong><br />
                        Criado: {{ $ticket->created_at->format('d/m/Y') }}
                    </td>
                    <td>{{ $ticket->collaborator?->full_name }}</td>
                    <td>
                        <span class="{{ $ticket->statusCast['color'] }} font-format"></span>
                        {{ $ticket->statusCast['description'] ?? '' }}
                    </td>
                    <td>{{ $ticket->timeForHuman ?? '' }}</td>
                    <td>{{ $ticket->date_attribute_ticket ? \Carbon\Carbon::parse($ticket->date_attribute_ticket)->format('d/m/Y à\s H:i:s') : '' }}
                    </td>
                    <td></td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Sem tickets no momento</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8" style="text-align: right;">
                    <i style="color: rgb(94, 92, 92);">Relatório impresso em:
                        {{ Carbon\Carbon::now()->format('d/m/Y à\s H:i:s') }} por:
                        {{ auth()->user()->name }}</i>
                </td>
            </tr>
        </tfoot>
    </table>
</body>

</html>

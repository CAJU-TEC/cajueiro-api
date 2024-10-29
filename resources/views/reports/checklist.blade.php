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
            font-size: 14px;
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
            <th width="5%">CÓD</th>
            <th width="30%">PROTOCOLO</th>
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
                    <td style="text-align: center;">{{ $ticket->code }}</td>
                    <td style="text-align: left;">{{ $ticket->subject }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
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
                <td colspan="8">
                    Relatório impresso em: {{ Carbon\Carbon::now()->format('d/m/Y à\s H:i:s') }}
                </td>
            </tr>
        </tfoot>
    </table>
</body>

</html>

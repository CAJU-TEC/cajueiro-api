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
        }

        th {
            background-color: #f2f2f2;
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

        th:first-child {
            width: 50%;
            background-color: {{ $payload['ticket']->priority === 'no' ? '#06A3DE' : '#E42D0C' }};
        }

        tr td:first-child {
            text-align: left;
        }

        tr td div {
            text-align: left;
            display: block;
        }

        tbody tr:nth-child(2) td {
            text-align: center;
            padding: 20px;
            font-size: 4rem;
            font-weight: bold;
        }

        tbody tr:nth-child(2) td div {
            text-align: center;
            padding: 5px;
            font-size: 1rem;
            font-weight: 100;
        }

        tfoot tr td {
            text-align: left;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th>PRIORIDADE</th>
                <?php
                $colors = [
                    'backlog' => ['color' => 'green-11', 'hex' => '#DAF7A6', 'title' => 'AGUARDANDO'],
                    'todo' => ['color' => 'orange', 'hex' => '#FFC300', 'title' => 'A FAZER'],
                    'analyze' => ['color' => 'deep-orange-4', 'hex' => '#FF5733', 'title' => 'ANALISE'],
                    'development' => ['color' => 'deep-orange-14', 'hex' => '#C70039', 'title' => 'DESENVOLVIMENTO'],
                    'test' => ['color' => 'red-14', 'hex' => '#900C3F', 'title' => 'TESTE'],
                    'pending' => ['color' => 'brown-10', 'hex' => '#581845', 'title' => 'PENDENTE'],
                    'done' => ['color' => 'blue-grey-2', 'hex' => '#A6A9A7', 'title' => 'FINALIZADO]'],
                ];
                ?>
                <th style="background-color: {{ $colors[$payload['ticket']->status]['hex'] }}">
                    {{ $colors[$payload['ticket']->status]['title'] }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="2">
                    <div>Técnico:</div>
                    <strong>{{ $payload['ticket']->user->name ?? 'SEM TÉCNICO' }}</strong>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div>Protocolo</div>
                    #{{ $payload['ticket']->code }}
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td>ABERTO EM:
                    <strong>{{ !empty($payload['ticket']->created_at) ? $payload['ticket']->created_at->format('d/m/Y H:i') : '' }}</strong>
                </td>
                <td>PRAZO:
                    <strong>{{ !empty($payload['ticket']->created_at) ? $payload['ticket']->created_at->addDays($payload['ticket']->impact->days)->format('d/m/Y') : '' }}
                        - {{ $payload['ticket']->impact->days }} dia(s)</strong>
                </td>
            </tr>
            <tr>
                <td>INÍCIO:
                    <strong>{{ !empty($payload['ticket']->date_attribute_ticket) ? Carbon\Carbon::parse($payload['ticket']->date_attribute_ticket)->format('d/m/Y H:i') : '' }}</strong>
                </td>
                <td>FECHADO EM:</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>

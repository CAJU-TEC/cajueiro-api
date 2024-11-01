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
                <th>Código: <?php echo e($payload['checkList']->code); ?></th>
                <th>Descrição: <?php echo e($payload['checkList']->description ?? 'Sem nomeclatura'); ?></th>
                <th>Responsáve(is):
                    <?php echo e($payload['checkList']->collaborators->map(function ($r) {return $r->fullname;})->implode(', ')); ?>

                </th>
                <th style="background-color: <?php echo e($payload['checkList']->status['color']); ?>">Situação:
                    <?php echo e($payload['checkList']->status['description']); ?></th>
                <th>Período: <?php echo e($payload['checkList']->started); ?> à <?php echo e($payload['checkList']->delivered); ?></th>
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
            <?php $__empty_1 = true; $__currentLoopData = $payload['checkList']->tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td style="text-align: center;"><?php echo e($ticket->code); ?></td>
                    <td style="text-align: left;"><?php echo e($ticket->subject); ?></td>
                    <td><?php echo e($ticket->collaborator?->full_name); ?></td>
                    <td>
                        <span class="<?php echo e($ticket->statusCast['color']); ?> font-format"></span>
                        <?php echo e($ticket->statusCast['description'] ?? ''); ?>

                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8">Sem tickets no momento</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8">
                    Relatório impresso em: <?php echo e(Carbon\Carbon::now()->format('d/m/Y à\s H:i:s')); ?>

                </td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
<?php /**PATH /home/caju10/Documentos/CAJUEIRO/API/resources/views/reports/checklist.blade.php ENDPATH**/ ?>
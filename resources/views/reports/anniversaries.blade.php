<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Aniversário de Casa Caju</title>
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

        .header {
            width: 100%;
            height: 150px;
        }

        .header td {
            vertical-align: middle;
            padding: 0;
        }

        .header-brand {
            background-color: #2a3ec8;
            width: 62%;
            padding: 18px 24px !important;
        }

        .header-art {
            background-color: #f4614e;
            width: 38%;
            text-align: center;
        }

        .header-title {
            color: #ffffff;
            font-size: 30px;
            font-weight: bold;
            letter-spacing: 1px;
            line-height: 1.05em;
        }

        .header-title-big {
            color: #ffffff;
            font-size: 62px;
            font-weight: bold;
            letter-spacing: 2px;
            line-height: 1em;
        }

        .header-signature {
            color: #ffffff;
            font-size: 15px;
            font-weight: bold;
            padding-top: 10px;
        }

        .header-signature span {
            font-weight: normal;
        }

        .header-year {
            color: #ffffff;
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 3px;
            padding-left: 10px;
        }

        /* line-height + vertical-align centram a logo dentro do caju
           sem depender de flex, que o QtWebKit não resolve. */
        .blob {
            background-color: #f0a03c;
            width: 128px;
            height: 104px;
            line-height: 104px;
            border-radius: 64px 52px 64px 52px;
            display: inline-block;
            margin-left: -46px;
            text-align: center;
        }

        .blob img {
            height: 62px;
            vertical-align: middle;
        }

        .months {
            margin-top: 22px;
        }

        /* O espaçamento entre as linhas vem do padding, e não de height:
           altura fixa na célula faz o QtWebKit esticar a tabela interna de nomes. */
        .months td {
            width: 25%;
            vertical-align: top;
            padding: 0 5px 78px 5px;
        }

        .month-card {
            height: 235px;
            border: 1px solid #dfe1ec;
            border-radius: 10px;
            padding: 8px;
        }

        .month-chip {
            border-radius: 8px;
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            padding: 7px 10px;
            margin-bottom: 4px;
        }

        .chip-orange {
            background-color: #f0a03c;
        }

        .chip-blue {
            background-color: #2a6cd4;
        }

        .chip-green {
            background-color: #2e7d5b;
        }

        .chip-coral {
            background-color: #f4614e;
        }

        .month-body {
            border-top: 1px solid #e4e6ef;
            padding-top: 8px;
        }

        .person {
            width: 100%;
            font-size: 13px;
        }

        .person td {
            padding: 4px 0;
            border: none;
        }

        .person-star {
            color: #f0a03c;
            font-size: 13px;
            padding-right: 4px;
        }

        .person-name {
            color: #55596b;
            font-weight: bold;
            letter-spacing: 0.5px;
            vertical-align: middle;
        }

        /* Data em linha própria: nome + data + selo na mesma linha estouram
           a largura de 25% do cartaz e quebravam de forma irregular. */
        .person-date {
            display: block;
            color: #9a9eb1;
            font-size: 11px;
            font-weight: normal;
            letter-spacing: 0;
            padding-left: 17px;
        }

        .person-years {
            text-align: right;
            white-space: nowrap;
            vertical-align: middle;
        }

        /* A cor vem da mesma classe chip-* do mês, para o tempo de casa casar com o cabeçalho do card. */
        .years-badge {
            display: inline-block;
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            border-radius: 9px;
            padding: 2px 7px;
        }

        .month-empty {
            color: #b5b9c9;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1px;
            text-align: center;
            line-height: 1.5em;
            padding: 14px 4px;
        }

        .footer {
            width: 100%;
            height: 150px;
            background-color: #2a3ec8;
        }

        .footer td {
            padding: 0;
            vertical-align: middle;
        }

        .footer-art {
            width: 30%;
        }

        /* A arte da árvore é densa demais para caber inteira em 150px: entra ampliada,
           recortada pela faixa e esmaecida, como marca d'água. */
        .footer-art__frame {
            height: 150px;
            overflow: hidden;
            position: relative;
        }

        .footer-art__frame img {
            position: absolute;
            top: -95px;
            left: -30px;
            height: 340px;
            opacity: 0.3;
        }

        .footer-text {
            color: #ffffff;
            font-size: 17px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-align: center;
        }

        .footer-mark {
            width: 20%;
            text-align: right;
            padding-right: 20px !important;
            white-space: nowrap;
        }

        .footer-mark img {
            height: 36px;
            vertical-align: middle;
        }

        .footer-signature {
            color: #ffffff;
            font-size: 14px;
            font-weight: bold;
            padding-left: 8px;
            vertical-align: middle;
        }

        .footer-signature span {
            font-weight: normal;
        }
    </style>
</head>

<body>
    @php
        $chips = ['chip-orange', 'chip-blue', 'chip-green', 'chip-coral'];
    @endphp

    <table class="header">
        <tr>
            <td class="header-brand">
                <div class="header-title">ANIVERSÁRIO DE</div>
                <div class="header-title-big">CASA</div>
                <div class="header-signature">
                    CAJU<span>tec.</span>
                    <span class="header-year">{{ $payload['year'] }}</span>
                </div>
            </td>
            <td class="header-art">
                <div class="blob">
                    @if ($payload['brand'])
                        <img src="{{ $payload['brand'] }}">
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="months">
        @foreach (array_chunk($payload['months'], 4, true) as $row)
            <tr>
                @foreach ($row as $number => $name)
                    <td>
                        <div class="month-card">
                            <div class="month-chip {{ $chips[($number - 1) % 4] }}">{{ $name }}</div>
                            <div class="month-body">
                                @if (count($payload['anniversaries'][$number]))
                                    <table class="person">
                                        @foreach ($payload['anniversaries'][$number] as $person)
                                            <tr>
                                                <td class="person-name">
                                                    <span class="person-star">&#10035;</span>{{ $person['name'] }}
                                                    <span class="person-date">{{ $person['day'] }}</span>
                                                </td>
                                                <td class="person-years" valign="top">
                                                    <span class="years-badge {{ $chips[($number - 1) % 4] }}">
                                                        {{ $person['label'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @else
                                    <div class="month-empty">NÃO HÁ<br>ANIVERSÁRIOS</div>
                                @endif
                            </div>
                        </div>
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>

    <table class="footer">
        <tr>
            <td class="footer-art">
                @if ($payload['tree'])
                    <div class="footer-art__frame"><img src="{{ $payload['tree'] }}"></div>
                @endif
            </td>
            <td class="footer-text">
                Caju &nbsp;&#10035;&nbsp; Castanha &nbsp;&#10035;&nbsp; Cajuína &amp; Doce de caju
            </td>
            <td class="footer-mark">
                @if ($payload['brand'])
                    <img src="{{ $payload['brand'] }}">
                @endif
                <span class="footer-signature">CAJU<span>tec.</span></span>
            </td>
        </tr>
    </table>
</body>

</html>

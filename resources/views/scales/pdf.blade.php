<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Escala</title>
    <style>
        body { font-family: sans-serif; font-size: 16px; }
        .header { text-align: center; margin-bottom: 100px; } /* Ajustei margem */
        .logo { float: left; display: block; margin-left: 50px; } /* Ajuste logo */
        .title { float: right; font-weight: bold; font-size: 30px; margin-top: 25px; margin-right: 100px; text-transform: uppercase; }
        .period-box { border: 2px solid black; padding: 5px; font-size: 17px; font-weight: bold; text-align: center; margin-bottom: 20px; clear: both; }
        
        /* Layout de Colunas */
        .row { width: 100%; clear: both; margin-bottom: 15px; }
        .col-left { float: left; width: 48%; margin-top: 25px; }
        .col-right { float: right; width: 48%; margin-top: 25px; }
        
        /* Caixas */
        .day-header { border: 2px solid black; padding: 5px; font-weight: bold; text-align: center; margin-bottom: 5px; background-color: #fff; }
        
        /* Tabela */
        table { width: 100%; border-collapse: collapse; }
        td { padding: 4px; }
        .time-col { width: 40%; white-space: nowrap; }
        .name-col { text-transform: uppercase; padding-left: 10px; }
        .folga { font-weight: bold; }

        /* Rodapé Fixo */
        .footer { position: fixed; bottom: 0; left: 0; width: 100%; text-align: center; font-size: 10px; color: #000; border-top: 1px solid #ccc; padding-top: 5px; }
    </style>
</head>
<body>

    <div class="footer">
        Gerado em: {{ date('d/m/Y \à\s H:i') }} por {{ Auth::user()->name }}
    </div>
    
    <div class="header">
        <img src="{{ public_path('band_logo.png') }}" class="logo">
        <div class="title">ESCALA EXIBIÇÃO</div>
    </div>

    <div class="period-box">
        PERÍODO DE {{ $startDate->format('d/m/Y') }} a {{ $endDate->format('d/m/Y') }}
    </div>

    @php 
        $chunkedDays = $days->chunk(2);
    @endphp

    @foreach($chunkedDays as $chunk)
    <div class="row">
        @foreach($chunk as $dateString => $shifts)
            @php $date = \Carbon\Carbon::parse($dateString); @endphp
            
            <div class="{{ $loop->first ? 'col-left' : 'col-right' }}">
                <div class="day-header">
                    {{ $date->format('d/m/Y') }} - {{ mb_strtoupper($date->locale('pt_BR')->dayName, 'UTF-8') }}
                </div>
                <table>
                    @foreach($shifts as $shift)
                    <tr class="{{ $shift->name == 'FOLGA' ? 'folga' : '' }}">
                        <td class="time-col">{{ $shift->name }}</td>
                        <td class="name-col">
                            @if($shift->user_id)
                                @php
                                    $smartUser = $users->firstWhere('id', $shift->user_id);
                                @endphp
                                {{ $smartUser ? $smartUser->display_name : 'Usuario Removido' }}
                            @else
                                ---
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
        @endforeach
    </div>
    @endforeach

</body>
</html>
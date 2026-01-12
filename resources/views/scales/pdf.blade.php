<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>ESCALA EXIBIÇÃO</title>
    <style>
        {!! file_get_contents(public_path('css/pdf.css')) !!}
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ public_path('band_logo.png') }}" class="logo">
        <div class="title">ESCALA EXIBIÇÃO</div>

        <div class="period-box">
            PERÍODO DE {{ $startDate->format('d/m/Y') }} a {{ $endDate->format('d/m/Y') }}
        </div>
    </div>

    <div class="footer">
        Gerado em: {{ date('d/m/Y \à\s H:i') }} por {{ Auth::user()->name }}
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
                                {{ $smartUser ? $smartUser->display_name : '---' }}
                            @else
                                ---
                            @endif
                        </td>
                    </tr>
                    @endforeach

                    @php
                        $padraoLinhas = 5; 
                        $linhasAtuais = count($shifts);
                        $linhasFaltantes = $padraoLinhas - $linhasAtuais;
                    @endphp

                    @if($linhasFaltantes > 0)
                        @for($i = 0; $i < $linhasFaltantes; $i++)
                            <tr>
                                <td class="time-col">&nbsp;</td>
                                <td class="name-col">&nbsp;</td>
                            </tr>
                        @endfor
                    @endif
                </table>
            </div>
        @endforeach
    </div>
    @endforeach

</body>
</html>
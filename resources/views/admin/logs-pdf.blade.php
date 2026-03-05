<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Logs de Atividades</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            margin-top: 130px;
            margin-bottom: 40px;
        }

        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 120px;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
        }

        .header .logo {
            float: left;
            margin-top: 8px;
            margin-left: 10px;
        }

        .header .title {
            float: right;
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 18px;
            margin-right: 10px;
        }

        .header .subtitle {
            clear: both;
            text-align: center;
            font-size: 11px;
            color: #444;
            margin-top: 6px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #555;
            border-top: 1px solid #ccc;
            padding-top: 4px;
            background-color: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead th {
            background-color: #222;
            color: #fff;
            padding: 5px 6px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }

        tbody tr:nth-child(even) {
            background-color: #f5f5f5;
        }

        tbody td {
            padding: 4px 6px;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }

        .badge {
            background-color: #555;
            color: #fff;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 9px;
        }

        .text-muted {
            color: #777;
        }

        ul {
            margin: 0;
            padding-left: 14px;
        }

        li {
            margin-bottom: 1px;
        }

        .col-datetime {
            width: 12%;
        }

        .col-user {
            width: 18%;
        }

        .col-module {
            width: 12%;
        }

        .col-action {
            width: 18%;
        }

        .col-details {
            width: 40%;
        }
    </style>
</head>

<body>

    <div class="header">
        <img src="{{ public_path('band_logo.png') }}" class="logo" height="70">
        <div class="title">SGCM - Logs de Atividades</div>
        <div class="subtitle">
            @if($filters['user'] || $filters['module'] || $filters['date'])
                Filtros:
                @if($filters['user']) Usuário: <strong>{{ $filters['user'] }}</strong> @endif
                @if($filters['module']) &nbsp;| Módulo: <strong>{{ $filters['module'] }}</strong> @endif
                @if($filters['date']) &nbsp;| Data:
                <strong>{{ \Carbon\Carbon::parse($filters['date'])->format('d/m/Y') }}</strong> @endif
            @else
                Todos os registros
            @endif
            &nbsp;&nbsp;|&nbsp;&nbsp; Total: <strong>{{ $logs->count() }}</strong> registros
        </div>
    </div>

    <div class="footer">
        Gerado em: {{ date('d/m/Y \à\s H:i') }} por {{ Auth::user()->name }}
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-datetime">Data/Hora</th>
                <th class="col-user">Usuário</th>
                <th class="col-module">Módulo</th>
                <th class="col-action">Ação</th>
                <th class="col-details">Detalhes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>
                        {{ $log->created_at->format('d/m/Y') }}<br>
                        <span class="text-muted">{{ $log->created_at->format('H:i:s') }}</span>
                    </td>
                    <td>
                        <strong>{{ $log->user ? $log->user->name : 'Usuário Removido (ID ' . $log->user_id . ')' }}</strong><br>
                        <span class="text-muted">{{ $log->user ? $log->user->email : '-' }}</span>
                    </td>
                    <td><span class="badge">{{ $log->module }}</span></td>
                    <td>{{ $log->action }}</td>
                    <td>
                        @if($log->details)
                            <ul>
                                @foreach($log->details as $key => $value)
                                    <li>
                                        <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                        @if(is_array($value))
                                            <ul>
                                                @foreach($value as $sk => $sv)
                                                    <li><em>{{ ucfirst(str_replace('_', ' ', $sk)) }}:</em>
                                                        {{ is_array($sv) ? json_encode($sv) : $sv }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: #777;">
                        Nenhum registro encontrado com os filtros aplicados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>
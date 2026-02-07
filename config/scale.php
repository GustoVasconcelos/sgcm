<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Número de Operadores Exigidos
    |--------------------------------------------------------------------------
    |
    | Quantidade de operadores ativos necessários para a geração automática
    | de escalas funcionar corretamente.
    |
    */
    'required_operators' => 5,

    /*
    |--------------------------------------------------------------------------
    | Layout de Turnos (6 horas)
    |--------------------------------------------------------------------------
    |
    | Estrutura padrão de turnos de 6 horas.
    | Cada turno tem um 'name' (rótulo exibido) e 'order' (posição).
    |
    */
    'shifts_6h' => [
        ['name' => '06:00 - 12:00', 'order' => 1],
        ['name' => '12:00 - 18:00', 'order' => 2],
        ['name' => '18:00 - 00:00', 'order' => 3],
        ['name' => '00:00 - 06:00', 'order' => 4],
        ['name' => 'FOLGA',         'order' => 5],
    ],

    /*
    |--------------------------------------------------------------------------
    | Layout de Turnos (8 horas)
    |--------------------------------------------------------------------------
    |
    | Estrutura alternativa de turnos de 8 horas.
    |
    */
    'shifts_8h' => [
        ['name' => '06:00 - 14:00', 'order' => 1],
        ['name' => '14:00 - 22:00', 'order' => 2],
        ['name' => '22:00 - 06:00', 'order' => 3],
        ['name' => 'FOLGA',         'order' => 4],
    ],

    /*
    |--------------------------------------------------------------------------
    | Usuário Placeholder
    |--------------------------------------------------------------------------
    |
    | Nome do usuário "dummy" usado para indicar que não há operador
    | designado para um turno.
    |
    */
    'placeholder_user' => 'NÃO HÁ',
];

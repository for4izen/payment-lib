<?php

return [


    /*
    |--------------------------------------------------------------------------
    | Provedores de Pagamento
    |--------------------------------------------------------------------------
    | Aqui você pode configurar cada gateway de pagamento disponível.
    | O pacote usa essas informações para resolver qual gateway usar.
    */
    'providers' => [
        'mercadopago' => [
            'name' => 'Mercado Pago',
            'class' => \EliteHub\Payment\Gateways\MercadoPagoGateway::class,
            'enabled' => true,
            'config' => [
                'token' => env('MERCADOPAGO_TOKEN'),
                'back_url' => env('MERCADOPAGO_BACK_URL'),
                'notification_url' => env('MERCADOPAGO_NOTIFICATION_URL'),
            ],
        ],
        // Você pode adicionar mais gateways aqui futuramente...
    ],

    /*
    |--------------------------------------------------------------------------
    | Comportamento Padrão
    |--------------------------------------------------------------------------
    | Defina o gateway padrão que será usado se não houver outro especificado.
    */
    'default_provider' => 'mercadopago',

    /*
    |--------------------------------------------------------------------------
    | Opções Gerais
    |--------------------------------------------------------------------------
    | Algumas opções úteis para controle do fluxo de pagamentos.
    */
    'options' => [
        'auto_confirm' => true, // confirma pagamentos aprovados automaticamente
        'log_enabled' => true, // habilita logs detalhados do processo de pagamento
    ],
];

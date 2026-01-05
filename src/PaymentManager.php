<?php

namespace EliteHub\Payment;

use EliteHub\Payment\Gateways\MercadoPagoGateway;
use EliteHub\Payment\Models\PaymentProvider;
use Exception;

use EliteHub\Payment\Contracts\PaymentGatewayInterface;

class PaymentManager
{
    public static function resolve(PaymentProvider $provider): PaymentGatewayInterface
    {
        return match ($provider->provider_key) {
            'mercadopago' => app(MercadoPagoGateway::class),
            default => throw new Exception('Gateway não suportado'),
        };
    }
}

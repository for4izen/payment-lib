<?php

namespace EliteHub\Payment;

use EliteHub\Payment\Gateways\MercadoPagoGateway;
use Exception;

use EliteHub\Payment\Contracts\PaymentGatewayInterface;

class PaymentManager
{
    public static function resolve(string $providerKey): PaymentGatewayInterface
    {
        return match ($providerKey) {
            'mercadopago' => app(MercadoPagoGateway::class),
            default => throw new Exception("Gateway '{$providerKey}' não suportado"),
        };
    }
}

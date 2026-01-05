<?php

namespace EliteHub\Payment\Contracts;

use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    public function initiate($order, $payment): array;
    public function handleWebhook(Request $request);
}

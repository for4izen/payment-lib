<?php

namespace EliteHub\Payment\Gateways;

use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use EliteHub\Payment\Contracts\PaymentGatewayInterface;

class MercadoPagoGateway implements PaymentGatewayInterface
{
    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(config('payments.providers.mercadopago.config.token'));
    }

    public function initiate($order, $payment): array
    {

        return match ($payment['payment_method']) {
            'pix' => $this->createPixPayment($order, $payment),
            default => throw new DomainException('Método de pagamento inválido'),
        };
    }

    protected function createPixPayment($order, $payment): array
    {
        $client = new PaymentClient();

        $items = [];
        // aqui para enviar os itens products
        // $order->load('items.product');
        // foreach ($order->items as $item) {
        //     $items[] = [
        //         'id' => (string) $item->product->id,
        //         'title' => $item->product->name,
        //         'quantity' => 1,
        //         'unit_price' => (float) $item->price,
        //     ];
        // }

        $response = $client->create([
            'transaction_amount' => (float) $order['total'],
            'payment_method_id' => 'pix',
            'description' => "Pedido {$order['id']}",
            'external_reference' => (string) $payment['reference'],
            // 'notification_url' => route('webhook.mercadopago'),
            'payer' => [
                'email' => $order['user']['email'],
            ],
            'additional_info' => [
                'items' => $items,
            ],
        ]);

        // aqui para atualizar o payment
        // $payment->update([
        //     'payload' => $response,
        //     'status' => 'pending',
        // ]);

        return [
            'type' => 'pix',
            'reference' => $payment['reference'],
            'qr_code' => $response->point_of_interaction->transaction_data->qr_code,
            'qr_code_base64' => $response->point_of_interaction->transaction_data->qr_code_base64,
            'expires_at' => $response->date_of_expiration,
        ];
    }


    public function handleWebhook(Request $request)
    {

    }
}

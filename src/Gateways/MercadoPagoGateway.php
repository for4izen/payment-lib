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
        return match ($payment->method->method_key) {
            'pix' => $this->createPixPayment($order, $payment),
            'preference' => $this->createPreference($order, $payment),
            default => throw new DomainException('Método de pagamento inválido'),
        };
    }

    protected function createPixPayment($order, $payment): array
    {
        $client = new PaymentClient();

        $items = [];
        $order->load('items.product');
        foreach ($order->items as $item) {
            $items[] = [
                'id' => (string) $item->product->id,
                'title' => $item->product->name,
                'quantity' => 1,
                'unit_price' => (float) $item->price,
            ];
        }

        $response = $client->create([
            'transaction_amount' => (float) $order->total,
            'payment_method_id' => 'pix',
            'description' => "Pedido {$order->id}",
            'external_reference' => (string) $payment->reference,
            'notification_url' => route('webhook.mercadopago'),
            'payer' => [
                'email' => $order->user->email,
            ],
            'additional_info' => [
                'items' => $items,
            ],
        ]);

        $payment->update([
            'payload' => $response,
            'status' => 'pending',
        ]);

        return [
            'type' => 'pix',
            'reference' => $payment->reference,
            'qr_code' => $response->point_of_interaction->transaction_data->qr_code,
            'qr_code_base64' => $response->point_of_interaction->transaction_data->qr_code_base64,
            'expires_at' => $response->date_of_expiration,
        ];
    }

    protected function createPreference($order, $payment): array
    {
        $client = new PreferenceClient();
        $order->load('items.product');

        $items = collect($order->items)->map(fn($item) => [
            'id' => (string) $item->product->id,
            'title' => $item->product->name,
            'quantity' => 1,
            'unit_price' => (float) $item->price,
        ])->toArray();

        $payload = [
            'items' => $items,
            'payer' => ['email' => $order->user->email],
            'back_urls' => [
                'success' => config('services.mercadopago.back_url'),
                'failure' => config('services.mercadopago.back_url'),
                'pending' => config('services.mercadopago.back_url'),
            ],
            'notification_url' => route('webhook.mercadopago'),
            'external_reference' => (string) $payment->reference,
            'expires' => true,
            'expiration_date_to' => now()->addHours(6)->format('Y-m-d\TH:i:s.000P'),
        ];

        $preference = $client->create($payload);

        $payment->update([
            'payload' => $preference,
            'status' => 'pending',
        ]);

        return [
            'type' => 'preference',
            'reference' => $payment->reference,
            'checkout_url' => $preference->init_point,
            'sandbox_url' => $preference->sandbox_init_point,
        ];
    }

    public function handleWebhook(Request $request)
    {
        if ($request->input('type') !== 'payment') {
            return null;
        }

        $providerPaymentId = $request->input('data.id');
        if (!$providerPaymentId) {
            return null;
        }

        $client = new PaymentClient();
        $mpPayment = $client->get($providerPaymentId);

        if ($mpPayment->status !== 'approved') {
            return null;
        }

        $paymentModel = config('payments.models.payment');
        $payment = $paymentModel::where('provider', 'mercadopago')
            ->where('provider_payment_id', (string) $providerPaymentId)
            ->first();

        if (!$payment || $payment->status === 'approved') {
            return null;
        }

        DB::transaction(function () use ($payment) {
            $payment->update(['status' => 'approved']);
            $payment->order->update(['status' => 'paid']);
        });

        return $payment;
    }
}

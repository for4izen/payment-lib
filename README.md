# 💳 EliteHub PHP Payment — Gateway Modular

![Packagist Version](https://img.shields.io/packagist/v/elitehub/payment.svg)
![Laravel](https://img.shields.io/badge/Laravel-10+-red.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)
![Status](https://img.shields.io/badge/status-stable-blue.svg)

Pacote Laravel para integração **modular, leve e desacoplada** com múltiplos gateways de pagamento — como **Mercado Pago**, com suporte a **PIX**, **Checkout** e **expansão customizada**.

---

## 🚀 Instalação

```bash
composer require elitehub/payment
```

O pacote registra automaticamente o `PaymentServiceProvider` e executa:

- Cópia do arquivo `config/payments.php`
- Migrations de `payment_providers` e `payment_methods`
- Models padrão: `PaymentProvider` e `PaymentMethod`

Se quiser publicar manualmente:
```bash
php artisan vendor:publish --provider="EliteHub\Payment\Providers\PaymentServiceProvider"
```

---

## 🧱 Estrutura gerada

```
app/
 └── Models/
      ├── PaymentProvider.php
      ├── PaymentMethod.php
database/
 └── migrations/
      ├── create_payment_providers_table.php
      ├── create_payment_methods_table.php
config/
 └── payments.php
```

---

## ⚡ Exemplo de uso

```php


    $order = [
        'id' => 555,
        'total' => 59.90,
        'user' => ['email' => 'user@teste.com'],
        'items' => collect([
            [
                'product' => ['id' => 1, 'name' => 'Plano Premium'],
                'price' => 59.90
            ]
        ])
    ];

    $method = PaymentMethod::with('provider')
        ->where('method_key', 'pix')
        ->whereHas('provider', fn($q) => $q->where('provider_key', 'mercadopago'))
        ->firstOrFail();

    $payment = [
        'payment_method' => $method->method_key,
        'reference' => uniqid('ref_', true),
    ];

    $gateway = PaymentManager::resolve($method->provider);
    $response = $gateway->initiate($order, $payment);

    return response()->json($response);

```

✅ Nenhum model é obrigatório — apenas informe o `payment_method` (ex: `pix`, `preference`) e o `PaymentManager` cuida do resto.

---

## 💡 Fluxo interno

1. O cliente envia `payment_method` (ex: `"pix"`);
2. O `PaymentManager` identifica o provedor ativo (`mercadopago`);
3. O gateway correspondente inicia a cobrança;
4. O retorno traz QR Code, URL ou dados de checkout.

---

## 💳 Exemplo de resposta (PIX)

```json
{
  "type": "pix",
  "reference": "ref_695b97a0dd6323.41320692",
  "qr_code": "000201...",
  "qr_code_base64": "data:image/png;base64,...",
  "expires_at": "2026-01-05T12:00:00Z"
}
```

---

## 💳 Exemplo de resposta (Checkout)

```json
{
  "type": "preference",
  "reference": "ref_695b97a0dd6323.41320692",
  "checkout_url": "https://www.mercadopago.com/checkout/v1/redirect?pref_id=...",
  "sandbox_url": "https://sandbox.mercadopago.com/checkout/v1/redirect?pref_id=..."
}
```

---

## 🔌 Gateways disponíveis

| Gateway | Provider Key | Métodos |
|----------|---------------|----------|
| **Mercado Pago** | `mercadopago` | `pix`, `preference` |

Para criar novos gateways, implemente:
```php
EliteHub\Payment\Contracts\PaymentGatewayInterface
```

---

## ⚙️ Configuração

Arquivo: `config/payments.php`

```php
return [
    'gateways' => [
        'mercadopago' => [
            'token' => env('MERCADOPAGO_TOKEN'),
            'webhook' => env('MERCADOPAGO_WEBHOOK_URL', 'https://example.com/webhook'),
        ],
    ],
];
```

Arquivo `.env`:

```env
MERCADOPAGO_TOKEN=SEU_TOKEN_AQUI
MERCADOPAGO_WEBHOOK_URL=https://seusite.com/webhook
```

---

## 🧩 Criando um novo Gateway

1️⃣ Crie `src/Gateways/MeuGateway.php`

```php
use EliteHub\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Http\Request;

class MeuGateway implements PaymentGatewayInterface
{
    public function initiate($order, $payment)
    {
        // Lógica de cobrança customizada
    }

    public function handleWebhook(Request $request)
    {
        // Lógica de callback (notificação)
    }
}
```

2️⃣ Registre o gateway no `PaymentManager`:

```php
return match ($provider->provider_key) {
    'mercadopago' => app(MercadoPagoGateway::class),
    'meugateway'  => app(MeuGateway::class),
    default => throw new Exception('Gateway não suportado'),
};
```

---

## 🧾 Logs e Debug

```php
use Illuminate\Support\Facades\Log;

Log::info('Order enviada', $order);
Log::info('Payment criado', $payment);
```

---

## ✅ Recursos

| Recurso | Suporte |
|----------|----------|
| Múltiplos gateways | ✅ |
| Estrutura modular | ✅ |
| PIX e Checkout | ✅ |
| Independente de Models | ✅ |
| Publicação automática | ✅ |
| Extensível com novos gateways | ✅ |
| Compatível com Laravel 10+ | ✅ |

---

## 🧑‍💻 Autor

**EliteHub Technologies**  
Desenvolvido por [EliteHub](https://elitehub.tech)  
para soluções modulares e escaláveis de pagamento em Laravel.

---

## 🪪 Licença

Open-source sob a licença **MIT**.

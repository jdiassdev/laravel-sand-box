<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Gateways\StripeGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * IOC CONTAINER — Inversion of Control.
     *
     * O container do Laravel é o responsável por construir objetos e resolver dependências.
     * Aqui configuramos o mapeamento: "quando alguém pedir PaymentGatewayInterface, entregue StripeGateway".
     *
     * bind() vs singleton():
     *   → bind():      cria uma nova instância a cada resolução (por request, por chamada)
     *   → singleton(): cria uma vez e reutiliza para sempre (cuidado com estado em Octane)
     *
     * Open/Closed na prática:
     *   → Para trocar de Stripe para PayPal em toda a aplicação, mude apenas esta linha:
     *       $this->app->bind(PaymentGatewayInterface::class, PaypalGateway::class);
     *   → PaymentService, OrderController — nada mais muda.
     *
     * Isso é Dependency Inversion: quem define qual implementação é usada é a configuração,
     * não o código que usa a dependência.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, StripeGateway::class);
    }

    public function boot(): void
    {
        //
    }
}

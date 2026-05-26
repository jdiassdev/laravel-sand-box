<?php

namespace App\Services;

// =============================================================================
// DEMONSTRAÇÃO DE STATE LEAK
//
// No Octane o processo fica vivo — statics vivem na RAM do worker entre requests.
// No Windows com artisan serve, cada request ganha processo novo, então usamos
// cache de arquivo para simular o comportamento idêntico.
//
// Leaky:  estado gravado persistentemente (cache = arquivo | Octane = RAM)
// Safe:   estado vive só no objeto, morre com o request
// =============================================================================

class CartService
{
    private const CACHE_KEY = 'cart_demo_leaky';

    // -------------------------------------------------------------------------
    // VERSÃO BUGADA
    // Em Octane: static array que fica na RAM do worker entre requests.
    // Aqui: cache de arquivo com o mesmo efeito — persiste entre requests.
    // -------------------------------------------------------------------------

    public function addLeaky(string $product): void
    {
        $items = cache()->get(self::CACHE_KEY, []);
        $items[] = ['product' => $product, 'request_ip' => request()->ip()];
        cache()->forever(self::CACHE_KEY, $items);
    }

    public function getLeaky(): array
    {
        return cache()->get(self::CACHE_KEY, []);
    }

    public function clearLeaky(): void
    {
        cache()->forget(self::CACHE_KEY);
    }

    // -------------------------------------------------------------------------
    // VERSÃO CORRETA
    // Estado vive só nesta instância — morre quando o request termina.
    // -------------------------------------------------------------------------

    private array $safeItems = [];

    public function addSafe(string $product): void
    {
        $this->safeItems[] = ['product' => $product, 'request_ip' => request()->ip()];
    }

    public function getSafe(): array
    {
        return $this->safeItems;
    }
}

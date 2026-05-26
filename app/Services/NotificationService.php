<?php

namespace App\Services;

// =============================================================================
// DEMONSTRAÇÃO SINGLETON vs BIND
//
// Em Octane: o container guarda o singleton na RAM do worker.
// No Windows com artisan serve: simulamos com cache de arquivo (mesmo efeito).
//
// A diferença que importa:
//
// SINGLETON → construtor roda UMA vez. Qualquer estado capturado no construtor
// (usuário autenticado, configuração, IP) fica congelado para sempre naquele worker.
//
// BIND → construtor roda a cada resolução. Estado sempre fresco.
// =============================================================================

class NotificationService
{
    private const COUNTER_KEY = 'notification_instance_counter';

    public readonly int    $instanceId;
    public readonly string $instantiatedAt;

    public function __construct()
    {
        // Incrementa um contador global (persistente entre requests via cache).
        // Em Octane isso viveria em memória — o efeito é o mesmo.
        $this->instanceId     = cache()->increment(self::COUNTER_KEY);
        $this->instantiatedAt = now()->format('H:i:s');
    }

    public function send(string $message): array
    {
        return [
            'message'         => $message,
            'instance_id'     => $this->instanceId,
            'instantiated_at' => $this->instantiatedAt,
            'called_at'       => now()->format('H:i:s'),
        ];
    }

    public static function resetCounter(): void
    {
        cache()->forget(self::COUNTER_KEY);
    }
}

<?php

namespace App\Services;

// =============================================================================
// SIMULA OPERAÇÕES LENTAS (queries pesadas, chamadas de API externas)
//
// usleep() simula latência real. Em produção seria:
//   - Query com JOIN em tabela grande
//   - Chamada para API externa (Stripe, SendGrid, etc.)
//   - Leitura de arquivo grande
// =============================================================================

class SlowReportService
{
    public function totalRevenue(): array
    {
        $start = microtime(true);
        usleep(300_000); // simula 300ms (query pesada de receita)
        return [
            'value'    => 'R$ 48.320,00',
            'duration' => round((microtime(true) - $start) * 1000) . 'ms',
        ];
    }

    public function pendingOrders(): array
    {
        $start = microtime(true);
        usleep(200_000); // simula 200ms (contagem com filtros)
        return [
            'value'    => 37,
            'duration' => round((microtime(true) - $start) * 1000) . 'ms',
        ];
    }

    public function newUsersToday(): array
    {
        $start = microtime(true);
        usleep(150_000); // simula 150ms (query de usuários)
        return [
            'value'    => 12,
            'duration' => round((microtime(true) - $start) * 1000) . 'ms',
        ];
    }
}

<?php

namespace App\Support;

use App\Enums\Tickets\Status;
use App\Models\Client;
use App\Models\Collaborator;
use App\Models\Impact;
use App\Models\User;

/**
 * Monta o resumo legível (em português) de uma linha de auditoria do protocolo.
 * Recebe os valores antes/depois dos campos alterados; ids viram nomes na época da alteração.
 */
class TicketAuditSummary
{
    private const LABELS = [
        'client_id' => 'Cliente',
        'collaborator_id' => 'Desenvolvedor',
        'tester_id' => 'Analista de qualidade',
        'impact_id' => 'Impacto',
        'created_id' => 'Criador',
        'code' => 'Código',
        'priority' => 'Prioridade',
        'platform' => 'Plataforma',
        'type' => 'Tipo',
        'dufy' => 'Plantão',
        'validated' => 'Validação',
        'subject' => 'Título',
        'message' => 'Descrição',
        'status' => 'Status',
        'date_attribute_ticket' => 'Início',
        'date_finish_ticket' => 'Fim',
        'time' => 'Tempo',
    ];

    private const VALUES = [
        'priority' => ['yes' => 'Sim', 'no' => 'Não'],
        'dufy' => ['yes' => 'Sim', 'no' => 'Não'],
        'validated' => ['yes' => 'Sim', 'no' => 'Não'],
        'type' => ['implementation' => 'Implementação', 'maintenance' => 'Manutenção'],
        'platform' => ['web' => 'WEB', 'mobile' => 'MOBILE', 'notifiq' => 'NOTIFIQ'],
    ];

    private const EVENTS = [
        'created' => 'Protocolo criado',
        'deleted' => 'Protocolo apagado',
        'restored' => 'Protocolo restaurado',
    ];

    public static function build(string $event, array $old, array $new): string
    {
        if (isset(self::EVENTS[$event])) return self::EVENTS[$event];

        if ($event === 'split') {
            return 'Dividido em WEB e MOBILE: criado o protocolo #' . ($new['split_to'] ?? '?');
        }

        if ($event === 'split_created') {
            $platform = self::value('platform', $new['platform'] ?? null);
            return 'Criado pela divisão do protocolo #' . ($new['created_from'] ?? '?')
                . ($platform ? " (plataforma {$platform})" : '');
        }

        $parts = [];
        foreach ($new as $field => $newValue) {
            if ($field === 'message') {
                $parts[] = 'Descrição alterada';
                continue;
            }

            $label = self::LABELS[$field] ?? $field;
            $parts[] = "{$label}: de " . self::show($field, $old[$field] ?? null) . ' para ' . self::show($field, $newValue);
        }

        return $parts ? implode('; ', $parts) : 'Sem alterações';
    }

    private static function show(string $field, $value): string
    {
        if ($value === null || $value === '') return '(vazio)';

        return (string) (self::name($field, $value) ?? self::value($field, $value));
    }

    private static function value(string $field, $value)
    {
        return self::VALUES[$field][$value] ?? $value;
    }

    // campos que guardam id (ou código de status): busca o nome, mesmo de cadastro apagado
    private static function name(string $field, $value): ?string
    {
        return match ($field) {
            'client_id' => Client::withoutGlobalScopes()->find($value)?->full_name,
            'impact_id' => Impact::withoutGlobalScopes()->find($value)?->description,
            'collaborator_id', 'tester_id' => Collaborator::withoutGlobalScopes()->find($value)?->full_name,
            'created_id' => User::withoutGlobalScopes()->find($value)?->name,
            'status' => Status::tryFrom($value)?->description(),
            default => null,
        };
    }
}

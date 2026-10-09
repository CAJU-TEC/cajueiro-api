<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Compara de forma exata (sargável, usa índice) quando o valor é um dos
 * valores conhecidos da coluna; qualquer outro valor mantém a busca parcial
 * (LIKE) que o filtro tinha antes, para não quebrar consumidores antigos.
 */
class AllowedExactOrPartialFilter implements Filter
{
    public function __construct(private array $known)
    {
    }

    public function __invoke(Builder $query, $value, string $property)
    {
        $values = array_values(array_filter((array) $value, fn ($v) => $v !== null && $v !== ''));

        if ($values === []) {
            return;
        }

        $exact = array_map(fn ($v) => in_array($v, $this->known, true), $values);

        if (! in_array(false, $exact, true)) {
            $query->whereIn($property, $values);
            return;
        }

        $query->where(function (Builder $q) use ($values, $property) {
            foreach ($values as $v) {
                $q->orWhere($property, 'LIKE', '%' . addcslashes((string) $v, '\\%_') . '%');
            }
        });
    }
}

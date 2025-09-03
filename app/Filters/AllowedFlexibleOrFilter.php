<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class AllowedFlexibleOrFilter implements Filter
{
    public function __construct(private array $fields = ['collaborator_id', 'tester_id'])
    {
        $this->fields = $fields;
    }

    public function __invoke(Builder $query, $value, string $property): Builder
    {
        return $query->where(function ($query) use ($value) {
            foreach ($this->fields as $field) {
                $query->orWhere($field, $value);
            }
        });
    }
}

<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class AllowedNullableOrIdFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property)
    {
        $query->whereHas('client', function (Builder $query) use ($value, $property) {
            $query->where('clients.corporate_id', 'like', '3a9a479d-529b-4cb3-bef6-214ebdd4036a');
        });
    }
}

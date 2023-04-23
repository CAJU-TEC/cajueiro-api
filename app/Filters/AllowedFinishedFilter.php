<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class AllowedFinishedFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property)
    {
        // $query->when('comments', function ($query) use ($value) {
        //     $query->whereMonth('created_at', $value);
        // });
        // dd($query->toSql());
        return $query;
    }
}

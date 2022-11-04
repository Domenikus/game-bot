<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ActiveScope implements Scope
{
    /**
     * @param  Builder<Model>  $builder
     * @param  Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        /** @phpstan-ignore-next-line */// Cannot be type-hinted
        $builder->where($model->getActiveColumn(), true);
    }

    /**
     * @param  Builder<Model>  $builder
     * @return void
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withInactive', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}

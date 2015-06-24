<?php

namespace Bosnadev\Repositories\Conditions;

use Bosnadev\Repositories\Contracts\RepositoryInterface;

abstract class Condition
{

    /**
     * @param $model
     *
     * @return mixed
     */
    public abstract function applyTo($model);
}
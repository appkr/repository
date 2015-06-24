<?php

namespace Appkr\Repository\Conditions;

abstract class Condition
{

    /**
     * @param $model
     *
     * @return mixed
     */
    public abstract function applyTo($model);
}
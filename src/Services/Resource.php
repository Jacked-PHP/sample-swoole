<?php

namespace MyCode\Services;

use Illuminate\Database\Eloquent\Model;
use JsonSerializable;

class Resource implements JsonSerializable
{
    public function __construct(
        protected Model $model
    ) { }


    public function jsonSerialize(): mixed
    {
        return [
            'data' => $this->model->toArray(),
        ];
    }
}
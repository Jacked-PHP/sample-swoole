<?php

namespace MyCode\DB\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MyCode\DB\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [];
    }
}

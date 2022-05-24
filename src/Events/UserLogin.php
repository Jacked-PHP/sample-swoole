<?php

namespace MyCode\Events;

use MyCode\DB\Models\User;

class UserLogin implements EventInterface
{
    public function __construct(
        public User $user
    ) {}
}
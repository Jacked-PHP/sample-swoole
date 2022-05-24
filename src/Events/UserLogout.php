<?php

namespace MyCode\Events;

use MyCode\DB\Models\User;

class UserLogout implements EventInterface
{
    public function __construct(
        public User $user
    ) {}
}
<?php

namespace MyCode\Events;

class UserLoginFail implements EventInterface
{
    public function __construct(
        public string $email
    ) {}
}
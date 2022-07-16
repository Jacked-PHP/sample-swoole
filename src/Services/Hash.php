<?php

namespace MyCode\Services;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class Hash
{
    protected PasswordHasherInterface $passwordHasher;

    public function __construct()
    {
        $factory = new PasswordHasherFactory([
            'common' => ['algorithm' => 'bcrypt'],
            'memory-hard' => ['algorithm' => 'sodium'],
        ]);
        $this->passwordHasher = $factory->getPasswordHasher('common');
    }

    public function make(string $text): string
    {
        return $this->passwordHasher->hash($text);
    }

    public function verify(string $hash, string $text): bool
    {
        $this->passwordHasher->verify($hash, $text);
    }
}
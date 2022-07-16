<?php

namespace MyCode\DB\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MyCode\DB\Factories\UserFactory;

class User extends Model
{
    use HasFactory;

    protected $connection = 'default';

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected static function newFactory()
    {
        return new UserFactory();
    }

    public function tokens()
    {
        return $this->hasMany(Token::class);
    }
}
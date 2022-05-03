<?php

namespace MyCode\DB\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $connection = 'default';

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
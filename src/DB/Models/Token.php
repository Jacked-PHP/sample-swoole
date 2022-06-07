<?php

namespace MyCode\DB\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    const TABLE_NAME = 'tokens';

    protected $table = self::TABLE_NAME;

    protected array $defaults = [];

    protected $fillable = [
        'name',
        'user_id',
        'expire_at',
        'token',
    ];
}

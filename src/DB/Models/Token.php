<?php

namespace MyCode\DB\Models;

use Exception;
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
        'uses',
        'use_limit',
    ];

    /**
     * @return $this
     * @throws Exception
     */
    public function consume(): self
    {
        if ($this->use_limit !== 0 && $this->uses >= $this->use_limit) {
            $this->delete();
            throw new Exception('Token already consumed!');
        }

        $this->uses = $this->uses + 1;
        $this->save();
        return $this;
    }
}

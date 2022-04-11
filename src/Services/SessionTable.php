<?php

namespace MyCode\Services;

use Carbon\Carbon;
use DateInterval;
use Psr\SimpleCache\CacheInterface;
use Swoole\Table;

class SessionTable implements CacheInterface
{
    const SESSION_KEY = 'my-code-session';

    protected static ?SessionTable $instance = null;

    protected static Table $table;

    private function __construct()
    {
        self::$table = $this->createTable();
    }

    public static function getInstance(): SessionTable
    {
        if (self::$instance === null) {
            self::$instance = new SessionTable;
        }
        return self::$instance;
    }
    public static function destroyInstance(): void
    {
        self::$table->destroy();
        self::$instance = null;
    }

    protected function createTable(): Table
    {
        $table = new Table(1024);
        $table->column('data', Table::TYPE_STRING, 1000);
        $table->column('ttl', Table::TYPE_INT, 8);
        $table->create();
        return $table;
    }

    public static function getTable(): Table
    {
        return self::$table;
    }

    /**
     * Fetches a value from the cache.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->has($key)) {
            $value = self::$table->get($key);
            if (Carbon::now()->getTimestamp() < $value['ttl']) {
                return json_decode($value['data'], true);
            }
        }
        return $default;
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     */
    public function set(string $key, mixed $value, DateInterval|int $ttl = null): bool
    {
        return self::$table->set($key, [
            'data' => json_encode($value),
            'ttl' => $ttl ?? Carbon::now()->addHours(4)->getTimestamp(),
        ]);
    }

    /**
     * Delete an item from the cache by its unique key.
     */
    public function delete(string $key): bool
    {
        return self::$table->del($key);
    }

    /**
     * Wipes clean the entire cache's keys.
     */
    public function clear(): bool
    {
        $result = self::$table->destroy();
        $this->createTable();
        return $result;
    }

    /**
     * Obtains multiple cache items by their unique keys.
     */
    public function getMultiple(iterable $keys, $default = null): iterable
    {
        $data = [];
        foreach ($keys as $key) {
            $data[] = $this->get($key);
        }
        return !empty($data) ? $data : $default;
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     */
    public function setMultiple(iterable $values, $ttl = null): bool
    {
        $inserted = [];
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value)) {
                $this->deleteMultiple($inserted);
                return false;
            }
            $inserted[] = $key;
        }
        return true;
    }

    /**
     * Deletes multiple cache items in a single operation.
     */
    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            if ($this->has($key)) {
                $this->delete($key);
            }
        }

        return true;
    }

    /**
     * Determines whether an item is present in the cache.
     */
    public function has(string $key): bool
    {
        return self::$table->exists($key);
    }
}
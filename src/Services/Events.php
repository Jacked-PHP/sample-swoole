<?php

namespace MyCode\Services;

use MyCode\Events\EventInterface;
use Swoole\Timer;

class Events
{
    /**
     * List of event listeners with its callbacks.
     *
     * @var array [EventInterface::class => callable[]]
     */
    protected array $listeners = [];

    protected function __construct()
    {
    }

    public static function getInstance(): self
    {
        static $instance;

        if (null === $instance) {
            $instance = new self();
        }

        return $instance;
    }

    public static function addListener(string $event, callable $callback): void
    {
        if (!isset(self::getInstance()->listeners[$event])) {
            self::getInstance()->listeners[$event] = [];
        }

        self::getInstance()->listeners[$event][] = $callback;
    }

    public static function dispatch(EventInterface $event): void
    {
        $listeners = [];
        if (isset(self::getInstance()->listeners[$event::class])) {
            $listeners = self::getInstance()->listeners[$event::class];
        }

        for ($i = 0; $i < count($listeners); $i++) {
            Timer::after(1, $listeners[$i], $event);
        }
    }

    public static function dispatchNow(EventInterface $event): void
    {
        $listeners = [];
        if (isset(self::getInstance()->listeners[$event::class])) {
            $listeners = self::getInstance()->listeners[$event::class];
        }

        for ($i = 0; $i < count($listeners); $i++) {
            call_user_func($listeners[$i], $event);
        }
    }
}
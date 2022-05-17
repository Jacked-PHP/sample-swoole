<?php

namespace MyCode\Services;

class Events
{
    /**
     * List of events with its callbacks.
     *
     * @var array ['event-key' => callable[]]
     */
    protected array $events = [];

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

    public static function addEvent(string $key, callable $callback): void
    {
        if (!isset(self::getInstance()->events[$key])) {
            self::getInstance()->events[$key] = [];
        }

        self::getInstance()->events[$key][] = $callback;
    }

    public static function getEvents(): array
    {
        return self::getInstance()->events;
    }

    public static function dispatch(string $key, string $data): void
    {
        global $app;
        $eventsTable = $app->getContainer()->get('events-table');
        $eventsTable->set(count($eventsTable), [
            'event_key' => $key,
            'event_data' => $data,
        ]);
    }

    public static function dispatchNow(): void
    {
        // TODO: implement
    }
}
<?php

namespace MyCode\Services;

use Chocookies\Cookies;
use Ramsey\Uuid\Uuid;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class Session
{
    public static function startSession(Request $request): array
    {
        $current_session = self::getCurrentSession($request);

        return $current_session;
    }

    public static function addCookiesToResponse(Request $request, ResponseInterface &$response): void
    {
        $data = $request->getAttribute('session');
        $session_data = SessionTable::getInstance()->get($data['id']);

        $cookies = [];

        foreach ($session_data as $key => $value) {
            if (!in_array($key, ['id'])) {
                continue;
            }

            $cookies[$key] = $session_data[$key];
        }

        Cookies::setCookie(
            $response,
            SessionTable::SESSION_KEY,
            self::encodeCookie($cookies)
        );
    }

    private static function getCurrentSession(Request $request): array
    {
        $session_table = SessionTable::getInstance();

        $session_data = Cookies::getCookie($request, SessionTable::SESSION_KEY);

        if (null !== $session_data) {
            $session_data = self::parseCookie($session_data);
        }

        if (empty($session_data)) {
            $session_data['id'] = Uuid::uuid4()->toString();
        }

        if (!$session_table->has($session_data['id'])) {
            $session_table->set($session_data['id'], $session_data);
        }

        $current_session = $session_table->get($session_data['id']);

        return $current_session ?? [];
    }

    public static function parseCookie(string $data)
    {
        $data = str_replace($_ENV['SESSION_KEY'], '', $data);
        return json_decode(base64_decode($data), true);
    }

    private static function encodeCookie(array $data): string
    {
        $data = json_encode($data);
        return $_ENV['SESSION_KEY'] . base64_encode($data);
    }
}

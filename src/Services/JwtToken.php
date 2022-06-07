<?php

namespace MyCode\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use MyCode\DB\Models\Token;
use Psr\Http\Message\ServerRequestInterface as Request;

class JwtToken
{
    const HS256_ALGORITHM = 'HS256';

    /**
     * @param string $token
     * @param string $name
     * @return array
     */
    public static function decodeJwtToken(string $token, string $name): array
    {
        $decoded = JWT::decode($token, new Key($name, self::HS256_ALGORITHM));

        return (array) $decoded;
    }

    public static function getToken(Request $request): ?Token
    {
        if (!$request->hasHeader('Authorization')) {
            return null;
        }

        $authorization = $request->getHeader('Authorization');
        $authorization = current($authorization);
        $authorization = explode(' ', $authorization);

        if ($authorization[0] !== 'Bearer') {
            return null;
        }

        $token = $authorization[1];

        return Token::where('token', $token)->first();
    }
}

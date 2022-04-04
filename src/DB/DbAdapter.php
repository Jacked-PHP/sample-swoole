<?php

namespace MyCode\DB;

use PDO;

class DbAdapter
{

    public static function getPdo()
    {
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];
        $db = $_ENV['DB_NAME'];
        
        return new PDO('mysql:host=127.0.0.1;port=33061;dbname=' . $db . ';charset=utf8', $user, $pass);
    }

    public static function execute(string $query, ?array $params = null): bool
    {
        $pdo = self::getPdo();
        $statement = $pdo->prepare($query);
        $result = $statement->execute($params);
        $pdo = null;
        return $result;
    }

    public static function fetchAll(string $query, array $params)
    {
        $pdo = self::getPdo();
        $statement = $pdo->prepare($query);
        $statement->execute($params);
        $data = $statement->fetchAll();
        $pdo = null;
        return $data;
    }
}
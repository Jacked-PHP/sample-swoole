<?php

namespace MyCode\DB;

class User
{

    public function createTable()
    {
        $sql = <<<SQL
    CREATE TABLE users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(30) NOT NULL,
        email VARCHAR(50) NOT NULL,
        password VARCHAR(150),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );
SQL;
        return DbAdapter::execute($sql);
    }

    public function find($id)
    {
        $sql = <<<SQL
    SELECT * FROM users WHERE id = :id 
SQL;
        return DbAdapter::fetchAll($sql, [':id' => $id]);
    }

    public function getAll()
    {
        $sql = <<<SQL
    SELECT * FROM users
SQL;
        return DbAdapter::fetchAll($sql, []);
    }

    public function get($field, $value)
    {
        $sql = <<<SQL
    SELECT * FROM users WHERE $field = :$field
SQL;
        return DbAdapter::fetchAll($sql, [':' . $field => $value]);
    }

    public function insert($data)
    {
        // TODO: validate fields

        $sql = <<<SQL
    INSERT INTO users (name, email, password) VALUES (:name, :email, :password)
SQL;
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return DbAdapter::execute($sql, $data);
    }

    public function update($id, $data)
    {
        // TODO: validate fields

        $user = $this->find($id);

        $sql = <<<SQL
    UPDATE users SET name = :name, email = :email, password = :password WERE id = :id
SQL;
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return DbAdapter::execute($sql, $data);
    }

    public function delete($id)
    {
        $sql = <<<SQL
    DELETE FROM users WHERE id = :id 
SQL;
        return DbAdapter::execute($sql, [':id' => $id]);
    }
}
<?php

namespace App\Models;

use App\Helpers\DB;

class UserModel
{
    public function find(int $id): ?array
    {
        return DB::queryOne(
            'SELECT * FROM users WHERE id = :id',
            [':id' => $id]
        );
    }

    public function findByEmail(string $email): ?array
    {
        return DB::queryOne(
            'SELECT * FROM users WHERE email = :email',
            [':email' => $email]
        );
    }

    public function create(string $name, string $email, string $password): int
    {
        DB::execute(
            'INSERT INTO users (name, email, password, created_at, updated_at)
             VALUES (:name, :email, :password, NOW(), NOW())',
            [
                ':name'     => $name,
                ':email'    => $email,
                ':password' => $password,
            ]
        );
        return (int)DB::lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $allowed = ['name', 'bio', 'location', 'avatar'];
        $sets    = [];
        $params  = [':id' => $id];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[]            = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (!$sets) return;

        $sets[] = 'updated_at = NOW()';
        DB::execute(
            'UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = :id',
            $params
        );
    }
}

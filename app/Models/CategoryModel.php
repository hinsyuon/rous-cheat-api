<?php

namespace App\Models;

use App\Helpers\DB;

class CategoryModel
{
    public function all(): array
    {
        return DB::query('SELECT * FROM categories ORDER BY sort_order, name_en');
    }

    public function find(int $id): ?array
    {
        return DB::queryOne(
            'SELECT * FROM categories WHERE id = :id',
            [':id' => $id]
        );
    }
}

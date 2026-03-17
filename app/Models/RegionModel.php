<?php

namespace App\Models;

use App\Helpers\DB;

class RegionModel
{
    public function all(): array
    {
        return DB::query('SELECT * FROM regions ORDER BY name_en');
    }

    public function find(int $id): ?array
    {
        return DB::queryOne(
            'SELECT * FROM regions WHERE id = :id',
            [':id' => $id]
        );
    }
}

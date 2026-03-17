<?php

namespace App\Models;

use App\Helpers\DB;

class IngredientModel
{
    public function all(int $page = 1, int $perPage = 50): array
    {
        $offset = ($page - 1) * $perPage;
        $total  = DB::count('SELECT COUNT(*) FROM ingredients');
        $rows   = DB::query(
            'SELECT * FROM ingredients ORDER BY name_en LIMIT :limit OFFSET :offset',
            [':limit' => $perPage, ':offset' => $offset]
        );
        return [$rows, $total];
    }

    public function find(int $id): ?array
    {
        $ing = DB::queryOne(
            'SELECT * FROM ingredients WHERE id = :id',
            [':id' => $id]
        );
        if (!$ing) return null;

        $ing['substitutes'] = DB::query('
            SELECT i.id, i.name_en, i.name_kh
            FROM ingredient_substitutes s
            JOIN ingredients i ON i.id = s.substitute_id
            WHERE s.ingredient_id = :ingredient_id
        ', [':ingredient_id' => $id]);

        return $ing;
    }

    public function search(string $q): array
    {
        $like = "%$q%";
        return DB::query(
            'SELECT * FROM ingredients
             WHERE name_en LIKE :like1 OR name_kh LIKE :like2
             ORDER BY name_en
             LIMIT 20',
            [':like1' => $like, ':like2' => $like]
        );
    }
}

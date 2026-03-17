<?php

namespace App\Models;

use App\Helpers\DB;

class FavoriteModel
{
    public function forUser(int $userId): array
    {
        return DB::query('
            SELECT r.id, r.title_en, r.title_kh, r.difficulty,
                   r.cook_time_minutes, r.thumbnail, f.created_at AS saved_at
            FROM favorites f
            JOIN recipes r ON r.id = f.recipe_id
            WHERE f.user_id = :user_id
            ORDER BY f.created_at DESC
        ', [':user_id' => $userId]);
    }

    public function add(int $userId, int $recipeId): void
    {
        $exists = DB::queryOne(
            'SELECT id FROM favorites WHERE user_id = :user_id AND recipe_id = :recipe_id',
            [':user_id' => $userId, ':recipe_id' => $recipeId]
        );

        if (!$exists) {
            DB::execute(
                'INSERT INTO favorites (user_id, recipe_id, created_at)
                 VALUES (:user_id, :recipe_id, NOW())',
                [':user_id' => $userId, ':recipe_id' => $recipeId]
            );
        }
    }

    public function remove(int $userId, int $recipeId): void
    {
        DB::execute(
            'DELETE FROM favorites WHERE user_id = :user_id AND recipe_id = :recipe_id',
            [':user_id' => $userId, ':recipe_id' => $recipeId]
        );
    }
}

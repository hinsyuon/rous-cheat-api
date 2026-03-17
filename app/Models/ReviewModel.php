<?php

namespace App\Models;

use App\Helpers\DB;

class ReviewModel
{
    public function forRecipe(int $recipeId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $total  = DB::count(
            'SELECT COUNT(*) FROM reviews WHERE recipe_id = :recipe_id',
            [':recipe_id' => $recipeId]
        );
        $rows = DB::query('
            SELECT rv.*, u.name AS author_name, u.avatar AS author_avatar
            FROM reviews rv
            JOIN users u ON u.id = rv.user_id
            WHERE rv.recipe_id = :recipe_id
            ORDER BY rv.created_at DESC
            LIMIT :limit OFFSET :offset
        ', [
            ':recipe_id' => $recipeId,
            ':limit'     => $perPage,
            ':offset'    => $offset,
        ]);

        return [$rows, $total];
    }

    public function create(int $recipeId, int $userId, int $rating, string $comment): int
    {
        // Upsert — one review per user per recipe
        $existing = DB::queryOne(
            'SELECT id FROM reviews WHERE recipe_id = :recipe_id AND user_id = :user_id',
            [':recipe_id' => $recipeId, ':user_id' => $userId]
        );

        if ($existing) {
            DB::execute(
                'UPDATE reviews
                 SET rating = :rating, comment = :comment, updated_at = NOW()
                 WHERE id = :id',
                [
                    ':rating'  => $rating,
                    ':comment' => $comment,
                    ':id'      => $existing['id'],
                ]
            );
            return $existing['id'];
        }

        DB::execute(
            'INSERT INTO reviews (recipe_id, user_id, rating, comment, created_at, updated_at)
             VALUES (:recipe_id, :user_id, :rating, :comment, NOW(), NOW())',
            [
                ':recipe_id' => $recipeId,
                ':user_id'   => $userId,
                ':rating'    => $rating,
                ':comment'   => $comment,
            ]
        );
        return (int)DB::lastInsertId();
    }
}

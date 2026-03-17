<?php

namespace App\Models;

use App\Helpers\DB;

class RecipeModel
{
    public function find(int $id): ?array
    {
        $recipe = DB::queryOne('
            SELECT r.*, c.name_en AS category_name, c.name_kh AS category_name_kh,
                   reg.name_en AS region_name, reg.name_kh AS region_name_kh,
                   u.name AS author_name,
                   COALESCE(AVG(rv.rating), 0) AS avg_rating,
                   COUNT(DISTINCT rv.id) AS review_count,
                   COUNT(DISTINCT f.id)  AS favorite_count
            FROM recipes r
            LEFT JOIN categories c ON r.category_id = c.id
            LEFT JOIN regions reg  ON r.region_id   = reg.id
            LEFT JOIN users u      ON r.user_id      = u.id
            LEFT JOIN reviews rv   ON rv.recipe_id   = r.id
            LEFT JOIN favorites f  ON f.recipe_id    = r.id
            WHERE r.id = :id
            GROUP BY r.id
        ', [':id' => $id]);

        if (!$recipe) return null;

        $recipe['ingredients']     = json_decode($recipe['ingredients']     ?? '[]', true);
        $recipe['instructions_en'] = json_decode($recipe['instructions_en'] ?? '[]', true);
        $recipe['instructions_kh'] = json_decode($recipe['instructions_kh'] ?? '[]', true);
        $recipe['tags']            = json_decode($recipe['tags']            ?? '[]', true);
        $recipe['images']          = json_decode($recipe['images']          ?? '[]', true);
        $recipe['avg_rating']      = round((float)$recipe['avg_rating'], 1);

        return $recipe;
    }

    public function list(int $page, int $perPage, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[]                  = 'r.category_id = :category_id';
            $params[':category_id']   = $filters['category_id'];
        }
        if (!empty($filters['region_id'])) {
            $where[]               = 'r.region_id = :region_id';
            $params[':region_id']  = $filters['region_id'];
        }
        if (!empty($filters['difficulty'])) {
            $where[]               = 'r.difficulty = :difficulty';
            $params[':difficulty'] = $filters['difficulty'];
        }

        $whereStr = implode(' AND ', $where);

        $total = DB::count("SELECT COUNT(*) FROM recipes r WHERE $whereStr", $params);

        $rows = DB::query("
            SELECT r.id, r.title_en, r.title_kh, r.difficulty, r.cook_time_minutes,
                   r.servings, r.thumbnail, r.created_at,
                   c.name_en AS category_name,
                   reg.name_en AS region_name,
                   u.name AS author_name,
                   COALESCE(AVG(rv.rating), 0) AS avg_rating,
                   COUNT(DISTINCT rv.id) AS review_count
            FROM recipes r
            LEFT JOIN categories c ON r.category_id = c.id
            LEFT JOIN regions reg  ON r.region_id   = reg.id
            LEFT JOIN users u      ON r.user_id      = u.id
            LEFT JOIN reviews rv   ON rv.recipe_id   = r.id
            WHERE $whereStr
            GROUP BY r.id
            ORDER BY r.created_at DESC
            LIMIT :limit OFFSET :offset
        ", array_merge($params, [':limit' => $perPage, ':offset' => $offset]));

        foreach ($rows as &$r) {
            $r['avg_rating'] = round((float)$r['avg_rating'], 1);
        }

        return [$rows, $total];
    }

    public function search(string $q, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $like   = "%$q%";

        $total = DB::count('
            SELECT COUNT(*) FROM recipes
            WHERE title_en LIKE :like1 OR title_kh LIKE :like2 OR description_en LIKE :like3
        ', [':like1' => $like, ':like2' => $like, ':like3' => $like]);

        $rows = DB::query('
            SELECT r.id, r.title_en, r.title_kh, r.difficulty, r.cook_time_minutes,
                   r.thumbnail, c.name_en AS category_name,
                   COALESCE(AVG(rv.rating), 0) AS avg_rating
            FROM recipes r
            LEFT JOIN categories c ON r.category_id = c.id
            LEFT JOIN reviews rv   ON rv.recipe_id   = r.id
            WHERE r.title_en LIKE :like1 OR r.title_kh LIKE :like2 OR r.description_en LIKE :like3
            GROUP BY r.id
            ORDER BY r.created_at DESC
            LIMIT :limit OFFSET :offset
        ', [
            ':like1'  => $like,
            ':like2'  => $like,
            ':like3'  => $like,
            ':limit'  => $perPage,
            ':offset' => $offset,
        ]);

        return [$rows, $total];
    }

    public function popular(int $limit = 10): array
    {
        return DB::query('
            SELECT r.id, r.title_en, r.title_kh, r.difficulty, r.cook_time_minutes,
                   r.thumbnail, c.name_en AS category_name,
                   COALESCE(AVG(rv.rating), 0) AS avg_rating,
                   COUNT(DISTINCT f.id) AS favorite_count
            FROM recipes r
            LEFT JOIN categories c ON r.category_id = c.id
            LEFT JOIN reviews rv   ON rv.recipe_id   = r.id
            LEFT JOIN favorites f  ON f.recipe_id    = r.id
            GROUP BY r.id
            ORDER BY favorite_count DESC, avg_rating DESC
            LIMIT :limit
        ', [':limit' => $limit]);
    }

    public function random(): ?array
    {
        $row = DB::queryOne('SELECT id FROM recipes ORDER BY RAND() LIMIT 1');
        return $row ? $this->find($row['id']) : null;
    }

    public function create(array $data, int $userId): int
    {
        DB::execute('
            INSERT INTO recipes
                (user_id, category_id, region_id,
                 title_en, title_kh, description_en, description_kh,
                 difficulty, cook_time_minutes, prep_time_minutes, servings,
                 ingredients, instructions_en, instructions_kh,
                 tags, images, thumbnail,
                 created_at, updated_at)
            VALUES
                (:user_id, :category_id, :region_id,
                 :title_en, :title_kh, :description_en, :description_kh,
                 :difficulty, :cook_time_minutes, :prep_time_minutes, :servings,
                 :ingredients, :instructions_en, :instructions_kh,
                 :tags, :images, :thumbnail,
                 NOW(), NOW())
        ', [
            ':user_id'            => $userId,
            ':category_id'        => $data['category_id'],
            ':region_id'          => $data['region_id'] ?? null,
            ':title_en'           => $data['title_en'],
            ':title_kh'           => $data['title_kh'],
            ':description_en'     => $data['description_en'] ?? null,
            ':description_kh'     => $data['description_kh'] ?? null,
            ':difficulty'         => $data['difficulty'],
            ':cook_time_minutes'  => (int)$data['cook_time_minutes'],
            ':prep_time_minutes'  => (int)($data['prep_time_minutes'] ?? 0),
            ':servings'           => (int)$data['servings'],
            ':ingredients'        => json_encode($data['ingredients']    ?? [], JSON_UNESCAPED_UNICODE),
            ':instructions_en'    => json_encode($data['instructions_en'] ?? [], JSON_UNESCAPED_UNICODE),
            ':instructions_kh'    => json_encode($data['instructions_kh'] ?? [], JSON_UNESCAPED_UNICODE),
            ':tags'               => json_encode($data['tags']           ?? [], JSON_UNESCAPED_UNICODE),
            ':images'             => json_encode($data['images']         ?? [], JSON_UNESCAPED_UNICODE),
            ':thumbnail'          => $data['thumbnail'] ?? null,
        ]);

        return (int)DB::lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $allowed = [
            'title_en', 'title_kh', 'description_en', 'description_kh',
            'difficulty', 'cook_time_minutes', 'prep_time_minutes', 'servings',
            'category_id', 'region_id', 'thumbnail',
        ];

        $sets   = [];
        $params = [':id' => $id];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[]            = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        // JSON fields
        $jsonFields = ['ingredients', 'instructions_en', 'instructions_kh', 'tags', 'images'];
        foreach ($jsonFields as $field) {
            if (isset($data[$field])) {
                $sets[]            = "$field = :$field";
                $params[":$field"] = json_encode($data[$field], JSON_UNESCAPED_UNICODE);
            }
        }

        if (!$sets) return;

        $sets[] = 'updated_at = NOW()';
        DB::execute(
            'UPDATE recipes SET ' . implode(', ', $sets) . ' WHERE id = :id',
            $params
        );
    }

    public function delete(int $id): void
    {
        DB::execute('DELETE FROM recipes WHERE id = :id', [':id' => $id]);
    }

    public function byUser(int $userId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $total  = DB::count(
            'SELECT COUNT(*) FROM recipes WHERE user_id = :user_id',
            [':user_id' => $userId]
        );
        $rows = DB::query('
            SELECT r.id, r.title_en, r.title_kh, r.difficulty,
                   r.cook_time_minutes, r.thumbnail, r.created_at
            FROM recipes r
            WHERE r.user_id = :user_id
            ORDER BY r.created_at DESC
            LIMIT :limit OFFSET :offset
        ', [':user_id' => $userId, ':limit' => $perPage, ':offset' => $offset]);

        return [$rows, $total];
    }

    public function byCategory(int $categoryId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $total  = DB::count(
            'SELECT COUNT(*) FROM recipes WHERE category_id = :category_id',
            [':category_id' => $categoryId]
        );
        $rows = DB::query('
            SELECT r.id, r.title_en, r.title_kh, r.difficulty,
                   r.cook_time_minutes, r.thumbnail
            FROM recipes r
            WHERE r.category_id = :category_id
            ORDER BY r.created_at DESC
            LIMIT :limit OFFSET :offset
        ', [':category_id' => $categoryId, ':limit' => $perPage, ':offset' => $offset]);

        return [$rows, $total];
    }

    public function byRegion(int $regionId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $total  = DB::count(
            'SELECT COUNT(*) FROM recipes WHERE region_id = :region_id',
            [':region_id' => $regionId]
        );
        $rows = DB::query('
            SELECT r.id, r.title_en, r.title_kh, r.difficulty,
                   r.cook_time_minutes, r.thumbnail
            FROM recipes r
            WHERE r.region_id = :region_id
            ORDER BY r.created_at DESC
            LIMIT :limit OFFSET :offset
        ', [':region_id' => $regionId, ':limit' => $perPage, ':offset' => $offset]);

        return [$rows, $total];
    }
}

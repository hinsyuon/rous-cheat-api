<?php

namespace App\Controllers;

use App\Helpers\{Response, Validator, Env};
use App\Middleware\AuthMiddleware;
use App\Models\RecipeModel;

class RecipeController
{
    private RecipeModel $model;

    public function __construct()
    {
        $this->model = new RecipeModel();
    }

    // GET /recipes
    public function index(): void
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min((int)Env::get('MAX_PAGE_SIZE', '100'), max(1, (int)($_GET['per_page'] ?? Env::get('DEFAULT_PAGE_SIZE', '20'))));
        $filters = [
            'category_id' => $_GET['category_id'] ?? null,
            'region_id'   => $_GET['region_id'] ?? null,
            'difficulty'  => $_GET['difficulty'] ?? null,
            'language'    => $_GET['lang'] ?? null,
        ];

        [$recipes, $total] = $this->model->list($page, $perPage, $filters);
        Response::paginated($recipes, $total, $page, $perPage);
    }

    // GET /recipes/{id}
    public function show(int $id): void
    {
        $recipe = $this->model->find($id);
        if (!$recipe) Response::error('Recipe not found', 404);
        Response::success($recipe);
    }

    // GET /recipes/popular
    public function popular(): void
    {
        $limit   = min(50, (int)($_GET['limit'] ?? 10));
        Response::success($this->model->popular($limit));
    }

    // GET /recipes/random
    public function random(): void
    {
        $recipe = $this->model->random();
        if (!$recipe) Response::error('No recipes found', 404);
        Response::success($recipe);
    }

    // GET /recipes/search?q=
    public function search(): void
    {
        $q       = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) Response::error('Search query must be at least 2 characters', 422);
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(50, max(1, (int)($_GET['per_page'] ?? 20)));
        [$results, $total] = $this->model->search($q, $page, $perPage);
        Response::paginated($results, $total, $page, $perPage);
    }

    // POST /recipes
    public function store(): void
    {
        $user = AuthMiddleware::require();
        $body = $this->body();

        Validator::make($body)
            ->required('title_en', 'English title')
            ->required('title_kh', 'Khmer title')
            ->required('category_id', 'Category')
            ->required('difficulty', 'Difficulty')
            ->in('difficulty', ['easy', 'medium', 'hard'])
            ->required('cook_time_minutes', 'Cook time')
            ->numeric('cook_time_minutes')
            ->required('servings', 'Servings')
            ->numeric('servings')
            ->required('instructions_en', 'English instructions')
            ->validate();

        $id = $this->model->create($body, (int)$user['sub']);
        $recipe = $this->model->find($id);
        Response::created($recipe);
    }

    // PUT /recipes/{id}
    public function update(int $id): void
    {
        $user   = AuthMiddleware::require();
        $recipe = $this->model->find($id);
        if (!$recipe) Response::error('Recipe not found', 404);
        if ($recipe['user_id'] !== $user['sub']) Response::error('Forbidden', 403);

        $body = $this->body();
        $this->model->update($id, $body);
        Response::success($this->model->find($id));
    }

    // DELETE /recipes/{id}
    public function destroy(int $id): void
    {
        $user   = AuthMiddleware::require();
        $recipe = $this->model->find($id);
        if (!$recipe) Response::error('Recipe not found', 404);
        if ($recipe['user_id'] !== $user['sub']) Response::error('Forbidden', 403);

        $this->model->delete($id);
        Response::noContent();
    }

    private function body(): array
    {
        return (array)json_decode(file_get_contents('php://input'), true);
    }
}

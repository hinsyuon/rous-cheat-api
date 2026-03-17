<?php
namespace App\Controllers;
use App\Helpers\Response;
use App\Models\IngredientModel;

class IngredientController {
    private IngredientModel $model;
    public function __construct() { $this->model = new IngredientModel(); }

    public function index(): void {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(1, min(100, (int)($_GET['per_page'] ?? 50)));
        [$rows, $total] = $this->model->all($page, $perPage);
        Response::paginated($rows, $total, $page, $perPage);
    }
    public function show(int $id): void {
        $ing = $this->model->find($id);
        if (!$ing) Response::error('Ingredient not found', 404);
        Response::success($ing);
    }
    public function search(): void {
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) Response::error('Query must be at least 2 characters', 422);
        Response::success($this->model->search($q));
    }
}

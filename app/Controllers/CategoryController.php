<?php

// ── CategoryController ───────────────────────────────────────────────────────
namespace App\Controllers;
use App\Helpers\Response;
use App\Models\{CategoryModel, RecipeModel};

class CategoryController {
    public function index(): void {
        Response::success((new CategoryModel())->all());
    }
    public function recipes(int $id): void {
        $cat = (new CategoryModel())->find($id);
        if (!$cat) Response::error('Category not found', 404);
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(1, min(50, (int)($_GET['per_page'] ?? 20)));
        [$rows, $total] = (new RecipeModel())->byCategory($id, $page, $perPage);
        Response::paginated($rows, $total, $page, $perPage);
    }
}

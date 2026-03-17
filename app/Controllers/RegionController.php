<?php
namespace App\Controllers;
use App\Helpers\Response;
use App\Models\{RegionModel, RecipeModel};

class RegionController {
    public function index(): void {
        Response::success((new RegionModel())->all());
    }
    public function recipes(int $id): void {
        $region = (new RegionModel())->find($id);
        if (!$region) Response::error('Region not found', 404);
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(1, min(50, (int)($_GET['per_page'] ?? 20)));
        [$rows, $total] = (new RecipeModel())->byRegion($id, $page, $perPage);
        Response::paginated($rows, $total, $page, $perPage);
    }
}

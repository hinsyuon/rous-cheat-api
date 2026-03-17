<?php
namespace App\Controllers;
use App\Helpers\Response;
use App\Middleware\AuthMiddleware;
use App\Models\FavoriteModel;

class FavoriteController {
    public function index(): void {
        $user = AuthMiddleware::require();
        Response::success((new FavoriteModel())->forUser((int)$user['sub']));
    }
    public function store(int $recipeId): void {
        $user = AuthMiddleware::require();
        (new FavoriteModel())->add((int)$user['sub'], $recipeId);
        Response::created(['message' => 'Recipe saved to favorites']);
    }
    public function destroy(int $recipeId): void {
        $user = AuthMiddleware::require();
        (new FavoriteModel())->remove((int)$user['sub'], $recipeId);
        Response::noContent();
    }
}

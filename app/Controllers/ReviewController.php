<?php
namespace App\Controllers;
use App\Helpers\{Response, Validator};
use App\Middleware\AuthMiddleware;
use App\Models\{ReviewModel, RecipeModel};

class ReviewController {
    public function index(int $recipeId): void {
        $recipe = (new RecipeModel())->find($recipeId);
        if (!$recipe) Response::error('Recipe not found', 404);
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(1, min(50, (int)($_GET['per_page'] ?? 20)));
        [$rows, $total] = (new ReviewModel())->forRecipe($recipeId, $page, $perPage);
        Response::paginated($rows, $total, $page, $perPage);
    }

    public function store(int $recipeId): void {
        $user   = AuthMiddleware::require();
        $recipe = (new RecipeModel())->find($recipeId);
        if (!$recipe) Response::error('Recipe not found', 404);

        $body = (array)json_decode(file_get_contents('php://input'), true);
        Validator::make($body)
            ->required('rating', 'Rating')
            ->numeric('rating')
            ->in('rating', ['1','2','3','4','5',1,2,3,4,5])
            ->validate();

        $id = (new ReviewModel())->create(
            $recipeId,
            (int)$user['sub'],
            (int)$body['rating'],
            trim($body['comment'] ?? '')
        );
        Response::created(['id' => $id, 'message' => 'Review saved']);
    }
}

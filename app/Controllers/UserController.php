<?php
namespace App\Controllers;
use App\Helpers\Response;
use App\Middleware\AuthMiddleware;
use App\Models\{UserModel, RecipeModel};

class UserController {
    public function show(int $id): void {
        $user = (new UserModel())->find($id);
        if (!$user) Response::error('User not found', 404);
        unset($user['password'], $user['email']); // public profile
        Response::success($user);
    }

    public function updateProfile(): void {
        $payload = AuthMiddleware::require();
        $body    = (array)json_decode(file_get_contents('php://input'), true);
        (new UserModel())->update((int)$payload['sub'], $body);
        $user = (new UserModel())->find((int)$payload['sub']);
        unset($user['password']);
        Response::success($user);
    }

    public function recipes(int $id): void {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = max(1, min(50, (int)($_GET['per_page'] ?? 20)));
        [$rows, $total] = (new RecipeModel())->byUser($id, $page, $perPage);
        Response::paginated($rows, $total, $page, $perPage);
    }
}

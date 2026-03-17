<?php

namespace App\Controllers;

use App\Helpers\{Response, Validator, JWT, DB};
use App\Middleware\AuthMiddleware;
use App\Models\UserModel;

class AuthController
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    // POST /auth/register
    public function register(): void
    {
        $body = $this->body();

        Validator::make($body)
            ->required('name', 'Name')
            ->required('email', 'Email')
            ->email('email')
            ->required('password', 'Password')
            ->min('password', 8)
            ->validate();

        if ($this->users->findByEmail($body['email'])) {
            Response::error('Email already registered', 409);
        }

        $id   = $this->users->create(
            name:     trim($body['name']),
            email:    strtolower(trim($body['email'])),
            password: password_hash($body['password'], PASSWORD_BCRYPT, ['cost' => 12]),
        );
        $user  = $this->users->find($id);
        $token = JWT::encode(['sub' => $id, 'email' => $user['email']]);

        Response::created(['user' => $this->safe($user), 'token' => $token]);
    }

    // POST /auth/login
    public function login(): void
    {
        $body = $this->body();

        Validator::make($body)
            ->required('email', 'Email')
            ->required('password', 'Password')
            ->validate();

        $user = $this->users->findByEmail(strtolower(trim($body['email'])));

        if (!$user || !password_verify($body['password'], $user['password'])) {
            Response::error('Invalid email or password', 401);
        }

        $token = JWT::encode(['sub' => $user['id'], 'email' => $user['email']]);
        Response::success(['user' => $this->safe($user), 'token' => $token]);
    }

    // POST /auth/logout
    public function logout(): void
    {
        // Stateless JWT — client discards the token
        Response::success(['message' => 'Logged out successfully']);
    }

    // GET /auth/me
    public function me(): void
    {
        $payload = AuthMiddleware::require();
        $user    = $this->users->find((int)$payload['sub']);
        if (!$user) Response::error('User not found', 404);
        Response::success($this->safe($user));
    }

    private function safe(array $user): array
    {
        unset($user['password']);
        return $user;
    }

    private function body(): array
    {
        return (array)json_decode(file_get_contents('php://input'), true);
    }
}

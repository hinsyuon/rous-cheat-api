<?php

namespace App\Middleware;

use App\Helpers\JWT;
use App\Helpers\Response;

class AuthMiddleware
{
    /**
     * Require a valid JWT. Returns the decoded payload.
     */
    public static function require(): array
    {
        $payload = JWT::fromRequest();
        if (!$payload) {
            Response::error('Unauthorized — valid Bearer token required', 401);
        }
        return $payload;
    }

    /**
     * Optionally parse JWT. Returns payload or null.
     */
    public static function optional(): ?array
    {
        return JWT::fromRequest();
    }
}

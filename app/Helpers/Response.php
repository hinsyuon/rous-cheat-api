<?php

namespace App\Helpers;

class Response
{
    public static function success(mixed $data, int $code = 200, array $meta = []): never
    {
        http_response_code($code);
        $payload = ['success' => true, 'data' => $data];
        if ($meta) $payload['meta'] = $meta;
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function error(string $message, int $code = 400, array $extra = []): never
    {
        http_response_code($code);
        $payload = ['success' => false, 'error' => ['code' => $code, 'message' => $message]];
        if ($extra) $payload['debug'] = $extra;
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function paginated(array $items, int $total, int $page, int $perPage): never
    {
        self::success($items, 200, [
            'page'        => $page,
            'per_page'    => $perPage,
            'total'       => $total,
            'total_pages' => (int)ceil($total / $perPage),
        ]);
    }

    public static function created(mixed $data): never
    {
        self::success($data, 201);
    }

    public static function noContent(): never
    {
        http_response_code(204);
        exit;
    }
}

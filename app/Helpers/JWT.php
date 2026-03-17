<?php

namespace App\Helpers;

class JWT
{
    private static function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64decode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public static function encode(array $payload): string
    {
        $secret  = Env::get('JWT_SECRET', 'default-secret-change-me');
        $expiry  = (int)Env::get('JWT_EXPIRY', '86400');

        $header  = self::base64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload['iat'] = time();
        $payload['exp'] = time() + $expiry;
        $body    = self::base64url(json_encode($payload));
        $sig     = self::base64url(hash_hmac('sha256', "$header.$body", $secret, true));

        return "$header.$body.$sig";
    }

    public static function decode(string $token): ?array
    {
        $secret = Env::get('JWT_SECRET', 'default-secret-change-me');
        $parts  = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$header, $body, $sig] = $parts;
        $expected = self::base64url(hash_hmac('sha256', "$header.$body", $secret, true));
        if (!hash_equals($expected, $sig)) return null;

        $payload = json_decode(self::base64decode($body), true);
        if (!$payload || $payload['exp'] < time()) return null;

        return $payload;
    }

    public static function fromRequest(): ?array
    {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($auth, 'Bearer ')) return null;
        return self::decode(substr($auth, 7));
    }
}

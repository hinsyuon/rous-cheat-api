<?php

declare(strict_types=1);

// ── Bootstrap ─────────────────────────────────────────────────────────────────
require_once __DIR__ . '/../app/Helpers/Env.php';
require_once __DIR__ . '/../app/Helpers/Response.php';
require_once __DIR__ . '/../app/Helpers/JWT.php';
require_once __DIR__ . '/../app/Helpers/Validator.php';
require_once __DIR__ . '/../app/Helpers/DB.php';
require_once __DIR__ . '/../app/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/Models/UserModel.php';
require_once __DIR__ . '/../app/Models/RecipeModel.php';
require_once __DIR__ . '/../app/Models/CategoryModel.php';
require_once __DIR__ . '/../app/Models/RegionModel.php';
require_once __DIR__ . '/../app/Models/IngredientModel.php';
require_once __DIR__ . '/../app/Models/ReviewModel.php';
require_once __DIR__ . '/../app/Models/FavoriteModel.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/RecipeController.php';
require_once __DIR__ . '/../app/Controllers/CategoryController.php';
require_once __DIR__ . '/../app/Controllers/RegionController.php';
require_once __DIR__ . '/../app/Controllers/IngredientController.php';
require_once __DIR__ . '/../app/Controllers/ReviewController.php';
require_once __DIR__ . '/../app/Controllers/FavoriteController.php';
require_once __DIR__ . '/../app/Controllers/UserController.php';

use App\Helpers\Env;
use App\Helpers\Response;
use App\Helpers\DB;

// ── Environment ───────────────────────────────────────────────────────────────
Env::load(__DIR__ . '/../.env');

// ── CORS Headers ──────────────────────────────────────────────────────────────
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type, Accept');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Database ──────────────────────────────────────────────────────────────────
DB::init();

// ── Routing ───────────────────────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/');

// Strip /api/v1 prefix
$base = '/api/v1';
if (str_starts_with($uri, $base)) {
    $uri = substr($uri, strlen($base)) ?: '/';
}

$segments = explode('/', trim($uri, '/'));

try {
    route($method, $uri, $segments);
} catch (Throwable $e) {
    $debug = Env::get('APP_DEBUG') === 'true';
    Response::error(
        $debug ? $e->getMessage() : 'Internal server error',
        500,
        $debug ? ['trace' => $e->getTraceAsString()] : []
    );
}

// ── Router ────────────────────────────────────────────────────────────────────
function route(string $method, string $uri, array $seg): void
{
    // ── Auth routes ───────────────────────────────────────────────────────────
    if ($seg[0] === 'auth') {
        $ctrl = new App\Controllers\AuthController();
        match([$method, $seg[1] ?? '']) {
            ['POST', 'register'] => $ctrl->register(),
            ['POST', 'login']    => $ctrl->login(),
            ['POST', 'logout']   => $ctrl->logout(),
            ['GET',  'me']       => $ctrl->me(),
            default              => Response::error('Auth route not found', 404),
        };
        return;
    }

    // ── Recipe routes ─────────────────────────────────────────────────────────
    if ($seg[0] === 'recipes') {
        $ctrl = new App\Controllers\RecipeController();
        $id   = isset($seg[1]) && is_numeric($seg[1]) ? (int)$seg[1] : null;
        $sub  = $id ? ($seg[2] ?? null) : ($seg[1] ?? null);

        match(true) {
            $method === 'GET'  && $uri === '/recipes'              => $ctrl->index(),
            $method === 'GET'  && $sub === 'popular'               => $ctrl->popular(),
            $method === 'GET'  && $sub === 'random'                => $ctrl->random(),
            $method === 'GET'  && $sub === 'search'                => $ctrl->search(),
            $method === 'GET'  && $id !== null && $sub === null    => $ctrl->show($id),
            $method === 'POST' && $uri === '/recipes'              => $ctrl->store(),
            $method === 'PUT'  && $id !== null && $sub === null    => $ctrl->update($id),
            $method === 'DELETE' && $id !== null && $sub === null  => $ctrl->destroy($id),
            $method === 'GET'  && $id !== null && $sub === 'reviews' => (new App\Controllers\ReviewController())->index($id),
            $method === 'POST' && $id !== null && $sub === 'reviews' => (new App\Controllers\ReviewController())->store($id),
            default => Response::error('Recipe route not found', 404),
        };
        return;
    }

    // ── Category routes ───────────────────────────────────────────────────────
    if ($seg[0] === 'categories') {
        $ctrl = new App\Controllers\CategoryController();
        $id   = isset($seg[1]) && is_numeric($seg[1]) ? (int)$seg[1] : null;
        match(true) {
            $method === 'GET' && $uri === '/categories'               => $ctrl->index(),
            $method === 'GET' && $id && ($seg[2] ?? '') === 'recipes' => $ctrl->recipes($id),
            default => Response::error('Category route not found', 404),
        };
        return;
    }

    // ── Region routes ─────────────────────────────────────────────────────────
    if ($seg[0] === 'regions') {
        $ctrl = new App\Controllers\RegionController();
        $id   = isset($seg[1]) && is_numeric($seg[1]) ? (int)$seg[1] : null;
        match(true) {
            $method === 'GET' && $uri === '/regions'                  => $ctrl->index(),
            $method === 'GET' && $id && ($seg[2] ?? '') === 'recipes' => $ctrl->recipes($id),
            default => Response::error('Region route not found', 404),
        };
        return;
    }

    // ── Ingredient routes ─────────────────────────────────────────────────────
    if ($seg[0] === 'ingredients') {
        $ctrl = new App\Controllers\IngredientController();
        $id   = isset($seg[1]) && is_numeric($seg[1]) ? (int)$seg[1] : null;
        $sub  = $id ? ($seg[2] ?? null) : ($seg[1] ?? null);
        match(true) {
            $method === 'GET' && $uri === '/ingredients'  => $ctrl->index(),
            $method === 'GET' && $sub === 'search'        => $ctrl->search(),
            $method === 'GET' && $id !== null             => $ctrl->show($id),
            default => Response::error('Ingredient route not found', 404),
        };
        return;
    }

    // ── Favorites routes ──────────────────────────────────────────────────────
    if ($seg[0] === 'favorites') {
        $ctrl      = new App\Controllers\FavoriteController();
        $recipeId  = isset($seg[1]) && is_numeric($seg[1]) ? (int)$seg[1] : null;
        match(true) {
            $method === 'GET'    && $uri === '/favorites'  => $ctrl->index(),
            $method === 'POST'   && $recipeId !== null     => $ctrl->store($recipeId),
            $method === 'DELETE' && $recipeId !== null     => $ctrl->destroy($recipeId),
            default => Response::error('Favorites route not found', 404),
        };
        return;
    }

    // ── User routes ───────────────────────────────────────────────────────────
    if ($seg[0] === 'users') {
        $ctrl = new App\Controllers\UserController();
        $id   = isset($seg[1]) && is_numeric($seg[1]) ? (int)$seg[1] : null;
        $sub  = $id ? ($seg[2] ?? null) : ($seg[1] ?? null);
        match(true) {
            $method === 'PUT'  && $sub === 'profile'               => $ctrl->updateProfile(),
            $method === 'GET'  && $id !== null && $sub === null     => $ctrl->show($id),
            $method === 'GET'  && $id !== null && $sub === 'recipes'=> $ctrl->recipes($id),
            default => Response::error('User route not found', 404),
        };
        return;
    }

    // ── Health check ──────────────────────────────────────────────────────────
    if ($uri === '/' || $uri === '/health') {
        Response::success(['status' => 'ok', 'app' => 'Rous Cheat API', 'version' => '1.0.0']);
        return;
    }

    Response::error('Route not found', 404);
}

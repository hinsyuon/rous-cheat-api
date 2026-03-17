# 🍲 Rous Cheat API — រសជាតិ
**RESTful PHP API for the Rous Cheat Cambodian Recipe App**

---

## Stack
- PHP 8.2+ (no framework, no composer — pure PHP)
- MySQL 8.0+
- JWT Authentication (built from scratch)
- PDO for database access

---

## Quick Start

```bash
# 1. Create MySQL database
mysql -u root -p -e "CREATE DATABASE rous_cheat_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Configure environment
cp .env.example .env
# Edit .env — set DB_HOST, DB_USERNAME, DB_PASSWORD, JWT_SECRET

# 3. Run migrations
php database/migrate.php

# 4. Seed sample data
php database/seed.php

# 5. Start dev server
php -S localhost:8000 -t public/
```

No `composer install`. No `vendor/` folder. Nothing to install.

---

## API Base URL
```
http://localhost:8000/api/v1
```

---

## Endpoints

### 🔐 Auth
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/auth/register` | Register new user |
| POST | `/auth/login` | Login & get JWT token |
| POST | `/auth/logout` | Invalidate token |
| GET  | `/auth/me` | Get current user |

### 🍲 Recipes
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/recipes` | List recipes (paginated) |
| GET | `/recipes/{id}` | Get single recipe |
| POST | `/recipes` | Create recipe (auth) |
| PUT | `/recipes/{id}` | Update recipe (auth) |
| DELETE | `/recipes/{id}` | Delete recipe (auth) |
| GET | `/recipes/search?q=amok` | Search recipes |
| GET | `/recipes/popular` | Trending recipes |
| GET | `/recipes/random` | Random recipe |

### 🗂️ Categories
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/categories` | All categories |
| GET | `/categories/{id}/recipes` | Recipes in category |

### 🗺️ Regions (Provinces)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/regions` | All 24 provinces |
| GET | `/regions/{id}/recipes` | Recipes by province |

### 🫙 Ingredients
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/ingredients` | All ingredients |
| GET | `/ingredients/{id}` | Ingredient detail + substitutes |
| GET | `/ingredients/search?q=lemongrass` | Search ingredients |

### ⭐ Favorites
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/favorites` | My favorites (auth) |
| POST | `/favorites/{recipe_id}` | Save favorite (auth) |
| DELETE | `/favorites/{recipe_id}` | Remove favorite (auth) |

### ⭐ Ratings & Reviews
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/recipes/{id}/reviews` | Get reviews |
| POST | `/recipes/{id}/reviews` | Add review (auth) |

### 👤 Users
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/users/{id}` | Public user profile |
| PUT | `/users/profile` | Update my profile (auth) |
| GET | `/users/{id}/recipes` | User's recipes |

---

## Authentication

Include JWT token in every protected request header:
```
Authorization: Bearer <token>
```

---

## Response Format

### Success
```json
{
  "success": true,
  "data": { ... },
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 500
  }
}
```

### Error
```json
{
  "success": false,
  "error": {
    "code": 404,
    "message": "Recipe not found"
  }
}
```

---

## Demo Credentials
```
Email:    admin@rouscheat.kh
Password: password123
```

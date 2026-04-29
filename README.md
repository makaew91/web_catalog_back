# Web Catalog — Backend

REST API на Laravel 11 для каталога товаров с авторизацией через Laravel Sanctum.

## Стек

- PHP 8.3+ / Laravel 11
- PostgreSQL 16
- Laravel Sanctum (token-based auth)

## Быстрый старт через Docker

```bash
cp .env.example .env

# Под docker (db host = "db", тот же 5432 внутри сети, креды совпадают с docker-compose.yml)
sed -i.bak 's/^DB_HOST=.*/DB_HOST=db/; s/^DB_USERNAME=.*/DB_USERNAME=web_catalog/; s/^DB_PASSWORD=.*/DB_PASSWORD=secret/' .env && rm .env.bak

docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

API будет доступен на `http://localhost:8000`.

## Локальный запуск без Docker

Требуется PHP 8.3+, Composer, PostgreSQL.

```bash
cp .env.example .env
# Отредактируйте DB_* в .env под свою базу

composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Аккаунт администратора

После сидеров создаётся:

- email: `admin@example.com`
- password: `password`

## Эндпоинты

### Публичные

| Метод | URL | Описание |
| --- | --- | --- |
| GET | `/api/categories` | Список категорий |
| GET | `/api/products` | Список товаров (пагинация, фильтр `?category_id=`, поиск `?search=`) |
| GET | `/api/products/{id}` | Карточка товара |
| POST | `/api/login` | Авторизация — возвращает токен |

### Требуют `Authorization: Bearer <token>`

| Метод | URL | Описание |
| --- | --- | --- |
| POST | `/api/products` | Создать товар |
| PUT/PATCH | `/api/products/{id}` | Обновить товар |
| DELETE | `/api/products/{id}` | Удалить товар (soft delete) |
| POST | `/api/logout` | Отозвать текущий токен |

## Тесты

```bash
# Создать тестовую базу один раз
createdb web_catalog_testing

vendor/bin/phpunit
```

Конфигурация тестов — `phpunit.xml`, по умолчанию использует `web_catalog_testing`.

## Структура

- `app/Http/Controllers/Api/` — Resource-контроллеры (`Product`, `Category`, `Auth`)
- `app/Http/Requests/` — Form Request классы для валидации
- `app/Http/Resources/` — Resource-классы для ответов API
- `app/Models/` — Eloquent-модели (`Product`, `Category`, `User`)
- `database/migrations/` — миграции схемы
- `database/seeders/` — наполнение базы тестовыми данными
- `routes/api.php` — определение API-роутов

# TDS User API

A containerised REST API for managing users, built from scratch with **Symfony 6.4 LTS**. The project follows a layered architecture (controller / service / repository) and the service layer is covered by unit tests.

## Tech stack

- **PHP 8.3** (php-fpm)
- **Symfony 6.4 LTS**
- **MySQL 8** (via Doctrine ORM)
- **Nginx** (reverse proxy)
- **Docker / Docker Compose** (fully containerised)
- **PHPUnit** and **Mockery** (unit testing)

## Architecture

Each layer has a single, clear responsibility:

- **Controller** (`src/Controller/UserController.php`) — handles HTTP: reads the request, returns a JSON response with an appropriate status code. Contains no business logic or database access.
- **Service** (`src/Service/UserService.php`) — the business-logic layer. All CRUD operations flow through here; the controller never talks to Doctrine directly.
- **Repository** (`src/Repository/UserRepository.php`) — data access. Holds query methods (`find`, `findLatestUsers`) and nothing else.
- **Entity** (`src/Entity/User.php`) — a plain Doctrine-mapped object (Data Mapper pattern): fields, getters, and setters, with no persistence logic of its own.

Persistence is handled by Doctrine's **EntityManageΩr** (writes: `persist`, `flush`, `remove`) and the **Repository** (reads).

## Getting started

Requires Docker and Docker Compose.

```bash
# 1. Build and start the containers (php, mysql, nginx)
docker compose up -d

# 2. Create the database schema (wait a few seconds for MySQL to be ready)
docker compose exec php php bin/console doctrine:migrations:migrate
```

The API is then available at **http://localhost:8080**.

## API endpoints

| Method | Path          | Description         |
|--------|---------------|---------------------|
| GET    | `/user`       | List users          |
| GET    | `/user/{id}`  | Get a single user   |
| POST   | `/user`       | Create a user       |
| PATCH  | `/user/{id}`  | Update a user       |
| DELETE | `/user/{id}`  | Delete a user       |

All endpoints return JSON and use appropriate HTTP status codes (e.g. `201 Created`, `404 Not Found`).

### Example

```bash
# Create a user
curl -X POST http://localhost:8080/user \
  -H "Content-Type: application/json" \
  -d '{"email":"a@b.com","firstName":"Ada","lastName":"Lovelace"}'

# Update a user
curl -X PATCH http://localhost:8080/user/1 \
  -H "Content-Type: application/json" \
  -d '{"email":"new@b.com"}'
```

## Running the tests

The service layer is unit-tested with the repository and EntityManager mocked, so tests run in isolation without a database.

```bash
docker compose exec php php bin/phpunit
```

Tests cover creating, retrieving, and updating a user, plus the not-found path. They are written using both PHPUnit's native mocks and Mockery.

# Appy Venture — API

Minimal freight marketplace API built with **Laravel 12** (PHP 8.2). It supports:
- Loads (draft/open/booked/closed)
- Bids on loads
- Booking on accept
- **ETag/If-Match** optimistic concurrency
- Role-based auth (shipper / carrier) with Bearer tokens

---

## Requirements
- **PHP 8.2+**
- **Mysql**
- **Composer**
- **SQLite** extensions for PHP CLI: `pdo_sqlite` and `sqlite3`  

---

## API Docs
- **OpenAPI (YAML):** [`docs/api/openapi.yaml`](docs/api/openapi.yaml)
- **cURL:** [`docs/api/README.md`](docs/api/README.md)
- **Postman collection:** [`./docs/postman/AppyVenture_API_postman_collection.json`](docs/postman/AppyVenture_API_postman_collection.json)

---

## Local Dev
```bash
cp .env.example .env
php artisan key:generate

# DB & seed
php artisan migrate --seed

# Run
php artisan serve
```

---

## Tests (Feature)

This project ships with feature tests covering the core flow:

- **Happy path:** shipper creates load → carrier bids → shipper accepts → booking created → other bids rejected
- **AuthZ:** carrier cannot create load; wrong shipper cannot accept
- **Business rules:** duplicate bid → 409; cannot accept when already booked
- **Filters:** `GET /api/loads?status=open`
- **Soft delete:** owner deletes load; subsequent GET/DELETE → 404

**Run all tests**
```bash
php artisan test

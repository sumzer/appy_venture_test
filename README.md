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

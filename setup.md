# Setup Guide

This file explains how to run the project locally and with Docker.

## Prerequisites

Install these first:

- PHP 8.4
- Composer
- Node.js 22+
- npm
- MySQL

## Option 1: Local Setup

This is the simplest way if you want to run Laravel directly on your machine.

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Create the environment file

```bash
copy .env.example .env
```

### 3. Configure the database

Update `.env` with your MySQL details:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=office_asset_request
DB_USERNAME=your_mysql_username
DB_PASSWORD=your_mysql_password
```

Create the database in MySQL first.

### 4. Generate the application key

```bash
php artisan key:generate
```

### 5. Run migrations and seeders

```bash
php artisan migrate
php artisan db:seed
```

### 6. Build frontend assets

```bash
npm run build
```

### 7. Start the app

```bash
php artisan serve --host=0.0.0.0 --port=8001
```

Open:

```text
http://localhost:8001
```

## Option 2: Docker Setup

Use this if you want Laravel to run inside Docker while your MySQL database stays on your machine.

### 1. Create the Docker env file

```bash
copy .env.docker.example .env.docker
```

### 2. Update `.env.docker`

Set your MySQL connection values:

```env
APP_PORT=8001
APP_RUN_SEED=false

DOCKER_DB_HOST=host.docker.internal
DOCKER_DB_PORT=3306
DOCKER_DB_DATABASE=office_asset_request
DOCKER_DB_USERNAME=your_mysql_username
DOCKER_DB_PASSWORD=your_mysql_password
```

### 3. What `host.docker.internal` means

This special hostname lets the Docker container connect back to your computer.

In this setup:

- Laravel runs inside Docker
- MySQL runs on your machine
- `host.docker.internal` points the container to your machine's MySQL server

### 4. Start the container

```bash
docker compose --env-file .env.docker up --build -d
```

### 5. Seed demo data

```bash
docker compose --env-file .env.docker exec app php artisan db:seed --force
```

### 6. Stop Docker

```bash
docker compose --env-file .env.docker down
```

## Demo Accounts

After seeding, you can log in with:

- `admin@office.test`
- `manager.it@office.test`
- `staff1@office.test`

Password:

```text
password
```

## Useful Commands

### Run tests

```bash
php artisan test
```

### Build frontend assets

```bash
npm run build
```

### Run Docker commands

```bash
docker compose --env-file .env.docker exec app php artisan test
docker compose --env-file .env.docker exec app php artisan migrate --force
docker compose --env-file .env.docker exec app php artisan db:seed --force
```

## CI/CD

The project includes a production GitHub Actions workflow:

- [`.github/workflows/production.yml`](.github/workflows/production.yml)

More details:

- [docs/cicd.md](docs/cicd.md)

# Laravel Analytics API

> Real-time event tracking and analytics API built with Laravel 11, Redis, PostgreSQL, and Docker.

[![CI](https://github.com/mahdicraft/laravel-analytics-api/actions/workflows/ci.yml/badge.svg)](https://github.com/mahdicraft/laravel-analytics-api/actions)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel)](https://laravel.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## Overview

A production-ready REST API for tracking user events (page views, clicks, purchases, etc.) and serving aggregated analytics. Designed to handle high-throughput ingestion without blocking the client, using Redis-backed queues and caching.

**Key design decisions:**
- Events are queued asynchronously — the API responds in `< 5ms` regardless of load
- Analytics queries are cached in Redis with a 5-minute TTL to avoid repeated DB hits
- Service layer separates business logic from controllers (SOLID principles)
- Fully containerized with Docker Compose — one command to run locally

---

## Architecture

```
Client
  │
  │  POST /api/v1/events
  ▼
Laravel API  ──── dispatches job ────▶  Redis Queue
                                              │
                                              │  async worker
                                              ▼
                                        PostgreSQL
                                        (persisted events)

  │
  │  GET /api/v1/analytics/summary
  ▼
Redis Cache (5 min TTL)
  │  cache miss
  ▼
PostgreSQL (aggregation query)
```

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 11 / PHP 8.3 |
| Database | PostgreSQL 16 |
| Cache & Queue | Redis 7 |
| Containerization | Docker + Docker Compose |
| Testing | PHPUnit |
| CI/CD | GitHub Actions |

---

## Getting Started

### Prerequisites

- Docker & Docker Compose

### Run locally

```bash
git clone https://github.com/mahdicraft/laravel-analytics-api.git
cd laravel-analytics-api

cp .env.example .env

docker compose up -d

docker compose exec app php artisan migrate
docker compose exec app php artisan queue:work &
```

API is available at `http://localhost:8000`

---

## API Reference

### Track an event

```http
POST /api/v1/events
Content-Type: application/json

{
  "event_type": "page_view",
  "session_id": "abc123xyz",
  "url": "https://example.com/products",
  "metadata": {
    "referrer": "google",
    "device": "mobile"
  }
}
```

**Response `202 Accepted`:**
```json
{
  "status": "queued"
}
```

---

### Get analytics summary

```http
GET /api/v1/analytics/summary?period=24h
```

Supported periods: `1h` · `24h` · `7d`

**Response `200 OK`:**
```json
{
  "total_events": 14823,
  "unique_sessions": 4210,
  "by_type": {
    "page_view": 9100,
    "click": 4500,
    "purchase": 1223
  },
  "period": "24h",
  "cached_at": "2025-03-12T10:42:00Z"
}
```

---

## Project Structure

```
app/
├── Http/Controllers/
│   ├── EventController.php        # Receives and queues events
│   └── AnalyticsController.php    # Serves aggregated stats
├── Services/
│   └── AnalyticsService.php       # Business logic + Redis caching
├── Jobs/
│   └── ProcessAnalyticEvent.php   # Async queue worker
└── Models/
    └── AnalyticEvent.php

database/migrations/
tests/Feature/
docker-compose.yml
.github/workflows/ci.yml
```

---

## Running Tests

```bash
docker compose exec app php artisan test
```

Test coverage includes:
- Event queuing and validation
- Cache hit/miss behavior
- Analytics aggregation per period

---

## What This Demonstrates

- **Async processing** via Redis queues — decouples ingestion from storage
- **Cache-aside pattern** with TTL invalidation — reduces DB load under high traffic
- **Service layer architecture** — business logic isolated from HTTP layer
- **PHPUnit integration tests** — including queue and cache faking
- **Docker-first setup** — zero local dependencies beyond Docker
- **GitHub Actions CI** — runs tests on every push against real Redis and PostgreSQL services

---

## Author

**Mahdi Moradi** — Senior PHP Backend Developer  
Berlin, Germany · [moradi.mahdi@gmx.de](mailto:moradi.mahdi@gmx.de) · [LinkedIn](https://www.linkedin.com/in/mahdi-moradi-543b53a3/) · [GitHub](https://github.com/mahdicraft)

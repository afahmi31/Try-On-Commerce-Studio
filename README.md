# Try-On Commerce Studio

Try-On Commerce Studio is an open-source fashion virtual try-on platform built with Laravel.
It helps fashion brands and stores let customers try outfits online before purchase.

## Core Capabilities

- Product catalog management from a web dashboard
- FASHN AI integration for garment try-on generation
- Public storefront try-on flow (`/{store_slug}/{product_ref?}`)
- Async processing with queue workers
- Public traffic protection (daily quota, IP/device limiter, polling throttle)
- Provider settings management (API key, model, dummy mode)

## AI Provider

Current integration:

- `FASHN AI` (`App\Domain\AI\Providers\FashnProvider`)

Supported models:

- `tryon-max`
- `tryon-v1.6`

Supported modes:

- Live mode (real API key)
- Dummy mode (for local/beta simulation)

## How to Buy FASHN Credits

Credit purchase is handled in your FASHN account, not inside this app.

### Purchase Steps

1. Sign in to your FASHN dashboard.
2. Open Billing / Credits.
3. Purchase credits (subscription or on-demand).
4. Copy your FASHN API key.
5. Open app settings at `/dashboard/settings`.
6. Paste API key, choose model, run API key test.
7. Save and run a try-on request.

### Verification Checklist

- API key test returns success.
- Dashboard can read FASHN credits.
- Try-on request moves `pending` -> `processing` -> `completed`.

### Troubleshooting

- If credits are purchased but shown as `0`, validate the API key in settings.
- Ensure outbound server access to FASHN API endpoints.
- Use dummy mode only for simulation, not production generation.

## Feature Overview

### Dashboard

Route prefix: `/dashboard`

Main sections:

- Overview and metrics
- Products
- Settings
- Recent try-on history and request details

### Product Management

- Create, update, delete products
- Upload product images or use external image URLs
- Configure AI metadata:
  - prompt
  - category (`auto`, `tops`, `bottoms`, `one-pieces`)
  - garment photo type
  - segmentation flag

### AI Settings

Configured from `/dashboard/settings`:

- FASHN API key (encrypted)
- API key testing
- Model selection
- Model-specific generation options
- Dummy mode + dummy URLs
- Public daily generate limit
- Public limiter toggles (IP / Device)

### Public Try-On Flow

Public route pattern:

- `/{store_slug}/{product_ref?}`

Try-on endpoints:

- `/{store_slug}/try-on/...`

Customer flow:

1. Open store page
2. Select product
3. Upload model photo (or dummy model if enabled)
4. Generate try-on
5. Poll session status
6. View result and recent history

### Queue Processing

Job:

- `App\Jobs\ProcessTryOnSessionJob`

Behavior:

- Create provider job
- Poll provider status
- Update session lifecycle
- Store provider audit logs

Queue notes:

- Recommended: Redis queue worker in staging/production
- Local fallback: `QUEUE_CONNECTION=sync` (blocking request)

### Media Retention

- Sessions store `expires_at` using `TRYON_RETENTION_MINUTES`
- Cleanup policy can be handled via external scheduler/job

## Route Summary

### Web Routes

- `GET /setup`
- `POST /setup`
- `GET /login`
- `POST /login`
- `POST /logout`
- `GET /dashboard`
- `GET /dashboard/products`
- `POST /dashboard/products`
- `PATCH /dashboard/products/{productId}`
- `DELETE /dashboard/products/{productId}`
- `POST /dashboard/products/{productId}/images`
- `GET /dashboard/settings`
- `POST /dashboard/settings`
- `POST /dashboard/settings/test-api-key`
- `POST /dashboard/model`
- `POST /{store_slug}/try-on/sessions`
- `GET /{store_slug}/try-on/quota`
- `GET /{store_slug}/try-on/sessions/{sessionId}`
- `GET /{store_slug}/try-on/sessions`
- `GET /{store_slug}/{product_ref?}`

### API Routes (Sanctum)

- `POST /api/auth/login`
- `POST /api/auth/logout`
- `GET /api/auth/me`
- `GET /api/seller/me`
- `GET /api/seller/profile`
- `PATCH /api/seller/profile`
- `GET /api/seller/products`
- `POST /api/seller/products`
- `GET /api/seller/products/{id}`
- `PATCH /api/seller/products/{id}`
- `DELETE /api/seller/products/{id}`
- `POST /api/seller/products/{id}/images`
- `POST /api/tryon/sessions`
- `GET /api/tryon/sessions/{id}`

## Tech Stack

- PHP `^8.2`
- Laravel `^12`
- PostgreSQL
- Redis (recommended for queue/cache)
- Laravel Sanctum
- Blade + vanilla JavaScript
- Vite

## Local Setup

From `core-app` directory:

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
```

Run app:

```bash
php artisan serve
npm run dev
```

Run queue worker:

```bash
php artisan queue:work
```

Or run combined dev command:

```bash
composer dev
```

## Demo Seed Account

Created by `DatabaseSeeder`:

- Email: `seller@tryon.test`
- Password: `password`
- Store slug: `ceriakid`

## Configuration

### Environment (`.env`)

- Provider transport URL and status template
- Timeout and retry policy
- Queue/cache/database runtime config
- Retention and polling config

See `.env.example` for current keys.

### Database Settings UI

Managed from dashboard settings:

- FASHN API key
- Model selection and model config
- Dummy mode and dummy URLs
- Public quota and limiter options

## Testing

```bash
php artisan test
```

Current test focus:

- Basic application response
- Profile/product/image ownership flow

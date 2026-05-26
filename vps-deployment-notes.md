# VPS Deployment Notes

## Prinsip Konfigurasi

Konfigurasi dibagi menjadi dua:

- **Server-level (.env)**: transport, timeout, retry, queue, cache, retention.
- **App-level (Dashboard Settings / DB)**: API key, model, dummy mode, dummy URL, dummy model URL, dan daily public limit.

Artinya, `FASHN_MODEL`, `FASHN_DUMMY_ENABLED`, `FASHN_DUMMY_RESULT_URL`, dan `TRYON_DUMMY_MODEL_IMAGE_URL` **tidak dipakai lagi dari env**.

---

## Wajib Dicek Saat Setup VPS

1. Pastikan env transport provider terisi:
   - `FASHN_BASE_URL=https://api.fashn.ai/v1/run`
   - `FASHN_STATUS_URL_TEMPLATE=https://api.fashn.ai/v1/status/{job_id}`
   - `FASHN_TIMEOUT_SECONDS=60` (sesuaikan kebutuhan)
   - `FASHN_RETRY_TIMES=2` (sesuaikan kebutuhan)
   - `FASHN_RETRY_SLEEP_MS=300` (sesuaikan kebutuhan)
2. Pastikan setting di Dashboard terisi:
   - FASHN API Key
   - Model
   - Dummy mode (on/off sesuai kebutuhan)
   - Dummy result URL (wajib jika dummy on)
   - Dummy model image URL (opsional, sesuai flow test)
   - Public generate per day
3. Setelah ubah env, jalankan:

```bash
php artisan optimize:clear
php artisan config:cache
```

---

## Catatan Operasional

- Credit FASHN tetap aman karena limit generate public diproteksi di backend.
- Jika dummy mode aktif, request tidak akan menembak proses real FASHN.
- Mode quality public tetap dikunci ke mode termurah dari backend.
- URL produk support dua format:
  - slug (contoh: `/ceriakid/gamis-anak-perempuan-fadia`)
  - SKU (contoh: `/ceriakid/NA1`)

# UAT Checklist

Dokumen ini dipakai untuk validasi sebelum pilot di real server.
Gunakan dua tahap:

- Tahap A: Local UAT (cepat, tanpa risiko credit besar).
- Tahap B: Real Server UAT (penentu go/no-go pilot).

---

## A. Local UAT (Pre-Check)

Checklist ini memastikan flow aplikasi benar sebelum deploy/staging:

- [ ] Seller bisa login ke dashboard.
- [ ] Halaman Products, Dashboard, Settings bisa diakses normal.
- [ ] Settings bisa disimpan (API key/model/dummy/public limit) tanpa error validasi.
- [ ] Tombol `Test API Key` merespons sesuai kondisi key (valid/invalid).
- [ ] Public page seller terbuka dengan URL slug seller.
- [ ] Upload model image di public page berhasil.
- [ ] Trigger generate dari public page berhasil membuat session.
- [ ] Status session berubah dari `pending/processing` ke `completed` atau `failed`.
- [ ] Saat sisa quota public = 0:
  - [ ] Tombol generate disable di frontend.
  - [ ] Request generate tetap ditolak dari backend.
- [ ] Pesan limit tampil jelas (`Too Many Attempts.` atau sisa kesempatan).
- [ ] Tabel `Recent Try-On` tampil kolom terbaru (IP + Product Name) sesuai update.
- [ ] Kolom `Quality` tidak muncul lagi di tabel recent try-on.

---

## B. Real Server UAT (Go/No-Go Pilot)

Checklist ini wajib lulus untuk status siap pilot:

### 1) Environment & Runtime

- [ ] `APP_ENV=production`.
- [ ] `APP_DEBUG=false`.
- [ ] `php artisan optimize:clear` sudah dijalankan.
- [ ] `php artisan config:cache` sudah dijalankan.
- [ ] Queue worker aktif dan stabil.
- [ ] Scheduler aktif (jika dipakai untuk cleanup/purge).

### 2) Provider Transport (Server-Level)

- [ ] `FASHN_BASE_URL` terisi benar.
- [ ] `FASHN_STATUS_URL_TEMPLATE` terisi benar (`{job_id}` valid).
- [ ] `FASHN_TIMEOUT_SECONDS` sesuai kebutuhan server.
- [ ] `FASHN_RETRY_TIMES` dan `FASHN_RETRY_SLEEP_MS` sesuai kebutuhan.

### 3) Seller Settings (DB-Level)

- [ ] FASHN API key seller terisi dan lolos test.
- [ ] Model aktif sesuai kebutuhan seller.
- [ ] Dummy mode OFF untuk test real credit.
- [ ] Dummy URLs tidak dipakai saat test real.
- [ ] Public generate per day terisi sesuai policy beta.

### 4) Public Asset Accessibility (Kritis)

- [ ] URL image produk bisa diakses publik oleh FASHN.
- [ ] URL image model (yang dipakai request) bisa diakses publik oleh FASHN.
- [ ] Tidak ada URL private/localhost yang terkirim ke FASHN.

### 5) End-to-End Functional

- [ ] Happy path generate real berhasil sampai output muncul.
- [ ] Output URL hasil try-on valid dan bisa diakses.
- [ ] Session tersimpan lengkap (status, provider_job_id, product, ip).
- [ ] Case provider timeout ditangani (status/pesan error jelas).
- [ ] Case provider error ditangani tanpa crash app.
- [ ] Rate limit per IP/device/day berjalan di backend.

### 6) Credit Protection

- [ ] User tidak bisa bypass limit via reload UI.
- [ ] User tidak bisa bypass limit via direct API call.
- [ ] Generate gagal karena limit tidak mengurangi credit di luar kebijakan.
- [ ] Mode termurah/konfigurasi hemat credit berjalan sesuai policy.

### 7) Monitoring & Traceability

- [ ] Log aplikasi cukup untuk tracing kegagalan generate.
- [ ] Seller bisa melihat riwayat generate terbaru dengan data yang dibutuhkan.
- [ ] Error utama bisa dipetakan cepat (auth provider, timeout, invalid image URL).

---

## C. Hasil UAT

Isi setelah testing:

- Tanggal UAT:
- Environment: `local` / `real-server`
- Tester:
- Ringkasan hasil:
- Blocking issues:
- Keputusan: `GO` / `NO-GO`

---

## D. Catatan Eksekusi

- Local UAT = validasi fungsional cepat.
- Real server UAT = validasi final integrasi nyata + keputusan pilot.
- Keputusan launch pilot hanya berdasarkan hasil Real Server UAT.

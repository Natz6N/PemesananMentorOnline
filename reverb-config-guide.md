# Panduan Konfigurasi Laravel Reverb

## Konfigurasi file .env

Tambahkan konfigurasi berikut ke file `.env` Anda:

```
# Konfigurasi Broadcasting
BROADCAST_DRIVER=reverb
REVERB_APP_ID=mentorconnect
REVERB_APP_KEY=JHe2zDTsm9fxsNNWucGbbGZsyM5c8pRU
REVERB_APP_SECRET=Y4b8j2MXKuNpLRV29K7JW6Fk27AZstqv
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_TLS_CERT_PATH=
REVERB_TLS_KEY_PATH=
```

Untuk production, pastikan mengubah:
- `REVERB_APP_KEY` dan `REVERB_APP_SECRET` menjadi nilai acak yang kuat
- `REVERB_HOST` ke domain atau IP publik server Anda
- `REVERB_SCHEME` menjadi `https` jika menggunakan TLS
- `REVERB_TLS_CERT_PATH` dan `REVERB_TLS_KEY_PATH` dengan path sertifikat TLS jika menggunakan HTTPS

## Jalankan Reverb Server

Development:
```bash
php artisan reverb:start
```

Production (dengan Supervisor):
```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
``` 

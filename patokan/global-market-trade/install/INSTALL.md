# Global Market Trade (Template / Kosongan)

Paket ini sudah **dibersihkan** dari data sensitif (KYC uploads, credential DB lama, dll).

## 1) Setup Database
1. Buat database kosong (MySQL/MariaDB), misalnya: `gmt_db`
2. Import schema:
   - Import file: `install/schema.sql`
3. (Opsional) Country dropdown untuk signup:
   - Import file: `install/sql/002_countries_table_seed.sql`

## 2) Config DB
Edit file:
- `config/db.php`

Isi sesuai database di VPS/domain baru, atau pakai environment variables:
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`

## 3) Buat Admin Pertama
Generate hash password (jalankan di server):

```bash
php -r 'echo password_hash("AdminPass123!", PASSWORD_DEFAULT).PHP_EOL;'
```

Lalu insert admin (ganti email + hash):

```sql
INSERT INTO users (full_name, email, password_hash, is_admin, created_at)
VALUES ('Admin', 'admin@example.com', 'PASTE_HASH_HERE', 1, NOW());
```

## 4) Upload File ke VPS
Upload folder project ke web root (contoh):
- `/var/www/global-market-trade`

Pastikan folder upload ada & writable (jika dipakai):
- `uploads/`
- `uploads/kyc/`

## 5) Login
- Login: `/login.php`
- Admin panel: `/admin.php` atau `/admin_users.php`

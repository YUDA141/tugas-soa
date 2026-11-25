# SIMRS SOA (appsimrssoa & soasimrs)

## Cara Pasang Langsung di htdocs XAMPP
1. Ekstrak isi ZIP ini langsung ke dalam folder `C:\xampp\htdocs\` sehingga terbentuk dua folder sejajar:
   - `C:\xampp\htdocs\soasimrs` (Server B: Database & Web Service API)
   - `C:\xampp\htdocs\appsimrssoa` (Server A: Aplikasi Utama & UI RBAC)
2. Nyalakan **Apache** dan **MySQL** di XAMPP.
3. Buka `http://localhost/phpmyadmin` lalu impor file `soasimrs/database.sql`.
4. Buka aplikasi utama langsung di browser:
   `http://localhost/appsimrssoa`
5. Untuk mengecek layanan Web Service Server B secara langsung (untuk bukti/screenshot API):
   `http://localhost/soasimrs/api/poliklinik.php`

## Akun Login Demo
- **Admin**: username `admin` | password `password`
- **Dokter**: username `dr.budi` | password `password`
- **Pasien**: username `pasien.andi` | password `password`

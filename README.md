# Dashboard Pelayanan

Aplikasi dashboard untuk manajemen data pelayanan publik dengan Laravel 11 dan Oracle 11g Database.

## 🚀 Features

- ✅ **Authentication & Authorization** - Login system dengan role-based access control
- ✅ **Dashboard Statistik** - Real-time statistics dan chart visualization
- ✅ **Management Wilayah** - CRUD untuk Kabupaten, Kecamatan, dan Desa
- ✅ **Management Pendamping** - User management dengan multi-level access
- ✅ **Management Petugas** - Petugas management dengan level Desa/Kecamatan/Dinas
- ✅ **Kinerja Tracking** - Input dan reporting kinerja petugas
- ✅ **Statistik Kependudukan** - Data kependudukan per semester
- ✅ **Tracking Pelayanan** - Monitoring pelayanan dan pengaduan
- ✅ **Sarpras & VPN Management** - Manajemen infrastruktur desa
- ✅ **Responsive Design** - Mobile-friendly interface

## 🛠️ Tech Stack

- **Backend**: Laravel 11 + PHP 8.1
- **Database**: Oracle 11g dengan OCI8 driver
- **Frontend**: Tailwind CSS + Alpine.js + Chart.js
- **Build Tool**: Vite

## 📋 Prerequisites

- PHP 8.1 atau lebih tinggi
- Composer
- Node.js 18+ dan NPM
- Oracle 11g Database
- Oracle Instant Client 11.2
- OCI8 Extension untuk PHP

## 🔧 Installation

### 1. Install System Requirements

Ikuti panduan lengkap di `installation_guide.md` untuk install:
- PHP 8.1 dan extensions
- Oracle Instant Client
- OCI8 Extension
- Composer
- Node.js & NPM

### 2. Clone/Setup Project

```bash
cd /home/kbm/appduk
```

### 3. Setup Permissions

```bash
chmod +x setup.sh
./setup.sh
```

Script ini akan:
- Membuat struktur direktori
- Set permissions untuk storage
- Copy .env.example ke .env
- Install dependencies (opsional)
- Generate application key

### 4. Configure Environment

Edit file `.env` dengan kredensial Oracle database Anda:

```env
DB_CONNECTION=oracle
DB_HOST=your_oracle_host
DB_PORT=1521
DB_DATABASE=your_service_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
DB_ORACLE_SERVICE_NAME=your_service_name
```

### 5. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 6. Test Database Connection

```bash
php artisan tinker
```

Dalam tinker, jalankan:
```php
DB::connection()->getPdo();
```

Jika berhasil, akan menampilkan PDO object.

### 7. Build Frontend Assets

```bash
# Development (dengan hot module reload)
npm run dev

# Production
npm run build
```

### 8. Run Development Server

```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2 (optional, if using npm run dev): Vite dev server
npm run dev
```

Akses aplikasi di: `http://localhost:8000`

## 👤 Default Login

Setelah database tersetup, gunakan credentials berikut untuk login:
- **NIK**: (sesuai data di tabel pendamping)
- **Password**: (password yang telah di-hash dengan bcrypt)

## 📁 Project Structure

```
/home/kbm/appduk/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Controllers untuk semua modul
│   │   ├── Middleware/        # Custom middleware (RoleMiddleware)
│   │   └── Requests/          # Form validation requests
│   ├── Models/                # Eloquent models (Kabupaten, Kecamatan, Desa, dll)
│   └── Services/              # Business logic services
├── config/
│   ├── auth.php              # Authentication configuration
│   ├── database.php          # Database connections (Oracle)
│   └── ...
├── database/
│   └── migrations/           # (Optional) Database migrations
├── public/                   # Public assets
├── resources/
│   ├── css/
│   │   └── app.css          # Tailwind CSS + custom styles
│   ├── js/
│   │   ├── app.js           # Alpine.js + Chart.js  + utilities
│   │   └── bootstrap.js     # Axios configuration
│   └── views/
│       ├── auth/            # Login views
│       ├── layouts/         # Layout templates
│       ├── dashboard.blade.php
│       ├── wilayah/         # Wilayah management views
│       ├── pendamping/      # Pendamping management views
│       ├── petugas/         # Petugas management views
│       ├── kinerja/         # Kinerja reporting views
│       ├── kependudukan/    # Kependudukan statistics views
│       ├── pelayanan/       # Pelayanan tracking views
│       ├── sarpras/         # Sarpras management views
│       └── vpn/             # VPN management views
├── routes/
│   ├── web.php              # Web routes
│   └── api.php              # API routes
├── storage/                 # Storage untuk logs, cache, dll
├── .env.example             # Environment template
├── composer.json            # PHP dependencies
├── package.json             # Node dependencies
├── tailwind.config.js       # Tailwind configuration
├── vite.config.js           # Vite configuration
└── setup.sh                 # Setup script
```

## 📚 Documentation

Berikut dokumentasi lengkap yang tersedia:

1. **implementation_plan.md** - Rencana implementasi lengkap
2. **installation_guide.md** - Panduan instalasi step-by-step
3. **source_code_structure.md** - Struktur source code dan file yang perlu dibuat
4. **task.md** - Task breakdown dan progress tracking

## 🔐 Security Features

- ✅ CSRF Protection
- ✅ SQL Injection Prevention (Eloquent ORM)
- ✅ Password Hashing (Bcrypt)
- ✅ Role-Based Access Control
- ✅ Rate Limiting pada Login
- ✅ Session Management
- ✅ Input Sanitization

## 🎨 UI/UX Features

- ✅ Responsive Design (Mobile/Tablet/Desktop)
- ✅ Collapsible Sidebar Navigation
- ✅ Interactive Data Tables dengan Search & Pagination
- ✅ Real-time Chart Updates (Chart.js)
- ✅ Toast Notifications
- ✅ Form Validation dengan Visual Feedback
- ✅ Loading States & Spinners
- ✅ Modern Color Scheme & Gradients

## 📊 Database Schema

Database terdiri dari 10 tabel utama:

1. **wilayah_kabupaten** - Data kabupaten
2. **wilayah_kecamatan** - Data kecamatan
3. **wilayah_desa** - Data desa dengan detail
4. **pendamping** - User pendamping (authenticatable)
5. **petugas** - Data petugas multi-level
6. **sarpras_desa** - Sarana prasarana per desa
7. **vpn_desa** - Konfigurasi VPN per desa
8. **kinerja_petugas** - Data kinerja bulanan
9. **kependudukan_semester** - Data kependudukan per semester
10. **header_pelayanan** - Header tracking pelayanan

Lihat `db.md` untuk struktur lengkap.

## 🔧 Troubleshooting

### OCI8 Extension Not Found

```bash
# Verify OCI8 installation
php -m | grep oci8

# If not found, install:
sudo pecl install oci8
echo "extension=oci8.so" | sudo tee /etc/php/8.1/cli/conf.d/20-oci8.ini
```

### Oracle Connection Failed

```bash
# Check Oracle listener
lsnrctl status

# Test tnsping
tnsping your_service_name

# Verify LD_LIBRARY_PATH
echo $LD_LIBRARY_PATH
# Should include: /opt/oracle/instantclient_11_2
```

### Permission Issues

```bash
# Fix storage permissions
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Vite Not Working

```bash
# Clear cache
rm -rf node_modules package-lock.json
npm install
npm run dev
```

## 📈 Performance Optimization

- Eloquent eager loading untuk prevent N+1 queries
- Database indexing pada foreign keys
- Query caching untuk statistics
- Asset optimization dengan Vite
- Pagination untuk large datasets

## 🤝 Contributing

1. Create feature branch
2. Commit changes
3. Push to branch
4. Create Pull Request

## 📝 License

This project is proprietary software.

## 👥 Support

Untuk pertanyaan atau issues, hubungi tim development.

---

## 📝 Next Steps

Setelah instalasi selesai:

1. ✅ Lengkapi semua controller yang belum dibuat (lihat `source_code_structure.md`)
2. ✅ Buat semua view files untuk CRUD operations
3. ✅ Implement form validation requests
4. ✅ Create export services (Excel/PDF)
5. ✅ Add unit tests
6. ✅ Setup production environment
7. ✅ Deploy ke server production

**Status Saat Ini:**
- ✅ Models: 10/10 (100%)
- ✅ Routes: Completed
- ✅ Middleware: Completed
- ⏳ Controllers: 2/10 (20%) - DashboardController,  LoginController
- ⏳ Views: 3/30+ (10%) - Layout, Login, Dashboard
- ⏳ Services: 0/3 (0%)
- ⏳ Form Requests: 0/5 (0%)

Lihat `task.md` untuk detail progress.

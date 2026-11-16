# Panduan Deployment & Konfigurasi Produksi SIMSE
## SD Muhammadiyah Al Mujahidin Wonosari

Dokumen ini merupakan panduan teknis resmi instalasi, konfigurasi, dan *hardening* server produksi SIMSE berbasis **Linux Debian 12 (Bookworm)** dengan target kapasitas beban **175 siswa ujian CBT serentak**.

---

## 1. Spesifikasi Server & Persyaratan Sistem

- **Sistem Operasi**: Debian 12 x86_64 (Bookworm)
- **CPU / Core**: Minimal 4 vCPU (Disarankan 8 vCPU untuk beban puncak 175+ siswa)
- **RAM**: Minimal 8 GB (Disarankan 16 GB)
- **Penyimpanan**: Minimal 50 GB NVMe/SSD
- **Perangkat Lunak Utama**:
  - Web Server: Nginx 1.22+
  - PHP: PHP 8.5-FPM (atau PHP 8.3/8.4 via PPA Ondřej Surý)
  - Basis Data: MySQL 8.4 LTS / MariaDB 10.11+
  - In-Memory Cache: Redis 7+
  - Process Manager: Supervisor
  - Runtime Frontend: Node.js 22 LTS & npm

---

## 2. Instalasi Paket & Ekstensi PHP di Debian 12

Jalankan perintah berikut pada terminal server sebagai `root` atau pengguna `sudo`:

```bash
# 1. Update repositori sistem
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl wget gnupg2 ca-certificates lsb-release apt-transport-https ufw git unzip supervisor nginx redis-server

# 2. Tambahkan Repositori PHP Ondřej Surý
sudo curl -sSLo /usr/share/keyrings/deb.sury.org-php.gpg https://packages.sury.org/php/apt.gpg
sudo sh -c 'echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'
sudo apt update

# 3. Instal PHP 8.5 dan ekstensi wajib SIMSE
sudo apt install -y php8.5-fpm php8.5-cli php8.5-mysql php8.5-mbstring php8.5-xml \
    php8.5-bcmath php8.5-curl php8.5-zip php8.5-gd php8.5-intl php8.5-redis php8.5-opcache

# 4. Instal Composer 2
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# 5. Instal Node.js 22 LTS
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs
```

---

## 3. Konfigurasi PHP-FPM Pool & Tuning Beban 175 Siswa

Untuk menjamin 175 siswa yang melakukan *autosave* berkala dan ujian online CBT tidak mengalami *gateway timeout* (HTTP 504), buat berkas pool khusus di `/etc/php/8.5/fpm/pool.d/simse.conf`:

```ini
[simse]
user = www-data
group = www-data

listen = /run/php/php8.5-fpm-simse.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 60
pm.start_servers = 15
pm.min_spare_servers = 10
pm.max_spare_servers = 25
pm.max_requests = 1000

request_terminate_timeout = 60s
php_admin_value[memory_limit] = 256M
php_admin_value[upload_max_filesize] = 10M
php_admin_value[post_max_size] = 12M
php_admin_value[max_execution_time] = 60
```

Aktifkan konfigurasi OPcache di `/etc/php/8.5/fpm/conf.d/10-opcache.ini`:

```ini
zend_extension=opcache.so
opcache.enable=1
opcache.enable_cli=0
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.save_comments=1
```

Muat ulang PHP-FPM:
```bash
sudo systemctl restart php8.5-fpm
```

---

## 4. Konfigurasi Web Server Nginx (VirtualHost)

Salin konfigurasi berikut ke `/etc/nginx/sites-available/simse.conf`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name simse.sdmujahidin-wns.sch.id;

    # Pengalihan otomatis ke HTTPS
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name simse.sdmujahidin-wns.sch.id;

    root /var/www/simse/public;
    index index.php index.html;

    # SSL Certificate (Certbot Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/simse.sdmujahidin-wns.sch.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/simse.sdmujahidin-wns.sch.id/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Keamanan Header HTTP
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Kompresi Gzip
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml application/json application/javascript application/rss+xml image/svg+xml;

    client_max_body_size 12M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Static assets cache
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|webp|woff|woff2|ttf|svg)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }

    # Pemrosesan PHP
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.5-fpm-simse.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 60s;
    }

    # Blokir akses ke file sensitif dan hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ^~ /.env {
        deny all;
    }
}
```

Aktifkan konfigurasi dan uji Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/simse.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 5. Konfigurasi Background Worker (Supervisor)

Buat berkas `/etc/supervisor/conf.d/simse-worker.conf`:

```ini
[program:simse-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/simse/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=90
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/simse/storage/logs/worker.log
stopwaitsecs=3600
```

Aktifkan worker:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

---

## 6. Konfigurasi Cron Scheduler

Buka cron untuk pengguna `www-data`:
```bash
sudo crontab -u www-data -e
```

Tambahkan satu baris berikut:
```bash
* * * * * cd /var/www/simse && php artisan schedule:run >> /dev/null 2>&1
```

---

## 7. Skrip Deployment Zero-Downtime (`deploy.sh`)

Buat berkas `/var/www/simse/deploy.sh` (berikan izin `chmod +x deploy.sh`):

```bash
#!/bin/bash
set -e

echo "=== Memulai Deployment SIMSE Produksi ==="

cd /var/www/simse

# 1. Aktifkan mode maintenance singkat
php artisan down --render="errors::503" --secret="simse-bypass-token"

# 2. Ambil kode terbaru dari git
git fetch origin main
git reset --hard origin/main

# 3. Instal dependensi PHP produksi
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Compile frontend Vite produksi & PWA
npm ci
npm run build

# 5. Jalankan migrasi database
php artisan migrate --force

# 6. Optimasi Cache Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Restart Queue Worker & PHP-FPM OPcache
sudo supervisorctl restart all
sudo systemctl reload php8.5-fpm

# 8. Matikan mode maintenance
php artisan up

echo "=== Deployment SIMSE Berhasil Selesai ==="
```

---

## 8. Firewall & Hardening Keamanan Server Debian

```bash
# Konfigurasi UFW (Uncomplicated Firewall)
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https
sudo ufw enable
```

SIMSE kini siap melayani seluruh civitas akademika SD Muhammadiyah Al Mujahidin dengan keandalan tinggi, perlindungan berlapis, dan performa optimal.


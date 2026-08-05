# ZOU Attendance System — Linux Server Deployment Guide

Complete step-by-step guide to deploy the ZOU Attendance System on a company Linux server. Do not skip any step.

---

## Prerequisites

Before starting, ensure you have:

- Root or sudo access on the Linux server
- A domain or subdomain (e.g. `attendance.zou.ac.zw`) or the server IP
- SSH access to the server
- Basic familiarity with the terminal

---

## Part 1: Server Preparation

### Step 1.1 — Update the System

```bash
sudo apt update && sudo apt upgrade -y
```

### Step 1.2 — Set Hostname (Optional but Recommended)

```bash
sudo hostnamectl set-hostname attendance.zou.ac.zw
```

### Step 1.3 — Create a Deployment User (Recommended)

Do not run the app as `root`. Create a dedicated user:

```bash
sudo adduser zouattendance
sudo usermod -aG sudo zouattendance
```

Switch to that user for deployment:

```bash
su - zouattendance
```

---

## Part 2: Install PHP 8.2+

### Step 2.1 — Add PHP Repository (Ubuntu/Debian)

```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
```

### Step 2.2 — Install PHP and Required Extensions

```bash
sudo apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-common \
  php8.2-mysql php8.2-pgsql php8.2-sqlite3 php8.2-curl php8.2-xml \
  php8.2-mbstring php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl
```

### Step 2.3 — Verify PHP Installation

```bash
php -v
```

Expected: `PHP 8.2.x` or higher.

### Step 2.4 — Configure PHP for Production

Edit the main PHP config:

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

Adjust the following values.
**Tip:** In `nano`, press `Ctrl+W` to search for a setting (e.g., type `memory_limit` and hit Enter). Use the arrow keys to move and backspace to delete.

```ini
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 25M
max_execution_time = 60
date.timezone = Africa/Harare
```

**To Save and Exit:**
1. Press `Ctrl+X`
2. Press `Y` (to confirm saving)
3. Press `Enter` (to confirm filename)

For CLI (used by artisan):

```bash
sudo nano /etc/php/8.2/cli/php.ini
```

Set the same `date.timezone` if needed.

---

## Part 3: Install Composer and Node.js

### Step 3.1 — Download Composer

```bash
cd ~
curl -sS https://getcomposer.org/installer | php
```

### Step 3.2 — Move Composer to Global Path

```bash
sudo mv composer.phar /usr/local/bin/composer
```

### Step 3.3 — Verify Composer

```bash
composer --version
```

### Step 3.4 — Install Node.js and NPM (Required for Vite)

Laravel 12 uses Vite for asset bundling, which requires Node.js.

```bash
# Add NodeSource PPA for Node.js 20 (LTS)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

Verify the installation:

```bash
node -v
npm -v
```

---

## Part 4: Install Database (PostgreSQL)

### Step 4.1 — Install PostgreSQL

```bash
sudo apt install -y postgresql postgresql-contrib
```

### Step 4.2 — Start and Enable PostgreSQL

```bash
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

### Step 4.3 — Create Database and User

```bash
sudo -u postgres psql
```

In the PostgreSQL prompt:

```sql
CREATE USER zou_attendance_user WITH PASSWORD 'z0uadm1n';
CREATE DATABASE zou_attendance OWNER zou_attendance_user;
GRANT ALL PRIVILEGES ON DATABASE zou_attendance TO zou_attendance_user;
\q
```

Replace `YOUR_STRONG_PASSWORD_HERE` with a strong password and store it safely.

---

## Part 5: Install Python and Facial Recognition Dependencies

### Step 5.1 — Install Python 3 and pip

```bash
sudo apt install -y python3 python3-pip python3-venv
```

### Step 5.2 — Install System Libraries (Required for face_recognition)

```bash
sudo apt install -y build-essential cmake libopenblas-dev liblapack-dev libx11-dev
```

### Step 5.3 — Create a Virtual Environment for the App

```bash
cd /var/www  # or wherever you will deploy
sudo mkdir -p zou-attendance
sudo chown $USER:$USER zou-attendance
cd zou-attendance
```

We will create the venv after the app is deployed (see Part 8).

---

## Part 6: Install Nginx (Web Server)

### Step 6.1 — Install Nginx

```bash
sudo apt install -y nginx
```

### Step 6.2 — Start and Enable Nginx

```bash
sudo systemctl start nginx
sudo systemctl enable nginx
```

### Step 6.3 — Test Nginx

Visit `http://10.1.3.15` in a browser. You should see the default Nginx page.

---

## Part 7: Deploy the Application

### Step 7.1 — Choose Deployment Directory

Typical paths: `/var/www/zou-attendance` or `/home/zouattendance/zou-attendance`.

```bash
sudo mkdir -p /var/www/zou-attendance
sudo chown $USER:$USER /var/www/zou-attendance
cd /var/www/zou-attendance
```

### Step 7.2 — Transfer Project Files

**Option A: Git (Recommended)**

```bash
git clone https://github.com/YOUR_ORG/zou-attendance.git .
```

Or if you deploy from a private repo:

```bash
git clone git@github.com:YOUR_ORG/zou-attendance.git .
```

**Option B: SCP from Your Machine**

On your Windows machine (PowerShell or Command Prompt):

```powershell
scp -r C:\xampp\htdocs\zou-attendance\* user@10.1.3.15:/var/www/zou-attendance/
```

**Option C: Rsync**

```bash
rsync -avz --exclude 'node_modules' --exclude 'vendor' --exclude '.env' \
  /path/to/local/zou-attendance/ user@YOUR_SERVER_IP:/var/www/zou-attendance/
```

### Step 7.3 — Install PHP Dependencies

```bash
cd /var/www/zou-attendance
composer install --no-dev --optimize-autoloader
```

### Step 7.4 — Copy Environment File

```bash
cp .env.example .env
```

### Step 7.5 — Generate Application Key

```bash
php artisan key:generate
```

### Step 7.6 — Configure .env for Production

```bash
nano .env
```

Set at least these values:

```env
APP_NAME="ZOU Attendance"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://attendance.zou.ac.zw

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=zou_attendance
DB_USERNAME=zou_attendance_user
DB_PASSWORD=YOUR_STRONG_PASSWORD_HERE

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true

# Facial recognition server (used by Laravel)
RECOGNITION_SERVER_URL=http://127.0.0.1:5001

# Optional: ZOU redirect URL after clock in/out
ZOU_WEBSITE_URL=https://www.zou.ac.zw
ATTENDANCE_REDIRECT_DELAY=2
```

Save and exit (`Ctrl+X`, then `Y`, then `Enter`).

### Step 7.7 — Add Recognition Server URL to .env

The app reads `RECOGNITION_SERVER_URL` from `.env` (configured in `config/services.php`). Add to `.env`:

```env
RECOGNITION_SERVER_URL=http://127.0.0.1:5001
```

### Step 7.8 — Run Migrations

```bash
php artisan migrate --force
```

### Step 7.9 — Set Storage Permissions

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Step 7.10 — Create Storage Link (if using public disk)

```bash
php artisan storage:link
```

### Step 7.11 — Frontend Assets (Vite/Build)

If using Vite for CSS/JS:

```bash
npm ci
npm run build
```

If the project serves assets directly from `public/css` and `public/js` (no Vite), skip this step.

### Step 7.12 — Clear and Cache Config

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Part 8: Python Facial Recognition Server

### Step 8.1 — Create Python Virtual Environment

```bash
cd /var/www/zou-attendance
python3 -m venv venv
source venv/bin/activate
```

### Step 8.2 — Install Python Dependencies

```bash
pip install --upgrade pip
pip install face_recognition numpy opencv-python Pillow
```

This can take several minutes (face_recognition compiles C extensions).

### Step 8.3 — Test the Recognition Server

```bash
python scripts/recognition_server.py
```

You should see: `Starting Facial Recognition Server on localhost:5001`. Press `Ctrl+C` to stop.

### Step 8.4 — Create systemd Service for Recognition Server

```bash
sudo nano /etc/systemd/system/zou-recognition.service
```

Paste:

```ini
[Unit]
Description=ZOU Facial Recognition Server
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/zou-attendance
Environment="PATH=/var/www/zou-attendance/venv/bin"
ExecStart=/var/www/zou-attendance/venv/bin/python /var/www/zou-attendance/scripts/recognition_server.py
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

Save and exit.

### Step 8.5 — Fix Ownership for Python Scripts

```bash
sudo chown -R www-data:www-data /var/www/zou-attendance
```

### Step 8.6 — Start and Enable Recognition Service

```bash
sudo systemctl daemon-reload
sudo systemctl start zou-recognition
sudo systemctl enable zou-recognition
sudo systemctl status zou-recognition
```

Verify it shows `active (running)`.

---

## Part 9: Configure Nginx

### Step 9.1 — Create Nginx Site Config

```bash
sudo nano /etc/nginx/sites-available/zou-attendance
```

Paste (replace `attendance.zou.ac.zw` and paths as needed):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name attendance.zou.ac.zw YOUR_SERVER_IP;
    root /var/www/zou-attendance/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 60;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Save and exit.

### Step 9.2 — Enable the Site

```bash
sudo ln -s /etc/nginx/sites-available/zou-attendance /etc/nginx/sites-enabled/
```

### Step 9.3 — Remove Default Site (Optional)

```bash
sudo rm /etc/nginx/sites-enabled/default
```

### Step 9.4 — Test and Reload Nginx

```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

## Part 10: SSL (HTTPS) with Let's Encrypt

HTTPS is required for camera access in the browser.

### Step 10.1 — Install Certbot

```bash
sudo apt install -y certbot python3-certbot-nginx
```

### Step 10.2 — Obtain Certificate

Ensure your domain `attendance.zou.ac.zw` points to this server's IP, then:

```bash
sudo certbot --nginx -d attendance.zou.ac.zw
```

Follow prompts. Provide email and agree to terms.

### Step 10.3 — Verify Auto-Renewal

```bash
sudo certbot renew --dry-run
```

### Step 10.4 — Update .env for HTTPS

```env
APP_URL=https://attendance.zou.ac.zw
SESSION_SECURE_COOKIE=true
```

Then:

```bash
php artisan config:cache
```

---

## Part 11: Queue Worker (Required for Email)

Because the system uses background queues to send emails instantly without freezing the web interface, the Queue Worker is required:

```bash
sudo nano /etc/systemd/system/zou-queue.service
```

```ini
[Unit]
Description=ZOU Attendance Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/zou-attendance
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3
Restart=always

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl start zou-queue
sudo systemctl enable zou-queue
```

---

## Part 12: Cron for Laravel Scheduler

```bash
crontab -e
```

Add:

```
* * * * * cd /var/www/zou-attendance && php artisan schedule:run >> /dev/null 2>&1
```

---

## Part 13: Firewall

### Step 13.1 — Allow Nginx and SSH

```bash
sudo ufw allow 'Nginx Full'
sudo ufw allow OpenSSH
sudo ufw enable
sudo ufw status
```

Port 5001 (recognition server) should stay local; do not expose it publicly.

---

## Part 14: Final Checks

### Step 14.1 — Verify PHP-FPM

```bash
sudo systemctl status php8.2-fpm
```

### Step 14.2 — Verify All Services

```bash
sudo systemctl status nginx
sudo systemctl status postgresql
sudo systemctl status zou-recognition
```

### Step 14.3 — Test the Application

1. Visit `https://attendance.zou.ac.zw`
2. Login with admin: `admin@zou.ac.zw` / `admin123`
3. Test registration and biometric enrollment
4. Test clock in/out (requires HTTPS for camera)

### Step 14.4 — Check Logs on Errors

```bash
tail -f /var/www/zou-attendance/storage/logs/laravel.log
```

---

## Part 15: Deployment Checklist

| Step | Action | Status |
|------|--------|--------|
| 1 | System updated | ☐ |
| 2 | PHP 8.2+ with extensions installed | ☐ |
| 3 | Composer and Node.js installed | ☐ |
| 4 | PostgreSQL installed, DB created | ☐ |
| 5 | Python 3 + face_recognition installed | ☐ |
| 6 | Nginx installed | ☐ |
| 7 | App deployed, .env configured | ☐ |
| 8 | Migrations run | ☐ |
| 9 | Storage permissions set | ☐ |
| 10 | Recognition server systemd service running | ☐ |
| 11 | Nginx configured | ☐ |
| 12 | SSL (HTTPS) enabled | ☐ |
| 13 | Firewall configured | ☐ |
| 14 | Application tested | ☐ |

---

## Troubleshooting

### 502 Bad Gateway
- Check PHP-FPM: `sudo systemctl status php8.2-fpm`
- Verify socket path in Nginx matches: `ls /var/run/php/php8.2-fpm.sock`

### Face Recognition Not Working
- Ensure `zou-recognition` service is running: `sudo systemctl status zou-recognition`
- Check logs: `sudo journalctl -u zou-recognition -f`
- Verify `.env` has `RECOGNITION_SERVER_URL=http://127.0.0.1:5001`
- Ensure `config/services.php` has the `recognition` entry

### Permission Denied on storage/logs
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Camera Not Working in Browser
- Must use HTTPS; browsers block camera on HTTP
- Use valid domain (or `localhost` for local testing)

---

## Security Reminders

1. Change the hardcoded admin password (`admin@zou.ac.zw` / `admin123`) after first login.
2. Use strong database passwords.
3. Keep `APP_DEBUG=false` in production.
4. Do not commit `.env` to version control.
5. Keep the system and packages updated: `sudo apt update && sudo apt upgrade`

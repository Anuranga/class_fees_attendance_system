# Deployment Guide: Alwaysdata.com

This guide provides step-by-step instructions for deploying the **EduTrack Attendance & Fee System** to [alwaysdata.com](https://www.alwaysdata.com).

A pre-packaged, production-ready archive has already been generated in your project root:
- **`alwaysdata_deploy.zip`** (contains all application code, pre-compiled Vite production assets, and migrations; excludes local dev files).

---

## 1. Create MySQL Database on Alwaysdata

1. Log in to your [Alwaysdata Administration Panel](https://admin.alwaysdata.com/).
2. In the left sidebar, navigate to **Databases** > **MySQL**.
3. Under **Databases**, click **Add a database**:
   - **Name**: e.g., `attendance` (the full database name will be `youraccount_attendance`).
4. Under **Users**, check or create a user with permissions to access the database and note down the password.
5. Take note of your **Host** shown at the top of the MySQL page:
   - Typically `mysql-youraccount.alwaysdata.net`.

---

## 2. Upload the Application Code

### Option A: Using Alwaysdata Web File Manager (Easiest)

1. In the Alwaysdata panel, go to **Files**.
2. Navigate to your home directory (`/home/youraccount/`).
3. Click **Upload** and upload the `alwaysdata_deploy.zip` file from your computer.
4. Once uploaded, select `alwaysdata_deploy.zip` and click **Extract**.
5. It will extract your files into a directory (e.g., `/home/youraccount/class_fees_attendance_system` or `/home/youraccount/`).

### Option B: Using SSH / SCP / Git

1. Enable SSH under **Remote access** > **SSH** in the Alwaysdata panel.
2. In your local terminal, transfer the zip file:
   ```bash
   scp alwaysdata_deploy.zip youraccount@ssh-youraccount.alwaysdata.net:/home/youraccount/
   ```
3. Connect via SSH:
   ```bash
   ssh youraccount@ssh-youraccount.alwaysdata.net
   unzip alwaysdata_deploy.zip -d class_fees_attendance_system
   cd class_fees_attendance_system
   ```

---

## 3. Configure the Web Site on Alwaysdata

1. In the Alwaysdata panel, go to **Web** > **Sites**.
2. Click **Edit** on your default site (e.g., `youraccount.alwaysdata.net`) or click **Add a site**:
   - **Addresses**: `youraccount.alwaysdata.net` (and your custom domain if any).
   - **Type**: Select **PHP**.
   - **PHP version**: Select **8.3** (or **8.4**).
   - **Application path / Root directory**: Set to the Laravel `public` folder:
     ```
     /home/youraccount/class_fees_attendance_system/public
     ```
     > **IMPORTANT**: Always point the path to `/public`, not the project root directory.
   - **Enable SSL**: Checked (Alwaysdata issues free Let's Encrypt certificates automatically).
   - **Force HTTPS**: Checked.
3. Click **Submit** to save.

---

## 4. Install Dependencies & Initialize Database

Open the Alwaysdata **Web SSH Console** (found under **Remote access** > **SSH** or connect via your SSH client):

```bash
cd /home/youraccount/class_fees_attendance_system

# 1. Install production dependencies with Composer
composer install --no-dev --optimize-autoloader

# 2. Set up the production environment file
cp .env.alwaysdata.example .env

# 3. Edit .env with your Alwaysdata credentials
nano .env
```

Inside `.env`, configure:
```dotenv
APP_NAME="EduTrack - Attendance & Fees"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://youraccount.alwaysdata.net

DB_CONNECTION=mysql
DB_HOST=mysql-youraccount.alwaysdata.net
DB_PORT=3306
DB_DATABASE=youraccount_attendance
DB_USERNAME=youraccount
DB_PASSWORD=your_actual_mysql_password
```
*(Press `Ctrl + O` then `Enter` to save, and `Ctrl + X` to exit `nano`)*

---

## 5. Generate Key, Run Migrations & Optimize

Run these commands in the terminal:

```bash
# 1. Generate application encryption key
php artisan key:generate

# 2. Run database migrations and seed default data
php artisan migrate --force --seed

# 3. Create storage symlink
php artisan storage:link

# 4. Set directory permissions for cache & uploads
chmod -R 775 storage bootstrap/cache

# 5. Cache configurations and routes for peak performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 6. Verification

Visit `https://youraccount.alwaysdata.net`:
1. Log in with the default seeded credentials:
   - **Admin**: `admin@edutrack.test` / `password`
   - **Teacher**: `teacher@edutrack.test` / `password`
2. Test camera QR attendance scanning, public registration links, and cash receipts.

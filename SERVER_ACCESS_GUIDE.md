# ChronoSync Server Management Guide

This guide contains the essential commands and instructions for accessing and maintaining your Oracle Cloud server.

## 1. Accessing the Server (SSH)

To access your server's terminal, open **PowerShell** on your Windows laptop and run the following command. It uses your private SSH key to securely log in as the `opc` user.

```powershell
ssh -i "C:\Users\M.T\Desktop\The Docs\ssh-key-2026-08-24 (1).key" opc@92.4.155.24
```

## 2. Navigating to the Codebase

Once you are logged into the server, all of the application code and Docker configurations are located in the `chronosync-attendance` folder. Always navigate here before running any Docker commands.

```bash
cd ~/chronosync-attendance
```

## 3. Managing the Server (Docker Compose)

Your entire system (Laravel app, Database, ML server, Caddy) runs inside Docker containers. You must use `sudo` for all Docker commands.

**Restarting the entire system:**
```bash
sudo docker compose down
sudo docker compose up -d
```

**Rebuilding the system after pushing new code to GitHub:**
```bash
sudo docker compose down
sudo docker compose up -d --build
```

**Viewing Live Logs:**
If something is broken or throwing a 500 error, use this command to watch the live terminal output of all your containers:
```bash
sudo docker compose logs -f
```
*(Press `Ctrl+C` to exit the logs)*

## 4. Deploying Updates from GitHub

When you make changes on your laptop and push them to GitHub, you need to pull those changes onto the server.

1. SSH into the server and navigate to the folder:
   ```bash
   cd ~/chronosync-attendance
   ```
2. Pull the latest code:
   ```bash
   git pull origin main
   ```
   > **Note:** Because your GitHub repository is private, this command will ask for your GitHub Username and Password. For the "Password", you must paste your **Personal Access Token (PAT)**, not your actual GitHub password.
3. Rebuild the Docker containers so they use the new code:
   ```bash
   sudo docker compose down
   sudo docker compose up -d --build
   ```

## 5. Running Laravel Commands

If you need to clear caches, run migrations, or seed the database, you have to execute the command *inside* the running Laravel container (`chronosync-attendance-app-1`).

**Clear Application Caches:**
```bash
sudo docker exec chronosync-attendance-app-1 php artisan optimize:clear
```

**Run Database Migrations:**
```bash
sudo docker exec chronosync-attendance-app-1 php artisan migrate --force
```

**Seed the Database:**
```bash
sudo docker exec chronosync-attendance-app-1 php artisan db:seed --force
```

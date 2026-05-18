# Deployment Guide

## 1. SSL Configuration (HTTPS)
For the camera to work on other devices, the site must be served over HTTPS.

### Using XAMPP (Local/Intranet)
1.  Open XAMPP Control Panel.
2.  Stop Apache.
3.  Open `C:\xampp\apache\conf\extra\httpd-ssl.conf`.
4.  Find `<VirtualHost _default_:443>`.
5.  Ensure `DocumentRoot` points to your project's public folder:
    ```apache
    DocumentRoot "C:/xampp/htdocs/zou-attendance/public"
    ServerName zse-attendance.local:443
    ```
6.  Generate a self-signed certificate (or use the default `server.crt` and `server.key` provided by XAMPP).
7.  Start Apache.
8.  On client devices, navigate to `https://[YOUR_SERVER_IP]`. Accept the security warning.

## 2. Python Facial Recognition Server
For fast performance, run the persistent Python server.

### Setup
1.  Ensure Python, `face_recognition`, `numpy`, `opencv-python`, and `pillow` are installed on the server.
2.  Navigate to the project directory.

### Running the Server
Run this command in a terminal window (keep it open):
```powershell
python scripts/recognition_server.py
```
You will see: `Starting Facial Recognition Server on localhost:5000`.

### Troubleshooting
-   **Server not found**: Check if firewall is blocking port 5000.
-   **Slow performance**: Verify the PHP application is connecting to `localhost:5000` and not falling back to CLI mode (check logs).

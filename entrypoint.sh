#!/bin/bash
# Run Laravel migrations securely on startup
php artisan migrate --force

# Start Apache
exec "$@"

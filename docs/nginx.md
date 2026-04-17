# Nginx Configuration Guide

The Emani Laundry POS uses Nginx as a reverse proxy to serve the Laravel application via PHP 8.4-FPM.

## Configuration Structure

Configurations are stored in `/etc/nginx/sites-available/` and linked to `/etc/nginx/sites-enabled/`.

- **Production**: `pos_prod.conf`
- **Staging**: `pos_staging.conf`

## Standard Laravel Settings

Each configuration includes:

- **Root**: Pointing to the `public` directory of the Laravel app.
- **Index**: `index.php index.html index.htm`.
- **PHP Handling**: Using `fastcgi_pass unix:/var/run/php/php8.4-fpm.sock`.
- **Security**: SSL via Certbot, Gzip compression, and security headers.

## SSL Setup (Certbot)

To issue a new certificate:

```bash
sudo certbot --nginx -d pos.emanilaundry.com -d www.pos.emanilaundry.com
```

## Common Commands

- **Test Config**: `sudo nginx -t`
- **Reload**: `sudo systemctl reload nginx`
- **Restart**: `sudo systemctl restart nginx`

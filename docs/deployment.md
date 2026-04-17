# Deployment Guide

## Server Prerequisites

To allow the deployment user to manage queues without `sudo`, configure Supervisor to grant access to a specific group (e.g., `supervisor`):

1. **Create the group and add users**:

    ```bash
    sudo groupadd supervisor
    sudo usermod -aG supervisor deploy
    ```

2. **Configure Supervisor Socket**:
   Edit `/etc/supervisor/supervisord.conf` and update the `[unix_http_server]` section:

    ```ini
    [unix_http_server]
    file=/var/run/supervisor.sock   ; (the path to the socket file)
    chmod=0770                       ; sock mode (default 0700)
    chown=root:supervisor           ; socket file uid:gid owner
    ```

3. **Reload Supervisor**:

    ```bash
    sudo systemctl restart supervisor
    ```

4. **Add NVM to deployment user's profile**:

    ```bash
    echo 'export NVM_DIR="$HOME/.nvm"' >> ~/.bashrc
    echo '[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"' >> ~/.bashrc
    echo '[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"' >> ~/.bashrc
    source ~/.bashrc
    ```

5. **Install Node.js**:

    ```bash
    curl -fsSL https://deb.nodesource.com/setup_24.x | sudo -E bash -
    sudo apt-get install -y nodejs
    ```

6. **Install pnpm**:

    ```bash
    sudo npm install -g pnpm
    ```

7. **Install Composer**:

    ```bash
    sudo apt-get install -y composer
    ```

8. **Install Supervisor**:

    ```bash
    sudo apt-get install -y supervisor
    ```

9. **Install Redis**:

    ```bash
    sudo apt-get install -y redis-server
    ```

10. **Install Nginx**:

    ```bash
    sudo apt-get install -y nginx
    ```

11. **Install PHP 8.4**:

    ```bash
    sudo apt-get install -y php8.4 php8.4-fpm php8.4-mysql php8.4-curl php8.4-gd php8.4-mbstring php8.4-xml php8.4-zip
    ```

12. **Install PHP extensions**:

    ```bash
    sudo apt-get install -y php8.4-mysql php8.4-curl php8.4-gd php8.4-mbstring php8.4-xml php8.4-zip
    ```

13. **Install Certbot**:
    ```bash
    sudo apt-get install -y certbot python3-certbot-nginx
    ```
    eg. `sudo certbot --nginx -d pos.emanilaundry.com -d www.pos.emanilaundry.com`

## Production Deployment

1 Pull latest code

cd /var/www/production/pos
git pull origin main

2 Install dependencies

composer install --no-dev
pnpm install

3 Build frontend

pnpm build

4 Run migrations

php artisan migrate --force

5 Cache config

php artisan config:cache
php artisan route:cache

6 Restart queues

sudo supervisorctl restart pos_prod_queue:\*

sudo supervisorctl restart pos_staging_queue:\*

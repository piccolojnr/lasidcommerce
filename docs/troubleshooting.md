# Troubleshooting Guide

Common issues and solutions for the Emani Laundry POS infrastructure.

## Log Locations

| Service         | Log Path                             |
| :-------------- | :----------------------------------- |
| **Laravel**     | `storage/logs/laravel.log`           |
| **Nginx Error** | `/var/log/nginx/error.log`           |
| **PHP-FPM**     | `/var/log/php8.4-fpm.log`            |
| **Supervisor**  | `/var/log/supervisor/supervisor.log` |

## Common Issues

### 502 Bad Gateway

- **Cause**: PHP-FPM is down or the socket path is incorrect.
- **Fix**: `sudo systemctl restart php8.4-fpm`

### 403 Forbidden

- **Cause**: Incorrect file permissions or missing `index.php`.
- **Fix**: Ensure `www-data` owns the directory: `sudo chown -R www-data:www-data /var/www/production/pos`

### Queue Workers Not Starting

- **Cause**: Supervisor config mismatch or permissions.
- **Fix**:
    ```bash
    sudo supervisorctl reread
    sudo supervisorctl update
    sudo supervisorctl restart all
    ```

### Memory Exhaustion (PHP)

- **Fix**: Increase `memory_limit` in `/etc/php/8.4/fpm/php.ini` and restart PHP-FPM.

### pnpm/composer: command not found

- **Issue**: Deployment scripts fail with "command not found" even if the tools are installed.
- **Cause**: Non-interactive shells (like those used by GitLab CI or SSH commands) often have a limited `PATH`.
- **Fix**:
    1. Find where the tool is: `which pnpm` or `which composer`.
    2. Ensure the path is included in the script (I've added a dynamic `PATH` export to the scripts, but you might need to specify the absolute path if it's in a non-standard location).
    3. Example for `.bashrc`: Ensure your tool paths are exported _before_ the "If not running interactively, don't do anything" check in `~/.bashrc`.

### Sudo Password Prompt in CI/CD

- **Issue**: The GitLab CI pipeline fails at `supervisorctl` with permission errors or password prompts.
- **Fix**: Grant the deployment user access to the Supervisor socket via group permissions (recommended) instead of full sudo.
- **Solution**: Follow the "Server Prerequisites" guide in [deployment.md](file:///c:/Users/USER/projects/emani/emani-laundry-pos/docs/deployment.md) to set up the `supervisor` group and update `supervisord.conf`.

## Debugging Commands

- **Check PHP Status**: `sudo systemctl status php8.4-fpm`
- **View Laravel Logs**: `tail -f storage/logs/laravel.log`
- **Test Database Connection**: `php artisan db:show`

Production Checklist — AtYarislari

1) Prepare server
- Ubuntu 22.04+ recommended
- Install PHP 8.1+, Composer, Nginx (or Apache), MySQL/MariaDB, Redis

2) Clone repository
- Place code in `/var/www/at-yarislari-tahmin`

3) Environment variables
- Copy `.env.production.example` to `.env` and fill values
- Generate `APP_KEY`:
  - `php artisan key:generate --force`

4) Composer + migrations
- `composer install --no-dev --optimize-autoloader`
- `php artisan migrate --force`

5) Set permissions
- `chown -R www-data:www-data storage bootstrap/cache`
- `chmod -R ug+rwx storage bootstrap/cache`

6) Configure queue/cache
- Use Redis for `QUEUE_CONNECTION` and `CACHE_DRIVER` in `.env`
- If using Horizon: install `laravel/horizon` and configure `config/horizon.php`.

7) Workers
- Use Supervisor or systemd to keep workers running.
- Example Supervisor config: `deploy/supervisor/laravel-worker.conf` (update paths/user)
- Example systemd unit: `deploy/systemd/laravel-worker.service` and `deploy/systemd/horizon.service`

8) Mail
- Fill SMTP details in `.env` (`MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`)

9) Cache/Optimize
- `php artisan config:cache`
- `php artisan route:cache`
- `php artisan view:cache`

10) SSL
- Install cert (Let's Encrypt) and configure Nginx/Apache to redirect HTTP to HTTPS

11) Monitoring & backups
- Set up log rotation, monitoring (Sentry/Prometheus), and DB backups
- Monitor `failed_jobs` table and set up alerts

12) Final smoke test
- Register a new user -> confirm job in `jobs` table -> verify queue worker processed it -> confirm email sent
- Test payment flow (sandbox), verify `payment_logs` entries in DB

If you want, I can:
- Create Supervisor system commands for installing the unit, enabling and starting it
- Create a `docker-compose` example that includes PHP-FPM + Nginx + Redis for local prod-like testing
- Add Horizon config and systemd unit customization


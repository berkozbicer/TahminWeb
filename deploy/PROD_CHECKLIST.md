Production Deployment Checklist

1) Environment
- Copy `.env` to the server and update the following values:
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `APP_URL=https://hipodromcasusu.com`
  - `APP_KEY` (keep existing)
  - PayTR settings: `PAYTR_MERCHANT_ID`, `PAYTR_MERCHANT_KEY`, `PAYTR_MERCHANT_SALT`, `PAYTR_TEST_MODE`
  - Mail settings: `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`
  - Database credentials
- Ensure `storage/` and `bootstrap/cache` are writable by the webserver user.

2) Database
- Run migrations: `php artisan migrate --force`
- If needed, seed minimal data: `php artisan db:seed --class=SomeSeeder --force`

3) Caching & Optimizations
- Run on server:
  ```bash
  composer install --no-dev --optimize-autoloader
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan migrate --force
  ```

4) Queue workers
- Use Supervisor or systemd to run queue workers (examples in `deploy/`):
  - Supervisor example: `deploy/supervisor/laravel-worker.conf`
  - Restart worker after deploy: `php artisan queue:restart`

5) PayTR
- Set `PAYTR_*` keys in `.env`.
- Set `PAYTR_TEST_MODE=0` for live.
- Ensure public callback URL `https://your-domain.example/paytr/callback` is reachable by PayTR.
- Configure PayTR panel if needed.

6) SSL
- Ensure site runs behind valid TLS. Use Let's Encrypt or other provider.

7) Logs & Monitoring
- Monitor `storage/logs/laravel.log`.
- Setup server monitoring and Sentry/Log shipper if desired.

8) Final smoke tests
- Test registration/email verification (ensure queue is working).
- Test payment flow in sandbox then in live mode (after setting keys).

Notes
- Do NOT store raw card data. Only store provider response IDs and metadata.
- Consider switching `QUEUE_CONNECTION` to `redis` and using Horizon for scale when user load grows.

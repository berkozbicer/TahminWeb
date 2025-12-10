Queue setup and recommendations

Local / development
- Ensure `.env` contains:

```
QUEUE_CONNECTION=database
MAIL_MAILER=smtp # or log for development
APP_LOCALE=tr
```

- Run migrations (creates `jobs` and `failed_jobs` tables if migrations present):

```powershell
php artisan migrate
```

- Start a queue worker (development):

```powershell
php artisan queue:work
```

Production checklist
- Use Redis for high throughput: set `QUEUE_CONNECTION=redis` and configure `config/database.php` / `config/queue.php`.
- Use Supervisor or systemd to keep workers running (sample config in `deploy/supervisor/laravel-worker.conf`). Update `command` path to your project root and `user` to your process user.
- Use Laravel Horizon if you use Redis for advanced metrics and scaling.
- Configure `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` with your SMTP provider.
- Monitor `failed_jobs` and exceptions. Use `php artisan queue:retry <id>` to retry failed jobs.

Mail sending strategy
- All verification emails are dispatched as queued jobs (`App\Jobs\SendVerificationEmail`).
- Job has retries/backoff configured; adjust `$tries` and `$backoff` in the job class if necessary.

Scaling tips
- Increase `numprocs` (Supervisor) and/or run multiple worker processes per machine depending on CPU.
- For many concurrent users, use Redis + Horizon and horizontal scaling behind a load balancer.
- Cache frequently-read values (subscription plans, settings) in Redis to reduce DB load.

Security
- Do not store full card details or highly sensitive payment payloads in DB. Store provider transaction IDs and minimal metadata only.

If you want, I can:
- Add a `Supervisor` install/start script and a `systemd` service example.
- Add a `docker-compose` example with Redis + worker for local testing.

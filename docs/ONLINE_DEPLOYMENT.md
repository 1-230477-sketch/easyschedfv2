# PostgreSQL Deployment

PostgreSQL is required for all application data. Startup fails clearly if `EASYSCHED_DATABASE_URL` is missing or invalid.

## Setup

1. Create a dedicated PostgreSQL database and store its connection URL in a server-side environment variable or an untracked `.env` file.
2. Enable PHP `pdo_pgsql` on the hosting server. Docker builds install the PostgreSQL PDO driver.
3. Set `EASYSCHED_DATABASE_URL` to a PostgreSQL URL, for example `postgresql://user:password@host:5432/database?sslmode=require`.
4. Open the application and sign in. The app applies its PostgreSQL schema and startup normalizations. Change demonstration passwords before using real records.

Keep the connection URL out of source control and logs. For Supabase or Neon, use the provider's recommended pooled URL and `sslmode=require`.

## Production checklist

- HTTPS enabled
- `pdo_pgsql` enabled
- Database URL stored only in hosting secrets
- PostgreSQL backups and a restore test configured
- Seed accounts rotated or disabled
- PHP error display disabled; logs retained privately
- One online generation worker or queue if multiple schedulers may generate concurrently

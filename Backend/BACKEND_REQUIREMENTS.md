# SkillMatch PHP/Laravel Backend Requirements

This document covers the PHP/Laravel backend only. It is based on the checked-in
Composer files, Laravel configuration, .env.example, Dockerfile, CI workflow,
and the verified local runtime.

## Verified local runtime

These are installed or runtime values verified locally; they are not inferred
from composer.lock.

| Component            | Verified value                  |
| -------------------- | ------------------------------- |
| PHP                  | 8.5.0                           |
| Laravel framework    | 13.31.0 (php artisan --version) |
| Composer             | 2.8.12                          |
| Database             | MySQL                           |
| Cache store          | database                        |
| Queue connection     | database                        |
| Session driver       | database                        |
| Mailer               | SMTP                            |
| Application timezone | UTC                             |

The database, cache, queue, session, and mail values above were verified with
Laravel's resolved configuration. config/app.php sets the timezone to UTC.

## Version constraints and lock state

The following columns deliberately distinguish the Composer constraint, the
version recorded in composer.lock, and the package currently installed in
vendor/.

### Runtime dependencies

| Package                   | composer.json constraint | composer.lock | Installed vendor version |
| ------------------------- | ------------------------ | ------------- | ------------------------ |
| dedoc/scramble            | ^0.13.45                 | v0.13.45      | Not installed            |
| inertiajs/inertia-laravel | ^3.0                     | v3.3.4        | v3.3.4                   |
| laravel/chisel            | ^0.1.0                   | v0.1.1        | v0.1.1                   |
| laravel/fortify           | ^1.37.2                  | v1.40.0       | v1.39.0                  |
| laravel/framework         | ^13.17                   | v13.33.0      | v13.31.0                 |
| laravel/sanctum           | ^4.0                     | v4.3.3        | v4.3.3                   |
| laravel/tinker            | ^3.0                     | v3.0.2        | v3.0.2                   |
| laravel/wayfinder         | ^0.1.14                  | v0.1.21       | v0.1.21                  |
| nesbot/carbon             | 3.14.0                   | 3.14.0        | 3.13.2                   |
| tymon/jwt-auth            | ^2.3                     | 2.3.0         | 2.3.0                    |

### Development dependencies

| Package                     | composer.json constraint | composer.lock | Installed vendor version |
| --------------------------- | ------------------------ | ------------- | ------------------------ |
| fakerphp/faker              | ^1.24                    | v1.24.1       | v1.24.1                  |
| larastan/larastan           | ^3.9                     | v3.12.2       | v3.12.0                  |
| laravel/boost               | ^2.2                     | v2.10.0       | v2.8.1                   |
| laravel/pail                | ^1.2.5                   | v1.2.7        | v1.2.7                   |
| laravel/pao                 | ^1.0.6                   | v1.1.5        | v1.1.5                   |
| laravel/pint                | ^1.27                    | v1.32.1       | v1.32.1                  |
| laravel/sail                | ^1.53                    | v1.68.0       | v1.67.0                  |
| mockery/mockery             | ^1.6                     | 1.6.15        | 1.6.15                   |
| nunomaduro/collision        | ^8.9.3                   | v8.9.5        | v8.9.5                   |
| pestphp/pest                | ^5.1                     | v5.2.1        | v5.1.4                   |
| pestphp/pest-plugin-laravel | ^5.0                     | v5.0.1        | v5.0.1                   |

PHP is constrained by composer.json to ^8.4 (>=8.4.0 <9.0.0). Its Composer
platform target is 8.4.25; that target controls dependency resolution and is
not the installed PHP runtime. PHP 8.5.0 satisfies the constraint.

The current vendor/ directory is not synchronized with composer.lock: Laravel,
Carbon, Fortify, several development tools, and dedoc/scramble differ. The
locked versions should be used for a reproducible installation; this
documentation task does not reconcile the existing installation.

## PHP extensions

The current PHP 8.5 installation satisfies the Composer platform checks for:

ctype, date, dom, fileinfo, filter, hash, iconv, json, libxml, mbstring,
openssl, pcre, phar, reflection, session, simplexml, sodium, tokenizer, xml,
and xmlwriter.

Native extensions required by the configured database and deployment paths:

- pdo_mysql is required for the verified local MySQL database.
- pgsql and pdo_pgsql are required by the PostgreSQL 17 CI workflow.
- The checked-in Dockerfile also enables intl, zip, bcmath, and pcntl.

ctype and mbstring passed the Composer check in the current installation.
Composer can satisfy them through Symfony polyfills, but native extensions are
preferred in deployed PHP images.

## Database and backend services

config/database.php defines SQLite, MySQL, MariaDB, PostgreSQL, and SQL Server
connections. The actual environments in scope are:

- Local development: MySQL with pdo_mysql.
- CI testing: PostgreSQL 17 with pgsql and pdo_pgsql, isolated by the GitHub
  Actions service container.

The verified local Laravel drivers are database-backed cache, queue, and
session. Their tables are created by migrations and must exist before the
application uses those services.

The checked-in .env.example currently describes PostgreSQL, Redis-backed
cache/queue/session, and log mail. Those are template values and do not
describe the verified local runtime above.

## Backend environment variables

Only variable names are listed. Supply credentials, keys, passwords, and tokens
through the deployment environment; never commit their values.

### Required application and local database settings

- APP_KEY
- APP_ENV
- APP_DEBUG
- APP_URL
- DB_CONNECTION
- DB_HOST
- DB_PORT
- DB_DATABASE
- DB_USERNAME
- DB_PASSWORD
- DB_URL when using a database URL instead of individual connection fields
- CACHE_STORE
- QUEUE_CONNECTION
- SESSION_DRIVER
- MAIL_MAILER
- MAIL_HOST
- MAIL_PORT
- MAIL_USERNAME
- MAIL_PASSWORD
- MAIL_FROM_ADDRESS
- MAIL_FROM_NAME
- JWT_SECRET for the default HS256 API authentication configuration

For asymmetric JWT algorithms, use the corresponding JWT_ALGO, JWT_PUBLIC_KEY,
JWT_PRIVATE_KEY, and JWT_PASSPHRASE settings.

### Other project settings

- Application and logging: APP_NAME, APP_LOCALE, APP_FAKER_LOCALE,
  BCRYPT_ROUNDS, LOG_CHANNEL, LOG_STACK, and LOG_LEVEL.
- Filesystem and broadcast: FILESYSTEM_DISK and BROADCAST_CONNECTION.
- Database-backed services: DB_CACHE_CONNECTION, DB_CACHE_TABLE,
  DB_CACHE_LOCK_CONNECTION, DB_CACHE_LOCK_TABLE, DB_QUEUE_CONNECTION,
  DB_QUEUE_TABLE, DB_QUEUE, DB_QUEUE_RETRY_AFTER, SESSION_CONNECTION, and
  SESSION_TABLE.
- AI client configuration: AI_SERVICE_BASE_URL, AI_SERVICE_API_KEY,
  AI_SERVICE_PROVIDER, AI_SERVICE_TIMEOUT, and AI_SERVICE_CONNECT_TIMEOUT.
- Private QA OTP only: STATIC_OTP_ENABLED and STATIC_OTP_CODE. Static OTP must
  remain disabled when APP_ENV=production.

APP_FALLBACK_LOCALE appears in .env.example but config/app.php currently fixes
the fallback locale to en instead of reading that variable.

## Installation and setup

From the repository root:

    composer install --no-interaction --prefer-dist
    cp .env.example .env
    php artisan key:generate
    php artisan jwt:secret

Configure the local MySQL connection, database-backed services, SMTP
connection, and other required variables before migrating:

    php artisan migrate
    php artisan db:seed
    php artisan serve

Seeding is optional for development. Do not run destructive reset commands
against shared or production databases.

## Migrations and queues

Apply schema changes with:

    php artisan migrate

The migrations cover Laravel infrastructure tables, users and authentication,
candidate data, jobs and applications, CV data, AI review records, roadmaps,
audit records, and job ingestion data. They also provide the database tables
used by the configured cache, queue, and session drivers.

For the database queue connection, run a worker in each environment that
processes jobs:

    php artisan queue:work

For production migrations, use:

    php artisan migrate --force

## Backend tests and code quality

Install development dependencies before running the full quality suite:

    php artisan test --compact
    vendor/bin/pest
    composer lint:check
    composer types:check
    php -d memory_limit=1G vendor/bin/phpstan analyse
    composer validate
    git diff --check

The GitHub Actions backend test job uses PHP 8.5 and PostgreSQL 17, then runs
the repository's Composer setup and CI checks against the temporary CI
database. Local tests may use the database configured by the test environment;
the CI database must never contain production credentials or data.

## Production prerequisites

- PHP 8.5 is the verified local/container runtime and satisfies the
  application's ^8.4 constraint.
- Install from composer.lock with development packages omitted:

      composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

- Use a web server whose document root is Laravel's public directory.
- Configure APP_ENV=production, APP_DEBUG=false, a persistent APP_KEY, JWT
  signing material, MySQL credentials, database-backed service settings, and
  SMTP credentials.
- Ensure the PHP image has the required extensions, including pdo_mysql.
- Make storage and bootstrap/cache writable by the web process.
- Run php artisan migrate --force after the database is reachable.
- Run php artisan config:cache after environment configuration is complete.
- Run a persistent php artisan queue:work process for database-backed jobs.
- Keep all secrets outside the repository and never enable static OTP in
  production.

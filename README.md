# MC Monitor — Minecraft Server Monitoring v2.1

Real-time, extremely fast monitoring platform for Minecraft servers featuring asynchronous pinging, composite rankings, gamification, and adaptive security.

## 🔥 Key Features (v2.1)

- **Asynchronous Daemon (ReactPHP)** — extremely fast parallel server polling, saving historical status and favicon without blocking the main app.
- **Advanced Gamification** — user experience points, real-time rank progression bars, seasonal leaderboards, and achievement badges.
- **Two-Factor Authentication (TOTP)** — built-in RFC 6238 implementation with Backup Codes and a "Soft-Lock" Grace Period for high-risk accounts.
- **Programmatic SEO** — automatic "Threshold Generator" prioritizing indexing for high-quality pages (e.g. `Version + Mode`).
- **High-Performance Public API** — read-only REST endpoint with Cursor-based pagination, protected by per-user manual API keys and rate limiting.
- **Rankings Logic** — composite scoring based on votes, dynamic 24h average online, precision uptime percent, and boost points.
- **Admin Panel** — server moderation, user management, boost/payments tracking, achievable badges editor, SEO management.

## Tech Stack

| Layer      | Technology                        |
|------------|-----------------------------------|
| Backend    | PHP 8.2+ (Fast Custom MVC Core)   |
| Database   | MySQL 8.x / MariaDB 10.x          |
| Daemon     | PHP CLI with ReactPHP (Event Loop)|
| Frontend   | Vanilla ECMAScript 6+, CSS Vars   |
| Icons      | Font Awesome 6                    |
| Server     | Nginx / Apache                    |

## Project Structure

```text
├── app/
│   ├── Controllers/      # App Routing (Web, API, Admin)
│   ├── Core/             # Custom Framework (Router, QueryBuilder)
│   ├── Middleware/       # Auth, CSRF, RateLimit, 2FA, ApiKey
│   ├── Models/           # DB Interactions
│   ├── Services/         # AsyncPing, Security, Ranking, Seo
│   └── Views/            # PHP Templates (layouts, pages, seo)
├── daemon/               # ReactPHP loops (ping_service.php)
├── public/               # Web Root (index.php, CSS, JS)
├── database/             # Sequential migrations & init schemas
├── routes/               # Declarations (web.php, api.php)
└── storage/              # Logs, cache, generated files
```

## Installation

### Requirements

- PHP 8.2+ with `sockets`, `pdo_mysql`, `mbstring`, `json`
- MySQL 8.0+ or MariaDB 10.5+
- Composer

### Setup Process

```bash
# Clone
git clone https://github.com/ByTheLid/minecraft-monitoring.git
cd minecraft-monitoring

# Install dependencies
composer install

# Configure environment variables
cp .env.example .env
# Edit .env with your absolute DB credentials and App URL

# Run migrations to build the schema
php database/migrate.php up
```

### Starting the Daemon (CRITICAL)

The project relies on a background worker to ping servers and recalculate rankings.
Run this script via Supervisor, Systemd, or Screen, and keep it alive in the background:

```bash
php daemon/ping_service.php
```

### Web Server Configuration (Nginx Example)

Point your webserver's document root to the `/public` folder.

```nginx
server {
    listen 80;
    server_name monitoring.local;
    root /path/to/minecraft-monitoring/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        include fastcgi.conf;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## API Highlights

Public endpoints are prefixed with `/api/v1/` and require an `X-API-Key` header (issued in the user dashboard).

| Method | Endpoint                    | Description            |
|--------|-----------------------------|------------------------|
| GET    | `/api/v1/servers`           | List servers (Cursor Pagination) |
| GET    | `/api/v1/server/{id}`       | Server details         |
| POST   | `/api/v1/server/{id}/vote`  | Vote via API (returns Votifier reward status) |

## License

MIT

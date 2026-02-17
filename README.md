# MC Monitor — Minecraft Server Monitoring

Real-time monitoring platform for Minecraft servers with rankings, voting, and analytics.

## Features

- **Server Monitoring** — automated pinging via Minecraft protocol, online/offline status, player count, version detection
- **Rankings** — composite scoring based on votes, online players, uptime, and boost points
- **Voting** — one vote per user per server per day, instant ranking update
- **Boost System** — purchasable boost packages for server promotion
- **Charts** — player activity graphs (24h / 7d / 30d) with Chart.js
- **Auto-Refresh** — server data updates on page load with rate limiting (60s global, 30s per IP)
- **Admin Panel** — server moderation, user management, post editor, ranking settings
- **News** — blog/news system with categories and pagination
- **Dark / Light Theme** — toggle with localStorage persistence
- **Responsive** — mobile-friendly layout with collapsible navbar and admin sidebar

## Tech Stack

| Layer      | Technology                        |
|------------|-----------------------------------|
| Backend    | PHP 8.2+ (custom MVC framework)   |
| Database   | MySQL 8+                          |
| Frontend   | Vanilla JS, CSS custom properties |
| Icons      | Font Awesome 6                    |
| Charts     | Chart.js 4                        |
| Fonts      | Inter, JetBrains Mono             |
| Server     | Nginx (OSPanel 6 / any)           |

## Project Structure

```
├── app/
│   ├── Controllers/      # Web & API controllers
│   ├── Core/             # Framework (Router, Request, Response, Database, etc.)
│   ├── Middleware/        # Auth, CSRF, CORS, Rate Limiting
│   ├── Models/           # Eloquent-style models (Server, User, Vote, etc.)
│   ├── Services/         # MinecraftPing, AuthService
│   └── Views/            # PHP templates (layouts, pages, admin)
├── config/               # App, database, session config
├── cron/                 # Scheduled tasks (ping, rankings, cleanup)
├── css/                  # Stylesheet
├── js/                   # Frontend scripts
├── database/
│   ├── init.sql          # Full schema
│   ├── migrations/       # Incremental migrations
│   └── seeds/            # Seed data
├── routes/
│   ├── web.php           # Web routes
│   └── api.php           # API routes
├── storage/              # Logs, cache
├── index.php             # Entry point
└── .env.example          # Environment template
```

## Installation

### Requirements

- PHP 8.2+ with `sockets`, `pdo_mysql`, `mbstring`, `json`
- MySQL 8.0+
- Nginx or Apache
- Composer

### Setup

```bash
# Clone
git clone https://github.com/ByTheLid/minecraft-monitoring.git
cd minecraft-monitoring

# Install dependencies
composer install

# Configure environment
cp .env.example .env
# Edit .env with your database credentials

# Initialize database
mysql -u root < database/init.sql
# Or run migrations:
php database/migrate.php

# Seed sample data (optional)
php database/seeds/seed.php
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name monitoring.local;
    root /path/to/minecraft-monitoring;
    index index.php;

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        include fastcgi.conf;
    }

    location / {
        try_files $uri $uri/ @php;
    }

    location @php {
        fastcgi_pass 127.0.0.1:9000;
        include fastcgi.conf;
        fastcgi_param SCRIPT_FILENAME $document_root/index.php;
    }
}
```

### Cron Jobs

```bash
# Ping servers every 3 minutes
*/3 * * * * php /path/to/cron/ping.php

# Recalculate rankings every 10 minutes
*/10 * * * * php /path/to/cron/rankings.php

# Aggregate hourly stats
0 * * * * php /path/to/cron/aggregate.php

# Cleanup old data weekly
0 3 * * 0 php /path/to/cron/cleanup.php

# Expire boosts daily
0 0 * * * php /path/to/cron/expire_boosts.php
```

## API Endpoints

| Method | Endpoint                    | Description            |
|--------|-----------------------------|------------------------|
| GET    | `/api/servers`              | List servers           |
| GET    | `/api/servers/refresh`      | Refresh server data    |
| GET    | `/api/servers/{id}`         | Server details         |
| GET    | `/api/servers/{id}/stats`   | Server statistics      |
| POST   | `/api/servers/{id}/vote`    | Vote for server        |
| POST   | `/api/servers`              | Add server (auth)      |
| PUT    | `/api/servers/{id}`         | Update server (auth)   |
| DELETE | `/api/servers/{id}`         | Delete server (auth)   |

## License

MIT

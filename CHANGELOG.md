# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [v2.2.0] - 2026-03-11

### Added
- **Server Verification**: Added MOTD-based ownership verification for server admins. Verifying a server grants a SEO & Trust rank bonus (+5 points) and displays a green `✔️ Verified` badge.
- **Dynamic Playerbar Banners**: Custom GD-based endpoint (`/api/server/{id}/banner.png`) to generate real-time 468x60 server banners containing custom TTF fonts, right-aligned stats, favicons, and background gradients.
- **Deep Analytics**: Chart.js integration on the server detail page rendering multi-axis data (Average Online vs Uptime percent).
- **Analytics Aggregator**: A non-blocking asynchronous routine runs within `ping_service.php` every 10 minutes to safely crunch millions of pings into hourly statistical rows inside `server_analytics_cache`.

## [v2.1.0] - 2026-03-09

### Added
- **Gamification Features**: Rank Progress Bar on user profiles and a Monthly Leaderboard (`/leaderboard`).
- **Public API**: New RESTful `/api/v1/servers` endpoint with cursor-based pagination.
- **API Keys**: Users can now generate, manage, and revoke API keys from their dashboard.
- **Programmatic SEO**: Dynamic SEO pages generation (`/servers/{category}/{value}`) via Threshold Generator (minimum 15 servers and 500 chars total description).
- **2FA (Two-Factor Authentication)**: Google Authenticator (TOTP RFC 6238) support for user accounts.
- **2FA Soft-Lock**: High-risk users (e.g., top-20 server owners, users with balance) are given a 72-hour grace period to enable 2FA before critical actions are blocked.
- **2FA Backup Codes**: Generated upon 2FA activation for account recovery.

### Changed
- **Server Ranking (`RankingService`)**: `rank_score` is now accurately recalculated across all servers by the background daemon after each ping cycle, dynamically factoring in 24h `avg_online` and `uptime_percent`.
- **Manual Server Refresh**: Using `force=1` now strictly bypasses the global cooldown but adheres to a per-IP rate limit of 60 seconds (increased from 30s).
- **Modal Logic**: Unified all modal backdrops across the admin panel to use CSS-based transitions (`classList.add('active')`) with global Escape and backdrop-click handlers.

### Fixed
- **Favicon Cache**: Fixed a critical bug in `daemon/ping_service.php` where `favicon_base64` and `motd` were nullified due to missing columns in the `INSERT INTO server_status_cache` statement.

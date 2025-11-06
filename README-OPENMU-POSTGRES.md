## WebEngine CMS — OpenMU + PostgreSQL Integration

This document describes how to install, migrate, and operate WebEngine CMS with an OpenMU server backed by PostgreSQL.

### Who is this for?
Admins running OpenMU (modern) who want WebEngine CMS to use the OpenMU PostgreSQL schema and the OpenMU Admin API for online status and server information.

## Requirements
- PHP with `pdo_pgsql` enabled
- PostgreSQL access to the OpenMU database (typically schemas: `data`, `config`, `guild`)
- OpenMU Admin Panel URL (base) for API access (e.g., `http://127.0.0.1:1234`)
- Game Server IP for server status checks

## Fresh Installation (Recommended)
1. Launch WebEngine installer.
2. Step 1 — Requirements: ensure `pdo_pgsql` passes.
3. Step 2 — Database Connection (defaults provided):
   - Host: `localhost`
   - Port: `5432`
   - Username: `postgres`
   - PDO Driver: `PostgreSQL (pgsql)`
   - Password Encryption: `Bcrypt`
   - Sha256 Salt: leave blank
4. Step 4 — Cron Jobs: installer ensures `public.webengine_cron` exists and registers cron entries.
5. Step 5 — Website Configuration:
   - Server Files: `OpenMU (Modern)`
   - OpenMU Admin Panel URL → stored as `openmu_api_base_url` in `webengine.json`
   - Game Server IP → stored as `game_server_ip` in `webengine.json`
6. Finish and remove `/install` as prompted.

## Migrating an Existing WebEngine to OpenMU + PostgreSQL
1. Back up your site and database.
2. Deploy the updated WebEngine files.
3. Update your database connection to PostgreSQL in `config` (via installer or config editor).
4. Ensure Step 4 of the installer ran at least once to create cron entries/tables (safe to re-run; guards prevent duplicates).
5. The system includes runtime guards that create required WebEngine tables if they’re missing:
   - `public.webengine_cron`
   - `public.webengine_fla`
   - `public.webengine_ban_log`, `public.webengine_bans` (+missing columns: `ban_date`, `ban_days`, `ban_reason`)
   - `public.webengine_blocked_ip`
   - `data.webengine_news_translations` and view `public."WEBENGINE_NEWS_TRANSLATIONS"`
6. Clear caches after deployment (news, downloads, rankings) if results look stale.
7. Configure `openmu_api_base_url` and `game_server_ip` in `webengine.json` if not set by installer.

### PostgreSQL Dialect Notes
- MSSQL `TOP N` → PostgreSQL `LIMIT N`
- `LIKE` → `ILIKE` for case-insensitive
- Identifiers in PostgreSQL fold to lowercase if not quoted; OpenMU uses quoted identifiers, e.g., `data."Account"`.

## OpenMU Schema Mapping Highlights
- Accounts: `data."Account"` (uses UUID `Id`, `LoginName`, `EMail`, `State`, `RegistrationDate`).
- Characters: `data."Character"` (fields like `Name`, `AccountId` (UUID), `Experience`, `LevelUpPoints`, `PlayerKillCount`, `ItemStorage`, `CharacterClassId`).
- Stats: `data."StatAttribute"` joined to `config."AttributeDefinition"` (`Designation`). Base stats use designations like `Base Strength`, `Base Agility`, `Base Vitality`, `Base Energy`, `Base Leadership`.
- Bans: `public.webengine_ban_log` is authoritative for temporal bans; cron lifts bans from here.
- Translations: `data.webengine_news_translations` with compatibility view `public."WEBENGINE_NEWS_TRANSLATIONS"`.

## Online Status and Server Info
- Account online: uses OpenMU Admin API `/api/is-online/{accountName}` when available; fallback to DB/cache.
- Character online: player profile and rankings use per-character API helper with cache fallback.
- Server status: TCP probe to configured `game_server_ip` and known ports; displays Online/Offline in header.

## Features Updated for OpenMU
- News management works with OpenMU-backed tables/views; added optional `category` (clickable to filter).
- News list truncates to 250 words and fixes encoding when truncating.
- Admin Home: stats from `data."Account"` and `data."Character"`.
- Account search/info: accepts UUID or `LoginName`; online status uses API.
- “Accounts from IP”, “Online Accounts”, “New Registrations”, “Search Character”, “Edit Character”, “Ban Account”, “Latest Bans”, “Search Ban”, “Blocked IPs”, “Credits Configs/Manager”, “Top Voters” updated to PostgreSQL + OpenMU.
- Rankings (level/killers) and player profiles show API-backed online indicators.
- Guild rankings: uses OpenMU schema with UUIDs; detects guild master FK; counts members by `GuildId`; handles logo LOB streams.

## Character Stat Management
- Add Stats reads `LevelUpPoints`, computes level from `Experience`, validates class for Leadership, checks money from `ItemStorage`.
- Updates target `data."StatAttribute"` with safe insert-if-missing for Base attributes (e.g., `Base Vitality`) and synonyms (`Str`/`Strength`, `Agi`/`Agility`, `Vit`/`Sta`/`Stamina`, `Ene`/`Energy`, `Cmd`/`Command`).
- Admin Panel character edit: most fields read-only for OpenMU; only `LevelUpPoints` editable.

## Cron Jobs & Caches
- Installer registers cron tasks (levels, resets, killers, master level, guilds, grand resets, online, gens, votes, castle siege, temporal bans, server info, account/character country, online characters).
- News/downloads caches are rebuilt when content changes; rankings caches rebuilt by cron and have dynamic fallback logic in UI.

## Troubleshooting
- relation does not exist: ensure the table names are lowercase when unquoted; guards create required tables/views on-demand.
- Syntax errors near numbers: replace `TOP N` with `LIMIT N`.
- LIKE not case-insensitive: use `ILIKE`.
- Online mismatch: API may lag 10–20 seconds; UI falls back to cache if API is unreachable.

### Known Limitations (OpenMU)
- UserCP Unstick is not supported in this release when using OpenMU + PostgreSQL. The page displays an advisory note: "Please contact MU administrator for support if your account is stuck." Use in-game GM tools or database-side corrections instead.

### Admin Settings (PostgreSQL/OpenMU)
- Connection Settings:
  - PDO Driver: select `pgsql` (value 3)
  - Password Encryption: select `bcrypt`
- Website Settings:
  - `openmu_api_base_url` (e.g., http://localhost:5000)
  - `game_server_ip` (for server status ports 55901/55902)
Both pages preserve unknown keys in `webengine.json` and prefill values from the current config.

## Configuration Keys (webengine.json)
- `openmu_api_base_url` — Base URL to OpenMU Admin API
- `game_server_ip` — IP used for TCP probe (server status)

## Notes on Compatibility
- UUIDs replace integer IDs for accounts/characters.
- Bans are logged in `WEBENGINE_BAN_LOG`; temporal bans are lifted by cron.
- Legacy credits tables are optional; virtual credits remain supported.

For a summary of changes, see `CHANGELOG.md`.

WebEngine CMS – OpenMU (PostgreSQL) Integration Guide

Overview
This document explains the key changes and guidance to run WebEngine CMS fully on PostgreSQL using OpenMU’s schema (UUID identifiers, bcrypt passwords) and Admin API. It focuses on compatibility, installer flow, schema creation, and runtime behavior.

Why PostgreSQL + OpenMU
- OpenMU uses PostgreSQL with normalized schemas (e.g., data."Account", data."Character") and UUIDs for Ids.
- Passwords are bcrypt hashes in PasswordHash.
- This migration replaces MSSQL-specific SQL and legacy tables with PostgreSQL-compatible SQL and compatibility layers.

Key Concepts
- UUIDs: Accounts and characters use UUID v4 (Id). User and character references must store/retrieve UUIDs.
- Bcrypt: Registration and login paths must generate/validate bcrypt hashes (OpenMU-compatible).
- SQL Syntax: Replace MSSQL TOP and bracketed identifiers with PostgreSQL LIMIT and schema-qualified quoted names. Use ILIKE for case-insensitive searches.
- Compatibility Views: Legacy WEBENGINE_* names are mapped as views to OpenMU-friendly tables when possible.
- Virtual Credits: Credits are computed from data.webengine_credits_logs so WEBENGINE_CREDITS_* tables are optional.
- Admin API: Homepage online users and status checks prefer OpenMU Admin API; DB fallbacks remain.
- Auto-Create Guards: First-time accesses auto-create missing helper tables (cron, FLA, blocked IPs, bans, news translations, etc.).

Installer Changes (Steps)
1) Web Server Requirements
   - Adds pdo_pgsql extension check.

2) Database Connection (Defaults for PostgreSQL/OpenMU)
   - Host: localhost
   - Port: 5432
   - Username: postgres
   - Database (1): openmu
   - PDO Driver: PostgreSQL (pgsql)
   - Password Encryption: Bcrypt (OpenMU compatible)
   - SHA256 Salt: Optional (only required if SHA256 selected)

3) Create Tables (OpenMU Mode)
   - Executes install/sql/openmu/webengine_tables.sql.
   - Splits SQL into individual statements to avoid “cannot insert multiple commands” errors.
   - Uses CREATE SCHEMA IF NOT EXISTS; creates required public/data/guild/friend/config schemas.
   - Creates key webengine tables and compatibility views.

4) Add Cron Jobs
   - Ensures public.webengine_cron exists before inserting cron entries.

5) Website Configuration
   - Default Server Files: OpenMU (Modern)
   - New fields saved to config (webengine.json):
     - openmu_api_base_url (e.g., http://localhost:5000)
     - game_server_ip (e.g., 127.0.0.1)

Database Schema Highlights
- Schémas created: public, data, guild, friend, config.
- WebEngine tables (PostgreSQL-native):
  - data.webengine_news (+ category), data.webengine_news_translations
  - data.webengine_vote_sites, data.webengine_vote_logs
  - data.webengine_credits_logs
  - data.webengine_bans, public.webengine_ban_log
  - public.webengine_cron (+ cron logs), data.webengine_plugins
  - data.webengine_downloads (admincp/inc/functions.php adjusted)
  - public.webengine_blocked_ip, public.webengine_fla
- Compatibility Views (legacy -> data/public):
  - public."WEBENGINE_NEWS" → data.webengine_news
  - public."WEBENGINE_DOWNLOADS" → data.webengine_downloads
  - public."WEBENGINE_NEWS_TRANSLATIONS" → data.webengine_news_translations

Core Code Changes
- includes/classes/class.database.php
  - Maps driver “3” → pgsql, sets pgsql PDO attributes.

- includes/classes/class.account.php
  - Registration inserts into data."Account" with UUID v4, bcrypt PasswordHash, required defaults.
  - Auto-login path updated for OpenMU.

- includes/classes/class.login.php
  - Uses MuOnline connection.
  - Ensures public.webengine_fla exists before use.

- includes/classes/class.vote.php
  - Uses data.webengine_vote_sites/logs; id and UUID account_id.

- includes/classes/class.credits.php
  - Virtual config id=1 if WEBENGINE_CREDITS_CONFIG missing.
  - Balances via data.webengine_credits_logs; add/subtract log there.
  - setIdentifier() supports UUIDs.

- includes/classes/class.common.php
  - Blocked IP features on MuOnline connection; ensures public.webengine_blocked_ip exists.
  - accountOnline prefers Admin API.

- includes/functions/openmu.php (new)
  - Admin API helpers: base URL, status, online counts, account/character checks.
  - OpenMU utilities: level/master level from experience, stats, money, class/map names.
  - Server probe: isGameServerOnline() tries ports 55901/55902.

- templates/default/index.php & css/style.css
  - Online Users uses Admin API (fallback to cache); fuller progress bar with effective max.
  - Server Status badge (Online/Offline) above Online Users.

UserCP Modules
- vote.php: Aligned with data.webengine_vote_sites/logs (id, UUIDs).
- buyzen.php: Uses UUIDs; fetches zen via ItemStorage helper; virtual credits default.
- myaccount.php: Calculates level and master level (no undefined keys); combined display.

AdminCP Modules (major fixes)
- home.php: Counts from data."Account" and data."Character"; no MSSQL tables.
- searchaccount.php: ILIKE on data."Account"; returns Id, LoginName.
- accountinfo.php: UUID-aware; pulls state/email/login; character list by AccountId.
- accountsfromip.php: Uses public.webengine_fla; maps username → account UUID.
- onlineaccounts.php: OpenMU-aware list/count; removes MSSQL server name.
- newregistrations.php: ORDER BY "RegistrationDate" DESC LIMIT 200.
- searchcharacter.php: ILIKE Name LIMIT 10; returns Name, AccountId.
- editcharacter.php: Read-only OpenMU-computed values (level, PK); stats via helpers; master level panel.
- searchban.php/latestbans.php: public.webengine_ban_log with ILIKE + LIMIT 25.
- banaccount.php: Ensures/extends public.webengine_bans/ban_log schema; bans via data."Account"."State".
- topvotes.php: LIMIT 100; date filter via TO_TIMESTAMP.
- inc/functions.php (downloads): data.webengine_downloads; type/category mapping.

Runtime Guards
- Auto-creates missing helper tables (FLA, cron, blocked IPs, bans) and columns (ban_date, ban_days, ban_reason) via ALTER TABLE IF NOT EXISTS.
- Ensures news translations table + compatibility view creation with proper quoting.

Configuration Keys
- openmu_api_base_url: Base URL of OpenMU Admin API.
- game_server_ip: IP used to probe ports 55901/55902 for Online status.
- maximum_online: Controls online bar; UI uses effective max (80%) for fuller bar.

Migration Guidance
Fresh Install (recommended)
1) Ensure PostgreSQL and pdo_pgsql are installed.
2) Run /install and choose PostgreSQL defaults.
3) Let Step 3 create schemas/tables/views; Step 4 add cron.
4) In Step 5, set Admin API URL and Game Server IP.
5) Delete /install.

Existing Install
- If upgrading from MSSQL-based WebEngine, adopt the new PostgreSQL SQL file to create the required tables/views.
- Enable virtual credits mode (default) and gradually migrate legacy credit data into data.webengine_credits_logs if needed.
- Verify AdminCP pages and news translations; the code will create missing tables/views on first access when possible.

SQL & Quoting Tips
- Always schema-qualify OpenMU tables (e.g., data."Account").
- Use double quotes for identifiers (case-sensitive), single quotes for strings.
- Use ILIKE for case-insensitive searches; use LIMIT instead of TOP.

Troubleshooting
- Relation does not exist: Ensure install step 3 ran; re-run or create with provided SQL file.
- Multiple commands error: Confirm installer split execution is active (already implemented).
- News translations/view errors: First access auto-creates; check permissions if creation fails.
- Credits config/table missing: Virtual mode is enabled by default; ensure data.webengine_credits_logs exists.
- FLA/cron/blocked IPs missing: Auto-create guards run on first use.

Support & Notes
- This integration targets OpenMU’s modern schema and APIs. Some legacy features (Resets/GrandResets) are computed or shown read-only.
- For large data sets, consider adding indexes on frequently queried columns (e.g., account_id, voted_at, news.created_at).



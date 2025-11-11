## WebEngine CMS — OpenMU + PostgreSQL Integration

This guide explains how to install, migrate, and operate WebEngine CMS with OpenMU on PostgreSQL, including Admin API integration and the responsive template.

### Who is this for?
Admins running OpenMU (modern) who want WebEngine CMS to use OpenMU’s PostgreSQL schema and Admin API for online status/server info.

## Requirements
- PHP with pdo_pgsql enabled (and pgsql)
- PostgreSQL access to the OpenMU database (schemas: data, config, guild, friend)
- OpenMU Admin Panel Base URL (e.g., http://127.0.0.1:5000)
- Game Server IP for server status probe

## Quick Start (XAMPP/Apache)
1) Copy project to C:\xampp\htdocs\webengine\
2) Start Apache from XAMPP Control Panel
3) Open http://localhost/webengine/ to run the installer
4) After install, visit the site root to verify

Tip: If you previously used the built‑in PHP server or helper scripts, they are no longer needed. Use XAMPP Apache as above.

## Fresh Installation (Recommended)
1. Requirements: ensure pdo_pgsql passes.
2. Database Connection (PostgreSQL defaults)
   - Host: localhost
   - Port: 5432
   - Username: postgres
   - PDO Driver: PostgreSQL (pgsql)
   - Password Encryption: bcrypt
3. Create Tables (OpenMU mode): installer creates schemas/tables/views.
4. Add Cron Jobs: guarded insert; creates public.webengine_cron if missing.
5. Website Configuration:
   - Server Files: OpenMU (Modern)
   - openmu_api_base_url (e.g., http://localhost:5000)
   - game_server_ip (e.g., 127.0.0.1)
6. Remove /install when prompted.

## Migrating an Existing WebEngine to OpenMU + PostgreSQL
1) Back up files and DB.
2) Deploy updated WebEngine files.
3) Switch DB config to PostgreSQL (installer or config editor).
4) Ensure cron tables were created (safe to re-run installer cron step).
5) Runtime guards create frequently missing tables/views when first used:
   - public.webengine_cron, public.webengine_fla, public.webengine_ban_log / public.webengine_bans (+ban_date/ban_days/ban_reason), public.webengine_blocked_ip
   - data.webengine_news_translations and view public."WEBENGINE_NEWS_TRANSLATIONS"
6) Configure openmu_api_base_url and game_server_ip if not set.
7) Clear caches (news, downloads, rankings) if results look stale.

## PostgreSQL Dialect Notes
- TOP N → LIMIT N
- LIKE → ILIKE (case-insensitive)
- Quote identifiers for OpenMU tables, e.g., data."Account"

## OpenMU Schema Mapping Highlights
- Accounts: data."Account" (UUID Id, LoginName, EMail, State, RegistrationDate)
- Characters: data."Character" (Id UUID, Name, AccountId UUID, Experience, LevelUpPoints, PlayerKillCount, ItemStorage, CharacterClassId)
- Stats: data."StatAttribute" joined with config."AttributeDefinition" by Designation (Base Strength/Agility/Vitality/Energy/Leadership)
- Bans: public.webengine_ban_log authoritative for temporal bans (cron lifts)
- Translations: data.webengine_news_translations with view public."WEBENGINE_NEWS_TRANSLATIONS"

## Online Status and Server Info
- Account online: OpenMU Admin API /api/is-online/{accountName} (fallback DB/cache)
- Character online: rankings and profiles use per-character API with cache fallback
- Server status: TCP probe to game_server_ip ports (e.g., 55901/55902); shows Online/Offline in header

## Features Updated for OpenMU
- News: optional category (click to filter); list truncates to 250 words with encoding fixes
- Admin Home: counts from data."Account" and data."Character"
- Account search/info: UUID or LoginName; online state via API
- Admin modules updated to PostgreSQL + OpenMU (accounts from IP, online accounts, new registrations, search/edit character, bans/blocked IPs, credits, top voters)
- Rankings: API-backed per-character online status; guild rankings use UUIDs, detect master FK, count by GuildId, and handle LOB logos

### Responsive Template (Mobile/Desktop)
- Mobile viewport; fluid container on small screens
- Navbar collapses to hamburger on mobile; desktop shows classic horizontal menu
- Header info (Server Status, Online Users, Server Time, Your Time):
  - Mobile: larger fonts and compact spacing
  - Desktop: smaller fonts; values right-aligned (legacy layout)
- News category badges auto-truncate with ellipsis; added spacing below news list
- Wide tables get horizontal scrolling on narrow screens

## Character Stat Management
- Add Stats reads LevelUpPoints, computes level from Experience, validates class for Leadership, and checks money from ItemStorage
- Updates target data."StatAttribute" with safe insert-if-missing for Base attributes and synonyms:
  - Strength (Str), Agility (Agi), Vitality (Vit/Sta/Stamina), Energy (Ene), Leadership (Cmd/Command)
- Admin character edit: most fields read-only under OpenMU; only LevelUpPoints editable

## Cron Jobs & Caches
- Installer registers cron jobs (rankings, online, gens, votes, siege, bans, server info, etc.)
- News/downloads caches rebuild on content change; rankings cached by cron with UI fallback

## Troubleshooting
- PostgreSQL extensions missing
  - Enable php_pgsql and pdo_pgsql in php.ini
- Database connection failed
  - Verify PostgreSQL is running, credentials are correct, DB exists
- Relation/table not found
  - Ensure install step created schemas; guards create missing tables/views on first use
- Configuration issues
  - Confirm includes/config/webengine.json is valid and server_files is “openmu”
- Online mismatch
  - Admin API may lag 10–20s; UI falls back to cache if API is unreachable

## Admin Settings (PostgreSQL/OpenMU)
- Connection Settings: PDO driver = pgsql (value 3), Password Encryption = bcrypt
- Website Settings: openmu_api_base_url (e.g., http://localhost:5000), game_server_ip (e.g., 127.0.0.1)
- Both preserve unknown keys and prefill from webengine.json

## Configuration Keys (webengine.json)
- openmu_api_base_url — Base URL to OpenMU Admin API
- game_server_ip — IP used for TCP probe (server status)
- maximum_online — used to render the online bar (UI uses an effective max for fullness)

## Known Limitations (OpenMU)
- UserCP Unstick is not supported in this release for OpenMU + PostgreSQL. The page shows an advisory to contact an administrator.



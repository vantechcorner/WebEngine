## Changelog

### 1.2.6+openmu-pg (2025-11-03)

#### Added
- PostgreSQL support and OpenMU schema integration across installer and runtime.
- Installer defaults for PostgreSQL: host `localhost`, port `5432`, username `postgres`, driver `PostgreSQL (pgsql)`, encryption `Bcrypt`.
- New OpenMU config inputs in installer: `OpenMU Admin Panel URL` and `Game Server IP` (stored in `webengine.json` as `openmu_api_base_url` and `game_server_ip`).
- Auto-creation guards for missing tables on first use:
  - `public.webengine_cron`
  - `public.webengine_fla`
  - `public.webengine_ban_log`, `public.webengine_bans`
  - `public.webengine_blocked_ip`
  - `data.webengine_news_translations` and compatibility view `public."WEBENGINE_NEWS_TRANSLATIONS"`
- OpenMU Admin API integration:
  - Account online: `/api/is-online/{accountName}`
  - Global status: `/api/status`
  - Character online by name helper
- Server Status added to header with TCP port probing (configurable IP/ports, timeout).
- News Categories: optional `category` per news item; clickable category filter on homepage and `/news`.
- Truncated news on `/news` list to 250 words with “Read more”; fixed encoding in truncation.
- Player Profile and Rankings show per-character online status via API with cache fallback.
- New helper file `includes/functions/openmu.php` with OpenMU-specific logic (level calculations, stat management, API wrappers, class/map helpers, server probe).

#### Changed
- Switched many queries to PostgreSQL dialect (`LIMIT`, `ILIKE`, quoted identifiers) and OpenMU schema mapping (e.g., `data."Account"`, `data."Character"`).
- `class.login.php`, `class.common.php`, `class.news.php`, and admin modules now use the `MuOnline` connection for OpenMU data.
- AdminCP Home stats read from OpenMU tables.
- Account/Character search and info modules adapted to UUIDs and OpenMU column names.
- Rankings rebuild/fallback logic improved; online indicators now use API when available.
- Downloads admin maps type to `data.webengine_downloads.category`; frontend categorizes legacy entries heuristically if missing.

#### Fixed
- Installer “Add Cron Jobs” failing due to `webengine_cron` not found (lowercase and guard creation).
- Login failing due to missing `webengine_fla` (table creation guard and correct connection).
- Numerous admin modules updated: accounts from IP, online accounts, new registrations, search character, edit character, search bans, latest bans, ban account, blocked IPs, credits configs/manager, top votes.
- News caching/translations errors resolved; runtime creation of translations table/view.
- Character edit: mapped OpenMU stats/fields; read-only fields set appropriately; PK level computed; money from `ItemStorage`.
- Add Stats: fixed LevelUpPoints/experience checks, money checks, and OpenMU stat attribute updates with safe insert-if-missing for Base attributes (including Vitality) and synonym handling.
- Encoding issues in news truncation fixed.

#### Known Limitations (OpenMU Mode)
- UserCP Unstick: not working reliably with OpenMU (UUID path). Please advise players to contact an administrator if accounts are stuck. The module remains present with an advisory notice.

#### Migration Notes
- Breaking/Important:
  - Uses UUIDs for Account/Character ids.
  - Some character fields are read-only in AdminCP under OpenMU (e.g., level, money, PK level).
  - Ban flow uses `WEBENGINE_BAN_LOG` for temporal bans; cron lifts bans from logs.
  - Credits legacy tables are optional; virtual credits remain supported.
- See `README-OPENMU-POSTGRES.md` for full migration and troubleshooting guide.

WebEngine CMS – OpenMU (PostgreSQL) Integration Changelog

Unreleased (OpenMU/PostgreSQL Integration)

Added
- PostgreSQL (pdo_pgsql) support across CMS.
- OpenMU Admin API integration for homepage online users and account/character status checks.
- Game Server Status probe (ports 55901/55902) with Online/Offline badge in the header.
- Virtual Credits mode removing dependency on legacy WEBENGINE_CREDITS_* tables; balances computed from data.webengine_credits_logs.
- Auto-create/guard logic for frequently missing tables: public.webengine_fla, public.webengine_cron, public.webengine_blocked_ip, ban tables, news translations, etc.
- News category feature: category badge (clickable) and filter on /news/; homepage shows category first.
- News list truncation (250 words) with encoding fixes (strip tags, decode entities, normalize whitespace).
- Installer additions:
  - Step 1: pdo_pgsql extension check.
  - Step 2: PostgreSQL defaults (host=localhost, port=5432, user=postgres, DB=openmu, driver=pgsql, encryption=bcrypt, SHA256 salt optional).
  - Step 3: PostgreSQL-aware table creation via install/sql/openmu/webengine_tables.sql executed statement-by-statement.
  - Step 4: Guarded cron setup if table missing.
  - Step 5: Default “OpenMU (Modern)”; inputs to capture OpenMU Admin API URL and Game Server IP.
- New helper library includes/functions/openmu.php with functions for: API calls, online player count, character stats/zen, class and map lookups, level/master level calculations, server probing.

Changed
- Core DB driver mapping: includes/classes/class.database.php maps driver “3” to pgsql and sets pgsql-specific PDO attributes.
- Registration and login (OpenMU path): includes/classes/class.account.php inserts into data."Account" with UUIDs and bcrypt PasswordHash; includes/classes/class.login.php uses MuOnline connection and ensures public.webengine_fla exists.
- Voting: includes/classes/class.vote.php now uses data.webengine_vote_sites/logs with id-based schema and UUID account_id.
- Credits: includes/classes/class.credits.php supports virtual config (id=1) if WEBENGINE_CREDITS_CONFIG missing; reads/writes to data.webengine_credits_logs; accepts UUIDs.
- Common utilities: includes/classes/class.common.php uses MuOnline connection for blocked IP functions; ensures public.webengine_blocked_ip exists; accountOnline prefers OpenMU API.
- Templates: templates/default/index.php prefers API count, fuller progress bar (effective max = 80%), new server status block; templates/default/css/style.css tweaks font sizes and status colors.
- AdminCP modules updated to OpenMU schema, UUIDs, and PostgreSQL syntax (ILIKE/LIMIT, schema-qualified names). Notable files: home.php, searchaccount.php, accountinfo.php, accountsfromip.php, onlineaccounts.php, newregistrations.php, searchcharacter.php, editcharacter.php, searchban.php, latestbans.php, banaccount.php, topvotes.php, inc/functions.php (downloads).
- News management: class.news.php and admincp modules ensure data.webengine_news_translations + compatibility view; fixed view creation quoting; cache update paths adjusted.
- UserCP modules: vote.php, buyzen.php (UUIDs and OpenMU money), myaccount.php (level/master level calculations).

Fixed
- Numerous SQLSTATE errors due to missing tables/columns or MSSQL syntax:
  - votesite_id → id (data.webengine_vote_sites);
  - WEBENGINE_CREDITS_CONFIG missing → virtual config fallback;
  - myaccount undefined keys → calculated level/master level;
  - registration duplicate LoginName → proper OpenMU insert with UUIDs;
  - installer multi-statement and MSSQL brackets → PostgreSQL SQL file split execution;
  - downloads/cron/fla relations missing → created in SQL and guarded at runtime;
  - AdminCP queries: replaced TOP with LIMIT, added ILIKE, schema-quoted tables, removed nonexistent columns, fixed ordering/filters;
  - News translations table/view creation and encoding issues.

Removed/Cleanup
- Deleted or relocated temporary/test scripts (e.g., install_webengine_tables*, fix_*, test_*, create_tables_simple, add_foreign*, skip_foreign_keys, setup_config.php, etc.).

Configuration Keys Added
- openmu_api_base_url: Base URL for OpenMU Admin API (e.g., http://localhost:5000).
- game_server_ip: IP for game server port probes (server status UI).

Schema and SQL
- install/sql/openmu/webengine_tables.sql creates schemas (data, guild, friend, config), core webengine tables, compatibility views (public."WEBENGINE_NEWS", public."WEBENGINE_DOWNLOADS", public."WEBENGINE_NEWS_TRANSLATIONS"), and supporting tables: vote sites/logs, credits logs, bans + ban log, cron tables, blocked IPs, news translations, paypal transactions, password/email flows, account_country, cron logs, plugins, public.webengine_fla.



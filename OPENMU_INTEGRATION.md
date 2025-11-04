## OpenMU Integration Guide

### Scope
This document summarizes the changes required to make WebEngine CMS work with an OpenMU server using PostgreSQL. It highlights database mappings, password verification, level computation, helpers, module updates, and operational notes so future maintainers can extend or debug the integration.

### Database schema mapping
- **Core tables**
  - Accounts: `data."Account"` (UUID `Id`, `LoginName`, `EMail`, `PasswordHash`, `State`, `RegistrationDate`, ...)
  - Characters: `data."Character"` (UUID `Id`, `Name`, `Class`, `CharacterClassId`, `CurrentMapId`, `PositionX`, `PositionY`, `Experience`, `MasterExperience`, `PlayerKillCount`, `Money`, ...)
  - Character classes: `config."CharacterClass"` (GUID `Id`, `Number`, `Name`)
  - Maps: `config."GameMapDefinition"` (GUID `Id`, numeric `Number`, `Name`)
  - Stats: `data."StatAttribute"` (uses FK to attribute definition, FK name may differ by version)
  - Attribute definitions: `config."AttributeDefinition"` (`Id`, `Designation` e.g., Strength/Agility/Vitality/Energy/Leadership)

- **Added OpenMU constants** in `includes/config/openmu.tables.php`:
  - Stats table: `_TBL_STAT_ATTRIBUTE_`, `_CLMN_STAT_ATTRIBUTE_ID_`, `_CLMN_STAT_ATTRIBUTE_CHARACTER_ID_`, `_CLMN_STAT_ATTRIBUTE_DEFINITION_ID_`, `_CLMN_STAT_ATTRIBUTE_VALUE_`
  - Attribute definition: `_TBL_ATTRIBUTE_DEFINITION_`, `_CLMN_ATTRIBUTE_DEFINITION_ID_`, `_CLMN_ATTRIBUTE_DEFINITION_DESIGNATION_`
  - Position: `_CLMN_CHR_MAP_X_` → `PositionX`, `_CLMN_CHR_MAP_Y_` → `PositionY`
  - Custom mappings:
    - `$custom['character_class']`: legacy numeric class → avatar/name
    - `$custom['map_list']`: numeric map id → name

- **UUID-first model**
  - Session stores `$_SESSION['userid']` (UUID, `Account.Id`) and `$_SESSION['username']` (`Account.LoginName`).
  - All account/character queries accept UUID, with login name fallback only when unavoidable.

### Password hashing (OpenMU/Game client compatible)
- Stored hashes are bcrypt (e.g., `$2a$11$...`).
- Verification logic:
  - If hash starts with `$2`, use `crypt($password, $storedHash)` and compare against `$storedHash` (matches OpenMU-produced hashes).
  - `password_verify()` may be used when normalization from `$2a$` to `$2y$` works, but `crypt()` is authoritative.
  - Fallbacks to other hash types left intact but not needed for bcrypt users.
- Diagnostic scripts:
  - `test_hash.php` and `test_bcrypt_crypt.php` demonstrate normalization and `crypt()` parity with stored hashes.

### Level and master level calculation
- Implemented exact total experience formula, per OpenMU config:
  - If `level == 0` → `0`
  - If `level < 256` → `10 * (level + 8) * (level - 1) * (level - 1)`
  - Else → previous total + `1000 * (level - 247) * (level - 256) * (level - 256)`
- `calculateOpenMULevel(experience)` performs a binary search (0..400) to find the highest level whose cumulative experience ≤ character Experience.
- Master level uses the analogous approach with `MasterExperience` as applicable.

### Helper functions (in `includes/functions/openmu.php`)
- `openMUTotalExperienceForLevel(level)`: total experience threshold for a given level.
- `calculateOpenMULevel(experience)`: derive level from Experience using binary search.
- `getOpenMUCharacterStats(characterId)`: loads stats by joining `data."StatAttribute"` with `config."AttributeDefinition"` via `Designation` (dynamically detects FK column name).
- `getOpenMUCharacterResets(characterId)`, `getOpenMUCharacterGrandResets(characterId)`: reset counters with consistent aliasing.
- `getOpenMUCharacterMoney(characterId)`: reads character Money.
- `getOpenMUClassNumberById(characterClassIdGuid)`: resolves OpenMU class GUID to legacy numeric id; prefers `CharacterClass.Name`, falls back to `Number`.
- `getOpenMUMapName(mapIdGuid)`: resolves map GUID to human-readable name.

### Rankings integration
- `includes/classes/class.rankings.openmu.php`:
  - Level and PK queries JOIN `config."CharacterClass"` and `config."GameMapDefinition"`.
  - PvP list includes zero-PK characters; excludes hidden/invalid characters.
  - Normalizes returned rows to WebEngine’s structure (character_name, class_id, level, map).
- `modules/rankings/level.php`, `modules/rankings/killers.php`:
  - Auto-rebuild cache if empty/timestamp-only or malformed.
  - Normalize delimiters from `Â¦` to `¦`.
  - Skip timestamp row on cached reads; render immediately on live rebuild.

### Player profiles
- `includes/classes/class.profiles.php`:
  - Derives display level from `Experience`/`MasterExperience`.
  - Resolves class via `getOpenMUClassNumberById()`.
  - Stats loaded by joining `StatAttribute` with `AttributeDefinition` (Designation mapping).
  - Guild lookup wrapped to avoid fatal errors when missing.
- `modules/profile/player.php`:
  - Uses shared helpers and cleaned debug output.
  - Online status fallback was added then removed by request; can be re-enabled if OpenMU semantics confirmed.

### UserCP modules updated (UUID-aware, OpenMU fields)
- Common changes applied:
  - `Character->AccountCharacter($_SESSION['userid'])` (UUID) instead of username.
  - Lowercase-normalization of character arrays and safe fallbacks for `Name`, `Experience`, etc.
  - Class GUID → legacy numeric for avatars.
  - Level from `Experience` using `calculateOpenMULevel()`.
  - Money via `getOpenMUCharacterMoney()`.
  - Stats via `getOpenMUCharacterStats()`.

- Modules impacted:
  - `modules/usercp/myaccount.php`: UUID account info; avatar/map resolution; combined level display.
  - `modules/usercp/reset.php`: level/zen/resets via OpenMU helpers; correct names/avatars.
  - `modules/usercp/unstick.php`: UUID; names/avatars; zen via helper.
  - `modules/usercp/clearpk.php`: UUID; names/avatars; zen via helper; PK via `PlayerKillCount` → `returnPkLevel()`.
  - `modules/usercp/resetstats.php`: UUID; avatars; level from `Experience`; stats via helper.

### Cache and API robustness
- Caches: ensure delimiter is `¦` and auto-rebuild malformed/empty caches.
- `api/castlesiege.php`: returns `{ "TimeLeft": 0 }` when no cache/live data to prevent 500s.

### Login lockouts table normalization
- `upgrade_fla_table.php` (run once):
  - Ensures `data.webengine_fla` has: `username`, `ip_address`, `unlock_timestamp` (BIGINT epoch), `failed_attempts`, `timestamp` (BIGINT epoch).
  - Migrates `attempts` → `failed_attempts` if present.
- `clear_lockouts.php`: clears lockouts by IP or all.

### Deployment notes
- Development repo: `D:\Github\WebEngine` → deploy to `D:\xampp\htdocs\` after changes.
- Files most changed: `includes/config/openmu.tables.php`, `includes/functions/openmu.php`, `includes/classes/class.rankings.openmu.php`, `includes/classes/class.profiles.php`, `includes/classes/class.login.openmu.php`, `modules/rankings/*.php`, `modules/profile/player.php`, `modules/usercp/*.php`, `api/castlesiege.php`.

### Testing checklist
- Rankings
  - `/rankings/level/` shows correct classes/maps; cache rebuilds cleanly.
  - `/rankings/killers/` shows players with 0+ PK; cache builds/loads.
- Profiles
  - Profile page shows correct class avatar, level, stats, guild (if configured), and map names.
- Login
  - Existing bcrypt accounts can log in; lockouts increment and expire as expected.
- UserCP
  - `myaccount`, `reset`, `unstick`, `clearpk`, `resetstats` load without warnings and show correct data.

### Known follow-ups
- Registration: implement bcrypt-based account creation directly into `data."Account"` (align UI with OpenMU rules/validation).
- Online status: re-enable OpenMU-backed online detection once server-side semantics are finalized.
- Additional modules (`addstats`, `clearskilltree`): adapt to OpenMU gameplay rules and APIs if desired.

---
For questions or future updates, search for “OpenMU” markers in the codebase and check the helpers in `includes/functions/openmu.php`.









<?php
/**
 * OpenMU Database Schema Definitions
 * Adapted for WebEngine CMS
 * 
 * @version 1.2.6
 * @author WebEngine CMS - OpenMU Integration
 * @copyright (c) 2025 WebEngine CMS, All Rights Reserved
 * 
 * Licensed under the MIT license
 * http://opensource.org/licenses/MIT
 */

// Account Table (replaces MEMB_INFO)
define('_TBL_ACCOUNT_', 'data."Account"');
    define('_CLMN_ACCOUNT_ID_', '"Id"');
    define('_CLMN_ACCOUNT_LOGIN_', '"LoginName"');
    define('_CLMN_ACCOUNT_PASSWORD_', '"PasswordHash"');
    define('_CLMN_ACCOUNT_EMAIL_', '"EMail"');
    define('_CLMN_ACCOUNT_STATE_', '"State"');
    define('_CLMN_ACCOUNT_SECURITY_CODE_', '"SecurityCode"');
    define('_CLMN_ACCOUNT_REGISTRATION_DATE_', '"RegistrationDate"');
    define('_CLMN_ACCOUNT_TIMEZONE_', '"TimeZone"');
    define('_CLMN_ACCOUNT_VAULT_PASSWORD_', '"VaultPassword"');
    define('_CLMN_ACCOUNT_VAULT_EXTENDED_', '"IsVaultExtended"');
    define('_CLMN_ACCOUNT_CHAT_BAN_', '"ChatBanUntil"');
    define('_CLMN_ACCOUNT_IS_TEMPLATE_', '"IsTemplate"');

// Character Table (OpenMU structure)
define('_TBL_CHARACTER_', 'data."Character"');
    define('_CLMN_CHAR_ID_', '"Id"');
    define('_CLMN_CHAR_NAME_', '"Name"');
    define('_CLMN_CHAR_ACCOUNT_ID_', '"AccountId"');
    define('_CLMN_CHAR_CLASS_ID_', '"CharacterClassId"');
    define('_CLMN_CHAR_MAP_ID_', '"CurrentMapId"');
    define('_CLMN_CHAR_INVENTORY_ID_', '"InventoryId"');
    define('_CLMN_CHAR_SLOT_', '"CharacterSlot"');
    define('_CLMN_CHAR_CREATE_DATE_', '"CreateDate"');
    define('_CLMN_CHAR_EXPERIENCE_', '"Experience"');
    define('_CLMN_CHAR_MASTER_EXPERIENCE_', '"MasterExperience"');
    define('_CLMN_CHAR_LEVEL_', '"Experience"'); // Will be calculated in PHP from Experience
    define('_CLMN_CHAR_MASTER_LEVEL_', '"MasterExperience"'); // Will be calculated in PHP from MasterExperience
    define('_CLMN_CHAR_LEVEL_UP_POINTS_', '"LevelUpPoints"');
    define('_CLMN_CHAR_MASTER_LEVEL_UP_POINTS_', '"MasterLevelUpPoints"');
    define('_CLMN_CHAR_POSITION_X_', '"PositionX"');
    define('_CLMN_CHAR_POSITION_Y_', '"PositionY"');
    define('_CLMN_CHAR_PK_COUNT_', '"PlayerKillCount"');
    define('_CLMN_CHAR_STATE_REMAINING_', '"StateRemainingSeconds"');
    define('_CLMN_CHAR_STATE_', '"State"');
    define('_CLMN_CHAR_STATUS_', '"CharacterStatus"');
    define('_CLMN_CHAR_POSE_', '"Pose"');
    define('_CLMN_CHAR_USED_FRUIT_POINTS_', '"UsedFruitPoints"');
    define('_CLMN_CHAR_USED_NEG_FRUIT_POINTS_', '"UsedNegFruitPoints"');
    define('_CLMN_CHAR_INVENTORY_EXTENSIONS_', '"InventoryExtensions"');
    define('_CLMN_CHAR_KEY_CONFIG_', '"KeyConfiguration"');
    define('_CLMN_CHAR_MU_HELPER_CONFIG_', '"MuHelperConfiguration"');
    define('_CLMN_CHAR_IS_STORE_OPENED_', '"IsStoreOpened"');
    define('_CLMN_CHAR_STORE_NAME_', '"StoreName"');

// Guild Table (OpenMU structure)
define('_TBL_GUILD_', 'guild."Guild"');
    define('_CLMN_GUILD_ID_', '"Id"');
    define('_CLMN_GUILD_NAME_', '"Name"');
    define('_CLMN_GUILD_HOST_ID_', '"HostId"'); // Guild Master
    define('_CLMN_GUILD_SCORE_', '"Score"');
    define('_CLMN_GUILD_LOGO_', '"Logo"');
    define('_CLMN_GUILD_NOTICE_', '"Notice"');
    define('_CLMN_GUILD_HOSTILITY_ID_', '"HostilityId"');
    define('_CLMN_GUILD_ALLIANCE_ID_', '"AllianceGuildId"');

// Guild Member Table
define('_TBL_GUILD_MEMBER_', 'guild."GuildMember"');
    define('_CLMN_GUILD_MEMBER_ID_', '"Id"');
    define('_CLMN_GUILD_MEMBER_GUILD_ID_', '"GuildId"');
    define('_CLMN_GUILD_MEMBER_CHARACTER_ID_', '"CharacterId"');
    define('_CLMN_GUILD_MEMBER_STATUS_', '"Status"');

// Item Storage Table (Warehouse)
define('_TBL_ITEM_STORAGE_', 'data."ItemStorage"');
    define('_CLMN_ITEM_STORAGE_ID_', '"Id"');
    define('_CLMN_ITEM_STORAGE_MONEY_', '"Money"');

// Item Table
define('_TBL_ITEM_', 'data."Item"');
    define('_CLMN_ITEM_ID_', '"Id"');
    define('_CLMN_ITEM_STORAGE_ID_', '"ItemStorageId"');
    define('_CLMN_ITEM_DEFINITION_ID_', '"DefinitionId"');
    define('_CLMN_ITEM_SLOT_', '"ItemSlot"');
    define('_CLMN_ITEM_DURABILITY_', '"Durability"');
    define('_CLMN_ITEM_LEVEL_', '"Level"');
    define('_CLMN_ITEM_HAS_SKILL_', '"HasSkill"');
    define('_CLMN_ITEM_SOCKET_COUNT_', '"SocketCount"');
    define('_CLMN_ITEM_STORE_PRICE_', '"StorePrice"');
    define('_CLMN_ITEM_PET_EXPERIENCE_', '"PetExperience"');

// Friend Table
define('_TBL_FRIEND_', 'friend."Friend"');
    define('_CLMN_FRIEND_ID_', '"Id"');
    define('_CLMN_FRIEND_CHARACTER_ID_', '"CharacterId"');
    define('_CLMN_FRIEND_FRIEND_ID_', '"FriendId"');
    define('_CLMN_FRIEND_ACCEPTED_', '"Accepted"');
    define('_CLMN_FRIEND_REQUEST_OPEN_', '"RequestOpen"');

// Character Class Configuration (for class names)
define('_TBL_CHARACTER_CLASS_', 'config."CharacterClass"');
    define('_CLMN_CHARACTER_CLASS_ID_', '"Id"');
    define('_CLMN_CHARACTER_CLASS_NAME_', '"Name"');
    define('_CLMN_CHARACTER_CLASS_NUMBER_', '"Number"');

// Game Map Configuration (for map names)
define('_TBL_GAME_MAP_', 'config."GameMapDefinition"');
    define('_CLMN_GAME_MAP_ID_', '"Id"');
    define('_CLMN_GAME_MAP_NAME_', '"Name"');
    define('_CLMN_GAME_MAP_NUMBER_', '"Number"');

// Stat Attributes (character stats stored separately)
define('_TBL_STAT_ATTRIBUTE_', 'data."StatAttribute"');
    define('_CLMN_STAT_ATTRIBUTE_ID_', '"Id"');
    define('_CLMN_STAT_ATTRIBUTE_CHARACTER_ID_', '"CharacterId"');
    define('_CLMN_STAT_ATTRIBUTE_DEFINITION_ID_', '"AttributeDefinition"');
    define('_CLMN_STAT_ATTRIBUTE_VALUE_', '"Value"');

// Attribute Definitions (designation/name of attributes)
define('_TBL_ATTRIBUTE_DEFINITION_', 'config."AttributeDefinition"');
    define('_CLMN_ATTRIBUTE_DEFINITION_ID_', '"Id"');
    define('_CLMN_ATTRIBUTE_DEFINITION_DESIGNATION_', '"Designation"');

// Stat Attributes (character stats stored separately)
define('_TBL_STAT_ATTRIBUTE_', 'data."StatAttribute"');
    define('_CLMN_STAT_ATTRIBUTE_ID_', '"Id"');
    define('_CLMN_STAT_ATTRIBUTE_CHARACTER_ID_', '"CharacterId"');
    define('_CLMN_STAT_ATTRIBUTE_DEFINITION_ID_', '"AttributeDefinitionId"');
    define('_CLMN_STAT_ATTRIBUTE_VALUE_', '"Value"');

// Attribute Definitions (designation/name of attributes)
define('_TBL_ATTRIBUTE_DEFINITION_', 'config."AttributeDefinition"');
    define('_CLMN_ATTRIBUTE_DEFINITION_ID_', '"Id"');
    define('_CLMN_ATTRIBUTE_DEFINITION_DESIGNATION_', '"Designation"');

// Legacy compatibility mappings (for existing WebEngine code)
// These map to the new OpenMU structure
define('_TBL_MI_', _TBL_ACCOUNT_);
    define('_CLMN_USERNM_', _CLMN_ACCOUNT_LOGIN_);
    define('_CLMN_PASSWD_', _CLMN_ACCOUNT_PASSWORD_);
    define('_CLMN_MEMBID_', _CLMN_ACCOUNT_ID_);
    define('_CLMN_EMAIL_', _CLMN_ACCOUNT_EMAIL_);
    define('_CLMN_BLOCCODE_', _CLMN_ACCOUNT_STATE_);
    define('_CLMN_SNONUMBER_', _CLMN_ACCOUNT_SECURITY_CODE_);
    define('_CLMN_MEMBNAME_', _CLMN_ACCOUNT_LOGIN_); // Use login name as display name
    define('_CLMN_CTLCODE_', _CLMN_ACCOUNT_IS_TEMPLATE_);

define('_TBL_CHR_', _TBL_CHARACTER_);
    define('_CLMN_CHR_NAME_', _CLMN_CHAR_NAME_);
    define('_CLMN_CHR_ACCID_', _CLMN_CHAR_ACCOUNT_ID_);
    define('_CLMN_CHR_CLASS_', _CLMN_CHAR_CLASS_ID_);
    define('_CLMN_CHR_MAP_', _CLMN_CHAR_MAP_ID_);
    define('_CLMN_CHR_ZEN_', _CLMN_ITEM_STORAGE_MONEY_); // Money is in ItemStorage
    define('_CLMN_CHR_LVL_', _CLMN_CHAR_LEVEL_);
    define('_CLMN_CHR_RSTS_', 'Resets'); // Will be calculated
    define('_CLMN_CHR_GRSTS_', 'GrandResets'); // Will be calculated
    define('_CLMN_CHR_LVLUP_POINT_', _CLMN_CHAR_LEVEL_UP_POINTS_);
    define('_CLMN_CHR_STAT_STR_', 'Strength'); // Will be in StatAttribute table
    define('_CLMN_CHR_STAT_AGI_', 'Agility'); // Will be in StatAttribute table
    define('_CLMN_CHR_STAT_VIT_', 'Vitality'); // Will be in StatAttribute table
    define('_CLMN_CHR_STAT_ENE_', 'Energy'); // Will be in StatAttribute table
    define('_CLMN_CHR_STAT_CMD_', 'Leadership'); // Will be in StatAttribute table
    define('_CLMN_CHR_PK_KILLS_', _CLMN_CHAR_PK_COUNT_);

define('_TBL_GUILD_', _TBL_GUILD_);
    define('_CLMN_GUILD_NAME_', _CLMN_GUILD_NAME_);
    define('_CLMN_GUILD_MASTER_', _CLMN_GUILD_HOST_ID_);
    define('_CLMN_GUILD_SCORE_', _CLMN_GUILD_SCORE_);
    define('_CLMN_GUILD_LOGO_', _CLMN_GUILD_LOGO_);

define('_TBL_GUILDMEMB_', _TBL_GUILD_MEMBER_);
    define('_CLMN_GUILDMEMB_NAME_', _CLMN_GUILD_MEMBER_GUILD_ID_);
    define('_CLMN_GUILDMEMB_CHAR_', _CLMN_GUILD_MEMBER_CHARACTER_ID_);
    define('_CLMN_GUILDMEMB_STATUS_', _CLMN_GUILD_MEMBER_STATUS_);

// Connection status (OpenMU doesn't have MEMB_STAT equivalent)
// We'll use the Character.State field instead
define('_TBL_MS_', _TBL_CHARACTER_);
    define('_CLMN_CONNSTAT_', _CLMN_CHAR_STATE_);
    define('_CLMN_MS_MEMBID_', _CLMN_CHAR_ACCOUNT_ID_);
    define('_CLMN_MS_GS_', 'ServerName'); // Will be handled differently
    define('_CLMN_MS_IP_', 'IP'); // Will be handled differently

// Account Character (OpenMU doesn't have this table)
// We'll use Character.CharacterSlot instead
define('_TBL_AC_', _TBL_CHARACTER_);
    define('_CLMN_AC_ID_', _CLMN_CHAR_ID_);
    define('_CLMN_GAMEIDC_', _CLMN_CHAR_NAME_);
    define('_CLMN_WHEXPANSION_', _CLMN_CHAR_INVENTORY_EXTENSIONS_);
    define('_CLMN_SECCODE_', _CLMN_ACCOUNT_SECURITY_CODE_);

// ------------------------------------------------------------
// OpenMU custom metadata (classes, filters, maps, pk levels)
// Used by helpers like getPlayerClassAvatar(), returnMapName(), etc.
// ------------------------------------------------------------
if(!isset($custom) || !is_array($custom)) $custom = array();

// Character class metadata indexed by class Number
$custom['character_class'] = array(
    0 => array('Dark Wizard', 'DW', 'dw.jpg', 'base_stats' => array('str' => 18, 'agi' => 18, 'vit' => 15, 'ene' => 30, 'cmd' => 0), 'class_group' => 0),
    1 => array('Soul Master', 'SM', 'dw.jpg', 'base_stats' => array('str' => 18, 'agi' => 18, 'vit' => 15, 'ene' => 30, 'cmd' => 0), 'class_group' => 0),
    3 => array('Grand Master', 'GM', 'dw.jpg', 'base_stats' => array('str' => 18, 'agi' => 18, 'vit' => 15, 'ene' => 30, 'cmd' => 0), 'class_group' => 0),
    7 => array('Soul Wizard', 'SW', 'dw.jpg', 'base_stats' => array('str' => 18, 'agi' => 18, 'vit' => 15, 'ene' => 30, 'cmd' => 0), 'class_group' => 0),
    16 => array('Dark Knight', 'DK', 'dk.jpg', 'base_stats' => array('str' => 28, 'agi' => 20, 'vit' => 25, 'ene' => 10, 'cmd' => 0), 'class_group' => 16),
    17 => array('Blade Knight', 'BK', 'dk.jpg', 'base_stats' => array('str' => 28, 'agi' => 20, 'vit' => 25, 'ene' => 10, 'cmd' => 0), 'class_group' => 16),
    19 => array('Blade Master', 'BM', 'dk.jpg', 'base_stats' => array('str' => 28, 'agi' => 20, 'vit' => 25, 'ene' => 10, 'cmd' => 0), 'class_group' => 16),
    23 => array('Dragon Knight', 'DGK', 'dk.jpg', 'base_stats' => array('str' => 28, 'agi' => 20, 'vit' => 25, 'ene' => 10, 'cmd' => 0), 'class_group' => 16),
    32 => array('Fairy Elf', 'ELF', 'elf.jpg', 'base_stats' => array('str' => 22, 'agi' => 25, 'vit' => 15, 'ene' => 20, 'cmd' => 0), 'class_group' => 32),
    33 => array('Muse Elf', 'ME', 'elf.jpg', 'base_stats' => array('str' => 22, 'agi' => 25, 'vit' => 15, 'ene' => 20, 'cmd' => 0), 'class_group' => 32),
    35 => array('High Elf', 'HE', 'elf.jpg', 'base_stats' => array('str' => 22, 'agi' => 25, 'vit' => 15, 'ene' => 20, 'cmd' => 0), 'class_group' => 32),
    39 => array('Noble Elf', 'NE', 'elf.jpg', 'base_stats' => array('str' => 22, 'agi' => 25, 'vit' => 15, 'ene' => 20, 'cmd' => 0), 'class_group' => 32),
    48 => array('Magic Gladiator', 'MG', 'mg.jpg', 'base_stats' => array('str' => 26, 'agi' => 26, 'vit' => 26, 'ene' => 16, 'cmd' => 0), 'class_group' => 48),
    50 => array('Duel Master', 'DM', 'mg.jpg', 'base_stats' => array('str' => 26, 'agi' => 26, 'vit' => 26, 'ene' => 16, 'cmd' => 0), 'class_group' => 48),
    54 => array('Magic Knight', 'MK', 'mg.jpg', 'base_stats' => array('str' => 26, 'agi' => 26, 'vit' => 26, 'ene' => 16, 'cmd' => 0), 'class_group' => 48),
    64 => array('Dark Lord', 'DL', 'dl.jpg', 'base_stats' => array('str' => 26, 'agi' => 20, 'vit' => 20, 'ene' => 15, 'cmd' => 25), 'class_group' => 64),
    66 => array('Lord Emperor', 'LE', 'dl.jpg', 'base_stats' => array('str' => 26, 'agi' => 20, 'vit' => 20, 'ene' => 15, 'cmd' => 25), 'class_group' => 64),
    70 => array('Empire Lord', 'EL', 'dl.jpg', 'base_stats' => array('str' => 26, 'agi' => 20, 'vit' => 20, 'ene' => 15, 'cmd' => 25), 'class_group' => 64),
    80 => array('Summoner', 'SUM', 'sum.jpg', 'base_stats' => array('str' => 21, 'agi' => 21, 'vit' => 18, 'ene' => 23, 'cmd' => 0), 'class_group' => 80),
    81 => array('Bloody Summoner', 'BS', 'sum.jpg', 'base_stats' => array('str' => 21, 'agi' => 21, 'vit' => 18, 'ene' => 23, 'cmd' => 0), 'class_group' => 80),
    83 => array('Dimension Master', 'DSM', 'sum.jpg', 'base_stats' => array('str' => 21, 'agi' => 21, 'vit' => 18, 'ene' => 23, 'cmd' => 0), 'class_group' => 80),
    87 => array('Dimension Summoner', 'DS', 'sum.jpg', 'base_stats' => array('str' => 21, 'agi' => 21, 'vit' => 18, 'ene' => 23, 'cmd' => 0), 'class_group' => 80),
    96 => array('Rage Fighter', 'RF', 'rf.jpg', 'base_stats' => array('str' => 32, 'agi' => 27, 'vit' => 25, 'ene' => 20, 'cmd' => 0), 'class_group' => 96),
    98 => array('Fist Master', 'FM', 'rf.jpg', 'base_stats' => array('str' => 32, 'agi' => 27, 'vit' => 25, 'ene' => 20, 'cmd' => 0), 'class_group' => 96),
    102 => array('Fist Blazer', 'FB', 'rf.jpg', 'base_stats' => array('str' => 32, 'agi' => 27, 'vit' => 25, 'ene' => 20, 'cmd' => 0), 'class_group' => 96),
    112 => array('Grow Lancer', 'GL', 'gl.jpg', 'base_stats' => array('str' => 30, 'agi' => 30, 'vit' => 25, 'ene' => 24, 'cmd' => 0), 'class_group' => 112),
    114 => array('Mirage Lancer', 'ML', 'gl.jpg', 'base_stats' => array('str' => 30, 'agi' => 30, 'vit' => 25, 'ene' => 24, 'cmd' => 0), 'class_group' => 112),
    118 => array('Shining Lancer', 'SL', 'gl.jpg', 'base_stats' => array('str' => 30, 'agi' => 30, 'vit' => 25, 'ene' => 24, 'cmd' => 0), 'class_group' => 112),
    128 => array('Rune Mage', 'RW', 'rw.jpg', 'base_stats' => array('str' => 13, 'agi' => 18, 'vit' => 14, 'ene' => 40, 'cmd' => 0), 'class_group' => 128),
    129 => array('Rune Spell Master', 'RSM', 'rw.jpg', 'base_stats' => array('str' => 13, 'agi' => 18, 'vit' => 14, 'ene' => 40, 'cmd' => 0), 'class_group' => 128),
    131 => array('Grand Rune Master', 'GRM', 'rw.jpg', 'base_stats' => array('str' => 13, 'agi' => 18, 'vit' => 14, 'ene' => 40, 'cmd' => 0), 'class_group' => 128),
    135 => array('Majestic Rune Wizard', 'MRW', 'rw.jpg', 'base_stats' => array('str' => 13, 'agi' => 18, 'vit' => 14, 'ene' => 40, 'cmd' => 0), 'class_group' => 128),
    144 => array('Slayer', 'SLR', 'sl.jpg', 'base_stats' => array('str' => 28, 'agi' => 30, 'vit' => 15, 'ene' => 10, 'cmd' => 0), 'class_group' => 144),
    145 => array('Royal Slayer', 'SLRR', 'sl.jpg', 'base_stats' => array('str' => 28, 'agi' => 30, 'vit' => 15, 'ene' => 10, 'cmd' => 0), 'class_group' => 144),
    147 => array('Master Slayer', 'MSLR', 'sl.jpg', 'base_stats' => array('str' => 28, 'agi' => 30, 'vit' => 15, 'ene' => 10, 'cmd' => 0), 'class_group' => 144),
    151 => array('Slaughterer', 'SLTR', 'sl.jpg', 'base_stats' => array('str' => 28, 'agi' => 30, 'vit' => 15, 'ene' => 10, 'cmd' => 0), 'class_group' => 144),
    160 => array('Gun Crusher', 'GC', 'gc.jpg', 'base_stats' => array('str' => 28, 'agi' => 30, 'vit' => 15, 'ene' => 10, 'cmd' => 0), 'class_group' => 160),
    161 => array('Gun Breaker', 'GB', 'gc.jpg', 'base_stats' => array('str' => 28, 'agi' => 30, 'vit' => 15, 'ene' => 10, 'cmd' => 0), 'class_group' => 160),
    163 => array('Master Gun Breaker', 'MGB', 'gc.jpg', 'base_stats' => array('str' => 28, 'agi' => 30, 'vit' => 15, 'ene' => 10, 'cmd' => 0), 'class_group' => 160),
    167 => array('Heist Gun Crusher', 'HGC', 'gc.jpg', 'base_stats' => array('str' => 28, 'agi' => 30, 'vit' => 15, 'ene' => 10, 'cmd' => 0), 'class_group' => 160),
    176 => array('Light Wizard', 'LIW', 'liw.jpg', 'base_stats' => array('str' => 19, 'agi' => 19, 'vit' => 15, 'ene' => 30, 'cmd' => 0), 'class_group' => 176),
    177 => array('Light Master', 'LIM', 'liw.jpg', 'base_stats' => array('str' => 19, 'agi' => 19, 'vit' => 15, 'ene' => 30, 'cmd' => 0), 'class_group' => 176),
    179 => array('Shining Wizard', 'SHW', 'liw.jpg', 'base_stats' => array('str' => 19, 'agi' => 19, 'vit' => 15, 'ene' => 30, 'cmd' => 0), 'class_group' => 176),
    183 => array('Luminous Wizard', 'LUW', 'liw.jpg', 'base_stats' => array('str' => 19, 'agi' => 19, 'vit' => 15, 'ene' => 30, 'cmd' => 0), 'class_group' => 176),
    192 => array('Lemuria Mage', 'LEM', 'lem.jpg', 'base_stats' => array('str' => 18, 'agi' => 18, 'vit' => 19, 'ene' => 30, 'cmd' => 0), 'class_group' => 192),
    193 => array('Warmage', 'WAM', 'lem.jpg', 'base_stats' => array('str' => 18, 'agi' => 18, 'vit' => 19, 'ene' => 30, 'cmd' => 0), 'class_group' => 192),
    195 => array('Archmage', 'ARM', 'lem.jpg', 'base_stats' => array('str' => 18, 'agi' => 18, 'vit' => 19, 'ene' => 30, 'cmd' => 0), 'class_group' => 192),
    199 => array('Mystic Mage', 'MYM', 'lem.jpg', 'base_stats' => array('str' => 18, 'agi' => 18, 'vit' => 19, 'ene' => 30, 'cmd' => 0), 'class_group' => 192),
    208 => array('Illusion Knight', 'IK', 'ik.jpg', 'base_stats' => array('str' => 25, 'agi' => 28, 'vit' => 15, 'ene' => 10, 'cmd' => 0), 'class_group' => 208),
    209 => array('Mirage Knight', 'MIK', 'ik.jpg', 'base_stats' => array('str' => 25, 'agi' => 28, 'vit' => 15, 'ene' => 10, 'cmd' => 0), 'class_group' => 208),
    211 => array('Illusion Master', 'IM', 'ik.jpg', 'base_stats' => array('str' => 25, 'agi' => 28, 'vit' => 15, 'ene' => 10, 'cmd' => 0), 'class_group' => 208),
    215 => array('Mystic Knight', 'MYK', 'ik.jpg', 'base_stats' => array('str' => 25, 'agi' => 28, 'vit' => 15, 'ene' => 10, 'cmd' => 0), 'class_group' => 208),
);

// Class group filter labels
$custom['rankings_classgroup_filter'] = array(
    0 => 'rankings_filter_2',
    16 => 'rankings_filter_3',
    32 => 'rankings_filter_4',
    48 => 'rankings_filter_5',
    64 => 'rankings_filter_6',
    80 => 'rankings_filter_7',
    96 => 'rankings_filter_8',
    112 => 'rankings_filter_9',
    128 => 'rankings_filter_10',
    144 => 'rankings_filter_11',
    160 => 'rankings_filter_12',
    176 => 'rankings_filter_13',
    192 => 'rankings_filter_14',
    208 => 'rankings_filter_15',
);

// PK level labels
$custom['pk_level'] = array(
    0 => 'Normal',
    1 => 'Hero',
    2 => 'Hero',
    3 => 'Commoner',
    4 => 'Warning',
    5 => 'Murder',
    6 => 'Outlaw',
);

// Classic map names (used by returnMapName)
$custom['map_list'] = array(
    0 => 'Lorencia',
    1 => 'Dungeon',
    2 => 'Devias',
    3 => 'Noria',
    4 => 'Lost Tower',
    6 => 'Arena',
    7 => 'Atlans',
    8 => 'Tarkan',
    9 => 'Devil Square',
    10 => 'Icarus',
    11 => 'Blood Castle',
    12 => 'Blood Castle',
    13 => 'Blood Castle',
    14 => 'Blood Castle',
    15 => 'Blood Castle',
    16 => 'Blood Castle',
    17 => 'Blood Castle',
    18 => 'Chaos Castle',
    19 => 'Chaos Castle',
    20 => 'Chaos Castle',
    21 => 'Chaos Castle',
    22 => 'Chaos Castle',
    23 => 'Chaos Castle',
    24 => 'Kalima 1',
    25 => 'Kalima 2',
    26 => 'Kalima 3',
    27 => 'Kalima 4',
    28 => 'Kalima 5',
    29 => 'Kalima 6',
    30 => 'Valley of Loren',
    31 => 'Land of Trials',
    32 => 'Devil Square',
    33 => 'Aida',
    34 => 'Crywolf Fortress',
    36 => 'Kalima 7',
    37 => 'Kanturu',
    38 => 'Kanturu',
    39 => 'Kanturu',
    40 => 'Silent Map',
    41 => 'Balgass Barracks',
    42 => 'Balgass Refuge',
    45 => 'Illusion Temple',
    46 => 'Illusion Temple',
    47 => 'Illusion Temple',
    48 => 'Illusion Temple',
    49 => 'Illusion Temple',
    50 => 'Illusion Temple',
    51 => 'Elbeland',
    52 => 'Blood Castle',
    53 => 'Chaos Castle',
    56 => 'Swamp of Calmness',
    57 => 'Raklion',
    58 => 'Raklion Boss',
    62 => 'Santa\'s Village',
    63 => 'Vulcanus',
    64 => 'Duel Arena',
    65 => 'Doppelganger',
    66 => 'Doppelganger',
    67 => 'Doppelganger',
    68 => 'Doppelganger',
    69 => 'Imperial Guardian',
    70 => 'Imperial Guardian',
    71 => 'Imperial Guardian',
    72 => 'Imperial Guardian',
    79 => 'Loren Market',
    80 => 'Karutan 1',
    81 => 'Karutan 2',
    82 => 'Doppelganger',
    91 => 'Acheron',
    92 => 'Acheron',
    95 => 'Debenter',
    96 => 'Debenter',
    97 => 'Chaos Castle',
    98 => 'Ilusion Temple',
    99 => 'Ilusion Temple',
    100 => 'Uruk Mountain',
    101 => 'Uruk Mountain',
    102 => 'Tormented Square',
    103 => 'Tormented Square',
    104 => 'Tormented Square',
    105 => 'Tormented Square',
    106 => 'Tormented Square',
    110 => 'Nars',
    112 => 'Ferea',
    113 => 'Nixie Lake',
    114 => 'Quest Zone',
    115 => 'Labyrinth',
    116 => 'Deep Dungeon',
    117 => 'Deep Dungeon',
    118 => 'Deep Dungeon',
    119 => 'Deep Dungeon',
    120 => 'Deep Dungeon',
    121 => 'Quest Zone',
    122 => 'Swamp of Darkness',
    123 => 'Kubera Mine',
    124 => 'Kubera Mine',
    125 => 'Kubera Mine',
    126 => 'Kubera Mine',
    127 => 'Kubera Mine',
    128 => 'Atlans Abyss',
    129 => 'Atlans Abyss 2',
    130 => 'Atlans Abyss 3',
    131 => 'Scorched Canyon',
    132 => 'Crimson Flame Icarus',
    133 => 'Temple of Arnil',
    134 => 'Aida Gray',
    135 => 'Old Kethotum',
    136 => 'Burning Kethotum',
    137 => 'Kanturu Undergrounds',
    138 => 'Volcano Ignis',
    139 => 'Boss Battle',
    140 => 'Bloody Tarkan',
);

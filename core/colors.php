<?
  /* Color definitions */
  define('COLOR_RED',         0xCC3300FF);
  define('COLOR_YELLOW',      0xFFFF00FF);
  define('COLOR_GREEN',       0x00FF00FF);
  define('COLOR_GRAY',        0x777777FF);
  define('COLOR_WHITE',       0xFFFFFFFF);
  define('COLOR_LIGHTGREY',   0xCCCCCCFF);
  define('COLOR_PURPLE',      0x800080FF);
  define('COLOR_PINK',        0xFFB6C1FF);
  define('COLOR_LIGHTGREEN',  0x00FF7FFF);
  define('COLOR_DARKBLUE',    0x00080AFF);
  define('COLOR_BLACK',       0x212121FF);
  define('COLOR_GREENYELLOW', 0xADFF2FFF);
  define('COLOR_VLIGHTBLUE',  0x66FFFFFF);
  define('COLOR_ORANGE',      0xFFB648CC);
  define('COLOR_LIGHTBLUE',   0x87CEFAFF);
  define('COLOR_BRIGHTORANGE',0xFF9933AA);

  /* Messages */
  define('COLOR_USAGE',      COLOR_RED);
  define('COLOR_INFO',       COLOR_GRAY);
  define('COLOR_LOCALOOC',   0x99CCFFFF);
  define('COLOR_GLOBALOOC',  0x8FFFCAFF);
  define('COLOR_PNOTFOUND',  COLOR_RED);
  define('COLOR_PNOTAUTHED', COLOR_RED);
  define('COLOR_WHISPER',    0xFFFF00FF);
  define('COLOR_ERROR',      COLOR_RED);
  define('COLOR_ADMINCHAT',  0xFF9933AA);
  define('COLOR_ACTION',     0xC2A2DAAA);

  /* Stats */
  define('COLOR_STATS_DECORATION',        COLOR_LIGHTGREEN);
  define('COLOR_STATS',                   COLOR_WHITE);
  define('COLOR_SKILLS_DECORATION',       COLOR_LIGHTGREEN);
  define('COLOR_SKILLS',                  COLOR_WHITE);
  define('COLOR_YOUHAVE_UPGRADE_POINTS',  COLOR_YELLOW);
  define('COLOR_NOT_UPGRADE_POINTS',      COLOR_RED);
  define('COLOT_UPGRADE_INTERROR',        COLOR_RED);
  define('COLOR_UPGRADE_DONE',            COLOR_GREEN);
  define('COLOR_SPEEDOMETER_SET',         COLOR_GREEN);
  define('COLOR_SPEEDOMETER_UNKNOWN',     COLOR_RED);


  /* Vehicles */
  define('COLOR_CARSHOP_HEADER',  COLOR_GREEN);
  define('COLOR_CARSHOP_INFO',    COLOR_WHITE);
  define('COLOR_ROLLING_WINDOWS', COLOR_ACTION);
  define('COLOR_LOCKING_WINDOWS', COLOR_ACTION);

  /* Bank */
  define('COLOR_BANK_FREEZED',        COLOR_RED);
  define('COLOR_BANK_BALANCE',        COLOR_WHITE);
  define('COLOR_BANK_DEPOSIT',        COLOR_WHITE);
  define('COLOR_BANK_WITHDRAW',       COLOR_WHITE);
  define('COLOR_BANK_TRANSFER',       COLOR_YELLOW);
  define('COLOR_INVALID_AMOUNT',      COLOR_RED);
  define('COLOR_NOTENOUGH_MONEY',     COLOR_RED);
  define('COLOR_NOTENOUGH_MONEYBANK', COLOR_RED);

  /* Factions */
  define('COLOR_INVITE_ALREADYFACTION', COLOR_RED);
  define('COLOR_INVITE_TOOYOUNG',       COLOR_RED);
  define('COLOR_FACTION_DISMATCH',      COLOR_RED);
  define('COLOR_RANKUP_MAX',            COLOR_RED);
  define('COLOR_RANKDOWN_MIN',          COLOR_RED);
  define('COLOR_RANKDOWN_DISALLOW',     COLOR_RED);
  define('COLOR_INVITE_FULL',           COLOR_RED);
  define('COLOR_INVITE_SUCCESS',        COLOR_VLIGHTBLUE);
  define('COLOR_UNINVITE_SUCCESS',      COLOR_VLIGHTBLUE);
  define('COLOR_RANKUP_SUCCESS',        COLOR_VLIGHTBLUE);
  define('COLOR_RANKDOWN_SUCCESS',      COLOR_VLIGHTBLUE);

  /* Players */
  define('COLOR_CIVILIAN',          COLOR_WHITE);
  define('COLOR_TOO_FAR',           COLOR_RED);
  define('COLOR_PAYMENT_TOO_HIGH',  COLOR_RED);
  define('COLOR_YOU_PAY',           COLOR_YELLOW);
  define('COLOR_GET_PAID',          COLOR_YELLOW);

  /* Houses */
  define('COLOR_HOUSE_CANTENTER',         COLOR_RED);
  define('COLOR_HOUSE_INVALID_ROOM',      COLOR_RED);
  define('COLOR_HOUSE_OPEN_DISALLOWED',   COLOR_RED);
  define('COLOR_HOUSE_CLOSE_DISALLOWED',  COLOR_RED);
  define('COLOR_HOUSE_OPENED',            COLOR_GREEN);
  define('COLOR_HOUSE_CLOSED',            COLOR_GREEN);
  define('COLOR_HOUSE_ALREADYOWN',        COLOR_RED);
  define('COLOR_HOUSE_DONTOWN',           COLOR_RED);
  define('COLOR_HOUSE_NOTFORSALE',        COLOR_RED);
  define('COLOR_HOUSE_BOUGHT',            COLOR_GREEN);
  define('COLOR_HOUSE_SELL_DISALLOWED',   COLOR_RED);
  define('COLOR_HOUSE_INVALID_PRICE',     COLOR_RED);
  define('COLOR_HOUSE_FORSALE',           COLOR_GREENYELLOW);
  define('COLOR_HOUSE_BUYTHEIROWN',       COLOR_RED);
  define('COLOR_HOUSE_SELLING_HQ',        COLOR_RED);
  define('COLOR_HOUSE_UNSELL_DISALLOWED', COLOR_RED);
  define('COLOR_HOUSE_HASNTFRIDGE',       COLOR_RED);
  define('COLOR_HOUSE_NOTNEARFRIDGE',     COLOR_RED);
  define('COLOR_HOUSE_FRIDGESTATS',       COLOR_YELLOW);
  define('COLOR_HOUSE_HASNTKITCHEN',      COLOR_RED);
  define('COLOR_HOUSE_NOTNEARKITCHEN',    COLOR_RED);
  define('COLOR_HOUSE_NOTENOUGHFOOD',     COLOR_RED);
  define('COLOR_HOUSE_ALREADYCOOKING',    COLOR_RED);

  /* Ownership */
  define('COLOR_OWNERSHIP_ADQUIRED',      COLOR_GREEN);
  define('COLOR_OWNERSHIP_SOLD',          COLOR_GREEN);

  /* Gym */
  define('COLOR_GYM_EARNT',               COLOR_YELLOW);
  define('COLOR_GYM_MACHINE_USED',        COLOR_RED);

  /* Admin */
  define('COLOR_KICK',                    COLOR_ORANGE);
  define('COLOR_ADMINLIST',               COLOR_LIGHTBLUE);
  define('COLOR_ACCESS_DENIED',           COLOR_RED);
  define('COLOR_ACCOUNT_NOTFOUND',        COLOR_RED);
  define('COLOR_ACCOUNT_NOTBANNED',       COLOR_RED);
  define('COLOR_UNSUSPEND',               COLOR_LIGHTBLUE);
  define('COLOR_ADMINCHAT',               COLOR_BRIGHTORANGE);
  define('COLOR_AJAIL',                   COLOR_RED);
  define('COLOR_AJAIL_INVALID_TIME',      COLOR_RED);
  define('COLOR_AUNJAIL',                 COLOR_GRAY);
  define('COLOR_GIVECASH_INVALID_AMOUNT', COLOR_RED);
  define('COLOR_GIVECASH',                COLOR_YELLOW);
  define('COLOR_SHOWME_WRONGPARAM',       COLOR_RED);
  define('COLOR_SHOWME_CHANGED',          COLOR_LIGHTBLUE);
  define('COLOR_SETFACTION',              COLOR_YELLOW);
  define('COLOR_SETRANK_NOFACTION',       COLOR_RED);
  define('COLOR_SETRANK_INVALID',         COLOR_RED);
  define('COLOR_SETRANK',                 COLOR_YELLOW);
?>

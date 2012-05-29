<?
require_once('account.php');
require_once('player.php');

/**
 ** Player constants
 **/
define ('MAX_HUNGER_POINTS', 5000);
define ('MAX_STRENGTH_POINTS', 1000);

define ('SKILL_COOKING',      0x000);
define ('SKILL_LUCK',         0x001);
define ('SKILL_ALCOHOLIC',    0x002);
define ('SKILL_DRUGSADDICT',  0x003);
define ('SKILL_PROFKILLER',   0x004);
define ('SKILL_GUNSDEALER',   0x005);
define ('SKILL_DRUGSDEALER',  0x006);
define ('SKILL_NEGSKILLS',    0x007);
define ('SKILL_MAXUPGRADE',   0x008);

define ('SKILL_FARMING',      0x008);
define ('SKILL_FISHING',      0x009);
define ('SKILL_MAX',          0x00A);

define ('GUN_SHIFT',          6);
define ('GUN_MASK',           0x0000003F);
define ('WEAPON_MASK',        0x03FFFFFF);


/**
 ** class Players
 **
 ** Connected players manager. Lists to iterate players with specific flags and functions to access them.
 **
 ** Methods:
 ** - Get:        Gets the list of all connected players
 ** - GetJQ:      Gets the list of players receiving join/quits
 ** - GetOOCers:  Gets the list of players who receive OOC
 ** - FindByID:   Finds a player by their ID
 ** - FindByName: Finds a player by their Name
 ** - Find:       Finds player by id or name, allowing part of name
 **
 **/
class Players
{
  private static $players = array();
  private static $players_jq = array();
  private static $players_ooc = array();
  private static $players_by_id;
  private static $incar_draws = array();
  private static $skills = array();
  private static $skills_dbname = array();
  private static $skills_name = array();
  private static $upgrade_menu;
  private static $upgrade_draws;

  public static function Init()
  {
    /* Initialize the skills */
    Players::RegisterSkill(SKILL_COOKING,     5,  'cooking',      'Cooking');
    Players::RegisterSkill(SKILL_LUCK,        10, 'luck',         'Luck');
    Players::RegisterSkill(SKILL_ALCOHOLIC,   5,  'alcoholic',    'Alcoholic');

    $skill = Players::RegisterSkill(SKILL_DRUGSADDICT, 4,  'drugsaddict',  'Drugs addict');
    $skill->SetReqLevel(2, 4);
    $skill->SetReqLevel(3, 7);
    $skill->SetReqLevel(4, 11);

    $skill = Players::RegisterSkill(SKILL_PROFKILLER,  6,  'profkiller',   'Professional killer');
    $skill->SetReqLevel(2, 6);
    $skill->SetReqLevel(3, 8);
    $skill->SetReqLevel(4, 11);
    $skill->SetReqLevel(5, 15);
    $skill->SetReqLevel(6, 20);

    $skill = Players::RegisterSkill(SKILL_GUNSDEALER,   6,  'gunsdealer',   'Guns dealer');
    $skill->SetReqLevel(2, 3);
    $skill->SetReqLevel(3, 5);
    $skill->SetReqLevel(4, 8);
    $skill->SetReqLevel(5, 12);
    $skill->SetReqLevel(6, 17);

    $skill = Players::RegisterSkill(SKILL_DRUGSDEALER,  4,  'drugsdealer',  'Drugs dealer');
    $skill->SetReqLevel(2, 3);
    $skill->SetReqLevel(3, 6);
    $skill->SetReqLevel(4, 10);

    Players::RegisterSkill(SKILL_NEGSKILLS,    5,  'negskills',    'Negotiation skills',   SKILL_LUCK,   5);

    Players::RegisterSkill(SKILL_FARMING, 0, 'farming', 'Farming');
    Players::RegisterSkill(SKILL_FISHING, 0, 'fishing', 'Fishing');

    /* Create the upgrade menu */
    $menu = new Menu(null, 'Upgrades', array('Players', 'menuUpgradesEnter'), array('Players', 'menuUpgradesExit'), 1, new Position(50, 120), 150);
    foreach (Players::$skills as $skill)
    {
      if ($skill->flag < SKILL_MAXUPGRADE)
        $menu->Add($skill->name, 0, ROW_ACTION, array('Players', 'menuUpgradesSelect'));
    }
    Players::$upgrade_menu = $menu;

    /* Create the upgrade textdraws list */
    Players::$upgrade_draws = array_fill(0, MAX_PLAYERS, null);
    
    /* Register timers, callbacks and commands */
    AddTimer(array('Players', 'Update'), 500, 1);
    AddTimer(array('Players', 'Stream'), 800, 1);
    Callbacks::Instance()->Register(cOnPlayerConnect, null, array('Players', 'OnPlayerConnect'), 0);
    Callbacks::Instance()->Register(cOnPlayerDisconnect, null, array('Players', 'OnPlayerDisconnect'), 5);
    Callbacks::Instance()->Register(cOnPlayerDeath, null, array('Players', 'OnPlayerDeath'));
    Callbacks::Instance()->Register(cOnPlayerSpawn, null, array('Players', 'OnPlayerSpawn'));
    Callbacks::Instance()->Register(cOnPlayerRequestClass, null, array('Players', 'OnPlayerRequestClass'), -1000);
    Callbacks::Instance()->Register(cOnPlayerRequestSpawn, null, array('Players', 'OnPlayerRequestSpawn'));
    Callbacks::Instance()->Register(cOnPlayerEnterVehicle, null, array('Players', 'OnPlayerEnterVehicle'), -1000);
    Callbacks::Instance()->Register(cOnPlayerKeyStateChange, null, array('Players', 'OnPlayerKeyStatechange'));

    CommandHandler::Register('togjq',       0, null, array('Players', 'cmdTogjq'),        '',               1, -1000);
    CommandHandler::Register('togooc',      0, null, array('Players', 'cmdTogooc'),       '',               1, -1000);
    CommandHandler::Register('die',         0, null, array('Players', 'cmdDie'),          '',               1, -1000);
    CommandHandler::Register('stats',       0, null, array('Players', 'cmdStats'),        '',               1, -1000);
    CommandHandler::Register('pay',         2, null, array('Players', 'cmdPay'),          '[ID] [amount]',  1, -1000);
    CommandHandler::Register('skills',      0, null, array('Players', 'cmdSkills'),       '',               1, -1000);
    CommandHandler::Register('upgrade',     0, null, array('Players', 'cmdUpgrade'),      '',               1, -1000);
    CommandHandler::Register('speedometer', 1, null, array('Players', 'cmdSpeedometer'),  '[kmh/mph]',      1, -1000);
    CommandHandler::Register('autowalk',    0, null, array('Players', 'cmdAutoWalk'),     '',               1, -1000);
    
    Players::$players_by_id = array_fill(0, MAX_PLAYERS, null);

    /* Create the incar textdraws */
    for ($i = 0; $i < MAX_PLAYERS; $i++)
    {
      Players::$incar_draws[$i] = TextDrawCreate(549.000000,393.000000, '.');
      TextDrawUseBox(Players::$incar_draws[$i],1);
      TextDrawBoxColor(Players::$incar_draws[$i],0x00000066);
      TextDrawTextSize(Players::$incar_draws[$i],627.000000,-10.000000);
      TextDrawAlignment(Players::$incar_draws[$i],0);
      TextDrawBackgroundColor(Players::$incar_draws[$i],0x000000ff);
      TextDrawFont(Players::$incar_draws[$i],1);
      TextDrawLetterSize(Players::$incar_draws[$i],0.499999,1.100000);
      TextDrawColor(Players::$incar_draws[$i],0xffffffff);
      TextDrawSetOutline(Players::$incar_draws[$i],1);
      TextDrawSetProportional(Players::$incar_draws[$i],1);
      TextDrawSetShadow(Players::$incar_draws[$i],1);
      TextDrawHideForAll(Players::$incar_draws[$i]);
    }
  }

  /**
   ** Methods
   **/
  private static function RegisterSkill($flag, $maxlevel, $dbname, $name, $reqskill = -1, $reqskill_level = -1)
  {
    $skill = new Skill($flag, $maxlevel, $dbname, $name, $reqskill, $reqskill_level);
    Players::$skills[$flag] = $skill;
    Players::$skills_dbname[$dbname] = $skill;
    Players::$skills_name[$name] = $skill;
    return $skill;
  }

  public static function GetSkillName($flag)
  {
    return Players::$skills[$flag]->name;
  }

  public static function GetSkillDBName($flag)
  {
    return Players::$skills[$flag]->dbname;
  }

  public static function GetSkillFlag($dbname)
  {
    return Players::$skills_dbname[$dbname]->flag;
  }

  public static function GetSkillByName($name)
  {
    return Players::$skills_name[$name];
  }

  public static function GetIncarDraw($playerid)
  {
    return Players::$incar_draws[$playerid];
  }

  public static function FindByDBID($id)
  {
    foreach (Players::$players as $player)
    {
      if ($player->account && $player->account->Authed() && $player->account->ID() == $id)
        return $player;
    }
    return null;
  }

  public static function FindByID($playerid)
  {
    return Players::$players_by_id[$playerid];
  }

  public static function FindByName($playername)
  {
    foreach (Players::$players as $player)
    {
      if ($player->name == $playername)
        return $player;
    }
    return null;
  }

  public static function Find($id)
  {
    if (ctype_digit($id))
      return Players::FindByID($id);

    $result = null;
    $len = strlen($id);

    foreach (Players::$players as $player)
    {
      if (!strncasecmp($id, $player->name, $len))
      {
        if ($result)
          return null;
        $result = $player;
      }
    }

    return $result;
  }

  public static function Get()
  {
    return Players::$players;
  }

  public static function GetOOCers()
  {
    return Players::$players_ooc;
  }

  public static function GetJQ()
  {
    return Players::$players_jq;
  }

  public static function CheckPayment(Player $player, $amount)
  {
    if ($amount < 1)
      $player->Send(COLOR_INVALID_AMOUNT, '[ERROR] Invalid amount');
    else if ($amount > $player->GetMoney())
      $player->Send(COLOR_NOTENOUGH_MONEY, '[ERROR] You don\'t have this amount of money');
    else
      return true;
    return false;
  }

  public static function CheckPayBank(Player $player, $amount, Player $target = null)
  {
    if ($amount < 1)
      $player->Send(COLOR_INVALID_AMOUNT, '[ERROR] Invalid amount');
    else if ($player->BankFreezed())
      $player->Send(COLOR_BANK_FREEZED, '[ERROR] Your bank account has been freezed, operation not completed');
    else if ($amount > $player->GetBank())
      $player->Send(COLOR_NOTENOUGH_MONEYBANK, '[ERROR] You don\'t have this amount of money in your bank account');
    else if ($target && $target->BankFreezed())
      $player->Send(COLOR_BANK_FREEZED, '[ERROR] Given player has his bank account freezed, operation cancelled');
    else
      return true;
    return false;
  }

  /**
   ** Internal managing functions
   **/
  public static function Update()
  {
    /* Updates need to be done respecting ID order */
    foreach (Players::$players_by_id as $player)
    {
      if ($player != null)
        $player->Update();
    }
  }

  public static function Stream()
  {
    foreach (Players::$players as $player)
      $player->Stream();
  }

  public static function AddToOOC($player)
  {
    $player->togooc = true;
    Players::$players_ooc[$player->id] = $player;
  }

  public static function DelFromOOC($player)  // only for premiums
  {
    $player->togooc = false;
    if (isset(Players::$players_ooc[$player->id]))
      unset(Players::$players_ooc[$player->id]);
  }

  public static function AddToJQ($player)
  {
    $player->togjq = true;
    Players::$players_jq[$player->id] = $player;
  }

  public static function DelFromJQ($player)
  {
    $player->togjq = false;
    if (isset(Players::$players_jq[$player->id]))
      unset(Players::$players_jq[$player->id]);
  }

  private static function MakeUpgradeDraw(Player $player)
  {
    $draw = TextDrawCreate(222.000000, 125.000000, '...');
    if ($draw == INVALID_TEXT_DRAW)
      return null;
    
    TextDrawUseBox($draw, true);
    TextDrawBoxColor($draw, 0x000000bb);
    TextDrawTextSize($draw, 424.000000, 20.000000);
    TextDrawAlignment($draw, 0);
    TextDrawBackgroundColor($draw, 0x000000ff);
    TextDrawFont($draw, true);
    TextDrawLetterSize($draw, 0.450000, 1.76);
    TextDrawColor($draw, 0x495461dd);
    TextDrawSetOutline($draw, true);
    TextDrawSetProportional($draw, true);

    $str = 'Upgrade to level~n~';
    foreach (Players::$skills as $skill)
    {
      if ($skill->flag == SKILL_MAXUPGRADE)
        break;

      $nextlevel = $player->GetSkill($skill->flag) + 1;

      if ($skill->maxlevel < $nextlevel)
        $str .= '~y~ Already highest level~n~';
      else if ($player->GetLevel() < $skill->reqlevels[$nextlevel])
        $str .= "~r~ Player level {$skill->reqlevels[$nextlevel]} required~n~";
      else if ($skill->reqskill != -1 && $player->GetSkill($skill->reqskill) < $skill->reqskill_level)
      {
        $reqskill = Players::GetSkillName($skill->reqskill);
        $str .= "~r~ {$reqskill} level {$skill->reqskill_level} required~n~";
      }
      else
        $str .= "~w~ {$nextlevel} / {$skill->maxlevel}~n~";
    }

    TextDrawSetString($draw, $str);
    return $draw;
  }


   /**
   ** Commands
   **/
  public static function cmdTogjq(Player $player, $numparams, $params)
  {
    if ($player->togjq == false)
    {
      Players::AddToJQ($player);
      $player->SendInfo('Join/quits are now enabled');
    }
    else
    {
      Players::DelFromJQ($player);
      $player->SendInfo('Join/quits are now disabled');
    }

    return COMMAND_BREAK;
  }

  public static function cmdTogooc(Player $player, $numparams, $params)
  {
    if ($player->togooc == false)
    {
      Players::AddToOOC($player);
      $player->SendInfo('Global OOC chat enabled for you');
    }
    else
    {
      Players::DelFromOOC($player);
      $player->SendInfo('Global OOC chat disabled for you');
    }
  }

  public static function cmdDie(Player $player, $numparams, $params)
  {
    if ($player->timedeath > 0)
    {
      $diff = time() - $player->timedeath;
      if ($diff > 10)
      {
        Messages::SendNear($player, COLOR_ACTION, "{$player->name} gave up fighting for his life and accepted his own death");
        $player->Kill();
      }
    }
    return COMMAND_BREAK;
  }

  public static function cmdSpeedometer(Player $player, $numparams, $params)
  {
    $type = null;

    if (!strcasecmp($params[1], 'kmh'))
      $type = 'kmh';
    else if (!strcasecmp($params[1], 'mph'))
      $type = 'mph';

    if ($type != null)
    {
      $player->SetSpeedometer($params[1]);
      $player->Send(COLOR_SPEEDOMETER_SET, "[SPEEDOMETER] Your speedometer has been set to {$type}");
    }
    else
      $player->Send(COLOR_SPEEDOMETER_UNKNOWN, '[ERROR] Unknown speedometer type, use kmh or mph');

    return COMMAND_BREAK;
  }

  /** TODO: Remove this and use a well-done function with Animations class and Skins manager */
  public static function cmdAutoWalk(Player $player, $numparams, $params)
  {
    if ($player->GetSex() == 'M')
      ApplyAnimation($player->id,"PED","WALK_gang1",4.1,1,1,1,1,1);
    else
      ApplyAnimation($player->id,"PED","WOMAN_walksexy",4.1,1,1,1,1,1);
    return COMMAND_BREAK;
  }

  public static function cmdPay(Player $player, $numparams, $params)
  {
    if ($target = Core::FindPlayer($player, $params[1]))
    {
      if ($player->Position()->DistanceTo($target->Position()) > 5)
      {
        $player->Send(COLOR_TOO_FAR, '[ERROR] Given player is too far');
        return COMMAND_BREAK;
      }
      $amount = (int)$params[2];
      if ($amount > 100000)
      {
        $player->Send(COLOR_PAYMENT_TOO_HIGH, '[ERROR] You can pay a max of 100,000$');
        return COMMAND_BREAK;
      }

      if (Players::CheckPayment($player, $amount))
      {
        $money = Core::FixIntegerDots($amount);
        $player->GiveMoney(-$amount);
        $player->Send(COLOR_YOU_PAY, "* You have given {$money}$ to {$target->name}");
        $target->GiveMoney($amount);
        $target->Send(COLOR_GET_PAID, "* {$player->name} has given you {$money}$");
        Messages::SendNear($player, COLOR_ACTION, "{$player->name} takes some money and gives it to {$target->name}");
      }
    }

    return COMMAND_BREAK;
  }

  /**
  ** Stats must show:
  ** Name(ID)[], CurrentName[] (for people who changed their name), Money[], Bank[], Level[], Deaths[], AcceptedDeaths[], SavedDeaths[],
  ** SpawnHealth[default 40], Level[], Experience[XX/YY], Phone[], Job[],
  ** Faction[], Rank[], Wife/Husband[], Sex[], Age[].
  **/
  public static function cmdStats(Player $player, $numparams, $params)
  {
    $money      = Core::FixIntegerDots($player->GetMoney());
    $bank       = Core::FixIntegerDots($player->GetBank());
    $level      = $player->GetLevel();
    $exp        = $player->GetExperience();
    $married    = $player->GetMarried();
    $hspawn     = 100 - $player->GetInjures();
    $age        = $player->GetAge();
    $faction    = $player->GetFaction(false);
    $hunger     = (int)($player->GetHunger() / 10);
    $strength   = $player->GetStrength();

    /* Find their marriage */
    if ($married != null)
    {
      if ($married['sex'] == 'M')
        $wordmarried = 'Husband';
      else
        $wordmarried = 'Wife';
      $marryname = $married['name'];
    }
    else
    {
      if ($player->GetSex() == 'M')
        $wordmarried = 'Wife';
      else
        $wordmarried = 'Husband';
      $marryname = '';
    }

    /* Complete the sex word */
    if ($player->GetSex() == 'M')
      $sex = 'Male';
    else
      $sex = 'Female';

    /* Find their faction name */
    if ($faction == null)
    {
      $facname = 'Civilian';
      $rank = '';
    }
    else
    {
      $facname = $faction->GetName();
      $rank = $faction->GetRankName($player->GetRank());
    }

    /* TODO: Complete the stats when they are being coded */
    $player->Send(COLOR_STATS_DECORATION, '-=-=-=-=-=-=-=-=-=-=-=-=-= Player stats -=-=-=-=-=-=-=-=-=-=-=-=-=-');
    $player->Send(COLOR_STATS, "{$player->name}({$player->id}) - Money[{$money}$] - Bank[{$bank}$] - Phone[] - PremiumLevel[TODO]");
    $player->Send(COLOR_STATS, "Level[{$level}] - Experience[{$exp}] - Deaths[TODO]: Accepted[TODO], Saved[TODO]");
    $player->Send(COLOR_STATS, "Health[{$hspawn} / 100] - Hunger[{$hunger} / " . (int)(MAX_HUNGER_POINTS / 10) . "] - ".
                               "Strength[{$strength} / " . MAX_STRENGTH_POINTS . "] - Age[{$age}] - Sex[{$sex}]");
    $player->Send(COLOR_STATS, "{$wordmarried}[{$marryname}] - Faction[{$facname}] - Rank[{$rank}] - Job[TODO]");
    $player->Send(COLOR_STATS_DECORATION, '-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-');

    return COMMAND_BREAK;
  }


  public static function cmdSkills(Player $player, $numparams, $params)
  {
    $player->Send(COLOR_SKILLS_DECORATION, '-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= Skills -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=');
    $buffer = '';
    $bufflen = 0;
    for ($i = 0; $i < SKILL_MAX; $i++)
    {
      $tmp = Players::GetSkillName($i) . "[{$player->GetSkill($i)}] - ";
      $tmplen = strlen($tmp);

      if ($bufflen + $tmplen > 70)
      {
        $player->Send(COLOR_SKILLS, substr($buffer, 0, $bufflen - 3));
        $buffer = '';
        $bufflen = 0;
      }
      $buffer .= $tmp;
      $bufflen += $tmplen;
    }
    if ($bufflen > 0)
      $player->Send(COLOR_SKILLS, substr($buffer, 0, $bufflen - 3));
    $player->Send(COLOR_SKILLS_DECORATION, '-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-');

    if (($pts = $player->GetUpgradePoints()) > 0)
      $player->Send(COLOR_YOUHAVE_UPGRADE_POINTS, "* You have {$pts} upgrade points to spend, type /upgrade to use them");

    return COMMAND_BREAK;
  }

  
  public static function cmdUpgrade(Player $player, $numparams, $params)
  {
    $pts = $player->GetUpgradePoints();
    if ($pts == 0)
    {
      $player->Send(COLOR_NOT_UPGRADE_POINTS, '[ERROR] You don\'t have upgrade points');
      return COMMAND_BREAK;
    }

    $player->SetMenu(Players::$upgrade_menu);

    return COMMAND_BREAK;
  }


  /**
   ** Menu callbacks
   **/
  public static function menuUpgradesEnter(Player $player)
  {
    $draw = Players::MakeUpgradeDraw($player);
    if ($draw == null)
    {
      $player->Send(COLOT_UPGRADE_INTERROR, '[ERROR] Internal error, try again later');
      $player->SetMenu(null);
      return;
    }

    TextDrawShowForPlayer($player->id, $draw);
    Players::$upgrade_draws[$player->id] = $draw;
  }

  public static function menuUpgradesExit(Player $player)
  {
    if (Players::$upgrade_draws[$player->id] != null)
    {
      TextDrawHideForPlayer($player->id, Players::$upgrade_draws[$player->id]);
      TextDrawDestroy(Players::$upgrade_draws[$player->id]);
      Players::$upgrade_draws[$player->id] = null;
    }
  }

  public static function menuUpgradesSelect(Player $player, $row)
  {
    $skill = Players::GetSkillByName($row);
    if (!$skill || ($pts = $player->GetUpgradePoints()) == 0)
      return;
    
    $nextlevel = $player->GetSkill($skill->flag) + 1;
    if (($nextlevel <= $skill->maxlevel) &&
        ($player->GetLevel() >= $skill->reqlevels[$nextlevel]) &&
        ($skill->reqskill == -1 || $player->GetSkill($skill->reqskill) >= $skill->reqskill_level)
       )
    {
      $player->SetSkill($skill->flag, $nextlevel);
      $player->SetUpgradePoints($pts - 1);
      $player->Send(COLOR_UPGRADE_DONE, "[UGPRADE] You upgraded the skill '{$skill->name}' to the level {$nextlevel}");
    }
  }


  /**
   ** Callbacks
   **/
  public static function OnPlayerConnect($playerid)
  {
    /* Add to connected players list */
    $player = new Player($playerid);
    Players::$players[$playerid] = $player;
    Players::$players_by_id[$playerid] = $player;

    /* TODO: Remove this */
    AllowPlayerTeleport($playerid, 1);

    /* Set a nice view when connecting */
    SetPlayerCameraPos($playerid, 1848.1669, -935.7288, 120.4602);
    SetPlayerCameraLookAt($playerid, 1954.8025, -1287.2996, 57.9777);
    GameTextForPlayer($playerid, 'Welcome to~n~~g~Italy~w~Mafia ~r~RolePlay', 3000, 6);

    Log::Append(LOG_JOINQUIT, "[{$player->id}] {$player->name}({$player->ip}) connected");

    return CALLBACK_OK;
  }

  public static function OnPlayerDisconnect(Player $player, $reasonid)
  {
    /* Remove from connected players list */
    unset(Players::$players[$player->id]);
    Players::$players_by_id[$player->id] = null;

    /* Remove from JQ list */
    Players::DelFromJQ($player);
    /* Remove from OOC list */
    Players::DelFromOOC($player);

    /* Send the quit info to players who requested it */
    $reason = Core::TranslateQuitReason($reasonid);
    if ($player->account && $player->account->Authed())
    {
      foreach (Players::$players_jq as $p)
        $p->Send(COLOR_GRAY, "[QUIT] {$player->name}({$player->id}) has quitted ({$reason})");
    }

    if (Players::$upgrade_draws[$player->id] != null)
    {
      TextDrawDestroy(Players::$upgrade_draws[$player->id]);
      Players::$upgrade_draws[$player->id] = null;
    }

    if ($player->account)
      $player->SetName($player->account->name);

    Log::Append(LOG_JOINQUIT, "[{$player->id}] {$player->name}({$player->ip}) disconnected ({$reason})");

    $player->Destroy();

    return CALLBACK_OK;
  }

  public static function OnPlayerDeath(Player $player, $killer, $reason)
  {
    return CALLBACK_OK;
  }

  public static function OnPlayerRequestSpawn(Player $player)
  {
    SetPlayerPos($player->id, 0, 0, -1);
    return CALLBACK_OK;
  }

  public static function OnPlayerSpawn(Player $player)
  {
    if ($player->firstSpawn == true)
    {
      $player->firstSpawn = false;
    }
    else
    {
      $player->SetHunger(MAX_HUNGER_POINTS);
      $player->SetInjures(60);
      $player->SetDead(false);
      $player->CheckHealth(true);
      if ($player->location)
        SetPlayerInterior($player->id, $player->location->GetInteriorID());
      $player->ClearAnimations();
    }

    return CALLBACK_OK;
  }

  public static function OnPlayerRequestClass(Player $player)
  {
    if ($player->account && $player->account->Authed())
    {
      $player->UpdateSpawnInfo();
      SetPlayerPos($player->id, 0, 0, 0);
      $player->Spawn();
    }
    return CALLBACK_DISALLOW;
  }

  public static function OnPlayerEnterVehicle(Player $player, $vehicle)
  {
    if ($player->timedeath != 0)
    {
      $pos = $player->Position();
      SetPlayerPos($player->id, $pos->x, $pos->y, $pos->z);
      return CALLBACK_DISALLOW;
    }
    return CALLBACK_OK;
  }

  public static function OnPlayerKeyStateChange(Player $player, $newkeys, $oldkeys)
  {
    if ($newkeys & KEY_SECONDARY_ATTACK)
    {
      /* Player wants to enter or exit a vehicle */
      $player->WantsEnterExitVehicle();
    }

    return CALLBACK_OK;
  }
}


/**
 ** class Skill
 ** Container for skill data
 **/
class Skill
{
  public $flag;
  public $dbname;
  public $name;
  public $maxlevel;
  public $reqlevels;
  public $reqskill;
  public $reqskill_level;

  public function __construct($flag, $maxlevel, $dbname, $name, $reqskill = -1, $reqskill_level = -1)
  {
    $this->flag           = $flag;
    $this->maxlevel       = $maxlevel;
    $this->dbname         = $dbname;
    $this->name           = $name;
    $this->reqskill       = $reqskill;
    $this->reqskill_level = $reqskill_level;
    if ($this->maxlevel > 0)
      $this->reqlevels      = array_fill(1, $this->maxlevel, 0);
  }

  public function SetReqLevel($level, $playerlevel)
  {
    $this->reqlevels[$level] = $playerlevel;
  }
}
?>

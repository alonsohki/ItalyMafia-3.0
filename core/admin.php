<?

/**
 ** Constants for Admin
 **/
define ('ADMIN_PREOPERATOR',  1);
define ('ADMIN_OPERATOR',     2);
define ('ADMIN_PREADMIN',     3);
define ('ADMIN_ADMIN',        4);
define ('ADMIN_SUPERADMIN',   5);

/**
 ** class Admin
 ** Manages everything related to the game administrators
 **/
class Admin
{
  private static $showadmins;

  public static function Init()
  {
    /* Register the admin commands */
    CommandHandler::Register('admins',    0, null, array('Admin', 'cmdAdmins'),     '');
    CommandHandler::Register('kick',      2, null, array('Admin', 'cmdKick'),       '[ID] [reason]');
    CommandHandler::Register('suspend',   3, null, array('Admin', 'cmdSuspend'),    '[ID] [time] [reason]');
    CommandHandler::Register('unsuspend', 1, null, array('Admin', 'cmdUnsuspend'),  '[exact name]');
    CommandHandler::Register('goto',      1, null, array('Admin', 'cmdGoto'),       '[ID]');
    CommandHandler::Register('bring',     1, null, array('Admin', 'cmdBring'),      '[ID]');
    CommandHandler::Register('ajail',     3, null, array('Admin', 'cmdAjail'),      '[ID] [time] [reason]');
    CommandHandler::Register('aunjail',   1, null, array('Admin', 'cmdAunjail'),    '[ID]');
    CommandHandler::Register('givecash',  2, null, array('Admin', 'cmdGivecash'),   '[ID] [amount]');
    CommandHandler::Register('showme',    1, null, array('Admin', 'cmdShowme'),     '[on/off]');
    CommandHandler::Register('setfaction',2, null, array('Admin', 'cmdSetfaction'), '[ID] [faction name]');
    CommandHandler::Register('setrank',   2, null, array('Admin', 'cmdSetrank'),    '[ID] [rank id]');

    /* Register callbacks */
    Callbacks::Instance()->Register(cOnPlayerConnect, null, array('Admin', 'OnPlayerConnect'));

    Admin::$showadmins = array_fill(0, MAX_PLAYERS, true);
  }

  /**
   ** Send
   ** Sends a message to all admins
   **
   ** Parameters:
   ** - color:    The message color
   ** - message:  The self message
   **/
  public static function Send($color, $message)
  {
    foreach (Players::Get() as $player)
    {
      if ($player->GetAdminLevel() > 0)
        $player->Send($color, $message);
    }
  }


  /**
   ** CheckLevel
   ** Checks if the given player has the enough admin level
   **
   ** Parameters:
   ** - player:   The player to check
   ** - level:    The required level
   **/
  public static function CheckLevel(Player $player, $level)
  {
    if ($player->GetAdminLevel() < $level)
    {
      $player->Send(COLOR_ACCESS_DENIED, 'Access denied!');
      return false;
    }
    return true;
  }


  /**
   ** GetLevelStr
   ** Returns the name of a given admin level
   **
   ** Parameters:
   ** - level: The level to translate
   **/
  public static function GetLevelStr($level)
  {
    switch ($level)
    {
      case ADMIN_PREOPERATOR:
        return 'Pre operator';
      case ADMIN_OPERATOR:
        return 'Operator';
      case ADMIN_PREADMIN:
        return 'Pre administrator';
      case ADMIN_ADMIN:
        return 'Administrator';
      case ADMIN_SUPERADMIN:
        return 'Super administrator';
      default:
        return 'Unknown';
    }
  }


  /***
   *** Callbacks
   ***/
  public static function OnPlayerConnect($playerid)
  {
    Admin::$showadmins[$playerid] = true;
    return CALLBACK_OK;
  }


  /***
   *** Commands
   ***/
  public static function cmdAdmins(Player $player, $numparams, $params)
  {
    $player->Send(COLOR_ADMINLIST, '* Current online admins:');
    foreach (Players::Get() as $p)
    {
      if ($p->GetAdminLevel() > 0 && Admin::$showadmins[$p->id])
        $player->Send(COLOR_ADMINLIST, " {$p->name} (" . Admin::GetLevelStr($p->GetAdminLevel()) . ')');
    }
    return COMMAND_OK;
  }

  public static function cmdShowme(Player $player, $numparams, $params)
  {
    if (!Admin::CheckLevel($player, ADMIN_OPERATOR))
      return COMMAND_OK;

    if (!strcasecmp($params[1], 'on'))
      $state = true;
    else if (!strcasecmp($params[1], 'off'))
      $state = false;
    else
    {
      $player->Send(COLOR_SHOWME_WRONGPARAM, '[ERROR] The parameter must be on or off');
      return COMMAND_OK;
    }

    Admin::$showadmins[$player->id] = $state;
    if ($state)
      $player->Send(COLOR_SHOWME_CHANGED, '* You will be now listed in /admins command');
    else
      $player->Send(COLOR_SHOWME_CHANGED, '* You wont be listed in /admins command');

    return COMMAND_OK;
  }

  public static function cmdKick(Player $player, $numparams, $params)
  {
    if (!Admin::CheckLevel($player, ADMIN_PREOPERATOR))
      return COMMAND_OK;

    if ($target = Core::FindPlayer($player, $params[1]))
    {
      $reason = implode(' ', array_slice($params, 2));
      $target->Kick("* You have been kicked: {$reason}");
      Admin::Send(COLOR_KICK, "[KICK] {$player->name} kicked {$target->name}: {$reason}");

      Log::Append(LOG_ADMIN, "[{$player->id}] {$player->name} kicked {$target->name}[{$target->id}]: {$reason}");
    }
    return COMMAND_OK;
  }

  public static function cmdSuspend(Player $player, $numparams, $params)
  {
    if (!Admin::CheckLevel($player, ADMIN_OPERATOR))
      return COMMAND_OK;

    if ($target = Core::FindPlayer($player, $params[1]))
    {
      $expiration = time() + Core::GetTime($params[2]);
      $reason = implode(' ', array_slice($params, 3));

      $data = array();
      $data['id'] = $target->account->ID();
      $data['banned'] = $reason;
      $data['banned_by'] = $player->account->ID();
      $data['ban_date'] = time();
      $data['ban_expiration'] = $expiration;
      DB::SaveAccount($data);

      $timestr = date('r', $expiration);
      $target->Send(COLOR_KICK, "* You have been banned until {$timestr}");
      $target->Kick("* Reason: {$reason}");
      Admin::Send(COLOR_KICK, "[SUSPEND] {$player->name} suspended {$target->name} until {$timestr}");
      Admin::Send(COLOR_KICK, "[SUSPEND] Reason: {$reason}");

      Log::Append(LOG_ADMIN, "[{$player->id}] {$player->name} suspended {$target->name}[{$target->id}] until {$timestr}: {$reason}");
    }
    return COMMAND_OK;
  }

  public static function cmdUnsuspend(Player $player, $numparams, $params)
  {
    if (!Admin::CheckLevel($player, ADMIN_OPERATOR))
      return COMMAND_OK;

    $acc = DB::GetAccount($params[1], 'name', 'id,banned');
    if ($acc == null)
    {
      $player->Send(COLOR_ACCOUNT_NOTFOUND, '[ERROR] Account not found');
      return COMMAND_OK;
    }
    else if ($acc['banned'] == null)
    {
      $player->Send(COLOR_ACCOUNT_NOTBANNED, '[ERROR] Given account is not suspended');
      return COMMAND_OK;
    }

    $data = array();
    $data['id'] = $acc['id'];
    $data['banned'] = null;
    $data['banned_by'] = null;
    $data['ban_date'] = null;
    $data['ban_expiration'] = null;
    DB::SaveAccount($data);

    Admin::Send(COLOR_UNSUSPEND, "[UNSUSPEND] {$player->name} unsuspended the account '{$params[1]}'");

    Log::Append(LOG_ADMIN, "[{$player->id}] {$player->name} unsuspended '{$params[1]}'");

    return COMMAND_OK;
  }

  public static function cmdGoto(Player $player, $numparams, $params)
  {
    if (!Admin::CheckLevel($player, ADMIN_PREOPERATOR))
      return COMMAND_OK;

    if ($target = Core::FindPlayer($player, $params[1]))
    {
      $player->SetLocation($target->location);
      $position = clone $target->Position();
      $position->x += 3 * sin(deg2rad($position->angle));
      $position->y -= 3 * cos(deg2rad($position->angle));
      $player->SetPosition($position);
      SetCameraBehindPlayer($player->id);

      Log::Append(LOG_ADMIN, "[{$player->id}] {$player->name} teleported to {$target->name}[{$target->id}]");
    }

    return COMMAND_OK;
  }

  public static function cmdBring(Player $player, $numparams, $params)
  {
    if (!Admin::CheckLevel($player, ADMIN_PREOPERATOR))
      return COMMAND_OK;

    if ($target = Core::FindPlayer($player, $params[1]))
    {
      $target->SetLocation($player->location);
      $position = clone $player->Position();
      $position->x -= 3 * sin(deg2rad($position->angle));
      $position->y += 3 * cos(deg2rad($position->angle));;
      $target->SetPosition($position);
      SetCameraBehindPlayer($target->id);

      Log::Append(LOG_ADMIN, "[{$player->id}] {$player->name} teleported {$target->name}[{$target->id}] to himself");
    }

    return COMMAND_OK;
  }

  public static function cmdAjail(Player $player, $numparams, $params)
  {
    if (!Admin::CheckLevel($player, ADMIN_PREOPERATOR))
      return COMMAND_OK;

    if ($target = Core::FindPlayer($player, $params[1]))
    {
      $time = Core::GetTime($params[2]);
      if ($time < 1)
      {
        $player->Send(COLOR_AJAIL_INVALID_TIME, '[ERROR] Invalid jail time');
        return COMMAND_OK;
      }

      $reason = implode(' ', array_slice($params, 3));

      $target->Jail($time);
      Messages::SendToAll(COLOR_AJAIL, "* Admin {$player->name} jailed {$target->name} for {$time} seconds: {$reason}");

      Log::Append(LOG_ADMIN, "[{$player->id}] {$player->name} jailed {$target->name}[{$target->id}] for {$time} seconds: {$reason}");
    }

    return COMMAND_OK;
  }

  public static function cmdAunjail(Player $player, $numparams, $params)
  {
    if (!Admin::CheckLevel($player, ADMIN_PREOPERATOR))
      return COMMAND_OK;

    if (($target = Core::FindPlayer($player, $params[1])) && $target->IsJailed())
    {
      $target->Unjail();
      Admin::Send(COLOR_AUNJAIL, "* Admin {$player->name} unjailed {$target->name}");

      Log::Append(LOG_ADMIN, "[{$player->id}] {$player->name} unjailed {$target->name}[{$target->id}]");
    }

    return COMMAND_OK;
  }

  public static function cmdGivecash(Player $player, $numparams, $params)
  {
    if (!Admin::CheckLevel($player, ADMIN_ADMIN))
      return COMMAND_OK;

    if ($target = Core::FindPlayer($player, $params[1]))
    {
      $amount = (int)$params[2];
      if ($amount == 0)
        $player->Send(COLOR_GIVECASH_INVALID_AMOUNT, '[ERROR] Invalid amount');
      else
      {
        $target->GiveMoney($amount);
        $amount = Core::FixIntegerDots($amount);
        Admin::Send(COLOR_GIVECASH, "* Admin {$player->name} has given {$amount}$ to {$target->name}");
        $target->Send(COLOR_GIVECASH, "* Admin {$player->name} has given you {$amount}$");

        Log::Append(LOG_ADMIN, "[{$player->id}] {$player->name} has given {$amount}$ to {$target->name}[{$target->id}]");
      }
    }

    return COMMAND_OK;
  }

  public static function cmdSetfaction(Player $player, $numparams, $params)
  {
    if (!Admin::CheckLevel($player, ADMIN_ADMIN))
      return COMMAND_OK;

    if ($target = Core::FindPlayer($player, $params[1]))
    {
      $facname = implode(' ', array_slice($params, 2));
      if (($faction = $target->GetFaction(false)) && !strcasecmp($facname, 'Civilian'))
      {
        $faction->SetMemberCount($faction->GetMemberCount() - 1);
        $target->SetFaction(null);
        $target->SetRank(null);
        Admin::Send(COLOR_SETFACTION, "* Admin {$player->name} has kicked {$target->name} from his faction");
        $target->Send(COLOR_SETFACTION, "* Admin {$player->name} has kicked you from your faction");

        Log::Append(LOG_ADMIN, "[{$player->id}] {$player->name} has kicked {$target->name}[{$target->id}] from his faction");
      }

      else if ($faction = Factions::FindByName($facname))
      {
        $oldfaction = $player->GetFaction(false);
        if ($oldfaction != null)
          $oldfaction->SetMemberCount($oldfaction->GetMemberCount() - 1);
        $faction->SetMemberCount($faction->GetMemberCount() + 1);

        $target->SetFaction($faction->ID());
        $target->SetRank($faction->LowestRank());
        Admin::Send(COLOR_SETFACTION, "* Admin {$player->name} has moved {$target->name} to the {$faction->GetName()} faction");
        $target->Send(COLOR_SETFACTION, "* Admin {$player->name} has moved you to the {$faction->GetName()} faction");

        Log::Append(LOG_ADMIN, "[{$player->id}] {$player->name} has moved {$target->name}[{$target->id}] to the faction {$faction->GetName()}");
      }
    }

    return COMMAND_OK;
  }

  public static function cmdSetrank(Player $player, $numparams, $params)
  {
    if (!Admin::CheckLevel($player, ADMIN_ADMIN))
      return COMMAND_OK;

    if ($target = Core::FindPlayer($player, $params[1]))
    {
      $faction = $target->GetFaction(false);
      if (!$faction)
      {
        $player->Send(COLOR_SETRANK_NOFACTION, '[ERROR] Given player is not in any faction');
        return COMMAND_OK;
      }

      $rank = (int)$params[2];
      if ($rank < 0 || $rank > $faction->LowestRank())
      {
        $player->Send(COLOR_SETRANK_INVALID, '[ERROR] Invalid rank id');
        return COMMAND_OK;
      }

      $target->SetRank($rank);
      Admin::Send(COLOR_SETRANK, "* Admin {$player->name} has set {$target->name} rank to {$faction->GetRankName($rank)}");
      $target->Send(COLOR_SETRANK, "* Admin {$player->name} has set your rank to {$faction->GetRankName($rank)}");

      Log::Append(LOG_ADMIN, "[{$player->id}] {$player->name} has set {$target->name} rank in faction " .
                             "{$faction->GetName()} to {$faction->GetRankName($rank)}");
    }

    return COMMAND_OK;
  }
}
?>

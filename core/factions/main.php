<?
require_once('faction.php');

class Factions
{
  private static $factions = array();

  /**
   ** MakeID
   ** Generates a unique ID for a faction.
   **
   ** Parameters:
   ** - name: The faction name to generate the ID
   **/
  public static function MakeID($name)
  {
    return crc32($name);
  }

  /**
   ** Init
   ** Initialize all factions.
   **/
  public static function Init()
  {
    $factions = DB::GetAllFactions();

    foreach ($factions as $name)
    {
      /* Load the faction source code */
      $fixed_name = str_replace(' ', '_', $name);
      require_once("gamemodes/factions/{$fixed_name}.php");

      $factiondata = DB::GetFactionData($name);
      $classname = 'Faction_' . $fixed_name;
      $newfaction = new $classname($name, $factiondata['color'], $factiondata['bank'],
                                   $factiondata['maxvehicles'], $factiondata['maxmembers'],
                                   $factiondata['membercount']);
      if ($factiondata['HQ'] != null)
      {
        $room = Houses::FindByDBID($factiondata['HQ']);
        if ($room)
          $newfaction->SetHQ($room);
      }

      if ($factiondata['bankfreezed'] == 1)
        $newfaction->FreezeBank();
      Factions::Register($newfaction, $newfaction->GetHQ());
      echo ">>>>> Loaded faction '{$name}'\n";
    }

    /* Register common faction commands */
    CommandHandler::Register('invite',   1, null, array('Factions', 'cmdInvite'),   '[ID]', 1);
    CommandHandler::Register('uninvite', 1, null, array('Factions', 'cmdUninvite'), '[ID]', 1);
    CommandHandler::Register('rankup',   1, null, array('Factions', 'cmdRankup'),   '[ID]', 1);
    CommandHandler::Register('rankdown', 1, null, array('Factions', 'cmdRankdown'), '[ID]', 1);
    CommandHandler::Register('duty',     1, null, array('Factions', 'cmdDuty'),     '[on/off]', 1, -1000);
    CommandHandler::Register('open',     0, null, array('Factions', 'cmdOpen'),     '', 1);
    CommandHandler::Register('close',    0, null, array('Factions', 'cmdClose'),    '', 1);
  }

  /**
   ** Save
   ** Save the factions data
   **/
  public static function Save()
  {
    foreach (Factions::$factions as $faction)
    {
      $data = $faction->GetData();
      DB::SaveFactionData($data);
    }
  }


  /**
   ** UpdateMembercount
   ** Updates the member count of all factions
   **/
  public static function UpdateMembercount()
  {
    foreach (Factions::$factions as $faction)
      $faction->UpdateMembercount();
  }

  /**
   ** FindByID
   ** Find a faction by its ID
   **/
  public static function FindByID($id)
  {
    if ($id != null)
    {
      if (isset(Factions::$factions[$id]))
        return Factions::$factions[$id];
      else
        echo "[WARNING] Asking for faction id '{$id}' that doesnt exist\n";
    }
    return null;
  }

  /**
   ** FindByName
   ** Find a faction by its name
   **/
  public static function FindByName($name)
  {
    foreach (Factions::$factions as $faction)
    {
      if (!strcasecmp($faction->GetName(), $name))
        return $faction;
    }

    return null;
  }

  /**
   ** Register
   ** Registers a faction in the factions list
   **
   ** Parameters:
   ** - faction:  The faction object to register
   **/
  private static function Register(Faction $faction)
  {
    Factions::$factions[$faction->ID()] = $faction;
  }


  /**
   ** EqualFactions
   ** Finds the Player class of the target and checks if
   ** the player and target factions match. Returns null
   ** in case of error, or the target class.
   **
   ** Parameters:
   ** - $player:   Player who requests this
   ** - $targetid: The target id for find
   **/
  private static function EqualFactions(Player $player, $targetid)
  {
    if ($target = Core::FindPlayer($player, $targetid))
    {
      if (!$target->GetFaction() || $target->GetFaction()->ID() != $player->GetFaction()->ID())
      {
        $player->Send(COLOR_FACTION_DISMATCH, '[ERROR] Given player isnt in your faction');
        return null;
      }
      return $target;
    }
    else
      return null;
  }


  public static function CheckPayBank(Player $player, Faction $faction, $amount, Faction $target = null)
  {
    if ($amount < 1)
      $player->Send(COLOR_INVALID_AMOUNT, '[ERROR] Invalid amount');
    else if ($faction->BankFreezed())
      $player->Send(COLOR_BANK_FREEZED, '[ERROR] Your faction bank account has been freezed, operation not completed');
    else if ($amount > $faction->GetBank())
      $player->Send(COLOR_NOTENOUGH_MONEYBANK, '[ERROR] Your faction bank account doesn\'t have this amount of money');
    else if ($target && $target->BankFreezed())
      $player->Send(COLOR_BANK_FREEZED, '[ERROR] Given faction bank account has been freezed, operation cancelled');
    else
      return true;
    return false;
  }


  /***
   *** COMMANDS
   ***/
  public static function cmdInvite(Player $player, $numparams, $params)
  {
    $faction = $player->GetFaction();
    if ($faction == null)
      return COMMAND_OK;

    if ($faction->AllowedTo($player, MEMBER_ALLOWINVITEKICK) == false)
      return COMMAND_OK;

    if ($faction->GetMemberCount() + 1 > $faction->GetMaxMembers())
    {
      $player->Send(COLOR_INVITE_FULL, '[ERROR] Your faction is full');
      return COMMAND_OK;
    }

    if ($target = Core::FindPlayer($player, $params[1]))
    {
      if ($target->GetFaction() != null)
        $player->Send(COLOR_INVITE_ALREADYFACTION, '[ERROR] Given player is already in a faction');
      else if ($target->GetAge() < 18)
        $player->Send(COLOR_INVITE_TOOYOUNG, '[ERROR] Given player is so young to join a faction');
      else
      {
        $faction->SetMemberCount($faction->GetMemberCount() + 1);
        $target->SetFaction($faction->ID());
        $target->SetRank($faction->LowestRank());
        $faction->Send(COLOR_INVITE_SUCCESS, "[FACTION] {$target->name} has been invited into " . $faction->GetName());
      }
    }

    return COMMAND_OK;
  }

  public static function cmdUninvite(Player $player, $numparams, $params)
  {
    $faction = $player->GetFaction();
    if ($faction == null)
      return COMMAND_OK;

    if ($faction->AllowedTo($player, MEMBER_ALLOWINVITEKICK) == false)
      return COMMAND_OK;

    if ($target = Factions::EqualFactions($player, $params[1]))
    {
      $faction->SetMemberCount($faction->GetMemberCount() - 1);
      $target->SetFaction(null);
      $target->SetRank(null);
      $faction->Send(COLOR_UNINVITE_SUCCESS, "[FACTION] {$target->name} has been kicked from " . $faction->GetName());
    }
    return COMMAND_OK;
  }

  public static function cmdRankup(Player $player, $numparams, $params)
  {
    $faction = $player->GetFaction();
    if ($faction == null)
      return COMMAND_OK;
  
    if ($faction->AllowedTo($player, MEMBER_ALLOWRANK) == false)
      return COMMAND_OK;

    if ($target = Factions::EqualFactions($player, $params[1]))
    {
      if ($player->id == $target->id)
        return COMMAND_OK;

      $requested_rank = $target->GetRank() - 1;

      if ($player->GetRank() >= $requested_rank)
      {
        $player->Send(COLOR_RANKUP_MAX, '[ERROR] Given player has the highest rank that you can give');
        return COMMAND_OK;
      }
      $target->SetRank($requested_rank);
      $faction->Send(COLOR_RANKUP_SUCCESS, "[FACTION] {$target->name} has been promoted to " . $faction->GetRankName($requested_rank));
    }

    return COMMAND_OK;
  }

  public static function cmdRankdown(Player $player, $numparams, $params)
  {
    $faction = $player->GetFaction();
    if ($faction == null)
      return COMMAND_OK;

    if ($faction->AllowedTo($player, MEMBER_ALLOWRANK) == false)
      return COMMAND_OK;

    if ($target = Factions::EqualFactions($player, $params[1]))
    {
      if ($player->id == $target->id)
        return COMMAND_OK;

      $requested_rank = $target->GetRank() + 1;
      if ($requested_rank > $faction->LowestRank())
      {
        $player->Send(COLOR_RANKDOWN_MIN, '[ERROR] Given player already has the lowest faction rank');
        return COMMAND_OK;
      }  
      if ($target->GetRank() <= $player->GetRank())
      {
        $player->Send(COLOR_RANKDOWN_DISALLOW, '[ERROR] You are not allowed to rank down this player');
        return COMMAND_OK;
      }
      $target->SetRank($requested_rank);
      $faction->Send(COLOR_RANKDOWN_SUCCESS, "[FACTION] {$target->name} has been demoted to " . $faction->GetRankName($requested_rank));
    }

    return COMMAND_OK;
  }

  public static function cmdDuty(Player $player, $numparams, $params)
  {
    if (!strcasecmp($params[1], 'on'))
      $state = true;
    else if (!strcasecmp($params[1], 'off'))
      $state = false;
    else
      return COMMAND_BREAK;

   
    if ($state != $player->IsOnDuty())
    {
      if ($faction = $player->GetFaction(false))
      {
        if ($faction->PlayerChangesDuty($player, $state) == DUTYCHANGE_ALLOW)
        {
          $player->SetOnDuty($state);
        }
      }
    }
      
    return COMMAND_BREAK;
  }

  public static function cmdOpen(Player $player, $numparams, $params)
  {
    if ($faction = $player->GetFaction())
    {
    }

    return COMMAND_OK;
  }

  public static function cmdClose(Player $player, $numparams, $params)
  {
    if ($faction = $player->GetFaction())
    {
    }

    return COMMAND_OK;
  }
}
?>

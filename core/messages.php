<?

class Messages
{
  public static function Init()
  {
    Callbacks::Instance()->Register(cOnPlayerText, null, array('Messages', 'OnPlayerText'));
    Callbacks::Instance()->Register(cOnPlayerPrivmsg, null, array('Messages', 'OnPlayerPrivmsg'));
    CommandHandler::Register('l', 1, null, array('Messages', 'cmdLocal'), '[message]', 1, -1000);
    CommandHandler::Register('s', 1, null, array('Messages', 'cmdShout'), '[message]', 1, -1000);
    CommandHandler::Register('w', 2, null, array('Messages', 'cmdWhisper'), '[id] [message]', 1, -1000);
    CommandHandler::Register('me', 1, null, array('Messages', 'cmdMe'), '[message]', 1, -1000);
    CommandHandler::Register('/', 1, null, array('Messages', 'cmdAdminchat'), '[message]', 1, -1000);
  }

  public static function OnPlayerText(Player $player, $text)
  {
    if (!$player->account || !$player->account->Authed())
      return CALLBACK_DISALLOW;

    $len = strlen($text);

    if ($text[0] == '(')
      Messages::SendLocalOOC($player, substr($text, 1));
    else if ($text[$len - 1] == ')' && $text[$len - 2] == ')')
      Messages::SendLocalOOC($player, substr($text, 0, $len - 2));
    else if ($text[0] == ';')
      Messages::SendGlobalOOC($player, $text);
    else
    {
      $vehicle = $player->GetVehicle();

      if ($vehicle == null || !$vehicle->HasWindows() || $vehicle->Windows() == WINDOWS_ROLLED_DOWN)
        Messages::SendStandardMessage($player, $text);
      else
        Messages::SendVehicleMessage($player, $vehicle, $text);
    }

    return CALLBACK_DISALLOW;
  }

  public static function OnPlayerPrivmsg(Player $player, Player $receiver, $text)
  {
    return CALLBACK_DISALLOW;
  }

  /**
   ** Commands
   **/
  public static function cmdShout(Player $player, $numparams, $params)
  {
    $text = implode(' ', array_slice($params, 1));
    Messages::SendShout($player, $text);

    return COMMAND_BREAK;
  }

  public static function cmdLocal(Player $player, $numparams, $params)
  {
    $text = implode(' ', array_slice($params, 1));
    $vehicle = $player->GetVehicle();

    if ($vehicle == null || !$vehicle->HasWindows() || $vehicle->Windows() == WINDOWS_ROLLED_DOWN)
      Messages::SendStandardMessage($player, $text);
    else
      Messages::SendVehicleMessage($player, $vehicle, $text);

    return COMMAND_BREAK;
  }

  public static function cmdWhisper(Player $player, $numparams, $params)
  {
    $target = Core::FindPlayer($player, $params[1]);
    if (!$target)
      return COMMAND_BREAK;

    $text = implode(' ', array_slice($params, 2));
    Messages::SendWhisper($player, $target, $text);

    return COMMAND_BREAK;
  }

  public static function cmdMe(Player $player, $numparams, $params)
  {
    $text = implode(' ', array_slice($params, 1));
    Messages::SendDescribe($player, $text);

    return COMMAND_BREAK;
  }

  public static function cmdAdminchat(Player $player, $numparams, $params)
  {
    if ($player->GetAdminLevel() > 0)
    {
      $text = implode(' ', array_slice($params, 1));
      Messages::SendAdminChat($player, $text);
    }
    return COMMAND_BREAK;
  }

  public static function SendNear(Player $player, $color, $message)
  {
    $sectors = $player->sector->FindSectors($player->Position(), 20);

    foreach ($sectors as $sector)
    {
      foreach ($sector->GetPlayers() as $target)
      {
        if ($player->vworld == $target->vworld &&
            $player->Position()->DistanceTo($target->Position()) < 20)
          $target->Send($color, $message);
      }
    }
  }

  public static function SendToAll($color, $message)
  {
    foreach (Players::Get() as $p)
    {
      $p->Send($color, $message);
    }
  }


  /**
   ** Message sending functions
   **
   ** - SendVehicleMessage:   Sends a message to the people inside a vehicle, except
   **                         if this one is an "open" vehicle.
   ** - SendStandardMessage:  Sends a standard talk message.
   ** - SendLocalOOC:         Sends a OOC message locally (with distance limit)
   ** - SendGlobalOOC:        Sends a OOC message globally (arrives to all players who
   **                         requested OOC.
   ** - SendShout:            Sends a shout.
   ** - SendWhisper:          Sends a whisper from a player to another player.
   ** - SendDescribe:         Sends a player description (/me).
   **
   **/
  private static function SendVehicleMessage(Player $player, Vehicle $vehicle, $text)
  {
    Log::Append(LOG_MESSAGE, "{VEHICLE} [{$player->id}] {$player->name} says: {$text}");
    $str = "{$player->name} says: {$text}";

    foreach (Players::Get() as $p)
    {
      $v = $p->GetVehicle();
      if ($v != null && $v->ID() == $vehicle->ID())
        $p->Send(0xAFFFAFFF, $str);
    }
  }

  private static function SendLocalOOC(Player $player, $text)
  {
    Log::Append(LOG_MESSAGE, "{LOCALOOC} [{$player->id}] {$player->name} says: {$text}");
    $str = "[OOC {$player->name}({$player->id}): {$text}]";

    $sectors = $player->sector->FindSectors($player->Position(), 50);
    foreach ($sectors as $sector)
    {
      foreach ($sector->GetPlayers() as $target)
      {
        if ($player->vworld == $target->vworld &&
            $player->Position()->DistanceTo($target->Position()) < 50)
          $target->Send(COLOR_LOCALOOC, $str);
      }
    }
  }

  private static function SendGlobalOOC(Player $player, $text)
  {
    if ($player->togooc == true)
    {
      Log::Append(LOG_MESSAGE, "{GLOBALOOC} [{$player->id}] {$player->name} says: {$text}");
      $str = "(({$player->name}: " . substr($text, 1) . '))';

      foreach (Players::GetOOCers() as $p)
        $p->Send(COLOR_GLOBALOOC, $str);
    }  
  }

  private static function SendStandardMessage(Player $player, $text)
  {
    Log::Append(LOG_MESSAGE, "{CHAT} [{$player->id}] {$player->name} says: {$text}");
    $str = "{$player->name} says: {$text}";

    $sectors = $player->sector->FindSectors($player->Position(), 30);
    foreach ($sectors as $sector)
    {
      foreach ($sector->GetPlayers() as $target)
      {
        if ($player->vworld == $target->vworld &&
            ($distance = $player->Position()->DistanceTo($target->Position())) < 30)
        {
          $color = 0xFF - (0xFF * $distance / 43);
          $color = 0xFF | $color << 8 | $color << 16 | $color << 24;
          $target->Send($color, $str);
        }
      }
    }
  }

  private static function SendShout(Player $player, $text)
  {
    Log::Append(LOG_MESSAGE, "{SHOUT} [{$player->id}] {$player->name} says: {$text}");
    $str = "{$player->name} shouts: {$text}!!";

    $sectors = $player->sector->FindSectors($player->Position(), 50);
    foreach ($sectors as $sector)
    {
      foreach ($sector->GetPlayers() as $target)
      {
        if ($player->vworld == $target->vworld &&
            $player->Position()->DistanceTo($target->Position()) < 50)
          $target->Send(0xFFFFFFFF, $str);
      }
    }
  }

  private static function SendWhisper(Player $player, $target, $text)
  {
    $playerv = GetPlayerVehicleID($player->id);
    $targetv = GetPlayerVehicleID($target->id);

    if ($playerv == 0 || $playerv != $targetv)
    {
      if ($player == $target || 
          $player->vworld != $target->vworld ||
          $player->Position()->DistanceTo($target->Position()) > 3.5)
        return; /* Too far or invalid target */
    }

    Log::Append(LOG_MESSAGE, "{WHISPER} [{$player->id}] {$player->name} whispers to {$target->name}[{$target->id}]: {$text}");

    $target->Send(COLOR_WHISPER, "{$player->name} whispers: {$text}");
    $player->Send(COLOR_WHISPER, "* Whisper sent to {$target->name}: {$text}");
  }

  private static function SendAdminChat(Player $player, $text)
  {
    Log::Append(LOG_MESSAGE, "{ADMCHAT} [{$player->id}] {$player->name} says: {$text}");
    Admin::Send(COLOR_ADMINCHAT, "{{$player->name}: {$text}}");
  }

  private static function SendDescribe(Player $player, $text)
  {
    Log::Append(LOG_MESSAGE, "{ACTION} [{$player->id}] {$player->name} describes: {$text}");
    $str = "** {$player->name} {$text}";

    $sectors = $player->sector->FindSectors($player->Position(), 20);
    foreach ($sectors as $sector)
    {
      foreach ($sector->GetPlayers() as $target)
      {
        if ($player->vworld == $target->vworld &&
            $player->Position()->DistanceTo($target->Position()) < 20)
          $target->Send(COLOR_ACTION, $str);
      }
    }
  }
}

?>

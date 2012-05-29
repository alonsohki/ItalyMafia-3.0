<?
define ('cOnPlayerConnect', 0);
define ('cOnPlayerDisconnect', 1);
define ('cOnPlayerSpawn', 2);
define ('cOnPlayerDeath', 3);
define ('cOnVehicleSpawn', 4);
define ('cOnVehicleDeath', 5);
define ('cOnPlayerText', 6);
define ('cOnPlayerInfoChange', 7);
define ('cOnPlayerRequestClass', 8);
define ('cOnPlayerEnterVehicle', 9);
define ('cOnPlayerExitVehicle', 10);
define ('cOnPlayerStateChange', 11);
define ('cOnPlayerEnterCheckpoint', 12);
define ('cOnPlayerLeaveCheckpoint', 13);
define ('cOnPlayerEnterRaceCheckpoint', 14);
define ('cOnPlayerLeaveRaceCheckpoint', 15);
define ('cOnRconCommand', 16);
define ('cOnPlayerPrivmsg', 17);
define ('cOnPlayerRequestSpawn', 18);
define ('cOnObjectMoved', 19);
define ('cOnPlayerPickUpPickup', 20);
define ('cOnVehicleMod', 21);
define ('cOnVehiclePaintjob', 22);
define ('cOnVehicleRespray', 23);
define ('cOnPlayerSelectedMenuRow', 24);
define ('cOnPlayerExitedMenu', 25);
define ('cOnPlayerInteriorChange', 26);
define ('cOnPlayerKeyStateChange', 27);

define ('CALLBACK_OK', 0x001);
define ('CALLBACK_BREAK', 0x002);
define ('CALLBACK_DISALLOW', 0x004);

class Callback
{
  private $object;
  private $func;
  private $priority;

  public function __construct($object, $func, $priority)
  {
    $this->object = $object;
    $this->func = $func;
    $this->priority = $priority;
  }

  public function GetObj() { return $this->object; }
  public function GetFunc() { return $this->func; }
  public function GetPrio() { return $this->priority; }
  public $next = null;
  public $prev = null;
}

class Callbacks
{
  private $callbacks = array(cOnPlayerConnect => null,
                             cOnPlayerDisconnect => null,
                             cOnPlayerSpawn => null,
                             cOnPlayerDeath => null,
                             cOnVehicleSpawn => null,
                             cOnVehicleDeath => null,
                             cOnPlayerText => null,
                             cOnPlayerInfoChange => null,
                             cOnPlayerRequestClass => null,
                             cOnPlayerEnterVehicle => null,
                             cOnPlayerExitVehicle => null,
                             cOnPlayerStateChange => null,
                             cOnPlayerEnterCheckpoint => null,
                             cOnPlayerLeaveCheckpoint => null,
                             cOnPlayerEnterRaceCheckpoint => null,
                             cOnPlayerLeaveRaceCheckpoint => null,
                             cOnRconCommand => null,
                             cOnPlayerPrivmsg => null,
                             cOnPlayerRequestSpawn => null,
                             cOnObjectMoved => null,
                             cOnPlayerPickUpPickup => null,
                             cOnVehicleMod => null,
                             cOnVehiclePaintjob => null,
                             cOnVehicleRespray => null,
                             cOnPlayerSelectedMenuRow => null,
                             cOnPlayerExitedMenu => null,
                             cOnPlayerInteriorChange => null,
                             cOnPlayerKeyStateChange => null);
  public static function Instance()
  {
    static $instance = 0;
    if ($instance == 0)
    {
      $instance = new Callbacks;
    }
    return $instance;
  }

  public function OnPlayerConnect($playerid)
  {
    if ($this->callbacks[cOnPlayerConnect] == null) return 1;

    for ($cbk = $this->callbacks[cOnPlayerConnect]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($playerid);
      else $ret = call_user_func($func, $playerid);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }
  
  public function OnPlayerDisconnect($playerid, $reason)
  {
    if ($this->callbacks[cOnPlayerDisconnect] == null) return 1;

    $player = Players::FindByID($playerid);
    if ($player == null)
      return 1;

    for ($cbk = $this->callbacks[cOnPlayerDisconnect]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($player, $reason);
      else $ret = call_user_func($func, $player, $reason);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnPlayerSpawn($playerid)
  {
    if ($this->callbacks[cOnPlayerSpawn] == null) return 1;

    $player = Players::FindByID($playerid);
    if ($player == null)
      return 1;

    for ($cbk = $this->callbacks[cOnPlayerSpawn]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($player);
      else $ret = call_user_func($func, $player);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnPlayerDeath($playerid, $killerid, $reason)
  {
    if ($this->callbacks[cOnPlayerDeath] == null) return 1;

    $player = Players::FindByID($playerid);
    if ($player == null)
      return 1;
    
    if ($killerid != INVALID_PLAYER_ID)
    {
      $killer = Players::FindByID($killerid);
      if ($killer == null)
        return 1;
    }
    else $killer = null;

    for ($cbk = $this->callbacks[cOnPlayerDeath]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($player, $killer, $reason);
      else $ret = call_user_func($func, $player, $killer, $reason);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnVehicleSpawn($vehicleid)
  {
    if ($this->callbacks[cOnVehicleSpawn] == null) return 1;

    $vehicle = Vehicles::FindByID($vehicleid);
    if ($vehicle == null)
      return 1;

    for ($cbk = $this->callbacks[cOnVehicleSpawn]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($vehicle);
      else $ret = call_user_func($func, $vehicle);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnVehicleDeath($vehicleid, $killerid)
  {
    if ($this->callbacks[cOnVehicleDeath] == null) return 1;

    $vehicle = Vehicles::FindByID($vehicleid);
    if ($vehicle == null)
      return 1;

    for ($cbk = $this->callbacks[cOnVehicleDeath]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($vehicle, $killerid);
      else $ret = call_user_func($func, $vehicle, $killerid);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnPlayerText($playerid, $text)
  {
    if ($this->callbacks[cOnPlayerText] == null) return 1;

    $player = Players::FindByID($playerid);
    if ($player == null)
      return 1;

    for ($cbk = $this->callbacks[cOnPlayerText]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($player, $text);
      else $ret = call_user_func($func, $player, $text);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnPlayerInfoChange($playerid)
  {
    if ($this->callbacks[cOnPlayerInfoChange] == null) return 1;

    $player = Players::FindByID($playerid);
    if ($player == null)
      return 1;

    for ($cbk = $this->callbacks[cOnPlayerInfoChange]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($player);
      else $ret = call_user_func($func, $player);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnPlayerRequestClass($playerid, $classid)
  {
    if ($this->callbacks[cOnPlayerRequestClass] == null) return 1;

    $player = Players::FindByID($playerid);
    if ($player == null)
      return 1;

    for ($cbk = $this->callbacks[cOnPlayerRequestClass]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($player, $classid);
      else $ret = call_user_func($func, $player, $classid);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnPlayerEnterVehicle($playerid, $vehicleid, $ispassenger)
  {
    if ($this->callbacks[cOnPlayerEnterVehicle] == null) return 1;

    $player = Players::FindByID($playerid);
    if ($player == null)
      return 1;

    $vehicle = Vehicles::FindByID($vehicleid);
    if ($vehicle == null)
      return 1;

    for ($cbk = $this->callbacks[cOnPlayerEnterVehicle]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($player, $vehicle, $ispassenger);
      else $ret = call_user_func($func, $player, $vehicle, $ispassenger);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnPlayerExitVehicle($playerid, $vehicleid)
  {
    if ($this->callbacks[cOnPlayerExitVehicle] == null) return 1;

    $player = Players::FindByID($playerid);
    if ($player == null)
      return 1;

    $vehicle = Vehicles::FindByID($vehicleid);
    if ($vehicle == null)
      return 1;

    for ($cbk = $this->callbacks[cOnPlayerExitVehicle]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($player, $vehicle);
      else $ret = call_user_func($func, $player, $vehicle);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnPlayerStateChange($playerid, $newstate, $oldstate)
  {
    if ($this->callbacks[cOnPlayerStateChange] == null) return 1;

    $player = Players::FindByID($playerid);
    if ($player == null)
      return 1;

    for ($cbk = $this->callbacks[cOnPlayerStateChange]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($player, $newstate, $oldstate);
      else $ret = call_user_func($func, $player, $newstate, $oldstate);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnPlayerEnterCheckpoint($playerid)
  {
    if ($this->callbacks[cOnPlayerEnterCheckpoint] == null) return 1;

    $player = Players::FindByID($playerid);
    if ($player == null)
      return 1;

    for ($cbk = $this->callbacks[cOnPlayerEnterCheckpoint]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($player);
      else $ret = call_user_func($func, $player);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnPlayerLeaveCheckpoint($playerid)
  {
    if ($this->callbacks[cOnPlayerLeaveCheckpoint] == null) return 1;

    $player = Players::FindByID($playerid);
    if ($player == null)
      return 1;

    for ($cbk = $this->callbacks[cOnPlayerLeaveCheckpoint]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($player);
      else $ret = call_user_func($func, $player);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnPlayerEnterRaceCheckpoint($playerid)
  {
    if ($this->callbacks[cOnPlayerEnterRaceCheckpoint] == null) return 1;

    $player = Players::FindByID($playerid);
    if ($player == null)
      return 1;

    for ($cbk = $this->callbacks[cOnPlayerEnterRaceCheckpoint]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($player);
      else $ret = call_user_func($func, $player);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnPlayerLeaveRaceCheckpoint($playerid)
  {
    if ($this->callbacks[cOnPlayerLeaveRaceCheckpoint] == null) return 1;

    $player = Players::FindByID($playerid);
    if ($player == null)
      return 1;

    for ($cbk = $this->callbacks[cOnPlayerLeaveRaceCheckpoint]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($player);
      else $ret = call_user_func($func, $player);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnRconCommand($cmd)
  {
    if ($this->callbacks[cOnRconCommand] == null) return 1;

    for ($cbk = $this->callbacks[cOnRconCommand]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($cmd);
      else $ret = call_user_func($func, $cmd);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnPlayerPrivmsg($playerid, $receiverid, $text)
  {
    if ($this->callbacks[cOnPlayerPrivmsg] == null) return 1;

    $player = Players::FindByID($playerid);
    if ($player == null)
      return 1;
    $receiver = Players::FindByID($receiverid);
    if ($receiver == null)
      return 1;

    for ($cbk = $this->callbacks[cOnPlayerPrivmsg]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($player, $receiver, $text);
      else $ret = call_user_func($func, $player, $receiver, $text);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnPlayerRequestSpawn($playerid)
  {
    if ($this->callbacks[cOnPlayerRequestSpawn] == null) return 1;

    $player = Players::FindByID($playerid);
    if ($player == null)
      return 1;

    for ($cbk = $this->callbacks[cOnPlayerRequestSpawn]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($player);
      else $ret = call_user_func($func, $player);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnObjectMoved($objectid)
  {
    if ($this->callbacks[cOnObjectMoved] == null) return 1;

    for ($cbk = $this->callbacks[cOnObjectMoved]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($objectid);
      else $ret = call_user_func($func, $objectid);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnPlayerPickUpPickup($playerid, $pickupid)
  {
    if ($this->callbacks[cOnPlayerPickUpPickup] == null) return 1;

    $player = Players::FindByID($playerid);
    if ($player == null)
      return 1;

    for ($cbk = $this->callbacks[cOnPlayerPickUpPickup]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($player, $pickupid);
      else $ret = call_user_func($func, $player, $pickupid);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnVehicleMod($vehicleid, $componentid)
  {
    if ($this->callbacks[cOnVehicleMod] == null) return 1;

    $vehicle == Vehicles::FindByID($vehicleid);
    if ($vehicle == null)
      return 1;

    for ($cbk = $this->callbacks[cOnVehicleMod]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($vehicle, $componentid);
      else $ret = call_user_func($func, $vehicle, $componentid);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnVehiclePaintjob($vehicleid, $paintjobid)
  {
    if ($this->callbacks[cOnVehiclePaintjob] == null) return 1;

    $vehicle == Vehicles::FindByID($vehicleid);
    if ($vehicle == null)
      return 1;

    for ($cbk = $this->callbacks[cOnVehiclePaintjob]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($vehicle, $paintjobid);
      else $ret = call_user_func($func, $vehicle, $paintjobid);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnVehicleRespray($vehicleid, $color1, $color2)
  {
    if ($this->callbacks[cOnVehicleRespray] == null) return 1;

    $vehicle == Vehicles::FindByID($vehicleid);
    if ($vehicle == null)
      return 1;

    for ($cbk = $this->callbacks[cOnVehicleRespray]; $cbk != null; $cbk = $cbk->next)
    {
      if (call_user_func($cbk->GetFunc(), $vehicle, $color1, $color2) == 1)
        return 1;
    }
    return 0;
  }

  public function OnPlayerSelectedMenuRow($playerid, $row)
  {
    if ($this->callbacks[cOnPlayerSelectedMenuRow] == null) return 1;

    $player = Players::FindByID($playerid);
    if ($player == null)
      return 1;

    for ($cbk = $this->callbacks[cOnPlayerSelectedMenuRow]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($player, $row);
      else $ret = call_user_func($func, $player, $row);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnPlayerExitedMenu($playerid)
  {
    if ($this->callbacks[cOnPlayerExitedMenu] == null) return 1;

    $player = Players::FindByID($playerid);
    if ($player == null)
      return 1;

    for ($cbk = $this->callbacks[cOnPlayerExitedMenu]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($player);
      else $ret = call_user_func($func, $player);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnPlayerInteriorChange($playerid, $newinteriorid, $oldinteriorid)
  {
    if ($this->callbacks[cOnPlayerInteriorChange] == null) return 1;

    $player = Players::FindByID($playerid);
    if ($player == null)
      return 1;

    for ($cbk = $this->callbacks[cOnPlayerInteriorChange]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($player, $newinteriorid, $oldinteriorid);
      else $ret = call_user_func($func, $player, $newinteriorid, $oldinteriorid);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function OnPlayerKeyStateChange($playerid, $newkeys, $oldkeys)
  {
    if ($this->callbacks[cOnPlayerKeyStateChange] == null) return 1;

    $player = Players::FindByID($playerid);
    if ($player == null)
      return 1;

    for ($cbk = $this->callbacks[cOnPlayerKeyStateChange]; $cbk != null; $cbk = $cbk->next)
    {
      $obj = $cbk->GetObj();
      $func = $cbk->GetFunc();
      if ($obj) $ret = $obj->$func($player, $newkeys, $oldkeys);
      else $ret = call_user_func($func, $player, $newkeys, $oldkeys);
      if ($ret != CALLBACK_OK) break;
    }

    if ($ret & CALLBACK_DISALLOW)
      return 0;
    return 1;
  }

  public function Register($type, $object, $func, $priority = 10)
  {
    $cbk = new Callback($object, $func, $priority);

    /* Insert this callback in the ordered list */
    $l = $this->callbacks[$type];
    $s;
    $s2 = null;
    for ($s = $l; $s != null; $s = $s->next)
    {
      if ($s->GetPrio() > $priority)
        break;
      $s2 = $s;
    }

    if ($s2 == null)
    {
      /* First position */
      if ($l != null)
      {
        $l->prev = $cbk;
      }
      $cbk->next = $l;
      $this->callbacks[$type] = $cbk;
    }
    else if ($s == null)
    {
      /* Last position */
      $s2->next = $cbk;
      $cbk->prev = $s2;
    }
    else
    {
      $s2->next = $cbk;
      $cbk->prev = $s2;
      $s->prev = $cbk;
      $cbk->next = $s;
    }
  }
}
?>

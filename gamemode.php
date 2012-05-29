<?
require_once('core/core.php');
require_once('locations/global.php');
require_once('test.php');

function OnGameModeInit()
{
  echo ">> ItalyMafia v3.0 is starting ...\n";

  echo ">>> Starting core ...\n";
  Core::Init();
  echo ">>> Core started!\n";

  echo ">>> Starting global locations ...\n";
  GlobalLocations::Init();
  echo ">>> Global locations started!\n";
  testinit();

  echo ">> Done!\n";
  echo "+--------------------------------------------+\n".
       "|                                            |\n".
       "|           ItalyMafia v3.0                  |\n".
       "|                   Los Santos               |\n".
       "|                                            |\n".
       "|    -= By ReD-ZerO and Alberto Alonso =-    |\n".
       "|                                            |\n".
       "+--------------------------------------------+\n";


  return 1;
}

function OnGameModeExit()
{
  Core::Cleanup();
  return 1;
}

function OnPlayerRequestClass($playerid, $classid)
{
  return Callbacks::Instance()->OnPlayerRequestClass($playerid, $classid);
}

function OnPlayerRequestSpawn($playerid)
{
  return Callbacks::Instance()->OnPlayerRequestSpawn($playerid);
}

function OnPlayerConnect($playerid)
{
  return Callbacks::Instance()->OnPlayerConnect($playerid);
}

function OnPlayerDisconnect($playerid, $reason)
{
  return Callbacks::Instance()->OnPlayerDisconnect($playerid, $reason);
}

function OnPlayerSpawn($playerid)
{
  return Callbacks::Instance()->OnPlayerSpawn($playerid);
}

function OnPlayerDeath($playerid, $killerid, $reason)
{
  return Callbacks::Instance()->OnPlayerDeath($playerid, $killerid, $reason);
}

function OnVehicleSpawn($vehicleid)
{
  return Callbacks::Instance()->OnVehicleSpawn($vehicleid);
}

function OnVehicleDeath($vehicleid, $killerid)
{
  return Callbacks::Instance()->OnVehicleDeath($vehicleid, $killerid);
}

function OnPlayerText($playerid, $text)
{
  return Callbacks::Instance()->OnPlayerText($playerid, $text);
}

function OnPlayerPrivmsg($playerid, $receiverid, $text)
{
  return Callbacks::Instance()->OnPlayerPrivmsg($playerid, $receiverid, $text);
}

function OnPlayerCommandText($playerid, $cmdtext)
{
  return CommandHandler::Handle($playerid, $cmdtext);
}

function OnPlayerEnterVehicle($playerid, $vehicleid, $ispassenger)
{
  return Callbacks::Instance()->OnPlayerEnterVehicle($playerid, $vehicleid, $ispassenger);
}

function OnPlayerExitVehicle($playerid, $vehicleid)
{
  return Callbacks::Instance()->OnPlayerExitVehicle($playerid, $vehicleid);
}

function OnPlayerStateChange($playerid, $newstate, $oldstate)
{
  return Callbacks::Instance()->OnPlayerStateChange($playerid, $newstate, $oldstate);
}

function OnPlayerEnterCheckpoint($playerid)
{
  return Callbacks::Instance()->OnPlayerEnterCheckpoint($playerid);
}

function OnPlayerLeaveCheckpoint($playerid)
{
  return Callbacks::Instance()->OnPlayerLeaveCheckpoint($playerid);
}

function OnPlayerEnterRaceCheckpoint($playerid)
{
  return Callbacks::Instance()->OnPlayerEnterRaceCheckpoint($playerid);
}

function OnPlayerLeaveRaceCheckpoint($playerid)
{
  return Callbacks::Instance()->OnPlayerLeaveRaceCheckpoint($playerid);
}

function OnRconCommand($cmd)
{
  return Callbacks::Instance()->OnRconCommand($cmd);
}

function OnObjectMoved($objectid)
{
  return Callbacks::Instance()->OnObjectMoved($objectid);
}

function OnPlayerObjectMoved($playerid, $objectid)
{
  return Callbacks::Instance()->OnPlayerObjectMoved($playerid, $objectid);
}

function OnPlayerPickUpPickup($playerid, $pickupid)
{
  return Callbacks::Instance()->OnPlayerPickUpPickup($playerid, $pickupid);
}

function OnPlayerSelectedMenuRow($playerid, $row)
{
  return Callbacks::Instance()->OnPlayerSelectedMenuRow($playerid, $row);
}

function OnPlayerExitedMenu($playerid)
{
  return Callbacks::Instance()->OnPlayerExitedMenu($playerid);
}

function OnPlayerKeyStateChange($playerid, $newkeys, $oldkeys)
{
  return Callbacks::Instance()->OnPlayerKeyStateChange($playerid, $newkeys, $oldkeys);
}
?>

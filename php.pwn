#include <a_samp>

native bool:PHPInit();
native PHPEnd();

native php_OnGameModeInit();
native php_OnGameModeExit();
native php_OnPlayerRequestClass(playerid, classid);
native php_OnPlayerRequestSpawn(playerid);
native php_OnPlayerConnect(playerid);
native php_OnPlayerDisconnect(playerid, reason);
native php_OnPlayerSpawn(playerid);
native php_OnPlayerDeath(playerid, killerid, reason);
native php_OnVehicleSpawn(vehicleid);
native php_OnVehicleDeath(vehicleid, killerid);
native php_OnPlayerText(playerid, text[]);
native php_OnPlayerPrivmsg(playerid, receiverid, text[]);
native php_OnPlayerCommandText(playerid, cmdtext[]);
native php_OnPlayerInfoChange(playerid);
native php_OnPlayerEnterVehicle(playerid, vehicleid, ispassenger);
native php_OnPlayerExitVehicle(playerid, vehicleid);
native php_OnPlayerStateChange(playerid, newstate, oldstate);
native php_OnPlayerEnterCheckpoint(playerid);
native php_OnPlayerLeaveCheckpoint(playerid);
native php_OnPlayerEnterRaceCheckpoint(playerid);
native php_OnPlayerLeaveRaceCheckpoint(playerid);
native php_OnObjectMoved(objectid);
native php_OnPlayerObjectMoved(playerid, objectid);
native php_OnPlayerPickUpPickup(playerid, pickupid);
native php_OnPlayerSelectedMenuRow(playerid, row);
native php_OnPlayerExitedMenu(playerid);
native php_OnPlayerKeyStateChange(playerid, newkeys, oldkeys);
native php_OnTimer();

main()
{
}

public OnGameModeInit()
{
  if (!PHPInit())
  {
	printf("Unable to initialize PHP parser");
	return 0;
  }
  else
  {
    return php_OnGameModeInit();
  }
}

public OnGameModeExit()
{
  new ret = php_OnGameModeExit();
  PHPEnd();
  return ret;
}

public OnPlayerRequestClass(playerid, classid)
{
  return php_OnPlayerRequestClass(playerid, classid);
}

public OnPlayerRequestSpawn(playerid)
{
  return php_OnPlayerRequestSpawn(playerid);
}

public OnPlayerConnect(playerid)
{
  return php_OnPlayerConnect(playerid);
}

public OnPlayerDisconnect(playerid, reason)
{
  return php_OnPlayerDisconnect(playerid, reason);
}

public OnPlayerSpawn(playerid)
{
  return php_OnPlayerSpawn(playerid);
}

public OnPlayerDeath(playerid, killerid, reason)
{
  return php_OnPlayerDeath(playerid, killerid, reason);
}

public OnVehicleSpawn(vehicleid)
{
  return php_OnVehicleSpawn(vehicleid);
}

public OnVehicleDeath(vehicleid, killerid)
{
  return php_OnVehicleDeath(vehicleid, killerid);
}

public OnPlayerText(playerid, text[])
{
  return php_OnPlayerText(playerid, text);
}

public OnPlayerPrivmsg(playerid, recieverid, text[])
{
  return php_OnPlayerPrivmsg(playerid, recieverid, text);
}

public OnPlayerCommandText(playerid, cmdtext[])
{
  return php_OnPlayerCommandText(playerid, cmdtext);
}

public OnPlayerInfoChange(playerid)
{
  return php_OnPlayerInfoChange(playerid);
}

public OnPlayerEnterVehicle(playerid, vehicleid, ispassenger)
{
  return php_OnPlayerEnterVehicle(playerid, vehicleid, ispassenger);
}

public OnPlayerExitVehicle(playerid, vehicleid)
{
  return php_OnPlayerExitVehicle(playerid, vehicleid);
}

public OnPlayerStateChange(playerid, newstate, oldstate)
{
  return php_OnPlayerStateChange(playerid, newstate, oldstate);
}

public OnPlayerEnterCheckpoint(playerid)
{
  return php_OnPlayerEnterCheckpoint(playerid);
}

public OnPlayerLeaveCheckpoint(playerid)
{
  return php_OnPlayerLeaveCheckpoint(playerid);
}

public OnPlayerEnterRaceCheckpoint(playerid)
{
  return php_OnPlayerEnterRaceCheckpoint(playerid);
}

public OnPlayerLeaveRaceCheckpoint(playerid)
{
  return php_OnPlayerLeaveRaceCheckpoint(playerid);
}

public OnObjectMoved(objectid)
{
  return php_OnObjectMoved(objectid);
}

public OnPlayerObjectMoved(playerid, objectid)
{
  return php_OnPlayerObjectMoved(playerid, objectid);
}

public OnPlayerPickUpPickup(playerid, pickupid)
{
  return php_OnPlayerPickUpPickup(playerid, pickupid);
}

public OnPlayerSelectedMenuRow(playerid, row)
{
  return php_OnPlayerSelectedMenuRow(playerid, row);
}

public OnPlayerExitedMenu(playerid)
{
  return php_OnPlayerExitedMenu(playerid);
}

public OnPlayerKeyStateChange(playerid, newkeys, oldkeys)
{
  return php_OnPlayerKeyStateChange(playerid, newkeys, oldkeys);
}

forward OnTimer();
public OnTimer()
{
  return php_OnTimer();
}


forward __ImportAllSampFunctions( );
public __ImportAllSampFunctions( )
{
	new Float:fVar;
	new Var[ 256 ];
	new iVar;

	// a_samp.inc
	SendClientMessage(0, 0, "");
	SendClientMessageToAll(0, "");
	SendDeathMessage(0, 0, 0);
	GameTextForAll("", 0, 0);
	GameTextForPlayer(0, "", 0, 0);
	GetTickCount();
	GetMaxPlayers();
	SetGameModeText("");
	SetTeamCount(0);
	AddPlayerClass(0, 0.0, 0.0, 0.0, 0.0, 0, 0, 0, 0, 0, 0);
	AddPlayerClassEx(0, 0, 0.0, 0.0, 0.0, 0.0, 0, 0, 0, 0, 0, 0);
	AddStaticVehicle(0, 0.0, 0.0, 0.0, 0.0, 0, 0);
	AddStaticVehicleEx(0, 0.0, 0.0, 0.0, 0.0, 0, 0, 0);
	AddStaticPickup(0, 0, 0.0, 0.0, 0.0);
	ShowNameTags(0);
	ShowPlayerMarkers(0);
	GameModeExit();
	SetWorldTime(0);
	GetWeaponName(0, Var, sizeof( Var ) );
	EnableTirePopping(0);
	AllowInteriorWeapons(0);
	SetWeather(0);
	SetGravity(0.0);
	AllowAdminTeleport(0);
	SetDeathDropAmount(0);
	CreateExplosion(0.0, 0.0, 0.0, 0, 0.0);
	SetDisabledWeapons();
	EnableZoneNames(0);
	IsPlayerAdmin(0);
	Kick(0);
	Ban(0);
	SendRconCommand("");

	// a_players.inc
	SetSpawnInfo(0, 0, 0, 0.0, 0.0, 0.0, 0.0, 0, 0, 0, 0, 0,0);
	SpawnPlayer(0);
	SetPlayerPos(0, 0.0, 0.0, 0.0);
	SetPlayerPosFindZ(0, 0.0, 0.0, 0.0);
	GetPlayerPos(0, fVar, fVar, fVar);
	SetPlayerFacingAngle(0,0.0);
	GetPlayerFacingAngle(0,fVar);
	SetPlayerInterior(0,0);
	GetPlayerInterior(0);
	SetPlayerHealth(0, 0.0);
	GetPlayerHealth(0, fVar);
	SetPlayerArmour(0, 0.0);
	GetPlayerArmour(0, fVar);
	SetPlayerAmmo(0, 0,0);
	GetPlayerAmmo(0);
	SetPlayerTeam(0,0);
	GetPlayerTeam(0);
	SetPlayerScore(0,0);
	GetPlayerScore(0);
	SetPlayerColor(0,0);
	GetPlayerColor(0);
	SetPlayerSkin(0,0);
	GetPlayerSkin(0);
	GivePlayerWeapon(0, 0,0);
	ResetPlayerWeapons(0);
	GetPlayerWeaponData(0, 0, iVar, iVar );
	GivePlayerMoney(0,0);
	ResetPlayerMoney(0);
	SetPlayerName(0, "");
	GetPlayerMoney(0);
	GetPlayerState(0);
	GetPlayerIp(0, Var, sizeof( Var ));
	GetPlayerPing(0);
	GetPlayerWeapon(0);
	GetPlayerKeys(0,iVar,iVar,iVar);
	GetPlayerName(0, Var, sizeof( Var ));
	PutPlayerInVehicle(0, 0,0);
	GetPlayerVehicleID(0);
	RemovePlayerFromVehicle(0);
	TogglePlayerControllable(0,0);
	PlayerPlaySound(0, 0, 0.0, 0.0,0.0);
	SetPlayerCheckpoint(0, 0.0, 0.0, 0.0,0.0);
	DisablePlayerCheckpoint(0);
	SetPlayerRaceCheckpoint(0, 0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0,0.0);
	DisablePlayerRaceCheckpoint(0);
	SetPlayerWorldBounds(0,0.0,0.0,0.0,0.0);
	SetPlayerMarkerForPlayer(0, 0,0);
	ShowPlayerNameTagForPlayer(0, 0,0);
	SetPlayerMapIcon(0, 0, 0.0, 0.0, 0.0, 0,0);
	RemovePlayerMapIcon(0,0);
	SetPlayerCameraPos(0,0.0, 0.0, 0.0);
	SetPlayerCameraLookAt(0, 0.0, 0.0, 0.0);
	SetCameraBehindPlayer(0);
	AllowPlayerTeleport(0,0);
	IsPlayerConnected(0);
	IsPlayerInVehicle(0,0);
	IsPlayerInAnyVehicle(0);
	IsPlayerInCheckpoint(0);
	IsPlayerInRaceCheckpoint(0);
	SetPlayerTime(0, 0,0);
	TogglePlayerClock(0,0);
	SetPlayerWeather(0,0);
	GetPlayerTime(0,iVar,iVar);
	SetPlayerVirtualWorld(0,0);
	GetPlayerVirtualWorld(0);
	ForceClassSelection(0);
	SetPlayerWantedLevel(0, 0);
	GetPlayerWantedLevel(0);

	// a_vehicle.inc
	CreateVehicle(0,0.0,0.0,0.0,0.0,0,0,0);
	DestroyVehicle(0);
	GetVehiclePos(0,fVar,fVar,fVar);
	SetVehiclePos(0,0.0,0.0,0.0);
	GetVehicleZAngle(0,fVar);
	SetVehicleZAngle(0,0.0);
	SetVehicleParamsForPlayer(0,0,0,0);
	SetVehicleToRespawn(0);
	LinkVehicleToInterior(0,0);
	AddVehicleComponent(0,0);
	ChangeVehicleColor(0,0,0);
	ChangeVehiclePaintjob(0,0);
	SetVehicleHealth(0,0.0);
	GetVehicleHealth(0,fVar);
	AttachTrailerToVehicle(0, 0);
	DetachTrailerFromVehicle(0);
	IsTrailerAttachedToVehicle(0);
	SetVehicleNumberPlate(0,"");
	SetVehicleVirtualWorld(0,0);
	GetVehicleVirtualWorld(0);

	ApplyAnimation(0,"","",1.0,0,0,0,0,0);
	ClearAnimations(0);
	GetPlayerSpecialAction(0);
	SetPlayerSpecialAction(0, 0);
	EnableStuntBonusForPlayer(0, 0);
	EnableStuntBonusForAll(0);
	TogglePlayerSpectating(0, 0);
	PlayerSpectatePlayer(0, 0, 0);
	PlayerSpectateVehicle(0, 0, 0);
	SendPlayerMessageToPlayer(0, 0, "");
	SendPlayerMessageToAll(0, "");
	LimitGlobalChatRadius(fVar);
	CreatePickup(0, 0, fVar, fVar, fVar);
	DestroyPickup(0);
	UsePlayerPedAnims();
	DisableInteriorEnterExits();
	SetNameTagDrawDistance(fVar);
	BanEx(0, "");
	GetServerVarAsString("", Var, sizeof(Var));
	GetServerVarAsInt("");
	GetServerVarAsBool("");
	GetPlayerMenu(0);
	TextDrawSetString(Text:0, "");
	GangZoneCreate(0.0, 0.0, 0.0, 0.0);
	GangZoneDestroy(0);
	GangZoneShowForPlayer(0, 0, 0);
	GangZoneShowForAll(0, 0);
	GangZoneHideForPlayer(0, 0);
	GangZoneHideForAll(0);
	GangZoneFlashForPlayer(0, 0, 0);
	GangZoneFlashForAll(0, 0);
	GangZoneStopFlashForPlayer(0, 0);
	GangZoneStopFlashForAll(0);
	RemoveVehicleComponent(0, 0);
	GetVehicleTrailer(0);
	GetVehicleModel(0);
	SetTimer("", 0, 0);
	SetTimerEx("", 0, 0, "");

	// a_objects.inc
	CreateObject(0,0.0,0.0,0.0,0.0,0.0,0.0);
	SetObjectPos(0,0.0,0.0,0.0);
	GetObjectPos(0,fVar,fVar,fVar);
	SetObjectRot(0,0.0,0.0,0.0);
	GetObjectRot(0,fVar,fVar,fVar);
	IsValidObject(0);
	DestroyObject(0);
	MoveObject(0,0.0,0.0,0.0,0.0);
	StopObject(0);
	CreatePlayerObject(0,0,0.0,0.0,0.0,0.0,0.0,0.0);
	SetPlayerObjectPos(0,0,0.0,0.0,0.0);
	GetPlayerObjectPos(0,0,fVar,fVar,fVar);
	GetPlayerObjectRot(0,0,fVar,fVar,fVar);
	SetPlayerObjectRot(0,0,0.0,0.0,0.0);
	IsValidPlayerObject(0,0);
	DestroyPlayerObject(0,0);
	MovePlayerObject(0,0,0.0,0.0,0.0,0.0);
	StopPlayerObject(0,0);
	AttachObjectToPlayer(0, 0, fVar, fVar, fVar, fVar, fVar, fVar);
	AttachPlayerObjectToPlayer(0, 0, 0, fVar, fVar, fVar, fVar, fVar, fVar);

	// Menu's
	CreateMenu("", 0, 0.0, 0.0, 0.0, 0.0);
	DestroyMenu(Menu:0);
	AddMenuItem(Menu:0, 0, "");
	SetMenuColumnHeader(Menu:0, 0, "");
	ShowMenuForPlayer(Menu:0, 0);
	HideMenuForPlayer(Menu:0, 0);
	IsValidMenu(Menu:0);
	DisableMenu(Menu:0);
	DisableMenuRow(Menu:0,0);

	// Textdraw
	TextDrawCreate(0.0,0.0,"");
	TextDrawDestroy(Text:0);
	TextDrawLetterSize(Text:0, 0.0,0.0);
	TextDrawTextSize(Text:0, 0.0,0.0);
	TextDrawAlignment(Text:0, 0);
	TextDrawColor(Text:0,0);
	TextDrawUseBox(Text:0, 0);
	TextDrawBoxColor(Text:0, 0);
	TextDrawSetShadow(Text:0, 0);
	TextDrawSetOutline(Text:0, 0);
	TextDrawBackgroundColor(Text:0,0);
	TextDrawFont(Text:0, 0);
	TextDrawSetProportional(Text:0, 0);
	TextDrawShowForPlayer(0, Text:0);
	TextDrawHideForPlayer(0, Text:0);
	TextDrawShowForAll(Text:0);
	TextDrawHideForAll(Text:0);
}

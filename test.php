<?
function testcash(Player $player, $numparams, $params)
{
  GivePlayerMoney($player->id, 1000000);
  return COMMAND_OK;
}

function testdelv(Player $player, $numparams, $params)
{
  DestroyVehicle($params[1]);
  return COMMAND_OK;
}

function testmain(Player $player, $numparams, $params)
{
  if ($player->object == null)
    $player->Send(0xFFFF00FF, 'You dont have a main object assigned');
  else
    $player->Send(0xFFFF00FF, "Your main object is ".$player->object->ID()." and your distance is " . $player->object->position->DistanceTo($player->Position()));
}

function buyarmor(Player $player, $numparams, $params)
{
  $player->SetInjures(0);
}

function cw(Player $player, $numparams, $params)
{
  for ($i = 0; $i < 13; $i++)
  {
    GetPlayerWeaponData($player->id, $i, &$weapon, &$ammo);
    GetWeaponName($weapon, &$name);
    $player->Send(COLOR_GREEN, "[{$i}] {$name} => {$ammo}");
  }
}

function gotoz(Player $player, $numparams, $params)
{
  $pos = $player->Position();
  $pos->z += (int)$params[1];
  $player->SetPosition($pos);
  return COMMAND_OK;
}

function freeze(Player $player, $numparams, $params)
{
  TogglePlayerControllable($player->id, 0);
}

function unfreeze(Player $player, $numparams, $params)
{
  TogglePlayerControllable($player->id, 1);
}

function doanim(Player $player, $numparams, $params)
{
  $fs = 4.0;
  $opt1 = 1;
  $opt2 = 0;
  $opt3 = 0;
  $opt4 = 0;
  $opt5 = -1;

  if ($numparams > 2)
  {
    $fs = $params[3];
    if ($numparams > 3)
    {
      $opt1 = $params[4];
      if ($numparams > 4)
      {
        $opt2 = $params[5];
        if ($numparams > 5)
        {
          $opt3 = $params[6];
          if ($numparams > 6)
          {
            $opt4 = $params[7];
            if ($numparams > 7)
              $opt5 = $params[8];
          }
        }
      }
    }
  }

  ApplyAnimation($player->id, $params[1], $params[2], $fs, $opt1, $opt2, $opt3, $opt4, $opt5);
}

function dostopanim(Player $player, $numparams, $params)
{
  ClearAnimations($player->id);
}

function dojetpack(Player $player, $numparams, $params)
{
  $pos = $player->Position();
  $id = CreatePickup(370, 2, $pos->x, $pos->y, $pos->z);
  AddTimer('DestroyPickup', 2000, 0, $id);
  return COMMAND_OK;
}

function dogotoxyz(Player $player, $numparams, $params)
{
  $pos = $player->Position();
  $pos->x += (double)$params[1];
  $pos->y += (double)$params[2];
  $pos->z += (double)$params[3];
  $player->SetPosition($pos);
  return COMMAND_OK;
}

function dosavedata(Player $player, $numparams, $params)
{
  Accounts::Save();
  DB::Commit();
  return COMMAND_OK;
}

function doeject(Player $player, $numparams, $params)
{
  RemovePlayerFromVehicle($player->id);
  return COMMAND_OK;
}

function dofreezebank(Player $player, $numparams, $params)
{
  if ($target = Core::FindPlayer($player, $params[1]))
  {
    if ($params[2] == 'true')
    {
      $target->FreezeBank(true);
      $target->Send(COLOR_RED, 'Your bank account has been frozen');
    }
    else if ($params[2] == 'false')
    {
      $target->FreezeBank(false);
      $target->Send(COLOR_RED, 'Your bank account has been unfrozen');
    }
  }
  return COMMAND_OK;
}

function doeat(Player $player, $numparams, $params)
{
  $player->SetHunger(MAX_HUNGER_POINTS);
}

function drophouse(Player $player, $numparams, $params)
{
  Houses::DestroyHouse(-1439014222);
  return COMMAND_OK;
}

function dobankcash(Player $player, $numparams, $params)
{
  $player->GiveBank(500000);
  $player->Send(COLOR_YELLOW, '500,000$ added to your bank account');
  return COMMAND_OK;
}

function domaketime(Player $player, $numparams, $params)
{
  $time = Core::GetTime($params[1]);
  $player->Send(COLOR_YELLOW, "You made a time of {$time} seconds");
  return COMMAND_OK;
}

function domyping(Player $player, $numparams, $params)
{
  $player->Send(COLOR_YELLOW, "Your ping is {$player->ping}ms");
  return COMMAND_OK;
}

function docage(Player $player, $numparams, $params)
{
  $pos = $player->Position();
  $min = new Position($pos->x - 5, $pos->y - 5);
  $max = new Position($pos->x + 5, $pos->y + 5);
  $player->SetWorldBounds($min, $max);
  $player->Send(COLOR_YELLOW, "* You are now in a cage!");

  return COMMAND_OK;
}

function dounsync(Player $player, $numparams, $params)
{
  SetDisabledWeapons(array(WEAPON_DEAGLE, WEAPON_M4));
  return COMMAND_OK;
}

function doresync(Player $player, $numparams, $params)
{
  SetDisabledWeapons(array());
  return COMMAND_OK;
}

function givewep(Player $player, $numparams, $params)
{
  $player->GiveGun($params[1], $params[2]);
  return COMMAND_OK;
}

function getwep(Player $player, $numparams, $params)
{
  $bullets = $player->GetGunAmmo($params[1]);
  $player->Send(COLOR_YELLOW, "You have {$bullets} bullets of gun {$params[1]}");
  return COMMAND_OK;
}

function dodrivenext(Player $player, $numparams, $params)
{
  $pos = $player->Position();
  $nearest = null;
  $distance = 0;

  for ($i = 0; $i < 700; $i++)
  {
    GetVehiclePos($i, &$x, &$y, &$z);
    if ($x == 0 && $y == 0 && $z == 0)
      continue;
    $vpos = new Position($x, $y, $z);
    $dist = $vpos->DistanceTo($pos);
    if ($nearest === null || $dist < $distance)
    {
      $distance = $dist;
      $nearest = $i;
    }
  }

  if ($nearest !== null)
    PutPlayerInVehicle($player->id, $nearest, 0);
  return COMMAND_OK;
}

function dopassengerof(Player $player, $numparams, $params)
{
  if ($target = Core::FindPlayer($player, $params[1]))
  {
    if (IsPlayerInAnyVehicle($target->id))
    {
      $vid = GetPlayerVehicleID($target->id);
      PutPlayerInVehicle($player->id, $vid, 1);
    }
  }

  return COMMAND_OK;
}

function testinit()
{
  CommandHandler::Register('myping', 0, null, 'domyping', '', 1);
  CommandHandler::Register('cash', 0, null, 'testcash', '', 1);
  CommandHandler::Register('delv', 1, null, 'testdelv', '[vehicle id]', 1);
  CommandHandler::Register('main', 0, null, 'testmain', '', 1);
  CommandHandler::Register('buyarmor', 0, null, 'buyarmor', '', 1);
  CommandHandler::Register('cw', 0, null, 'cw', '', 1);
  CommandHandler::Register('gotoz', 1, null, 'gotoz', '', 1);
  CommandHandler::Register('freeze', 1, null, 'freeze', '', 1);
  CommandHandler::Register('unfreeze', 1, null, 'unfreeze', '', 1);
  CommandHandler::Register('anim', 2, null, 'doanim', '', 1);
  CommandHandler::Register('stopanim', 0, null, 'dostopanim', '', 1);
  CommandHandler::Register('jetpack', 0, null, 'dojetpack', '', 1);
  CommandHandler::Register('gotoxyz', 3, null, 'dogotoxyz', 'x y z', 1);
  CommandHandler::Register('savedata', 0, null, 'dosavedata', '', 1);
  CommandHandler::Register('eject', 0, null, 'doeject', '', 1);
  CommandHandler::Register('freezebank', 2, null, 'dofreezebank', '[ID] [true/false]', 1);
  CommandHandler::Register('eat', 0, null, 'doeat', '', 1);
  CommandHandler::Register('drophouse', 0, null, 'drophouse', '', 1);
  CommandHandler::Register('bankcash', 0, null, 'dobankcash', '', 1);
  CommandHandler::Register('maketime', 1, null, 'domaketime', '[timeformat]', 1);
  CommandHandler::Register('cage', 0, null, 'docage', '', 1);
  CommandHandler::Register('unsync', 0, null, 'dounsync', '', 1);
  CommandHandler::Register('resync', 0, null, 'doresync', '', 1);
  CommandHandler::Register('givewep', 2, null, 'givewep', '[weapon] [ammo]', 1);
  CommandHandler::Register('getwep', 1, null, 'getwep', '[weapon]', 1);
  CommandHandler::Register('drivenext', 0, null, 'dodrivenext', '', 1);
  CommandHandler::Register('passengerof', 1, null, 'dopassengerof', '[ID]', 1);
}

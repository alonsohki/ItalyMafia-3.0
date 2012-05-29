<?
/**
 ** class Player
 **
 ** This class controls all the player temporary data, and performs actions on them.
 **
 ** Attributes:
 ** - next, prev: Links to the players list
 ** - id:         Player id
 ** - account:    Associated account with this player
 ** - name:       Player name
 ** - ip:         The player ip
 ** - ping:       The player current ping
 ** - position:   The player coordinates, autoupdated
 ** - location:   The player location, autoupdated
 ** - sector:     Sector in the location's area in what the player is
 ** - object:     Current player assigned object
 ** - checkHealth: Toggles if the core must check the player health for hunger and injures
 ** - hunger:     Player hunger, if negative substracts health
 ** - injures:    Player injures lost health
 ** - timedeath:  Player death time, to allow others ending after a time, or finishing him
 ** - dead:       Tells if a player is dead
 ** - vehicle:    The player bought vehicle
 ** - state:      SAMP player state
 ** - money:      Player's money
 ** - bank:       Player's bank money
 ** - faction:    Player's faction, null if none
 ** - rank:       Player's rank inside his faction
 ** - skin:       Player skin
 ** - color:      Player color
 ** - alpha:      Player color alpha (transparency)
 ** - adminlevel: Player admin level
 ** - menu:       Player current browsing menu
 ** - updates:    Number of performed updates
 **
 ** - togjq:      If true, the player receives join/quits
 ** - togooc:     If true, the player receives global OOC messages
 ** - deadtimer:  Timer to set people alive after spawn
 **
 **
 ** Methods:
 ** - SetName:    Changes the player name (NOT ACCOUNT NAME!)
 ** - Send:       Sends a message to the player
 ** - SendInfo:   Sends a server info message
 ** - Spawn:      Forces theplayer spawning
 **
 **/
class Player
{
  private $spawn_position;
  private $spawn_weapons;
  private $spawn_skin = 0;
  private $spawn_team;

  public  $id;
  public  $account = null;
  public  $name;
  public  $ip;
  public  $ping = 0;
  private $position;
  private $last_updated_position;
  public  $location = null;
  public  $vworld = 0;
  public  $sector;
  public  $checkpoint = null;
  public  $object = null;
  private $hunger = 1000;
  private $injures = 0;
  private $dead = false;
  public  $timedeath = 0;
  public  $firstSpawn = true;
  private $freezed = false;
  private $vehicle = null;
  private $state;
  private $lagprotection = 0;
  private $lagprotection_guns = 0;
  private $money;
  private $bank;
  private $faction = null;
  private $onduty = false;
  private $rank = null;
  private $level = 1;
  private $experience;
  private $marry_name = null;
  private $marry_sex;
  private $sex = 'M';
  private $age = 15;
  private $reports = null;
  private $bankfreezed = false;
  private $strength = 0;
  private $skin = 0;
  private $color;
  private $alpha = 0x00;
  private $seeingNametags;
  private $adminlevel = 0;
  private $speed = 0.0;
  private $incar_draw = null;
  private $animation = null;
  private $animtimer = null;
  private $menu = null;
  private $updates = 0;
  private $skills = array();
  private $upgradepts = 0;
  private $checkhealth = false;
  private $checkguns = false;
  private $speedometer = 'kmh';
  private $jailtime = 0;
  private $guns;

  public  $togjq = true;
  public  $togooc = true;

  public function __construct($id)
  {
    $this->id = $id;
    GetPlayerName($id, &$this->name);
    $this->position = new Position();
    $this->last_updated_position = new Position();
    $this->spawn_position = new Position(0, 0, 0, 0);
    $this->spawn_weapons = array();
    $this->spawn_skin = 0;
    $this->spawn_team = 0;
    $this->UpdateSpawnInfo();
    /* Initially, set the player in the location0 OOB sector */
    $this->sector = Locations::Find(0)->Locate(new Position(99999999, 99999999), 0, 0);
    $this->sector->AddPlayer($this);
    $this->PreloadAnimLibs();

    $this->SetColor(COLOR_CIVILIAN);
    /* TODO: Set alpha to 0x00 when you build a release */
    $this->SetAlpha(0xFF);
    $this->Update();

    /* Hide this player nametag for all, and all nametags for him */
    $this->seeingNametags = array_fill(0, MAX_PLAYERS, false);
    foreach (Players::Get() as $p)
    {
      $p->CanIdentify($this, false);
      $this->CanIdentify($p, false);
    }

    GetPlayerIP($this->id, &$this->ip);

    $this->guns = array_fill(0, 13, 0);
  }

  public function GetData()
  {
    $data = array();
    $data['location']    = $this->location->ID();
    $data['x']           = $this->position->x;
    $data['y']           = $this->position->y;
    $data['z']           = $this->position->z;
    $data['angle']       = $this->position->angle;
    $data['injures']     = $this->injures;
    $data['hunger']      = $this->hunger;
    $data['money']       = $this->money;
    $data['bank']        = $this->bank;
    $data['level']       = $this->level;
    $data['experience']  = $this->experience;
    if ($this->faction != null)
      $data['faction']   = $this->faction->ID();
    else
      $data['faction']   = null;
    $data['onduty']      = $this->onduty;
    $data['rank']        = $this->rank;
    $data['sex']         = $this->sex;
    $data['age']         = $this->age;
    $data['reports']     = $this->reports;
    $data['bankfreezed'] = $this->bankfreezed;
    $data['strength']    = $this->strength;
    $data['skin']        = $this->skin;
    $data['vworld']      = $this->vworld;
    $data['speedo']      = $this->speedometer;
    $data['jailtime']    = $this->jailtime;

    $data['upgradepts']  = $this->upgradepts;
    foreach ($this->skills as $skill => $value)
    {
      $data['sk_' . Players::GetSkillDBName($skill)] = $value;
    }

    return $data;
  }

  public function Stream()
  {
    if ($this->location != null)
    {
      $this->sector->StreamCheckpoints($this);
      $this->location->StreamObjects($this, $this->position, $this->sector);
    }
  }

  /**
   ** When you set something in the player, like money health or armor it takes some time to take effect
   ** depending on their ping. You could set their armor to 100% after buying armor and set the player
   ** injures to 0 and armor to 100, but if the updater is executed 20ms later the player still hasnt
   ** that armor and the injures data is then outdated. To avoid this, we can prevent the updater of
   ** getting that data for some cycles. The timer is executed every 500ms, so increasing a value of
   ** 4 means 1.5-2 seconds of no checks. Of course, if the player has a ping higher than 2 seconds he
   ** wont be protected, but with those high pings it's their problem.
   **/
  public function LagProtection()
  {
    $this->lagprotection = 4;
  }

  public function LagProtectionGuns()
  {
    $this->lagprotection_guns = 4;
  }

  public function Update()
  {
    if ($this->location == null)
      return;

    $this->updates++;

    /* Update player position, speed and area sector */
    GetPlayerPos($this->id, &$this->position->x, &$this->position->y, &$this->position->z);
    GetPlayerFacingAngle($this->id, &$this->position->angle);
    $this->speed = $this->last_updated_position->DistanceTo($this->position);
    $sector = $this->location->Locate($this->position);
    if ($this->sector->id != $sector->id)
    {
      $this->sector->RemovePlayer($this);
      $sector->AddPlayer($this);
    }
    $this->sector = $sector;
    $this->last_updated_position = clone $this->position;

    /* Save player state */
    $this->state = GetPlayerState($this->id);

    if ($this->firstSpawn)
      return;

    /* Update ping */
    $this->ping = GetPlayerPing($this->id);

    /* Check the player vehicle */
    if (IsPlayerInAnyVehicle($this->id))
    {
      if ($this->vehicle === null)
      {
        /* Show incar textdraw */
        $this->incar_draw = Players::GetIncarDraw($this->id);
        TextDrawShowForPlayer($this->id, $this->incar_draw);
        TextDrawSetString($this->incar_draw, '...');
      }

      $this->vehicle = Vehicles::FindByID(GetPlayerVehicleID($this->id));
      if ($this->vehicle != null)
      {
        if ($this->state == PLAYER_STATE_DRIVER && $this->vehicle->Type() == VEHICLE_SHOP)
          $this->Freeze();
      }
    }
    else
    {
      if ($this->incar_draw !== null)
      {
        /* Hide incar textdraw */
        TextDrawHideForPlayer($this->id, $this->incar_draw);
        $this->incar_draw = null;
      }

      if ($this->vehicle != null)
      {
        if ($this->vehicle->Type() == VEHICLE_SHOP)
        {
          /* Player was freezed in a shop vehicle but left it, so allow him to move again */
          $this->Freeze(false);
        }
      }
      $this->vehicle = null;
    }

    /* Update the incar textdraw */
    if ($this->incar_draw !== null && $this->updates & 1)
    {
      if ($this->speedometer == 'mph')
        $speed = $this->speed * 4.464;
      else
        $speed = $this->speed * 7.2;
      $string = sprintf('~y~Speed~n~~w~%d %s~n~~y~Fuel~n~~w~%.1f LT', (int)$speed, $this->speedometer, $this->vehicle->Fuel());
      TextDrawSetString($this->incar_draw, $string);
    }


    /**
     ** Update the nametag drawing
     **/
    $sectors = $this->sector->FindSectors($this->position, 12.5);
    $watchplayers = array();
    foreach ($sectors as $sector)
    {
      foreach ($sector->GetPlayers() as $player)
      {
        if ($player->id >= $this->id)
          break;
        if ($this->position->DistanceTo($player->position) < 12.5)
          $watchplayers[$player->id] = $player;
      }
    }
    ksort($watchplayers);

    $i = 0;
    foreach ($watchplayers as $id => $player)
    {
      for (; $i < $id; $i++)
      {
        if ($this->seeingNametags[$i] == true)
        {
          $p = Players::FindByID($i);
          if ($p)
          {
            $this->CanIdentify($p, false);
            $p->CanIdentify($this, false);
          }
          $this->seeingNametags[$i] = false;
        }
      }
      $i++;

      if ($this->vehicle == $player->vehicle)
        $equalv = true;
      else
        $equalv = false;

      if (!$player->vehicle || !$player->vehicle->HasWindows() || ($this->vehicle && $equalv))
      {
        if ($this->seeingNametags[$id] == false)
          $this->CanIdentify($player, true);
      }
      else if ($this->seeingNametags[$id] == true)
        $this->CanIdentify($player, false);


      if (!$this->vehicle || !$this->vehicle->HasWindows() || ($player->vehicle && $equalv))
      {
        if ($player->seeingNametags[$this->id] == false)
          $player->CanIdentify($this, true);
      }
      else if ($player->seeingNametags[$this->id] == true)
        $player->CanIdentify($this, false);
    }

    while ($i < $this->id)
    {
      if ($this->seeingNametags[$i] == true)
      {
        $player = Players::FindByID($i);
        if ($player)
        {
          $this->CanIdentify($player, false);
          $player->CanIdentify($this, false);
        }
      }
      $i++;
    }



    /**
     ** Update the hunger and injures health
     **/
    if ($this->checkhealth && !$this->dead)
    {
      $cur = time();

      if ($this->timedeath > 0)
      {
        if ($cur - $this->timedeath > 60)
        {
          /* If the player was 60 seconds dieing, finish him */
          $this->Kill();
        }
        else if ($cur - $this->timedeath > 5)
        {
          /* After 5 seconds, other players are allowed to finish him */
          GetPlayerHealth($this->id, &$health);
          if ($health + 2 < ($this->hunger * 100 / MAX_HUNGER_POINTS))
          {
            $this->Kill();
          }
        }
        else
        {
          $health = $this->hunger * 100 / MAX_HUNGER_POINTS;
          if ($health <= 0)
          {
            $this->Kill();
          }
          else
            SetPlayerHealth($this->id, $health);
        }
      }
      else
      {
        $this->hunger--;
        $health = $this->hunger * 100 / MAX_HUNGER_POINTS;

        if ($health == 0)
        {
          $this->Kill();
        }
        else if (!$this->lagprotection)
        {
          GetPlayerHealth($this->id, &$tmphealth);

          if ($tmphealth == 0)
          {
            $this->Kill();
          }
          else
          {
            if (!($this->hunger % 20)) /* Update health only every 10 seconds */
              SetPlayerHealth($this->id, $health);

            GetPlayerArmour($this->id, &$armor);
            if ($tmphealth + 2 < $health) /* Player lost health (fall), so apply to the injures (armor) health */
            {
              SetPlayerHealth($this->id, $health);
              $armor -= $health - $tmphealth;
              SetPlayerArmour($this->id, $armor);
            }
            if ($armor < 0)
              $armor = 0; /* The lowest possible armor points is 0 */

            $injures = 100 - $armor;

            if ($injures < $this->injures)
            {
              /* The player has less injures than he should have */
              SetPlayerArmour($this->id, 100 - $this->injures);
            }
            else if ($injures > $this->injures)
            {
              /* The player has more injures that the last registered ones */
              $this->injures = $injures;
            }

            if ($this->injures == 100)
            {
              /* The player is dead, full injures */
              if (IsPlayerInAnyVehicle($this->id))
              {
                $this->Kill();
              }
              else
              {
                $anim = Animations::GetDeathAnim();
                $this->ClearAnimations();
                $this->Animate($anim);
                $this->timedeath = time();
                $this->Send(COLOR_YELLOW, '* You are dieing! You will be dead in one minute if you don\'t get a medic help.');
                $this->Send(COLOR_YELLOW, '* You can also accept your own death after 10 seconds using /die command.');
                SetPlayerArmour($this->id, 0);
              }
            }
          }
        }
      }
    }

    /* If the player has lag protection, decrease one unit */
    if ($this->lagprotection)
      $this->lagprotection--;
    if ($this->lagprotection_guns)
      $this->lagprotection_guns--;


    /* Update the jail time */
    if ($this->jailtime != 0 && $this->updates & 1)
    {
      $this->jailtime--;
      if ($this->jailtime < 1)
      {
        /* Unjail the player */
        $this->jailtime = 1;
        $this->Unjail();
      }
      else
      {
        /* Ensure that the player is still in jail */
        $bounds = PoliceDepartment::AdminCellBounds();
        if (!$this->position->IsInCube($bounds['min'], $bounds['max']))
        {
          $this->SetLocation(PoliceDepartment::Instance());
          $this->SetPosition(PoliceDepartment::AdminCell());
        }

        /* Send the time remaining message */
        GameTextForPlayer($this->id, "Jail time remaining: {$this->jailtime} seconds", 1000, 4);
      }
    }

    /*
     * Update animation
     */
    if ($this->animation && $this->updates & 1 && $this->vehicle == null && ($this->animation->forced || $this->animation->continue))
    {
      ApplyAnimation($this->id, $this->animation->lib, $this->animation->anim, 4.0,
                     $this->animation->loop, $this->animation->movex, $this->animation->movey,
                     $this->animation->continue, $this->animation->time);
    }
  }

  public function GiveGun($gunid_, $ammo_)
  {
    $gunid = (int)$gunid_;
    $ammo  = (int)$ammo_;
    GivePlayerWeapon($this->id, $gunid, $ammo);
    $this->guns[$gunid] += $ammo;
    $this->LagProtectionGuns();
  }

  public function GetGunAmmo($gunid)
  {
    return $this->guns[(int)$gunid];
  }

  public function TakeGun($gunid)
  {
    unset($this->guns[(int)$gunid]);
    
  }

  public function ResetGuns()
  {
  }

  public function RestoreGuns()
  {
  }

  public function SetDead($state)
  {
    if ($state == false)
    {
      $this->timedeath = 0;
    }
    $this->dead = $state;
  }

  public function CheckHealth($state)
  {
    $this->checkhealth = $state;
  }

  public function CheckGuns($state)
  {
    $this->checkguns = $state;
  }

  public function Kick($reason = null)
  {
    if ($reason != null)
      $this->Send(COLOR_KICK, $reason);
    Kick($this->id);
  }

  public function IsDriver()
  {
    if ($this->state == PLAYER_STATE_DRIVER)
      return true;
    return false;
  }

  public function GetVehicle()
  {
    return $this->vehicle;
  }

  public function Freeze($state = true)
  {
    if ($state == true)
    {
      $this->freezed = true;
      TogglePlayerControllable($this->id, 0);
    }
    else
    {
      $this->freezed = false;
      TogglePlayerControllable($this->id, 1);
    }
  }

  public function WantsEnterExitVehicle()
  {
    if ($this->vehicle && $this->vehicle->Type() == VEHICLE_SHOP)
    {
      RemovePlayerFromVehicle($this->id);
    }
  }

  public function Kill()
  {
    SetPlayerHealth($this->id, 0);
    $this->SetDead(true);
    $this->CheckHealth(false);
    $this->LagProtection();
  }

  public function SetHunger($hunger)
  {
    if ($hunger > MAX_HUNGER_POINTS)
      $hunger = MAX_HUNGER_POINTS;
    $this->hunger = (int)$hunger;
    $health = $this->hunger * 100 / MAX_HUNGER_POINTS;
    SetPlayerHealth($this->id, $health);
    $this->LagProtection();
  }

  public function GetHunger()
  {
    return $this->hunger;
  }

  public function SetInjures($injures)
  {
    $this->injures = $injures;
    SetPlayerArmour($this->id, 100 - $injures);
    $this->LagProtection();
  }

  public function GetInjures()
  {
    return $this->injures;
  }

  public function Destroy()
  {
    if ($this->account != null)
    {
      $this->account->Destroy();
      $this->account = null;
    }
    if ($this->location != null)
    {
      $this->location->UnstreamObjects($this);
    }
    if ($this->animtimer != null)
    {
      KillTimer($this->animtimer);
      $this->animtimer = null;
    }
    if ($this->faction != null)
      $this->faction->UnregisterMember($this);
    $this->sector->RemovePlayer($this);
  }

  public function SetName($name)
  {
    if ($name != $this->name)
    {
      Log::Append(LOG_JOINQUIT, "[{$this->id}] {$this->name} nick changed to {$name}");
      SetPlayerName($this->id, $name);
      $this->name = $name;
    }
  }

  public function SetLocation(Location $location, $vworld_offset = 0, LocationEntrance $entrance = null)
  {
    if ($this->location != null)
    {
      if ($this->location->ID() == $location->ID())
        return;
      $this->location->UnstreamObjects($this);
    }
    $this->location = $location;
    $location->MovePlayer($this, $vworld_offset, $entrance);
    SetCameraBehindPlayer($this->id);
  }

  public function SetVirtualWorld($vworld)
  {
    $this->vworld = $vworld;
    SetPlayerVirtualWorld($this->id, $this->vworld);
  }

  public function SetPosition(Position $position)
  {
    SetPlayerPos($this->id, $position->x, $position->y, $position->z);
    SetPlayerFacingAngle($this->id, $position->angle);
    $this->Update();
  }

  public function SetPositionFindZ(Position $position)
  {
    SetPlayerPosFindZ($this->id, $position->x, $position->y, $position->z);
    SetPlayerFacingAngle($this->id, $position->angle);
    $this->Update();
  }

  public function Position()
  {
    GetPlayerPos($this->id, &$this->position->x, &$this->position->y, &$this->position->z);
    GetPlayerFacingAngle(&$this->id, &$this->position->angle);
    return $this->position;
  }

  public function SetCamera(Position $camera)
  {
    SetPlayerCameraPos($this->id, $camera->x, $camera->y, $camera->z);
  }

  public function CameraLookAt(Position $lookat)
  {
    SetPlayerCameraLookAt($this->id, $lookat->x, $lookat->y, $lookat->z);
  }

  public function Send($color, $message)
  {
    SendClientMessage($this->id, $color, substr($message, 0, 256));
  }

  public function SendInfo($message)
  {
    SendClientMessage($this->id, COLOR_INFO, ".: Info: {$message} :.");
  }

  public function UpdateSpawnInfo()
  {
    SetSpawnInfo($this->id, $this->spawn_team, $this->spawn_skin,
                 $this->spawn_position->x,
                 $this->spawn_position->y,
                 $this->spawn_position->z,
                 $this->spawn_position->angle,
                 WEAPON_DEAGLE, 300, WEAPON_M4, 1000, WEAPON_SAWEDOFF, 1000);
  }

  public function Spawn()
  {
    SpawnPlayer($this->id);
    $this->LagProtection();
    $this->Update();
  }

  public function SetSpawnPosition(Position $position)
  {
    $this->spawn_position = $position;
    $this->UpdateSpawnInfo();
  }

  public function SetSpawnSkin($skinid)
  {
    if ($skinid != $this->spawn_skin)
    {
      $this->spawn_skin = $skinid;
      SetPlayerSkin($this->id, $skinid);
      $this->UpdateSpawnInfo();
    }
  }

  private function PreloadAnimLibs()
  {
    ApplyAnimation($this->id, 'PED', 'null', 0.0, 0, 0, 0, 0, 0);
    ApplyAnimation($this->id, 'MISC', 'null', 0.0, 0, 0, 0, 0, 0);
    ApplyAnimation($this->id, 'WUZI', 'null', 0.0, 0, 0, 0, 0, 0);
    ApplyAnimation($this->id, 'SUNBATHE', 'null', 0.0, 0, 0, 0, 0, 0);
    ApplyAnimation($this->id, 'BEACH', 'null', 0.0, 0, 0, 0, 0, 0);
    ApplyAnimation($this->id, 'FOOD', 'null', 0.0, 0, 0, 0, 0, 0);
    ApplyAnimation($this->id, 'DEALER', 'null', 0.0, 0, 0, 0, 0, 0);
    ApplyAnimation($this->id, 'benchpress', 'null', 0.0, 0, 0, 0, 0, 0);
    ApplyAnimation($this->id, 'GYMNASIUM', 'null', 0.0, 0, 0, 0, 0, 0);
  }

  public function GiveMoney($money)
  {
    $this->money += $money;
    GivePlayerMoney($this->id, $money);
  }

  public function SetMoney($money)
  {
    $this->money = $money;

    ResetPlayerMoney($this->id);
    GivePlayerMoney($this->id, $money);
  }

  public function GetMoney()
  {
    return $this->money;
  }

  public function GiveBank($money)
  {
    $this->bank += $money;
  }

  public function SetBank($money)
  {
    $this->bank = $money;
  }

  public function GetBank()
  {
    return $this->bank;
  }

  private function UpdateFactionData()
  {
    if ($this->faction)
    {
      if ($this->onduty)
      {
        /* Update skin */
        $skin = $this->faction->GetSkinForPlayer($this);
        if ($skin != -1)
          $this->SetSpawnSkin($skin);
        else
          $this->SetSpawnSkin($this->skin);

        /* Update color */
        $color = $this->faction->GetPlayerColor($this);
        $this->SetColor($color);
      }
      else
      {
        $this->SetSpawnSkin($this->skin);
        $this->SetColor(COLOR_CIVILIAN);
      }
      $this->SetName($this->faction->GetNameForPlayer($this));
    }
    else
    {
      $this->SetSpawnSkin($this->skin);
      $this->SetName($this->account->name);
      $this->SetColor(COLOR_CIVILIAN);
    }
  }

  public function SetFaction($id)
  {
    if ($this->faction != null)
      $this->faction->UnregisterMember($this);

    $this->faction = Factions::FindByID($id);
    if ($this->faction != null)
      $this->faction->RegisterMember($this);
    else
      $this->onduty = true;
  }

  public function GetFaction($check_duty = true)
  {
    if ($check_duty && $this->onduty == false)
      return null;
    return $this->faction;
  }

  public function SetRank($rank)
  {
    $this->rank = $rank;
    $this->UpdateFactionData();
  }

  public function SetOnDuty($state)
  {
    $this->onduty = $state;
    if ($this->faction != null)
      $this->UpdateFactionData();
  }

  public function IsOnDuty()
  {
    return $this->onduty;
  }

  public function GetRank()
  {
    return $this->rank;
  }

  public function GetLevel()
  {
    return $this->level;
  }

  public function SetLevel($level)
  {
    $this->level = (int)$level;
    SetPlayerScore($this->id, $this->level);
  }

  public function GetExperience()
  {
    return $this->experience;
  }

  public function SetExperience($experience)
  {
    $this->experience = $experience;
  }

  public function GetMarried()
  {
    if ($this->marry_name != null)
      return array('name' => $this->marry_name, 'sex' => $this->marry_sex);
    return null;
  }

  public function MarryTo($name, $sex)
  {
    $this->marry_name = $name;
    $this->marry_sex = $sex;
  }

  public function GetSex()
  {
    return $this->sex;
  }

  public function SetSex($sex)
  {
    if ($sex == 'M')
      $this->sex = 'M';
    else
      $this->sex = 'F';
  }
  
  public function GetAge()
  {
    return $this->age;
  }
  
  public function SetAge($age)
  {
    $this->age = $age;
  }

  public function SetReports($reports)
  {
    $this->reports = $reports;
  }

  public function GetReports()
  {
    return $this->reports;
  }

  public function FreezeBank($status)
  {
    $this->bankfreezed = $status;
    if ($this->bankfreezed)
    {
      /* Unsell all this player properties */
      Houses::UnsellPlayerRooms($this);
    }
  }

  public function BankFreezed()
  {
    return $this->bankfreezed;
  }

  public function SetStrength($strength)
  {
    if ($strength < 0)
      $strength = 0;
    else if ($strength > MAX_STRENGTH_POINTS)
      $strength = MAX_STRENGTH_POINTS;
    $this->strength = $strength;
  }

  public function GetStrength()
  {
    return $this->strength;
  }

  public function SetSkin($skin)
  {
    $this->skin = $skin;
    $this->SetSpawnSkin($this->skin);
  }

  public function GetSkin()
  {
    return $this->skin;
  }



  /**
   ** Animations
   **/
  public function Animate(Animation $animation)
  {
    if ($this->animation)
    {
      if ($this->animation->forced)
        return;
      else
        $this->ClearAnimations();
    }
    $this->animation = $animation;

    if ($animation->forced == false)
    {
      $draw = Animations::GetStoppingDraw();
      TextDrawShowForPlayer($this->id, $draw);
    }
    $this->ApplyAnimation();
  }

  public static function AnimationStep_static(Player $player)
  {
    $player->animtimer = null;
    $player->AnimationStep();
  }

  private function ApplyAnimation()
  {
    ApplyAnimation($this->id, $this->animation->lib, $this->animation->anim, 4.0,
                   $this->animation->loop, $this->animation->movex, $this->animation->movey,
                   $this->animation->continue, $this->animation->time);

    if ($this->animation->steptime > 1)
    {
      $this->animtimer = AddTimer(array('Player', 'AnimationStep_static'), $this->animation->steptime, 0, $this);
    }
  }

  private function AnimationStep()
  {
    if ($this->animation)
    {
      if ($this->animation->child)
      {
        if ($this->animation->stop_cbk)
          call_user_func($this->animation->stop_cbk, $this, $this->animation->stop_data);
        $this->animation = $this->animation->child;
        $this->ApplyAnimation();
      }
      else
      {
        $this->ClearAnimations();
      }
    }
  }

  public function ClearAnimations($stop_current = true)
  {
    if ($this->animation)
    {
      if ($stop_current)
      {
        if ($this->animation->stop)
        {
          $this->animation = $this->animation->stop;
          $this->ApplyAnimation();
        }
        else
        {
          ClearAnimations($this->id);
        }
      }

      if ($this->animation->stop_cbk)
        call_user_func($this->animation->stop_cbk, $this, $this->animation->stop_data);

      if ($this->animation->forced == false)
      {
        $draw = Animations::GetStoppingDraw();
        TextDrawHideForPlayer($this->id, $draw);
      }

      $this->animation = null;
      if ($this->animtimer)
      {
        KillTimer($this->animtimer);
        $this->animtimer = null;
      }
    }
  }

  public function StopAnimation()
  {
    if ($this->animation && !$this->animation->forced)
    {
      $this->ClearAnimations();
    }
  }

  public function GetAnimation()
  {
    return $this->animation;
  }




  public function SetColor($color)
  {
    $this->color = (int)$color;
    SetPlayerColor($this->id, ($this->color & 0xFFFFFF00) | $this->alpha);
  }

  public function SetAlpha($alpha)
  {
    $this->alpha = (int)$alpha & 0x000000FF;
    SetPlayerColor($this->id, ($this->color & 0xFFFFFF00) | $this->alpha);
  }

  public function CanIdentify(Player $player, $can)
  {
    if ($this->adminlevel == 0)
    {
      $this->seeingNametags[$player->id] = $can;
      ShowPlayerNameTagForPlayer($this->id, $player->id, $can);
    }
    else if ($this->seeingNametags[$player->id] == false)
    {
      $this->seeingNametags[$player->id] = true;
      ShowPlayerNameTagForPlayer($this->id, $player->id, true);
    }
  }

  public function SetAdminLevel($level)
  {
    $this->adminlevel = (int)$level;
    if ($this->adminlevel > 0)
    {
      /* Show nametags */
      foreach (Players::Get() as $p)
        $this->CanIdentify($p, true);
    }
  }

  public function GetAdminLevel()
  {
    return $this->adminlevel;
  }

  public function GetState()
  {
    return GetPlayerState($this->id);
  }


  /**
   ** Menus
   **/
  public function SetMenu(Menu $menu = null)
  {
    if ($this->menu == $menu)
      return;

    if ($this->menu != null)
      $this->menu->HideForPlayer($this);
    if ($menu != null)
      $menu->ShowForPlayer($this);
    $this->menu = $menu;
  }

  public function GetMenu()
  {
    return $this->menu;
  }

  /**
   ** Skills
   **/
  public function SetSkill($skill, $value)
  {
    $this->skills[$skill] = $value;
  }

  public function GetSkill($skill)
  {
    if (isset($this->skills[$skill]))
      return $this->skills[$skill];
    return 0;
  }

  public function SetUpgradePoints($points)
  {
    $this->upgradepts = $points;
  }

  public function GetUpgradePoints()
  {
    return $this->upgradepts;
  }

  public function ApplyCookingSkill($food)
  {
    switch ($this->skills[SKILL_COOKING])
    {
      case 1:
        $food *= 1.02;
        break;
      case 2:
        $food *= 1.05;
        break;
      case 3:
        $food *= 1.09;
        break;
      case 4:
        $food *= 1.14;
        break;
      case 5:
        $food *= 1.19;
        break;
    }

    return $food;
  }

  public function SetSpeedometer($type)
  {
    if ($type == 'kmh')
      $this->speedometer = 'kmh';
    else if ($type == 'mph')
      $this->speedometer = 'mph';
  }

  public function SetWorldBounds(Position $min = null, Position $max = null)
  {
    if ($min == null)
      SetPlayerWorldBounds($this->id, 40000, -40000, 40000, -40000);
    else
      SetPlayerWorldBounds($this->id, $max->x, $min->x, $max->y, $min->y);
  }

  public function Jail($time_)
  {
    $time = (int)$time_;

    if ($time < 1)
      return;
    $this->jailtime = $time;
    $this->SetLocation(PoliceDepartment::Instance());
    $this->SetPosition(PoliceDepartment::AdminCell());
    SetPlayerTeam($this->id, 1); /* Friendly fire */
  }

  public function Unjail()
  {
    if ($this->jailtime != 0)
    {
      $this->jailtime = 0;
      $this->SetLocation(PoliceDepartment::Instance());
      $this->SetPosition(PoliceDepartment::CellOut());
      SetPlayerTeam($this->id, NO_TEAM); /* Disable friendly fire */
    }
  }

  public function IsJailed()
  {
    if ($this->jailtime != 0)
      return true;
    return false;
  }
}
?>

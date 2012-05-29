<?

class Account
{
  public  $data = null;
  public  $stats = null;
  public  $wrongpassAttempts = 3;
  private $loginTimer;
  public  $player;
  public  $name;
  private $authed = false;
  private $id;

  public function __construct(Player $player, $data)
  {
    $this->player = $player;
    $this->name = $data['name'];
    $this->id = $data['id'];
    $this->data = $data;
    $this->loginTimer = AddTimer(array('Account', 'CallLoginTimer'), 2 * 60 * 1000, 0, $this);
  }

  public function ID()
  {
    return $this->id;
  }

  public static function CallLoginTimer($obj)
  {
    $obj->LoginTimeout();
  }

  public function LoginTimeout()
  {
    $this->player->Send(COLOR_RED, '* Your time to login expired. Try to reconnect.');
    $this->loginTimer = null;
    $this->player->Kick();
  }

  public function Destroy()
  {
    if ($this->loginTimer != null)
    {
      KillTimer($this->loginTimer);
    }
    $this->player = null;
  }

  public function Auth()
  {
    /* Load the player stats */
    $this->stats = Accounts::GetLoadedStats($this->id);
    if ($this->stats == null)
      $this->stats = DB::GetAccountStats($this->id);

    if ($this->stats == null)
    {
      $this->player->Send(COLOR_RED, '* Error loading your stats, contact an administrator.');
      $this->player->Kick();
      return;
    }

    Log::Append(LOG_JOINQUIT, "[{$this->player->id}] {$this->player->name} logged in");

    /* Set the player authed and stop the limit login time */
    Accounts::EraseLoadedStats($this->id);
    $this->authed = true;
    $this->data['password'] = null;
    KillTimer($this->loginTimer);
    $this->loginTimer = null;
    $this->player->SetAdminLevel($this->data['adminlevel']);

    /* Save the player last ip */
    $data = array();
    $data['id'] = $this->id;
    $data['last_ip'] = $this->player->ip;
    DB::SaveAccount($data);


    /*
     * Set the player starting location
     */
    $location0 = Locations::Find(0);
    $location = Locations::Find($this->stats['location']);
    $vworld = $this->stats['vworld'];
    if ($location != null)
    {
      if ($location->StayAfterLogout() == false)
      {
        /* Find the location entrance to location0 */
        while ($location->GetParent() != null && $location->GetParent() != $location0)
          $location = $location->GetParent();
        if ($location->GetParent() != null)
        {
          $entrance = $location->GetEntrance();
          $this->stats['x'] = $entrance->position->x;
          $this->stats['y'] = $entrance->position->y;
          $this->stats['z'] = $entrance->position->z;
          $this->stats['angle'] = $entrance->position->angle;
        }
        $location = $location0;
        $vworld = 0;
      }
    }
    else
    {
      $location = $location0;
      $vworld = 0;
    }

    $this->player->SetLocation($location);
    $this->player->SetVirtualWorld($vworld);

    /* Set player data */
    $this->player->SetSkin($this->stats['skin']);
    $this->player->SetExperience($this->stats['experience']);
    $this->player->SetLevel($this->stats['level']);
    $this->player->SetSex($this->stats['sex']);
    $this->player->SetAge($this->stats['age']);
    $this->player->SetReports($this->stats['reports']);
    $this->player->SetBank($this->stats['bank']);
    $this->player->FreezeBank($this->stats['bankfreezed']);
    $this->player->SetStrength($this->stats['strength']);
    $this->player->SetSpeedometer($this->stats['speedo']);

    /* Marriage is a special case */
    if ($this->stats['married'] != null)
    {
      /* Find their marriage */
      $marrystats = DB::GetAccountStats($this->stats['married'], 'sex');
      $marryaccount = DB::GetAccount($this->stats['married'], 'id', 'name');

      $this->player->MarryTo($marryaccount['name'], $marrystats['sex']);
    }

    /* Set the player faction and rank */
    $faction_is_null = 0;
    $rank_is_null = 0;
    if ($this->stats['faction'] == null)
      $faction_is_null = 1;
    if ($this->stats['rank'] == null)
      $rank_is_null = 1;

    if ($faction_is_null ^ $rank_is_null)
    {
      echo "[WARNING] Player {$this->player->name} has faction but not rank, or viceversa. Setting both to null\n";
      $this->stats['faction'] = null;
      $this->stats['rank'] = null;
    }
    $this->player->SetFaction($this->stats['faction']);
    $this->player->SetRank($this->stats['rank']);
    $this->player->SetOnduty($this->stats['onduty']);

    /**
     ** Load player skills
     **/
    $this->player->SetUpgradePoints($this->stats['upgradepts']);
    foreach ($this->stats as $name => $value)
    {
      if (!strncmp($name, 'sk_', 3))
      {
        $name = substr($name, 3);
        $this->player->SetSkill(Players::GetSkillFlag($name), $value);
      }
    }

    /**
     ** Load player properties
     **/

    
    /* Spawn the player and set his last position, if he had */
    $this->player->LagProtection();
    $this->player->Spawn();
    if ($this->stats['x'] != 0 || $this->stats['y'] != 0 || $this->stats['z'] != 0 || $this->stats['angle'] != 0)
    {
      $pos = new Position($this->stats['x'], $this->stats['y'], $this->stats['z'], $this->stats['angle']);
      $sector = $location->Locate($pos);
      $this->player->SetPosition($pos);
      $location->StreamObjects($this->player, $pos, $sector);
    }
    
    /* Set player data required to be set after spawn */
    $this->player->SetHunger($this->stats['hunger']);
    $this->player->SetInjures($this->stats['injures']);
    $this->player->SetMoney($this->stats['money']);
    $this->player->CheckHealth(true);
    $this->player->CheckGuns(true);

    /* Set the player guns */
    for ($i = 0; $i < 13; $i++)
    {
      if ($this->stats['slot_' . $i] > 0)
      {
      }
    }

    /* Send the join info to players who requested it */
    foreach (Players::GetJQ() as $p)
      $p->Send(COLOR_GRAY, "[JOIN] {$this->player->name}({$this->player->id}) has logged in");

    /* Set the player message receiving defaults */
    if ($this->player->togjq == true)
      Players::AddToJQ($this->player);
    if ($this->player->togooc == true)
      Players::AddToOOC($this->player);

    /* Check if the player must spawn in jail */
    if ($this->stats['jailtime'] > 0)
      $this->player->Jail($this->stats['jailtime']);

    /* Show the clock */
    TextDrawShowForPlayer($this->player->id, Core::GetClock());
    $this->player->Update();
  }

  public function Authed()
  {
    return $this->authed;
  }
}


class Accounts
{
  private static $loaded_stats = array();

  public static function Init()
  {
    Callbacks::Instance()->Register(cOnPlayerConnect, null, array('Accounts', 'OnPlayerConnect'), 5);
    Callbacks::Instance()->Register(cOnPlayerDisconnect, null, array('Accounts', 'OnPlayerDisconnect'), 0);
    CommandHandler::Register('login', 1, null, array('Accounts', 'cmdLogin'), '[password]', 0, -1000);
  }

  public static function cmdLogin(Player $player, $numparams, $params)
  {
    if ($player->account->Authed())
    {
      $player->Send(COLOR_GREEN, '* You are already logged in');
      return COMMAND_BREAK;
    }

    if ($player->account->data['password'] == md5($params[1]))
    {
      $player->Send(COLOR_GREEN, '* Password accepted, welcome to ItalyMafia');
      $player->account->Auth();
    }
    else
    {
      $player->account->wrongpassAttempts--;
      if ($player->account->wrongpassAttempts == 0)
      {
        $player->Send(COLOR_RED, '* Too many invalid passwords!');
        Kick($player->id);
      }
      else
        $player->Send(COLOR_RED, '* Wrong password!');
    }

    return COMMAND_BREAK;
  }

  public static function Save()
  {
    /* First save all loaded stats */
    foreach (Accounts::$loaded_stats as $stats)
    {
      unset($stats['married']);
      DB::SaveAccountStats($stats);
    }
    Accounts::$loaded_stats = array();

    /* Save all connected players data */
    foreach (Players::Get() as $p)
    {
      if ($p->account && $p->account->Authed())
      {
        $stats = $p->GetData();
        $stats['acc'] = $p->account->ID();
        DB::SaveAccountStats($stats);
      }
    }
  }

  public static function LoadStats($id)
  {
    if (!isset(Accounts::$loaded_stats[$id]))
    {
      $stats = DB::GetAccountStats($id);
      if ($stats != null)
      {
        Accounts::$loaded_stats[$id] = $stats;
      }
    }
    else
    {
      $stats = Accounts::$loaded_stats[$id];
    }
    return $stats;
  }

  public static function SetLoadedStats($id, $stats)
  {
    Accounts::$loaded_stats[$id] = $stats;
  }

  public static function GetLoadedStats($id)
  {
    if (isset(Accounts::$loaded_stats[$id]))
      return Accounts::$loaded_stats[$id];
    return null;
  }

  public static function EraseLoadedStats($id)
  {
    if (isset(Accounts::$loaded_stats[$id]))
      unset(Accounts::$loaded_stats[$id]);
  }

  public static function OnPlayerConnect($playerid)
  {
    $player = Players::FindByID($playerid);
    if ($player == null)
      return CALLBACK_BREAK;

    $data = DB::GetAccount($player->name);
    if ($data == null)
    {
      $player->Send(COLOR_YELLOW, '[ACCOUNT REGISTRATION REQUIRED]');
      $player->Send(COLOR_RED,    '* Your name is not registered at ItalyMafia.');
      $player->Send(COLOR_RED,    '* If you want to create a new account visit');
      $player->Send(COLOR_RED,    '* http://panel.mafiaroleplay.net');
      $player->Kick();
      return CALLBACK_BREAK;
    }
    else if ($data['banned'] != null)
    {
      if (time() < $data['ban_expiration'])
      {
        $datestr = date('r', $data['ban_expiration']);
        $player->Kick("* You are banned until {$datestr}: {$data['banned']}");
        return CALLBACK_BREAK;
      }
      else
      {
        /* Ban expired */
        $unban['id'] = $data['id'];
        $data['banned'] = null;
        $unban['banned'] = null;
        $data['banned_by'] = null;
        $unban['banned_by'] = null;
        $data['ban_date'] = null;
        $unban['ban_date'] = null;
        $data['ban_expiration'] = null;
        $unban['ban_expiration'] = null;
        DB::SaveAccount($unban);
      }
    }

    $player->Send(COLOR_YELLOW, '* This account is registered. If it is your account then type');
    $player->Send(COLOR_YELLOW, '* \'/login your_password\'. You have two minutes to login.');
    $player->account = new Account($player, $data);
  }

  public static function OnPlayerDisconnect(Player $player, $reason)
  {
    if ($player->account && $player->account->Authed())
    {
      /*
       * Never save data on disconnects, wait for the datasave
       * timer to avoid bug abusing to create fake money.
       */
      $data = $player->GetData();
      $stats = $player->account->stats;
      /* Merge the player new data with the last stats data */
      foreach ($data as $key => $value)
      {
        $stats[$key] = $value;
      }
      /* Save the new stats to the loaded stats */
      Accounts::$loaded_stats[$player->account->ID()] = $stats;
    }
    return CALLBACK_OK;
  }
}

?>

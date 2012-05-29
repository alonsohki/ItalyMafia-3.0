<?
  require_once('colors.php');
  require_once('database.php');
  require_once('position.php');
  require_once('callbacks.php');
  require_once('players/main.php');
  require_once('commands.php');
  require_once('messages.php');
  require_once('keybinds.php');
  require_once('locations/main.php');
  require_once('vehicles/main.php');
  require_once('factions/main.php');
  require_once('animations.php');
  require_once('skins.php');
  require_once('bitmap.php');
  require_once('ownership.php');
  require_once('houses/main.php');
  require_once('menu.php');
  require_once('gym.php');
  require_once('admin.php');
  require_once('log.php');

  class Core
  {
    private static $clock;

    public static function Init()
    {
      echo ">>>> Setting SAMP globals, PHP settings and timers ...\n";
      SetGameModeText('ItalyMafia RPG v3.0');
      AllowInteriorWeapons(1);
      DisableInteriorEnterExits();
      EnableStuntBonusForAll(false);
      AllowAdminTeleport(true);
      AddTimer(array('Core', 'SaveData'), 10000, 1);
      ini_set('max_execution_time', 300);

      echo ">>>> Starting logs ...\n";
      Log::Init();
      echo ">>>> Starting the keybinds manager ...\n";
      Keybinds::Init();
      echo ">>>> Starting the menus manager ...\n";
      Menu::Init();
      echo ">>>> Starting the skins manager ...\n";
      Skins::Init();
      echo ">>>> Starting the animations manager ...\n";
      Animations::Init();
      echo ">>>> Starting locations ...\n";
      Locations::Init();
      echo ">>>> Starting DB engine ...\n";
      DB::Init();
      echo ">>>> Starting houses ...\n";
      Houses::Init();
      echo ">>>> Starting factions ...\n";
      Factions::Init();
      echo ">>>> Starting players manager ...\n";
      Players::Init();
      echo ">>>> Starting vehicles manager ...\n";
      Vehicles::Init();
      echo ">>>> Starting accounts manager ...\n";
      Accounts::Init();
      echo ">>>> Starting messages manager ...\n";
      Messages::Init();
      echo ">>>> Creating the clock ...\n";
      Core::StartClock();
      echo ">>>> Starting gyms ...\n";
      Gym::Init();
      echo ">>>> Starting admins ...\n";
      Admin::Init();
    }

    public static function Cleanup()
    {
      Core::SaveData();
      Keybinds::Cleanup();
      Log::Destroy();
    }

    private static function StartClock()
    {
      $time = time();
      Core::$clock = TextDrawCreate(551, 24, '00:00');
      TextDrawLetterSize(Core::$clock, 0.5, 1.8);
      TextDrawFont(Core::$clock, 3);
      TextDrawSetOutline(Core::$clock, 2);
      Core::UpdateClock();
    }

    public static function UpdateClock()
    {
      static $last_hour = -1;
      $time = time();
      $hour = ((int)($time / 3600 + 1) % 24);
      if ($hour != $last_hour)
      {
        SetWorldTime($hour);
        $last_hour = $hour;
      }
      TextDrawSetString(Core::$clock, date('H:i', $time));
      $remaining = 60 - (time() % 60);
      AddTimer(array('Core', 'UpdateClock'), $remaining * 1000, 0);
    }

    public static function GetClock()
    {
      return Core::$clock;
    }

    public static function SaveData()
    {
      static $numsaves = 0;
//      echo "*** Starting data save ...\n";
      DB::StartTransaction();
      Accounts::Save();
      Factions::Save();
      Houses::Save();
      DB::Commit();
//      echo "*** Data saved sucessfully!\n";
      $numsaves++;

      if ($numsaves == 12)
      {
//        echo "*** Doing manteinance...\n";
        /* Make manteinance */
        Factions::UpdateMembercount();
        $numsaves = 0;
//        echo "*** Manteinance complete!\n";
      }
    }

    public static function TranslateQuitReason($reasonid)
    {
      $reason = 'Unknown';

      switch ($reasonid)
      {
        case 0: $reason = 'Timeout'; break;
        case 1: $reason = 'Left'; break;
        case 2: $reason = 'Kicked'; break;
      }

      return $reason;
    }

    public static function GetTime($str)
    {
      $time = 0;
      $temp = 0;

      $len = strlen($str);
      for ($i = 0; $i < $len; $i++)
      {
        switch ($str[$i])
        {
          case '0':
          case '1':
          case '2':
          case '3':
          case '4':
          case '5':
          case '6':
          case '7':
          case '8':
          case '9':
            $temp *= 10;
            $temp += ord($str[$i]) - 48;
            break;
          case 's':
            $time += $temp;
            $temp = 0;
            break;
          case 'm':
            $time += $temp * 60;
            $temp = 0;
            break;
          case 'h':
            $time += $temp * 3600;
            $temp = 0;
            break;
          case 'd':
            $time += $temp * 86400;
            $temp = 0;
            break;
          case 'w':
            $time += $temp * 86400 * 7;
            $temp = 0;
            break;
        }
      }

      $time += $temp;
      return $time;
    }

    public static function FindPlayer($player, $id)
    {
      $res = Players::Find($id);
      if (!$res)
      {
        $player->Send(COLOR_PNOTFOUND, '[ERROR] Given player id not found or more than one result');
        return null;
      }
      else if (!$res->account || $res->account->Authed() == false)
      {
        $player->Send(COLOR_PNOTAUTHED, '[ERROR] Given player is not authed');
        return null;
      }
      return $res;
    }

    public static function FixIntegerDots($value)
    {
      return number_format($value);
    }
  }
?>

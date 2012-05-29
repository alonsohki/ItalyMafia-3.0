<?
dl('keybinds.so');

define ('KEYBIND_OK', 0x001);
define ('KEYBIND_BREAK', 0x002);

/**
 ** class Keybinds
 ** Keybinds manager. Allows you to register a sequence of keys that if are pressed by the player, a function is called.
 **/
class Keybinds
{
  private static $actions;
  private static $keyt_onfoot = array();
  private static $keyt_vehicle = array();

  /**
   ** Init
   ** Register the callback for OnPlayerKeyStateChange
   **/
  public static function Init()
  {
    sampkeys_init(MAX_PLAYERS);
    Keybinds::$actions = array();
    Callbacks::Instance()->Register(cOnPlayerKeyStateChange, null, array('Keybinds', 'OnPlayerKeyStateChange'));
    Callbacks::Instance()->Register(cOnPlayerConnect, null, array('Keybinds', 'OnPlayerConnect'));

    /* Make the list of key translations */
    Keybinds::$keyt_onfoot[KEY_ACTION]            = 'PED_ANSWER_PHONE';
    Keybinds::$keyt_onfoot[KEY_CROUCH]            = 'PED_DUCK';
    Keybinds::$keyt_onfoot[KEY_FIRE]              = 'PED_FIREWEAPON';
    Keybinds::$keyt_onfoot[KEY_SPRINT]            = 'PED_SPRINT';
    Keybinds::$keyt_onfoot[KEY_SECONDARY_ATTACK]  = 'VEHICLE_ENTER_EXIT';
    Keybinds::$keyt_onfoot[KEY_LOOK_BEHIND]       = 'PED_LOOKBEHIND';
    Keybinds::$keyt_onfoot[KEY_WALK]              = 'SNEAK_ABOUT';
    Keybinds::$keyt_onfoot[KEY_ANALOG_LEFT]       = 'VEHICLE_LOOKLEFT';
    Keybinds::$keyt_onfoot[KEY_ANALOG_RIGHT]      = 'VEHICLE_LOOKRIGHT';

    Keybinds::$keyt_vehicle[KEY_FIRE]             = 'VEHICLE_FIREWEAPON';
    Keybinds::$keyt_vehicle[KEY_SECONDARY_ATTACK] = 'VEHICLE_FIREWEAPON_ALT';
    Keybinds::$keyt_vehicle[KEY_LOOK_RIGHT]       = 'VEHICLE_LOOKRIGHT';
    Keybinds::$keyt_vehicle[KEY_LOOK_LEFT]        = 'VEHICLE_LOOKLEFT';
    Keybinds::$keyt_vehicle[KEY_HANDBRAKE]        = 'VEHICLE_HANDBRAKE';
    Keybinds::$keyt_vehicle[KEY_SUBMISSION]       = 'TOGGLE_SUBMISSIONS';
    Keybinds::$keyt_vehicle[KEY_ANALOG_UP]        = 'VEHICLE_TURRETUP';
    Keybinds::$keyt_vehicle[KEY_ANALOG_DOWN]      = 'VEHICLE_TURRETDOWN';
    Keybinds::$keyt_vehicle[KEY_ANALOG_LEFT]      = 'VEHICLE_TURRETLEFT';
    Keybinds::$keyt_vehicle[KEY_ANALOG_RIGHT]     = 'VEHICLE_TURRETRIGHT';
  }

  /**
   ** TranslateKey
   ** Returns the key format translation for a given key, for gametexts
   **
   ** Parameters:
   ** - key:        The required key
   ** - onvehicle:  Tells if a vehicle key is required
   **/
  public static function TranslateKey($key, $onvehicle)
  {
    if ($onvehicle == false)
    {
      if (isset(Keybinds::$keyt_onfoot[$key]))
        return '~k~~' . Keybinds::$keyt_onfoot[$key] . '~';
      else
        return '';
    }
    else
    {
      if (isset(Keybinds::$keyt_vehicle[$key]))
        return '~k~~' . Keybinds::$kety_vehicle[$key] . '~';
      else
        return '';
    }
  }

  /**
   ** Cleanup
   ** Free the memory allocated by the sampkeys extension
   **/
  public static function Cleanup()
  {
    sampkeys_cleanup();
  }

  /**
   ** MakeID
   ** Makes a unique ID for an action
   **/
  private static function MakeID()
  {
    static $id = 0;
    $id++;
    return $id;
  }

  /**
   ** Register
   ** Registers a new sequence of keys.
   ** The parameters are:
   ** - sequence: The key sequence in an array
   ** - object:   The object instance to be called when the trigger happens
   ** - function: The function to call "
   **/
  public static function Register($ksequence, $object, $function)
  {
    /* Find if this sequence already exists */
    $id = -1;
    foreach (Keybinds::$actions as $action)
    {
      if ($action->sequence == $ksequence)
      {
        $id = $action->id;
        break;
      }
    }

    if ($id == -1)
    {
      $id = Keybinds::MakeID();
      sampkeys_addseq($id, $ksequence);
    }

    $action = new KeybindAction($id, $ksequence, $object, $function);
    $action->next = Keybinds::$actions[$id];
    Keybinds::$actions[$id] = $action;
  }

  /**
   ** ProcessPlayer
   ** Process a player key press
   **/
  private static function ProcessPlayer(Player $player, $newkeys, $oldkeys)
  {
    $action = sampkeys_playerkeys($player->id, $newkeys, $oldkeys);
    if ($action != 0 && isset(Keybinds::$actions[$action]))
    {
      for ($actiondata = Keybinds::$actions[$action]; $actiondata != null; $actiondata = $actiondata->next)
      {
        $obj = $actiondata->object;
        $func = $actiondata->func;

        if ($obj != null)
          $res = $obj->$func($player);
        else
          $res = call_user_func($func, $player);

        if ($res == KEYBIND_BREAK)
          break;
      }
    }
  }

  /**
   ** ResetPlayer
   ** Resets a player key state to 0
   **/
  public static function ResetPlayer($playerid)
  {
    sampkeys_resetplayer($playerid);
  }


  /** Callbacks **/
  public static function OnPlayerKeyStateChange(Player $player, $newkeys, $oldkeys)
  {
    Keybinds::ProcessPlayer($player, $newkeys, $oldkeys);
    return CALLBACK_OK;
  }

  public static function OnPlayerConnect($playerid)
  {
    Keybinds::ResetPlayer($playerid);
    return CALLBACK_OK;
  }
}


/**
 ** class KeybindAction
 ** Container for created action sequences
 **/
class KeybindAction
{
  public $id;
  public $sequence;
  public $object;
  public $func;
  public $next = null;

  public function __construct($id, $sequence, $object, $func)
  {
    $this->id = $id;
    $this->sequence = $sequence;
    $this->object = $object;
    $this->func = $func;
  }
}

?>

<?
require_once('area.php');
require_once('location.php');
require_once('location0.php');
require_once('objects.php');

class Locations
{
  private static $locations = array();

  public static function Init()
  {
    Callbacks::Instance()->Register(cOnPlayerPickUpPickup, null, array('Locations', 'OnPickUpPickup'));
    Callbacks::Instance()->Register(cOnPlayerEnterCheckpoint, null, array('Locations', 'OnPlayerEnterCheckpoint'));
    CommandHandler::Register('enter', 0, null, array('Locations', 'cmdEnter'), '', 1, -1000);
    CommandHandler::Register('exit', 0, null, array('Locations', 'cmdExit'), '', 1, -1000);
    Keybinds::Register(array(KEY_ACTION, KEY_ACTION), null, array('Locations', 'PlayerEnterExitKeybind'));

    echo ">>>>> Creating Location0 ...\n";
    $location0 = new Location0();
  }

  public static function Add(Location $location)
  {
    Locations::$locations[$location->ID()] = $location;
  }

  public static function Del(Location $location)
  {
    if (isset(Locations::$locations[$location->ID()]))
    {
      unset(Locations::$locations[$location->ID()]);
    }
  }

  public static function Find($id)
  {
    if (isset(Locations::$locations[$id]))
      return Locations::$locations[$id];

    return null;
  }

  public static function MakeID($str)
  {
    return crc32($str);
  }

  public static function VWorldOffset($offset)
  {
    return (($offset << 16) & 0xFFFF0000);
  }

  public static function SendLocationInfo(Player $player, $name, $owner = null, $price = -1)
  {
    $str = "~r~{$name}";
    if ($owner != null)
      $str .= "~n~~y~Owner:~w~ {$owner}";
    if ($price > -1)
      $str .= "~n~~y~Price:~w~ {$price}$";
    GameTextForPlayer($player->id, $str, 3000, 4);
  }


  /**
   ** Callbacks
   **/
  public static function OnPickUpPickup(Player $player, $pickupid)
  {
    if ($player->location != null)
      $player->location->WalksInPickup($player, $pickupid);

    return CALLBACK_OK;
  }

  public static function OnPlayerEnterCheckpoint(Player $player)
  {
    if ($player->location != null)
      $player->location->WalksInCheckpoint($player);

    return CALLBACK_OK;
  }


  /**
   ** Keybinds
   **/
  public static function PlayerEnterExitKeybind(Player $player)
  {
    if ($player->location && $player->location->EnterExitKeybind($player))
      return KEYBIND_BREAK;
    return KEYBIND_OK;
  }


  /**
   ** Commands
   **/
  public static function cmdEnter(Player $player, $numparams, $params)
  {
    if ($player->location != null && $player->timedeath == 0)
      $player->location->WantsEnter($player, $numparams, $params);
    return COMMAND_BREAK;
  }

  public static function cmdExit(Player $player, $numparams, $params)
  {
    if ($player->location != null && $player->timedeath == 0)
      $player->location->WantsExit($player, $numparams, $params);
    return COMMAND_BREAK;
  }
}
?>

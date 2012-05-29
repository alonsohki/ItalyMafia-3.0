<?
class NeutralZone extends Location
{
  public static function Init()
  {
    $neutralzone = new NeutralZone();
    new NeutralZoneSecret($neutralzone);
  }

  public function __construct()
  {
    $location0 = Locations::Find(0);
    $id = Locations::MakeID('Neultral zone');

    parent::__construct($id,
                        new Position(1085, -1990),  /* Top-left corner */
                        new Position(1130, -2005),  /* Bottom-right corner */
                        15,                         /* Sector edge size */
                        new Position(1121.2744, -2063.9800, 69.0078, 90) /* Default spawn: inside the hall */,
                        $location0, /* Parent: location 0 */
                        -1,         /* Object sight (unstreamed) */
                        0,          /* Interior ID */
                        0           /* Virtual world */
                       );
    parent::AddEntrance(1272, 'main', new Position(1125.6589, -2037.1530, 69.8805, 270));
    parent::LoadObjectsFromFile('neutralzone_hall.txt');

  }

  protected function OnWalkin(Player $player, LocationEntrance $entrance)
  {
    Locations::SendLocationInfo($player, 'Neutral zone', 'Capo dei capi');
  }
  protected function OnWalkout(Player $player, LocationEntrance $entrance) { }
  protected function OnEnter(Player $player, $numparams, $params) { return LOCATION_ALLOW; }
  protected function OnExit(Player $player, $numparams, $params) { return LOCATION_ALLOW; }
  public function StayAfterLogout() { return true; }
}

class NeutralZoneSecret extends Location
{
  public function __construct(Location $parent)
  {
    $id = Locations::MakeID('Neutral zone secret room');

    parent::__construct($id,
                        new Position(1085, -1990),  /* Top-left corner */
                        new Position(1130, -2005),  /* Bottom-right corner */
                        15,                         /* Sector edge size */
                        new Position(1115.8734, -2022.8872, 69.0078, 274.9148) /* Default spawn: inside the room */,
                        $parent,  /* Parent: Neutral Zone hall */
                        -1,       /* Object sight (unstreamed) */
                        0,        /* Interior ID */
                        0         /* Virtual world */
                       );
    parent::AddEntrance(-1, 'main', new Position(1120.3972, -2051.2332, 69.0078, 180));
    parent::LoadObjectsFromFile('neutralzone_secret.txt');
  }

  protected function OnWalkin(Player $player, LocationEntrance $entrance)
  {
    Locations::SendLocationInfo($player, 'Secret room');
  }
  protected function OnWalkout(Player $player, LocationEntrance $entrance) { }
  protected function OnEnter(Player $player, $numparams, $params) { return LOCATION_ALLOW; }
  protected function OnExit(Player $player, $numparams, $params) { return LOCATION_ALLOW; }
  public function StayAfterLogout() { return true; }
}
?>

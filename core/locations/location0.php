<?
class Location0 extends Location
{
  public function __construct()
  {
    parent::__construct(0, /* location id */
                        new Position(-3250, 3250), /* Top-left corner */
                        new Position(3250, -3250), /* Bottom-right corner */
                        250,                       /* Sector edge size */
                        new Position(1682.2909, -2330.0222, 13.5469, 1.1528, 0) /* Default spawn: LS airport gate */,
                        null, /* No parent */
                        75    /* Object sight */
                       );
    parent::LoadObjectsFromFile('location0.txt');
  }

  protected function OnEnter(Player $player, $numparams, $params) { return LOCATION_DISALLOW; }
  protected function OnExit(Player $player, $numparams, $params) { return LOCATION_DISALLOW; }
  protected function OnWalkin(Player $player, LocationEntrance $entrance) { }
  protected function OnWalkout(Player $player, LocationEntrance $entrance) { }
  public function StayAfterLogout() { return true; }
}
?>

<?
class Hitman_HQ extends Location
{
  public static function Init()
  {
    new Hitman_HQ();
  }

  public function __construct()
  {
    $id = Locations::MakeID('Hitman_HQ');
    $location0 = Locations::Find(0);

    parent::__construct($id,
                        new Position(1950, 170),  /* Top-left corner */
                        new Position(1780, 340),  /* Bottom-right corner */
                        85,                      /* Sector edge size */
                        new Position(202.1420, 1869.3627, 13.1406, 280), /* Default spawn */
                        $location0, /* Parent: location 0 */
                        -1,         /* Object sight */
                        0,          /* Interior ID */
                        $id /* Virtual world */
                       );

    $this->LoadObjectsFromFile('hitman_hq.txt');
    parent::AddEntrance(-1, 'main', new Position(2263.5876, -755.4053, 38.0447, 120.0885));
  }

  protected function OnWalkin(Player $player, LocationEntrance $entrance)
  {
  }

  protected function OnWalkout(Player $player, LocationEntrance $entrance)
  {
  }

  protected function OnEnter(Player $player, $numparams, $params)
  {
    return LOCATION_ALLOW;
  }

  protected function OnExit(Player $player, $numparams, $params)
  {
    return LOCATION_ALLOW;
  }

  public function StayAfterLogout()
  {
    return true;
  }
}
?>

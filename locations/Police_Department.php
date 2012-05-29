<?
class PoliceDepartment extends Location
{
  private static $pd;
  private static $admincell;
  private static $admincell_bounds;
  private static $cellout;

  public static function Init()
  {
    PoliceDepartment::$pd = new PoliceDepartment();
    PoliceDepartment::$admincell = new Position(227.6175, 111.0830, 999.0156, 0);
    $admincell_bounds = array('min' => new Position(225, 105, 995),
                              'max' => new Position(235, 115, 1010));
    PoliceDepartment::$admincell_bounds = $admincell_bounds;
    PoliceDepartment::$cellout = new Position(226.3066, 114.9983, 999.0156, 90);
  }

  public static function Instance()
  {
    return PoliceDepartment::$pd;
  }

  public static function AdminCell()
  {
    return PoliceDepartment::$admincell;
  }

  public static function AdminCellBounds()
  {
    return PoliceDepartment::$admincell_bounds;
  }

  public static function CellOut()
  {
    return PoliceDepartment::$cellout;
  }

  public function __construct()
  {
    $id = Locations::MakeID('Police_Department');
    $location0 = Locations::Find(0);

    parent::__construct($id,
                        new Position(200, 130),  /* Top-left corner */
                        new Position(290, 100),  /* Bottom-right corner */
                        30,                      /* Sector edge size */
                        new Position(246.3663, 108.5394, 1003.2188, 0), /* Default spawn */
                        $location0, /* Parent: location 0 */
                        -1,         /* Object sight */
                        10,         /* Interior ID */
                        $id /* Virtual world */
                       );

   $this->AddEntrance(1239, 'street',  new Position(1554.2861, -1675.6412,  16, 90));
   $this->AddEntrance(1239, 'parking', new Position(1568.6268, -1691.0177,  5.8906, 180),
                                       new Position(214.8052,   118.2134,   1003.2188, 90));
   $this->AddEntrance(1239, 'roof',    new Position(1564.9879, -1685.7429,  28.3956, 180),
                                       new Position(237.9606,   114.7937,   1010.2188, 90));
  }

  protected function OnWalkin(Player $player, LocationEntrance $entrance)
  {
    GameTextForPlayer($player->id, '~r~Police department', 4000, 4);
  }

  protected function OnWalkout(Player $player, LocationEntrance $entrance)
  {
    GameTextForPlayer($player->id, "Exit to the {$entrance->name}", 4000, 4);
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

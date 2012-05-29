<?
/**
 ** class HQ
 ** Special location, created because all factions have one and they have
 ** common functions.
 **/
class HQ extends Location
{
  /**
   ** Private attributes:
   ** - owner:    Faction that owns this HQ
   ** - open:     Set the HQ open or closed for non-faction players
   **/
  private $owner;
  private $open = true;

  public function __construct(Faction $owner,
                              Position $start,
                              Position $topleft,
                              Position $bottomright,
                              $edge,
                              $interior)
  {
    $location0 = Locations::Find(0);
    parent::__construct($owner->ID(), $topleft, $bottomright, $edge, $start, $location0, -1, $interior, $owner->ID());
  }

  public function Open()
  {
    $this->open = true;
  }

  public function Close()
  {
    $this->open = false;
  }

  public function GetOwner()
  {
    return $this->owner;
  }


  /**
   ** Location callbacks
   **/
  protected function OnEnter(Player $player, $numparams, $params)
  {
    if ($this->open)
      return LOCATION_ALLOW;
    else if ($player->GetFaction() && $player->GetFaction()->ID() == $this->owner->ID())
      return LOCATION_ALLOW;
    GameTextForPlayer($player->id, '~r~Closed', 2000, 3);
    return LOCATION_DISALLOW;
  }

  protected function OnExit(Player $player, $numparams, $params)
  {
    return LOCATION_ALLOW;
  }

  protected function OnWalkin(Player $player, LocationEntrance $entrance)
  {
  }

  protected function OnWalkout(Player $player, LocationEntrance $entrance)
  {
    GameTextForPlayer($player->id, "Exit to the {$entrance->name}" , 3000, 4);
  }

  public function StayAfterLogout()
  {
    return true;
  }
}
?>

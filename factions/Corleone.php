<?
class Faction_Corleone extends Faction
{
  public function __construct($name, $color, $bank, $maxvehicles, $maxmembers, $membercount)
  {
    /* Create the faction */
    parent::__construct($name, $color, $bank, $maxvehicles, $maxmembers, $membercount);

    /* Create the ranks */
    parent::RegisterRank(0, 'Godfather',   MEMBER_ALLOWALL, 120, 216);
    parent::RegisterRank(1, 'Right Hand',  MEMBER_ALLOWALL, 240, 216);
    parent::RegisterRank(2, 'Consigliere', 0,               165, 169);
    parent::RegisterRank(3, 'Capo',        0);
    parent::RegisterRank(4, 'Soldier',     0);
    parent::RegisterRank(5, 'Scagnozzo',   0);
    parent::RegisterRank(6, 'Picciotto',   0);
  }

  /**
   ** Abstract functions
   **/
  public function PlayerChangesDuty(Player $player, $state)
  {
    return DUTYCHANGE_DISALLOW;
  }

  public function GetPlayerColor(Player $player)
  {
    return COLOR_CIVILIAN;
  }

  public function GetNameForPlayer(Player $player)
  {
    $faction = $player->GetFaction();
    if (!$faction || $faction->id != $this->id)
      return $player->account->name;

    $rank = $player->GetRank();
    if ($rank === null || $rank > 1)
      return $player->account->name;

    $name = $player->account->name;
    $p = strpos($name, '_');
    if ($p === FALSE)
      return $name;

    return substr($name, 0, $p) . '_Corleone';
  }
}
?>

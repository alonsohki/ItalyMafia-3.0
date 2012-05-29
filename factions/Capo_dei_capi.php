<?
class Faction_Capo_dei_capi extends Faction
{
  public function __construct($name, $color, $bank, $maxvehicles, $maxmembers, $membercount)
  {
    parent::__construct($name, $color, $bank, $maxvehicles, $maxmembers, $membercount);
    parent::RegisterRank(0, 'Capo dei capi', MEMBER_ALLOWALL, 186);
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
    return $player->account->name;
  }
}
?>

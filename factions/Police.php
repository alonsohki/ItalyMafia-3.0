<?
class Faction_Police extends Faction
{
  public function __construct($name, $color, $bank, $maxvehicles, $maxmembers, $membercount)
  {
    /* Create the faction */
    parent::__construct($name, $color, $bank, $maxvehicles, $maxmembers, $membercount);

    /* Register ranks */
    parent::RegisterRank(0, 'Chief',  MEMBER_ALLOWALL,  280);
    parent::RegisterRank(1, 'Cadet',  MEMBER_ALLOWBANK,  280);
  }


  /**
   ** Abstract functions
   **/
  public function PlayerChangesDuty(Player $player, $state)
  {
    return DUTYCHANGE_ALLOW;
  }

  public function GetPlayerColor(Player $player)
  {
    return parent::GetColor();
  }

  public function GetNameForPlayer(Player $player)
  {
    $faction = $player->GetFaction();
    if (!$faction || $faction->id != $this->id)
      return $player->account->name;

    $name = $player->account->name;
    $p = strpos($name, '_');
    if ($p === FALSE)
      return $name;
    $initial = $name[0];
    $badge = (crc32($name) % 99) + 1;
    if ($badge < 0)
      $badge = -$badge;

    return $initial . '_' . substr($name, $p + 1) . '_' . sprintf('%02d', $badge);
  }
}
?>

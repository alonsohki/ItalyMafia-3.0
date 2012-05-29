<?
require_once('bank.php');
require_once('neutralzone.php');
require_once('hitman_hq.php');
require_once('Police_Department.php');

class GlobalLocations
{
  public static function Init()
  {
    echo ">>>> Creating LS Bank ...\n";
    LSBank::Init();
    echo ">>>> Creating Police department ...\n";
    PoliceDepartment::Init();
    echo ">>>> Creating neutral zone ...\n";
    NeutralZone::Init();
    echo ">>>> Creating hitman HQ ...\n";
    Hitman_HQ::Init();
  }
}
?>

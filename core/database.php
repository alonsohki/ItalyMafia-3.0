<?
interface Database
{
  public static function Init();
  public static function StartTransaction();
  public static function Commit();
  public static function DBdie($reason);

  public static function GetAccount($key, $by = 'name', $fields = '*');
  public static function SaveAccount($data);
  public static function GetAccountStats($id, $fields = '*');
  public static function SaveAccountStats($data);

  public static function GetAllFactions();
  public static function GetFactionMemberCount($factionid);
  public static function GetFactionData($faction);
  public static function SaveFactionData($data);

  public static function GetVehicleInfo($vehicle);
  public static function SaveVehicleInfo($vehicle);

  public static function GetAllHouses();
  public static function GetHouseData($name);
  public static function GetHouseRooms($id);
  public static function SaveRoom($data);

  public static function GetBizInfo($biz);
  public static function SaveBizInfo($biz);
  public static function GetJobInfo($job);
  public static function SaveJobInfo($job);
  public static function Close();
}

require_once('database/mysql.php');
?>

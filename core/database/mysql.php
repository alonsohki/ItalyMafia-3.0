<?

class DB implements Database
{
  private static $config = array('host' => 'localhost',
                                 'user' => 'italymafia',
                                 'pass' => 'Nwn39M[ani_3kn12=',
                                 'db'   => 'italymafia'
                                );
  private static $handler = null;
  private static $transaction = false;


  public static function Init()
  {
    $config = DB::$config;

    DB::$handler = @mysql_pconnect($config['host'], $config['user'], $config['pass'])
      or DB::DBdie('Error connecting to MySQL: ' . mysql_error() . "\n");
    @mysql_select_db($config['db'], DB::$handler)
      or DB::DBdie('Error selecting MySQL database: ' . mysql_error() . "\n");
    mysql_query('SET AUTOCOMMIT=0', DB::$handler);
  }

  public static function StartTransaction()
  {
    DB::$transaction = true;
    mysql_query('START TRANSACTION', DB::$handler);
  }

  public static function Commit()
  {
    DB::$transaction = false;
    mysql_query('COMMIT', DB::$handler);
  }

  public static function DBdie($reason)
  {
    $rollback = '';
    if (DB::$transaction)
    {
      mysql_query('ROLLBACK', DB::$handler);
      $rollback = ' (rolled back)';
    }
    echo "MySQL fatal error{$rollback}: {$reason}\n";
    exit();
  }

  public static function GetAccount($key_, $by_ = 'name', $fields_ = '*')
  {
    $key    = mysql_real_escape_string($key_,    DB::$handler);
    $by     = mysql_real_escape_string($by_,     DB::$handler);
    $fields = mysql_real_escape_string($fields_, DB::$handler);

    $res = mysql_query("SELECT {$fields} FROM account WHERE {$by}='{$key}'", DB::$handler)
      or DB::DBdie('Error fetching account data: ' . mysql_error() . "\n");
    if (!@mysql_num_rows($res))
      return null;
    return mysql_fetch_assoc($res);
  }

  public static function SaveAccount($data)
  {
    $query = 'UPDATE account SET ';
    foreach ($data as $key_ => $value_)
    {
      $key   = mysql_real_escape_string($key_, DB::$handler);
      $value = mysql_real_escape_string($value_, DB::$handler);

      if ($key != 'id')
      {
        if ($value == null)
          $query .= "{$key}=NULL, ";
        else
          $query .= "{$key}='{$value}', ";
      }
    }

    $id = mysql_real_escape_string($data['id'], DB::$handler);
    $query = substr($query, 0, strlen($query) - 2) . ' WHERE id=' . $id;
    mysql_query($query, DB::$handler)
      or DB::DBdie('Error updating account data: ' . mysql_error() . "\n");
    DB::Commit();
    return true;
  }

  public static function GetAccountStats($id_, $fields_ = '*')
  {
    $id     = mysql_real_escape_string($id_,     DB::$handler);
    $fields = mysql_real_escape_string($fields_, DB::$handler);

    $res = mysql_query("SELECT {$fields} FROM stats WHERE acc='{$id}'", DB::$handler)
      or DB::DBdie('Error fetching account stats: ' . mysql_error() . "\n");

    if (!@mysql_num_rows($res))
      return null;

    return mysql_fetch_assoc($res);
  }

  public static function SaveAccountStats($data)
  {
    $query = 'UPDATE stats SET ';
    foreach ($data as $stat_ => $value_)
    {
      $stat  = mysql_real_escape_string($stat_,  DB::$handler);
      $value = mysql_real_escape_string($value_, DB::$handler);

      if ($stat != 'acc')
      {
        if ($value == null)
          $query .= "{$stat}=NULL, ";
        else
          $query .= "{$stat}='{$value}', ";
      }
    }

    $acc = mysql_real_escape_string($data['acc'], DB::$handler);
    $query = substr($query, 0, strlen($query) - 2) . ' WHERE acc=' . $acc;
    mysql_query($query, DB::$handler)
      or DB::DBdie('Error updating account stats: ' . mysql_error() . "\n");
    return true;
  }

  public static function GetAllFactions()
  {
    $factions = array();
    $res = mysql_query('SELECT name FROM faction', DB::$handler)
      or DB::DBdie('Error fetching all factions: ' . mysql_error() . "\n");
    while ($data = mysql_fetch_assoc($res))
    {
      $factions[] = $data['name'];
    }
    return $factions;
  }

  public static function GetFactionMemberCount($factionid_)
  {
    $factionid = mysql_real_escape_string($factionid_, DB::$handler);

    $query = 'SELECT COUNT(stats.faction) AS membercount FROM faction LEFT OUTER JOIN stats ON (stats.faction = faction.id)'.
             "WHERE faction.id='{$factionid}' GROUP BY(faction.id)";
    $res = mysql_query($query, DB::$handler)
      or DB::DBdie("Error fetching faction '{$factionid}' member count: " . mysql_error() . "\n");
    $data = mysql_fetch_assoc($res);
    return (int)$data['membercount'];
  }

  public static function GetFactionData($faction_)
  {
    $faction = mysql_real_escape_string($faction_, DB::$handler);

    $query = 'SELECT COUNT(stats.faction) AS membercount, faction.* FROM faction LEFT OUTER JOIN stats ON (stats.faction = faction.id)'.
             "WHERE faction.name='{$faction}' GROUP BY(faction.id)";
    $res = mysql_query($query, DB::$handler)
      or DB::DBdie("Error fetching faction '{$faction}' data: " . mysql_error() . "\n");
    return mysql_fetch_assoc($res);
  }

  public static function SaveFactionData($data)
  {
    $query = 'UPDATE faction SET ';
    foreach ($data as $key_ => $value_)
    {
      $key   = mysql_real_escape_string($key_,   DB::$handler);
      $value = mysql_real_escape_string($value_, DB::$handler);

      if ($key != 'id')
      {
        if ($value == null)
          $query .= "{$key}=NULL, ";
        else
          $query .= "{$key}='{$value}', ";
      }
    }

    $id = mysql_real_escape_string($data['id'], DB::$handler);
    $query = substr($query, 0, strlen($query) - 2) . ' WHERE id=' . $id;
    if (!mysql_query($query, DB::$handler))
    {
      if (mysql_errno() != 1452)
        DB::DBdie('Error updating faction data: ' . mysql_error() . "\n");
      echo "WARNING! Saving faction data failed for integrity, setting null HQ and re-saving\n";
      $faction = Factions::Find($data['id']);
      if ($faction)
      {
        $faction->SetHQ(null);
        $faction->Save();
      }
    }
    return true;
  }

  public static function GetVehicleInfo($vehicle)
  {
  }

  public static function SaveVehicleInfo($vehicle)
  {
  }

  public static function GetAllHouses()
  {
    $houses = mysql_query('SELECT name FROM house', DB::$handler)
      or DB::DBdie('Error fetching all house names: ' . mysql_error() . "\n");
    $ret = array();

    while ($data = mysql_fetch_array($houses))
    {
      $ret[] = $data[0];
    }

    return $ret;
  }

  public static function GetHouseData($name_)
  {
    $name = mysql_real_escape_string($name_, DB::$handler);

    $res = mysql_query("SELECT * FROM house WHERE name='{$name}'", DB::$handler)
      or DB::DBdie('Error fetching house data: ' . mysql_error() . "\n");

    return mysql_fetch_assoc($res);
  }

  public static function GetHouseRooms($id_)
  {
    $id = mysql_real_escape_string($id_, DB::$handler);

    $res = mysql_query("SELECT * FROM room WHERE house='{$id}'", DB::$handler)
      or DB::DBdie("Error fetching house '{$id}' rooms: " . mysql_error() . "\n");

    $ret = array();
    while ($data = mysql_fetch_assoc($res))
      $ret[] = $data;

    return $ret;
  }

  public static function SaveRoom($data)
  {
    $query = 'UPDATE room SET ';
    foreach ($data as $key_ => $value_)
    {
      $key   = mysql_real_escape_string($key_,   DB::$handler);
      $value = mysql_real_escape_string($value_, DB::$handler);

      if ($key != 'house' && $key != 'id' && $key != 'number')
      {
        if ($value == null)
          $query .= "{$key}=NULL, ";
        else
          $query .= "{$key}='{$value}', ";
      }
    }

    $id     = mysql_real_escape_string($data['id'],     DB::$handler);
    $house  = mysql_real_escape_string($data['house'],  DB::$handler);
    $number = mysql_real_escape_string($data['number'], DB::$handler);

    $tmpquery = $query . "house='{$house}' WHERE id='{$id}'";
    if (!mysql_query($tmpquery, DB::$handler))
    {
      if (mysql_errno() != 1452)
        DB::DBdie("Error saving room '{$room}' of house '{$house}': " . mysql_error() . "\n");
      $tmpquery = substr($query, 0, strlen($query) - 2) . " WHERE id='{$id}'";
      if (mysql_query($tmpquery, DB::$handler))
      {
        echo "WARNING: House '{$house}' seems to have been deleted from database, removing from gamemode\n";
        Houses::DestroyHouse($data['house']);
        return false;
      }
      else
      {
        if (mysql_errno() != 1452)
          DB::DBdie("Error when checking house '{$house}' integrity in room '{$number}' saving: " . mysql_error() . "\n");
        echo "WARNING: House '{$house}' room number '{$number}' fails integrity for owner, setting nobody\n";
        $house = Houses::Find($data['house']);
        if ($house)
        {
          $room = $house->GetRoom($data['number']);
          if ($room)
            $house->UpdateOwnership($room, new Ownership(null, null));
        }
      }
    }

    return true;
  }

  public static function GetBizInfo($biz)
  {
  }

  public static function SaveBizInfo($biz)
  {
  }

  public static function GetJobInfo($job)
  {
  }

  public static function SaveJobInfo($job)
  {
  }

  public static function Close()
  {
  }
}
?>

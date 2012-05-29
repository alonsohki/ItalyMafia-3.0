<?
/**
 ** Log constants
 **/
define ('LOG_STANDARD',   0);
define ('LOG_JOINQUIT',   1);
define ('LOG_COMMAND',    2);
define ('LOG_MESSAGE',    3);
define ('LOG_ADMIN',      4);

/**
 ** class Log
 ** Manages logging
 **/
class Log
{
  private static $handlers = array();

  public static function Init()
  {
    Log::OpenLog(LOG_JOINQUIT,  'joinquit.log');
    Log::OpenLog(LOG_COMMAND,   'commands.log');
    Log::OpenLog(LOG_MESSAGE,   'messages.log');
    Log::OpenLog(LOG_ADMIN,     'admin.log');
  }

  public static function Destroy()
  {
    foreach (Log::$handlers as $handler)
      fclose($handler);
  }

  private static function OpenLog($type, $file)
  {
    Log::$handlers[$type] = fopen('scriptfiles/Logs/' . $file, 'a');
  }

  public static function Append($type, $str)
  {
    if ($type)
    {
      $datestr = date('[j/M/y H:i:s] ');
      fwrite(Log::$handlers[$type], $datestr . $str . "\n");
    }
    /* else TODO: Keep the else when its a release */
    echo $str . "\n";
  }
}
?>

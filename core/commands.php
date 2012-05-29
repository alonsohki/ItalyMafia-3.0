<?

define ('COMMAND_OK', 0x001);
define ('COMMAND_BREAK', 0x002);
define ('COMMAND_ERROR', 0x004);

class CommandCallback
{
  public $next;
  public $prev;

  public $object;
  public $func;
  public $prio;

  public function __construct($object, $func, $prio)
  {
    $this->object = $object;
    $this->func = $func;
    $this->prio = $prio;

    $this->next = null;
    $this->prev = null;
  }
}

class Command
{
  private $callbacks = null;

  public $numparams;
  public $usage;
  public $authed;

  public function __construct($numparams, $usage, $authed)
  {
    $this->numparams = $numparams;
    $this->usage = $usage;
    $this->authed = $authed;
  }

  public function AddCallback($object, $func, $prio)
  {
    $callback = new CommandCallback($object, $func, $prio);

    $cbk = null;
    $cbk2 = null;
    for ($cbk = $this->callbacks; $cbk != null; $cbk = $cbk->next)
    {
      if ($cbk->prio > $prio)
        break;
      $cbk2 = $cbk;
    }

    if ($cbk2 == null)
    {
      $callback->next = $this->callbacks;
      $this->callbacks = $callback;
    }
    else if ($cbk == null)
    {
      $cbk2->next = $callback;
      $callback->prev = $cbk2;
    }
    else
    {
      $cbk2->next = $callback;
      $cbk->prev = $callback;
      $callback->next = $cbk;
      $callback->prev = $cbk2;
    }
  }

  public function Call($player, $numparams, $params)
  {
    $ret = COMMAND_OK;

    for ($callback = $this->callbacks; $callback != null; $callback = $callback->next)
    {
      $obj = $callback->object;
      $func = $callback->func;

      if ($obj) $ret = $obj->$func($player, $numparams, $params);
      else $ret = call_user_func($func, $player, $numparams, $params);

      if ($ret != COMMAND_OK)
        break;
    }

    if ($ret & COMMAND_ERROR)
      return 0;
    return 1;
  }
}

class CommandHandler
{
  private static $commands = array();

  public static function Register($name_, $numparams, $obj, $func, $usage, $authed = 1, $prio = 10)
  {
    $name = strtolower($name_);

    if (!isset(CommandHandler::$commands[$name]))
    {
      $command = new Command($numparams, $usage, $authed);
      CommandHandler::$commands[$name] = $command;
    }
    else
      $command = CommandHandler::$commands[$name];

    $command->AddCallback($obj, $func, $prio);
  }

  public static function Handle($playerid, $cmdtext)
  {
    $player = Players::FindByID($playerid);
    if ($player == null)
      return 0;

//    Log::Append(LOG_COMMAND, "[{$player->id}] {$player->name} -> {$cmdtext}");

    $params = explode(' ', substr($cmdtext, 1));
    $name = strtolower($params[0]);

    if (!isset(CommandHandler::$commands[$name]))
      return 0;

    $cmd = CommandHandler::$commands[$name];
    if ($cmd->authed && (!$player->account || !$player->account->Authed()))
    {
      $player->Send(COLOR_RED, '* Login before sending this command');
      return 1;
    }

    $numparams = count($params) - 1;
    if ($numparams < $cmd->numparams)
      CommandHandler::SendUsage($player, $name, $cmd);
    else if (!$cmd->Call($player, $numparams, $params))
      CommandHandler::SendUsage($player, $name, $cmd);

    return 1;
  }

  private static function SendUsage(Player $player, $cmdname, $cmd)
  {
    if ($cmd->usage != null && !empty($cmd->usage))
      $player->Send(COLOR_USAGE, '[ERROR] Usage: /' . $cmdname . ' ' . $cmd->usage);
  }
}

?>

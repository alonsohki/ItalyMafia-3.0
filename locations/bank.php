<?
class LSBank extends Location
{
  private static $interest;
  private static $id;

  public static function Init()
  {
    new LSBank();
    LSBank::$interest = 0.3;

    /* Register commands */
    CommandHandler::Register('balance',   0, null, array('LSBank', 'cmdBalance'),  '', 1);
    CommandHandler::Register('deposit',   1, null, array('LSBank', 'cmdDeposit'),  '[amount]', 1);
    CommandHandler::Register('withdraw',  1, null, array('LSBank', 'cmdWithdraw'), '[amount]', 1);
    CommandHandler::Register('transfer',  2, null, array('LSBank', 'cmdTransfer'), '[ID] [amount]', 1);
    CommandHandler::Register('fbalance',  0, null, array('LSBank', 'cmdFbalance'), '', 1);
    CommandHandler::Register('fdeposit',  1, null, array('LSBank', 'cmdFdeposit'), '[amount]', 1);
    CommandHandler::Register('ftransfer', 2, null, array('LSBank', 'cmdFtransfer'), '[amount] [destination faction]', 1);
  }

  public static function AllowPlayer(Player $player)
  {
    if ($player->BankFreezed())
    {
      $player->Send(COLOR_BANK_FREEZED, '[ERROR] Your bank account has been freezed, operation not completed');
      return false;
    }
    return true;
  }

  public static function AllowFaction(Player $player, Faction $faction)
  {
    if ($faction->BankFreezed())
    {
      $player->Send(COLOR_BANK_FREEZED, '[ERROR] Your faction bank account has been freezed, operation not completed');
      return false;
    }
    return true;
  }

  public function __construct()
  {
    LSBank::$id = Locations::MakeID('Los Santos Bank');
    $location0 = Locations::Find(0);

    parent::__construct(LSBank::$id,
                        new Position(2300, 15),  /* Top-left corner */
                        new Position(2340, -25), /* Bottom-right corner */
                        40,                      /* Sector edge size */
                        new Position(2306.1182, -16.2380, 26.7496, 281.6266) /* Default spawn: inside the bank main door */,
                        $location0, /* Parent: location 0 */
                        -1,         /* Object sight */
                        0,          /* Interior ID */
                        LSBank::$id /* Virtual world */
                       );

    parent::AddEntrance(1274, 'main', new Position(1462.2100, -1013.2396, 26.8438, 178.2465)); /* ID=1274 Dollar icon ($) */
    
    parent::AddEntrance(1274, 'back', new Position(1426.8937, -966.2885, 37.4297, 349.1802),
                                      new Position(2315.2346, -1.3617, 26.7422, 180.4423));
  }

  protected function OnWalkin(Player $player, LocationEntrance $entrance)
  {
    Locations::SendLocationInfo($player, 'Los Santos bank', 'The state');
  }

  protected function OnWalkout(Player $player, LocationEntrance $entrance)
  {
    GameTextForPlayer($player->id, "Exit to the {$entrance->name} entrance" , 3000, 4);
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

  /**
   ** Commands
   **/
  public static function cmdBalance(Player $player, $numparams, $params)
  {
    if ($player->location->ID() == LSBank::$id)
    {
      $bank = Core::FixIntegerDots($player->GetBank());
      $interest = LSBank::$interest;
      $player->Send(COLOR_BANK_BALANCE, "[BANK] Balance: {$bank}$, default interests: {$interest}%%");
    }

    return COMMAND_OK;
  }

  public static function cmdDeposit(Player $player, $numparams, $params)
  {
    if ($player->location->ID() == LSBank::$id && LSBank::AllowPlayer($player))
    {
      $amount = (int)$params[1];
      if (Players::CheckPayment($player, $amount))
      {
        $player->GiveBank($amount);
        $player->GiveMoney(-$amount);
        $bank = Core::FixIntegerDots($player->GetBank());
        $amount = Core::FixIntegerDots($amount);
        $player->Send(COLOR_BANK_DEPOSIT, "[BANK] Deposited {$amount}$ in your account. Your new balance is {$bank}$");
      }
    }

    return COMMAND_OK;
  }

  public static function cmdWithdraw(Player $player, $numparams, $params)
  {
    if ($player->location->ID() == LSBank::$id)
    {
      $amount = (int)$params[1];
      if (Players::CheckPaybank($player, $amount))
      {
        $player->GiveBank(-$amount);
        $player->GiveMoney($amount);
        $bank = Core::FixIntegerDots($player->GetBank());
        $amount = Core::FixIntegerDots($amount);
        $player->Send(COLOR_BANK_WITHDRAW, "[BANK] Withdrawn {$amount}$ from your bank account. Your new balance is {$bank}$");
      }
    }

    return COMMAND_OK;
  }

  public static function cmdTransfer(Player $player, $numparams, $params)
  {
    if ($player->location->ID() == LSBank::$id)
    {
      $target = Core::FindPlayer($player, $params[1]);
      $amount = (int)$params[2];
      if ($target && Players::CheckPaybank($player, $amount, $target))
      {
        $player->GiveBank(-$amount);
        $target->GiveBank($amount);
        $bank1 = $player->GetBank();
        $bank2 = $target->GetBank();
        $amount = Core::FixIntegerDots($amount);
        $player->Send(COLOR_BANK_TRANSFER, "[BANK] Transfered {$amount}$ to {$target->name}");
        $target->Send(COLOR_BANK_TRANSFER, "[BANK] {$player->name} transfered you {$amount}$");
      }
    }

    return COMMAND_OK;
  }

  public static function cmdFbalance(Player $player, $numparams, $params)
  {
    if ($player->location->ID() == LSBank::$id &&
        ($faction = $player->GetFaction()) &&
        $faction->AllowedTo($player, MEMBER_ALLOWBANK))
    {
      $bank = Core::FixIntegerDots($faction->GetBank());
      $interest = LSBank::$interest;
      $player->Send(COLOR_BANK_BALANCE, "[FACTION BANK] Balance: {$bank}$, default interests: {$interest}%%");
    }

    return COMMAND_OK;
  }

  public static function cmdFdeposit(Player $player, $numparams, $params)
  {
    if ($player->location->ID() == LSBank::$id &&
        ($faction = $player->GetFaction()) &&
        $faction->AllowedTo($player, MEMBER_ALLOWBANK) &&
        LSBank::AllowPlayer($player) &&
        LSBank::AllowFaction($player, $faction))
    {
      $amount = (int)$params[1];
      if (Players::CheckPayment($player, $amount))
      {
        $faction->GiveBank($amount);
        $player->GiveMoney(-$amount);
        $bank = Core::FixIntegerDots($faction->GetBank());
        $amount = Core::FixIntegerDots($amount);
        $faction->Send(COLOR_BANK_DEPOSIT,
                       "[FACTION BANK] {$player->name} deposited {$amount}$ in the faction account. The new balance is {$bank}$",
                       MEMBER_ALLOWBANK);
      }
    }

    return COMMAND_OK;
  }

  public static function cmdFtransfer(Player $player, $numparams, $params)
  {
    if ($player->location->ID() == LSBank::$id &&
        ($faction = $player->GetFaction()) &&
        $faction->AllowedTo($player, MEMBER_ALLOWBANK) &&
        LSBank::AllowFaction($player, $faction))
    {
      $amount = (int)$params[1];
      $target_name = implode(' ', array_slice($params, 2));
      $target = Factions::FindByName($target_name);

      if ($target && Factions::CheckPaybank($player, $faction, $amount, $target))
      {
        $faction->GiveBank(-$amount);
        $target->GiveBank($amount);
        $bank1 = $faction->GetBank();
        $bank2 = $target->GetBank();
        $amount = Core::FixIntegerDots($amount);
        $faction->Send(COLOR_BANK_TRANSFER, "[FACTION BANK] {$player->name} transfered {$amount}$ to {$target->GetName()} faction", MEMBER_ALLOWBANK);
        $target->Send(COLOR_BANK_TRANSFER, "[FACTION BANK] {$faction->GetName()} faction transfered {$amount}$ to your faction", MEMBER_ALLOWBANK);
      }
    }

    return COMMAND_OK;
  }
}
?>

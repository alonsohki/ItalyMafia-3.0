<?
/**
 ** class Ownership
 ** This class is done to verify if a player is
 ** allowed to access something ownable (like
 ** houses, vehicles, etc), checking not only
 ** the owner but also his faction and his
 ** keys.
 **
 ** Also performs ownership transactions, to check
 ** if the given player is online, or if it must go
 ** to a faction bank, etc, and sends the old owner.
 **/

/* Contants for Ownership */
define('OWNERSHIP_ACCOUNT',   0x001);
define('OWNERSHIP_FACTION',   0x002);
define('OWNERSHIP_NULL',      0x004);

class Ownership
{
  private $account;
  private $faction;
  private $type;

  public function __construct($account = null, $faction = null)
  {
    $this->account = $account;
    $this->faction = $faction;

    if ($this->account != null)
      $this->type = OWNERSHIP_ACCOUNT;
    else if ($this->faction != null)
      $this->type = OWNERSHIP_FACTION;
    else
      $this->type = OWNERSHIP_NULL;
  }

  public function GetOwner()
  {
    if ($this->account != null)
      return $this->account;
    return $this->faction;
  }

  public function GetType()
  {
    return $this->type;
  }

  public function Allowed(Player $player)
  {
    if ($this->account != null)
    {
      if ($player->account && $player->account->ID() == $this->account)
        return true;
      return false;
    }
    else if ($this->faction != null)
    {
      $faction = $player->GetFaction();
      if ($faction != null && $this->faction == $faction->ID())
        return true;
      return false;
    }
    return false;
  }

  public function TransferOwnership(Player $player, Ownership $newowner, $price)
  {
    $oldid = $this->GetOwner();
    $newid = $newowner->GetOwner();
    $oldobj = null;
    $newobj = null;
    $oldstats = null;
    $newstats = null;

    switch ($this->GetType())
    {
      case OWNERSHIP_ACCOUNT:
      {
        $oldobj = Players::FindByDBID($oldid);
        if (!$oldobj)
          $oldstats = Accounts::LoadStats($oldid);
        if (!$oldobj && !$oldstats)
        {
          $player->Send(COLOR_RED, '[ERROR] Internal error, a log of this error has been saved to be fixed');
          echo "WARNING! Trying to transfer ownership from an old owner identified as OWNERSHIP_ACCOUNT({$oldid}), ".
               "but unable to load their stats (account deleted?)\n";
          return false;
        }
        break;
      }
      case OWNERSHIP_FACTION:
      {
        $oldobj = Factions::FindByID($oldid);
        if (!$oldobj)
        {
          $player->Send(COLOR_RED, '[ERROR] Internal error, a log of this error has been saved to be fixed');
          echo "WARNING! Trying to transfer ownership from an old owner identified as OWNERSHIP_FACTION({$oldid}), ".
               "but unable to find the faction (corrupted database?)\n";
          return false;
        }
        break;
      }
    }

    switch ($newowner->GetType())
    {
      case OWNERSHIP_ACCOUNT:
      {
        $newobj = Players::FindByDBID($newid);
        if (!$newobj)
          $newstats = Accounts::LoadStats($newid);
        if (!$newobj && !$newstats)
        {
          $player->Send(COLOR_RED, '[ERROR] Internal error, a log of this error has been saved to be fixed');
          echo "WARNING! Trying to transfer ownership to a new owner identified as OWNERSHIP_ACCOUNT({$newid}), ".
               "but unable to load their stats (account deleted?)\n";
          return false;
        }
        break;
      }
      case OWNERSHIP_FACTION:
      {
        $newobj = Factions::FindByID($newid);
        if (!$newobj)
        {
          $player->Send(COLOR_RED, '[ERROR] Internal error, a log of this error has been saved to be fixed');
          echo "WARNING! Trying to transfer ownership to a new owner identified as OWNERSHIP_FACTION({$newid}), ".
               "but unable to find the faction (corrupted database?)\n";
          return false;
        }
        break;
      }
    }

    /* Check if the bank accounts are frozen */
    if ($oldobj && $oldobj->BankFreezed())
    {
      $player->Send(COLOR_BANK_FREEZED, '[ERROR] The current owner of this property has his bank account frozen, operation cancelled');
      return false;
    }

    if ($newobj && $newobj->BankFreezed())
    {
      $facstr = ' ';
      if ($newobj instanceof Faction)
        $facstr = ' faction ';
      $player->Send(COLOR_BANK_FREEZED, "[ERROR] Your{$facstr}bank account has been freezed, operation cancelled");
      return false;
    }

    /* Check if the buyer has enough money */
    $facstr = ' ';
    if ($newobj)
    {
      $bank_buyer = $newobj->GetBank();
      if ($newobj instanceof Faction)
        $facstr = ' faction ';
    }
    else
      $bank_buyer = $newstats['bank'];

    if ($bank_buyer < $price)
    {
      $player->Send(COLOR_NOTENOUGH_MONEYBANK, "[ERROR] Your{$facstr}bank account hasn\'t enough money to buy this property");
      return false;
    }

    /* Perform the transaction */
    $newname = 'Somebody';
    if ($newobj)
    {
      $newobj->GiveBank(-$price);
      $fixed_price = Core::FixIntegerDots($price);
      if ($newobj instanceof Faction)
      {
        $newobj->Send(COLOR_OWNERSHIP_ADQUIRED,
                      "[FACTION BANK] {$player->name} adquired a new property for the faction for {$fixed_price}$",
                      MEMBER_ALLOWBANK);
        $newname = $newobj->GetName() . ' faction';
      }
      else
      {
        $newobj->Send(COLOR_OWNERSHIP_ADQUIRED, "[BANK] New property adquired for {$fixed_price}$");
        $newname = $newobj->name;
      }
    }
    else if ($newstats != null)
    {
      $newstats['bank'] -= $price;
      Accounts::SetLoadedStats($newid, $newstats);
    }

    if ($oldobj)
    {
      $oldobj->GiveBank($price);
      $fixed_price = Core::FixIntegerDots($price);
      if ($oldobj instanceof Faction)
      {
        $oldobj->Send(COLOR_OWNERSHIP_SOLD,
                      "[FACTION BANK] {$newname} bought one of the faction properties for sale for {$fixed_price}$",
                      MEMBER_ALLOWBANK);
      }
      else
      {
        $oldobj->Send(COLOR_OWNERSHIP_SOLD, "[BANK] {$newname} bought one of your properties for sale for {$fixed_price}$");
      }
    }
    else if ($oldstats != null)
    {
      $oldstats['bank'] += $price;
      Accounts::SetLoadedStats($oldid, $oldstats);
    }

    return true;
  }
}
?>

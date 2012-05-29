<?
require_once('house.php');
require_once('room.php');


/**
 ** class Houses
 ** The houses manager
 **/
class Houses
{
  private static $houses = array();
  private static $cookmenu;
  private static $cookanim;
  private static $cookitems = array();

  public static function Init()
  {
    /* Register commands */
    CommandHandler::Register('open',  0, null, array('Houses', 'cmdOpen'), '', 1);
    CommandHandler::Register('close', 0, null, array('Houses', 'cmdClose'), '', 1);
    CommandHandler::Register('buy',   0, null, array('Houses', 'cmdBuy'), '', 1);
    CommandHandler::Register('fbuy',  0, null, array('Houses', 'cmdFbuy'), '', 1);
    CommandHandler::Register('sell',  1, null, array('Houses', 'cmdSell'), '[price]', 1);
    CommandHandler::Register('unsell',0, null, array('Houses', 'cmdUnsell'), '', 1);

    CommandHandler::Register('fridgestats', 0, null, array('Houses', 'cmdFridgestats'), '', 1);
    CommandHandler::Register('cook',        0, null, array('Houses', 'cmdCook'), '', 1);

    /* Generate cooking items */
    Houses::RegisterCookItem('Pizza portion', 50,   4000,   70);
    Houses::RegisterCookItem('Full pizza',    100,  6000,   100);
    Houses::RegisterCookItem('Spaghetti',     200,  6000,   70);
    Houses::RegisterCookItem('Tuna',          300,  7000,   50);
    Houses::RegisterCookItem('Lobster',       500,  9000,   40);

    /* Generate menus */
    Houses::$cookmenu = new Menu(null, 'Cook', array('Houses', 'cookStart'), array('Houses', 'cookStop'),
                                 2, new Position(150, 150), 100, 50);
    Houses::$cookmenu->SetHeader('Meal', 0);
    Houses::$cookmenu->SetHeader('Food', 1);
    foreach (Houses::$cookitems as $item)
    {
      Houses::$cookmenu->Add($item->name, 0, ROW_ACTION, array('Houses', 'cookItem'));
      Houses::$cookmenu->Add($item->food, 1);
    }

    /* Create animations */
    $cook_eat = new Animation('FOOD',  'eat_pizza', true, false, false, false, -1, true, 3600);
    $cook     = new Animation('DEALER', 'shop_pay', true, false, false, false, -1, true, 4000, null, $cook_eat);
    Houses::$cookanim = $cook;


    /* Get all existing houses from database */
    $houses = DB::GetAllHouses();
    foreach ($houses as $name)
    {
      /* Replace the spaces in the name to underlines */
      $fixed_name = str_replace(' ', '_', $name);
      $fixed_name = str_replace(',', '_', $fixed_name);
      require_once('gamemodes/houses/' . $fixed_name . '.php');
      $classname = 'House_' . $fixed_name;
      /* Load the database data for this house */
      $data = DB::GetHouseData($name);
      $house = new $classname($name, $data['maxrooms'], $data['price']);
      $house->SetFridgeSize($data['fridgesize']);
      Houses::Register($house);
      echo ">>>>> Loaded house '{$name}'\n";
    }
  }

  /**
   ** Register
   ** Registers a new house for the manager
   **
   ** Parameters:
   ** - house: The house to register
   **/
  private static function Register(House $house)
  {
    Houses::$houses[$house->ID()] = $house;
  }


  /**
   ** RegisterCookItem
   ** Register a cooking item
   **
   ** Parameters:
   ** - name:     Name of the item
   ** - food:     Required and gained food
   ** - time:     Required time to cook it
   ** - strength: Lost strength eating this
   **/
  private static function RegisterCookItem($name, $food, $time, $strength)
  {
    Houses::$cookitems[$name] = new CookItem($name, $food, $time, $strength);
  }


  /**
   ** MakeID
   ** Creates a unique ID for a given house
   **
   ** Parameters:
   ** - name: The house name
   **/
  public static function MakeID($name)
  {
    return crc32($name);
  }


  /**
   ** Find
   ** Finds a house by its unique ID. If doesnt exist, returns null.
   **
   ** Parameters:
   ** - houseid: The unique ID of the house to find
   **/
  public static function Find($houseid)
  {
    if (isset(Houses::$houses[$houseid]))
      return Houses::$houses[$houseid];
    return null;
  }

  /**
   ** FindByDBID
   ** Finds a room in all houses by a database ID.
   **
   ** Parameters:
   ** - id: The room database id
   **/
  public static function FindByDBID($id)
  {
    foreach (Houses::$houses as $house)
    {
      $room = $house->GetRoomByDBID($id);
      if ($room)
        return $room;
    }
    return null;
  }


  /**
   ** UnsellPlayerRooms
   ** If the given player has some house for sale, unsell it.
   **
   ** Parameters:
   ** - player: The player to check
   **/
  public static function UnsellPlayerRooms(Player $player)
  {
    foreach (Houses::$houses as $house)
    {
      $room = $house->GetRoom($player);
      if ($room && $room->IsAvailable())
      {
        $room->SetPrice(-1);
        $room->SetAvailable(false);
        $house->UpdateCheapest();
      }
    }
  }


  /**
   ** UnsellFactionRooms
   ** Same as UnsellPlayerRooms, but for faction.
   **
   ** Parameters:
   ** - faction: The faction to check
   **/
  public static function UnsellFactionRooms(Faction $faction)
  {
    foreach (Houses::$houses as $house)
    {
      $room = $house->GetRoom($faction);
      if ($room && $room->IsAvailable())
      {
        $room->SetPrice(-1);
        $room->SetAvailable(false);
        $house->UpdateCheapest();
      }
    }
  }



  /**
   ** LocatePlayerRoom
   ** For internal use. Locates what house/room is a player requesting in a command,
   ** checking if he is inside some room or in the house icon specifying a room number
   ** in the command. The parameters are the standard command parameters plus the
   ** return values as reference.
   **/
  private static function LocatePlayerRoom(Player $player, $numparams, $params, &$house, &$room, $param_offset = 0)
  {
    $house = null;
    $room  = null;

    if ($player->location instanceof House)
    {
      $house = $player->location;
      $room  = $house->GetPlayerRoom($player);
      return;
    }
    else
    {
      foreach (Houses::$houses as $curhouse)
      {
        if ($curhouse->IsInMyEntrance($player))
        {
          $house = $curhouse;
          break;
        }
      }
    }

    if ($house != null && !$room)
    {
      if ($house->GetMaxRooms() == 1)
      {
        $room = $house->GetRoom(0);
      }
      else if ($numparams > $param_offset)
      {
        $number = (int)$params[1 + $param_offset];
        if ($number < 0 || $number >= $house->GetMaxRooms())
        {
          $player->Send(COLOR_HOUSE_INVALID_ROOM, '[ERROR] Invalid apartment number');
          $house = null;
          return;
        }
        $room = $house->GetRoom($number);
      }
    }
  }

  /**
   ** DestroyHouse
   ** Deletes a house from list and gamemode
   **
   ** Parameters:
   ** - id: The house id to destroy
   **/
  public static function DestroyHouse($id)
  {
    if (isset(Houses::$houses[$id]))
    {
      $house = Houses::$houses[$id];
      unset(Houses::$houses[$id]);
      $house->Destroy();
    }
  }

  /**
   ** Save
   ** Save all houses rooms data
   **/
  public static function Save()
  {
    foreach (Houses::$houses as $house)
      $house->Save();
  }

  /**
   ** Commands
   **/
  public static function cmdOpen(Player $player, $numparams, $params)
  {
    Houses::LocatePlayerRoom($player, $numparams, $params, &$house, &$room);

    if ($house == null)
      return COMMAND_OK;

    if ($player->location instanceof House && !$player->location->IsInExit($player))
      return COMMAND_OK;

    if (!$room)
    {
      if ($numparams == 0)
      {
        /* Check if the player or his faction have a room */
        if (($room = $house->GetRoom($player)) == null)
        {
          $faction = $player->GetFaction();
          if (!$faction || ($room = $house->GetRoom($faction)) == null)
          {
            $player->Send(COLOR_HOUSE_DONTOWN, '[ERROR] You dont own any apartment in this building');
            return COMMAND_BREAK;
          }
        }
      }
      else
        return COMMAND_BREAK;
    }

    if ($room->GetOwnership()->Allowed($player) == false)
    {
      if ($house->GetMaxRooms() > 1)
        $player->Send(COLOR_HOUSE_OPEN_DISALLOWED, '[ERROR] You are not allowed to open that apartment');
      else
        $player->Send(COLOR_HOUSE_OPEN_DISALLOWED, '[ERROR] You are not allowed to open this house');
      return COMMAND_BREAK;
    }

    if (!$room->IsOpened())
    {
      $room->Open();
      if ($house->GetMaxRooms() > 1)
      {
        $player->Send(COLOR_HOUSE_OPENED, "[HOUSE] You opened the apartment number {$room->Number()}");
        Messages::SendNear($player, COLOR_ACTION, "{$player->name} is opening his apartment door");
      }
      else
      {
        $player->Send(COLOR_HOUSE_OPENED, '[HOUSE] You opened this house');
        Messages::SendNear($player, COLOR_ACTION, "{$player->name} is opening the house door");
      }
    }

    return COMMAND_BREAK;;
  }

  public static function cmdClose(Player $player, $numparams, $params)
  {
    Houses::LocatePlayerRoom($player, $numparams, $params, &$house, &$room);

    if ($house == null)
      return COMMAND_OK;

    if ($player->location instanceof House && !$player->location->IsInExit($player))
      return COMMAND_OK;

    if ($room == null)
    {
      if ($numparams == 0)
      {
        /* Check if the player or his faction have a room */
        if (($room = $house->GetRoom($player)) == null)
        {
          $faction = $player->GetFaction();
          if (!$faction || ($room = $house->GetRoom($faction)) == null)
          {
            $player->Send(COLOR_HOUSE_DONTOWN, '[ERROR] You dont own any apartment in this building');
            return COMMAND_BREAK;
          }
        }
      }
      else
        return COMMAND_BREAK;
    }

    if ($room->GetOwnership()->Allowed($player) == false)
    {
      if ($house->GetMaxRooms() > 1)
        $player->Send(COLOR_HOUSE_CLOSE_DISALLOWED, '[ERROR] You are not allowed to close that apartment');
      else
        $player->Send(COLOR_HOUSE_CLOSE_DISALLOWED, '[ERROR] You are not allowed to close this house');
      return COMMAND_BREAK;
    }

    if ($room->IsOpened())
    {
      $room->Close();
      if ($house->GetMaxRooms() > 1)
      {
        $player->Send(COLOR_HOUSE_CLOSED, "[HOUSE] You closed the apartment number {$room->Number()}");
        Messages::SendNear($player, COLOR_ACTION, "{$player->name} is closing his apartment door");
      }
      else
      {
        $player->Send(COLOR_HOUSE_CLOSED, '[HOUSE] You closed this house');
        Messages::SendNear($player, COLOR_ACTION, "{$player->name} is closing the house door");
      }
    }

    return COMMAND_BREAK;
  }

  public static function cmdBuy(Player $player, $numparams, $params)
  {
    Houses::LocatePlayerRoom($player, $numparams, $params, &$house, &$room);

    if ($house == null)
      return COMMAND_OK;

    if ($room == null)
    {
      if ($numparams == 0)
      {
        $room = $house->GetCheapest();
        if ($room == null)
        {
          $player->Send(COLOR_HOUSE_NOTFORSALE, '[ERROR] There arent apartments for sale in this building');
          return COMMAND_BREAK;
        }
      }
      else
        return COMMAND_BREAK;
    }

    if ($room->GetOwnership()->Allowed($player))
    {
      $player->Send(COLOR_HOUSE_BUYTHEIROWN, '[ERROR] You cant buy your own properties');
      return COMMAND_BREAK;
    }

    if ($house->GetMaxRooms() > 1 && $house->GetRoom($player))
    {
      $player->Send(COLOR_HOUSE_ALREADYOWN, '[ERROR] You already own an apartment in this building');
      return COMMAND_BREAK;
    }

    if (!$room->IsAvailable())
    {
      if ($house->GetMaxRooms() > 1)
        $player->Send(COLOR_HOUSE_NOTFORSALE, '[ERROR] This apartment is not for sale');
      else
        $player->Send(COLOR_HOUSE_NOTFORSALE, '[ERROR] This house is not for sale');
      return COMMAND_BREAK;
    }

    $newowner = new Ownership($player->account->ID());
    if ($room->GetOwnership()->TransferOwnership($player, $newowner, $room->GetPrice()))
    {
      $room->SetAvailable(false);
      $room->SetPrice(-1);
      $house->UpdateOwnership($room, $newowner);
      $house->UpdateCheapest();
      if ($house->GetMaxRooms() > 1)
        $player->Send(COLOR_HOUSE_BOUGHT, "[HOUSE] You adquired the apartment number {$room->Number()} in this house");
      else
        $player->Send(COLOR_HOUSE_BOUGHT, "[HOUSE] You bought the house {$house->GetName()}");
    }

    return COMMAND_BREAK;
  }

  public static function cmdFbuy(Player $player, $numparams, $params)
  {
    Houses::LocatePlayerRoom($player, $numparams, $params, &$house, &$room);

    if ($house == null)
      return COMMAND_OK;

    $faction = $player->GetFaction();
    if (!$faction || !$faction->AllowedTo($player, MEMBER_ALLOWBANK))
      return COMMAND_BREAK;

    if ($room == null)
    {
      if ($numparams == 0)
      {
        $room = $house->GetCheapest();
        if ($room == null)
        {
          $player->Send(COLOR_HOUSE_NOTFORSALE, '[ERROR] There arent apartments for sale in this building');
          return COMMAND_BREAK;
        }
      }
      else
        return COMMAND_BREAK;
    }

    if ($room->GetOwnership()->Allowed($player))
    {
      $player->Send(COLOR_HOUSE_BUYTHEIROWN, '[ERROR] You cant buy your own properties');
      return COMMAND_BREAK;
    }

    if ($house->GetRoom($faction))
    {
      if ($house->GetMaxRooms() > 1)
        $player->Send(COLOR_HOUSE_ALREADYOWN, '[ERROR] Your faction already owns an apartment in this building');
      else
        $player->Send(COLOR_HOUSE_ALREADYOWN, '[ERROR] Your faction already owns this house');
      return COMMAND_BREAK;
    }

    if (!$room->IsAvailable())
    {
      if ($house->GetMaxRooms() > 1)
        $player->Send(COLOR_HOUSE_NOTFORSALE, '[ERROR] This apartment is not for sale');
      else
        $player->Send(COLOR_HOUSE_NOTFORSALE, '[ERROr] This house is not for sale');
      return COMMAND_BREAK;
    }

    $newowner = new Ownership(null, $faction->ID());
    if ($room->GetOwnership()->TransferOwnership($player, $newowner, $room->GetPrice()))
    {
      $room->SetAvailable(false);
      $room->SetPrice(-1);
      $house->UpdateOwnership($room, $newowner);
      $house->UpdateCheapest();

      if ($house->GetMaxRooms() > 1)
        $msg = "[HOUSE] {$player->name} adquired the apartment number {$room->Number()} in '{$house->GetName()}' for the faction";
      else
        $msg = "[HOUSE] {$player->name} bought for the faction the house '{$house->GetName()}'";
      $faction->Send(COLOR_HOUSE_BOUGHT, $msg, MEMBER_ALLOWBANK);
    }

    return COMMAND_BREAK;
  }

  public static function cmdSell(Player $player, $numparams, $params)
  {
    Houses::LocatePlayerRoom($player, $numparams, $params, &$house, &$room, 1);

    if ($house == null)
      return COMMAND_OK;

    if ($room == null)
    {
      if ($numparams == 1)
      {
        /* Check if the player or his faction have a room */
        if (($room = $house->GetRoom($player)) == null)
        {
          $faction = $player->GetFaction();
          if (!$faction || ($room = $house->GetRoom($faction)) == null)
          {
            $player->Send(COLOR_HOUSE_DONTOWN, '[ERROR] You dont own any apartment in this building');
            return COMMAND_BREAK;
          }
        }
      }
      else
        return COMMAND_BREAK;
    }

    $owner = $room->GetOwnership();
    if (!$owner->Allowed($player))
    {
      $player->Send(COLOR_HOUSE_SELL_DISALLOWED, '[ERROR] You are not allowed to sell that property');
      return COMMAND_BREAK;
    }

    $faction = $player->GetFaction();
    if ($faction && $owner->GetType() == OWNERSHIP_FACTION &&
        !$faction->AllowedTo($player, MEMBER_ALLOWEBANK))
    {
      $player->Send(COLOR_HOUSE_SELL_DISALLOWED, '[ERROR] You are not allowed to sell that property');
      return COMMAND_BREAK;
    }

    $price = (int)$params[1];
    $maxprice = $house->GetDefaultPrice() << 1;
    if ($price < 1 || $price > $maxprice) 
    {
      $maxprice = Core::FixIntegerDots($maxprice);
      $player->Send(COLOR_HOUSE_INVALID_PRICE, "[ERROR] Invalid price: must be between 1$ and {$maxprice}$");
      return COMMAND_BREAK;
    }

    if ($faction && $faction->GetHQ() && $faction->GetHQ()->DBID() == $room->DBID())
    {
      $player->Send(COLOR_HOUSE_SELLING_HQ, "[ERROR] You can't sell the faction HQ");
      return COMMAND_BREAK;
    }

    $room->SetAvailable(true);
    $room->SetPrice($price);
    $house->UpdateCheapest();
    $price = Core::FixIntegerDots($price);

    if ($owner->GetType() == OWNERSHIP_ACCOUNT)
    {
      if ($house->GetMaxRooms() > 1)
        $player->Send(COLOR_HOUSE_FORSALE, "[HOUSE] The apartment number {$room->Number()} is now for sale for {$price}$");
      else
        $player->Send(COLOR_HOUSE_FORSALE, "[HOUSE] This house is now for sale for {$price}$");
    }
    else if ($faction && $owner->GetType() == OWNERSHIP_FACTION)
    {
      if ($house->GetMaxRooms() > 1)
        $str = "[HOUSE] {$player->name} has put the faction apartment number {$room->Number()} of '{$house->GetName()}' for sale for {$price}$";
      else
        $str = "[HOUSE] {$player->name} has put the faction house '{$house->GetName()}' for sale for {$price}$";
      $faction->Send(COLOR_HOUSE_FORSALE, $str, MEMBER_ALLOWBANK);
    }

    return COMMAND_BREAK;
  }

  public static function cmdUnsell(Player $player, $numparams, $params)
  {
    Houses::LocatePlayerRoom($player, $numparams, $params, &$house, &$room);

    if ($house == null)
      return COMMAND_OK;

    if ($room == null)
    {
      if ($numparams == 0)
      {
        /* Check if the player or his faction have a room */
        if (($room = $house->GetRoom($player)) == null)
        {
          $faction = $player->GetFaction();
          if (!$faction || ($room = $house->GetRoom($faction)) == null)
          {
            $player->Send(COLOR_HOUSE_DONTOWN, '[ERROR] You dont own any apartment in this building');
            return COMMAND_BREAK;
          }
        }
      }
      else
        return COMMAND_BREAK;
    }

    $owner = $room->GetOwnership();
    if (!$owner->Allowed($player))
    {
      $player->Send(COLOR_HOUSE_UNSELL_DISALLOWED, '[ERROR] You are not allowed to unsell that property');
      return COMMAND_BREAK;
    }

    $faction = $player->GetFaction();
    if ($faction && $owner->GetType() == OWNERSHIP_FACTION &&
        !$faction->AllowedTo($player, MEMBER_ALLOWEBANK))
    {
      $player->Send(COLOR_HOUSE_UNSELL_DISALLOWED, '[ERROR] You are not allowed to unsell that property');
      return COMMAND_BREAK;
    }

    $room->SetAvailable(false);
    $room->SetPrice(-1);
    $house->UpdateCheapest();

    if ($owner->GetType() == OWNERSHIP_ACCOUNT)
    {
      if ($house->GetMaxRooms() > 1)
        $player->Send(COLOR_HOUSE_FORSALE, "[HOUSE] Your apartment is not anymore for sale");
      else
        $player->Send(COLOR_HOUSE_FORSALE, "[HOUSE] This house is not anymore for sale");
    }
    else if ($faction && $owner->GetType() == OWNERSHIP_FACTION)
    {
      if ($house->GetMaxRooms() > 1)
        $str = "[HOUSE] {$player->name} has put the faction apartment number {$room->Number()} of '{$house->GetName()}' as not for sale";
      else
        $str = "[HOUSE] {$player->name} has put the faction house '{$house->GetName()}' as not for sale";
      $faction->Send(COLOR_HOUSE_FORSALE, $str, MEMBER_ALLOWBANK);
    }

    return COMMAND_BREAK;

  }

  public static function cmdFridgestats(Player $player, $numparams, $params)
  {
    if ($player->location instanceof House)
    {
      $house = $player->location;
      if (($pos = $house->GetFridgePos()) == null)
      {
        $player->Send(COLOR_HOUSE_HASNTFRIDGE, '[ERROR] This house hasnt fridge');
        return COMMAND_OK;
      }
      if (!$pos->IsInSphere($player->Position(), 2.5))
      {
        $player->Send(COLOR_HOUSE_NOTNEARFRIDGE, '[ERROR] You are not near the fridge');
        return COMMAND_OK;
      }
      $room = $house->GetPlayerRoom($player);
      Messages::SendNear($player, COLOR_ACTION, "{$player->name} is looking inside the fridge");
      $player->Send(COLOR_HOUSE_FRIDGESTATS, "[HOUSE] Fridge status: {$room->GetFridge()}/{$house->GetFridgeSize()}");
    }

    return COMMAND_OK;
  }

  public static function cmdCook(Player $player, $numparams, $params)
  {
    if ($player->location instanceof House)
    {
      $house = $player->location;
      if (($pos = $house->GetKitchen()) == null)
      {
        $player->Send(COLOR_HOUSE_HASNTKITCHEN, '[ERROR] This house doesn\'t have a cooker');
        return COMMAND_OK;
      }
      if (!$pos->IsInSphere($player->Position(), 2.5))
      {
        $player->Send(COLOR_HOUSE_NOTNEARKITCHEN, '[ERROR] You are not near the cooker');
        return COMMAND_OK;
      }
      $player->SetMenu(Houses::$cookmenu);
    }

    return COMMAND_OK;
  }


  /**
   ** Cooking
   **/
  public static function cookStart(Player $player)
  {
    $player->Freeze(true);
  }

  public static function cookStop(Player $player)
  {
    $player->Freeze(false);
  }

  public static function playerCooked(Player $player, CookItem $item)
  {
    if ($player->location instanceof House)
    {
      $player->SetHunger($player->GetHunger() + $player->ApplyCookingSkill($item->food) * 10);
      $player->SetStrength($player->GetStrength() - $item->strength);
    }
  }

  public static function cookItem(Player $player, $item)
  {
    if (($player->location instanceof House) && isset(Houses::$cookitems[$item]))
    {
      $house = $player->location;
      $room = $house->GetPlayerRoom($player);
      $cook = Houses::$cookitems[$item];

      $fridge = $room->GetFridge();
      if ($fridge < $cook->food)
      {
        $player->Send(COLOR_HOUSE_NOTENOUGHFOOD, "[ERROR] There is not enough food in your fridge to cook a {$item}");
      }

      else if (($anim = $player->GetAnimation()) && $anim->stop_data instanceof CookItem)
      {
        $player->Send(COLOR_HOUSE_ALREADYCOOKING, "[ERROR] You are already cooking something");
      }

      else
      {
        Messages::SendNear($player, COLOR_ACTION, "{$player->name} is cooking {$cook->name}");

        $cookanim = clone Houses::$cookanim;
        $cookanim->steptime = $cook->time;
        $cookanim->stop_cbk = array('Houses', 'playerCooked');
        $cookanim->stop_data = $cook;
        $player->Animate($cookanim);
        $room->SetFridge($fridge - $cook->food);
      }
    }
  }
}

class CookItem
{
  public $name;
  public $food;
  public $time;
  public $strength;

  public function __construct($name, $food, $time, $strength)
  {
    $this->name     = $name;
    $this->food     = $food;
    $this->time     = $time;
    $this->strength = $strength;
  }
}
?>

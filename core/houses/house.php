<?
/**
 ** class House
 ** Manages each block of apartments(rooms), that can be a single apartment(room) (luxury houses).
 **/
abstract class House extends Location
{
  /**
   ** Attributes
   ** - fridgesize:     The fridge size of all rooms in this building
   ** - maxrooms:       The number of rooms that can allow this house
   ** - availrooms:     The number of available rooms
   ** - name:           Name for this building
   ** - id:             Unique ID for this building, used for core and for database
   ** - rooms:          This house rooms sorted by number
   ** - prooms:         This house rooms sorted by account
   ** - frooms:         This house rooms sorted by faction
   ** - default_price:  Default price for not owned rooms, and max price for rooms for sale
   ** - cheapest:       The cheapest room in this house
   ** - kitchen:        The kitchen position, if it has
   **/
  private $fridgesize = 0;
  private $maxrooms = 0;
  private $availrooms = 0;
  private $name;
  private $id;
  private $rooms = array();
  private $prooms = array();
  private $frooms = array();
  private $default_price = 0;
  private $cheapest = null;
  private $kitchen = null;
  private $fridgepos = null;

  /**
   ** Default constructor
   **
   ** Parameters:
   ** - id:             The unique ID for this house
   ** - name:           The name of this hosue
   ** - maxrooms:       Maximum number of rooms
   ** - default_price:  Default price for unowned rooms, and max price for rooms for sale
   ** - corner1:        Top-Left corner of the location
   ** - corner2:        Bottom-right corner of the location
   ** - edge:           Edge size for the area of this location
   ** - start:          Starting point (default spawn)
   ** - interior:       Interior ID for this location
   **/
  public function __construct($id, $name, $maxrooms, $default_price, Position $corner1, Position $corner2, $edge, Position $start, $interior)
  {
    $location0 = Locations::Find(0);
    $this->id = $id;
    $this->name = $name;
    $this->maxrooms = $maxrooms;
    $this->availrooms = 0;
    $this->default_price = $default_price;
    parent::__construct($id, $corner1, $corner2, $edge, $start, $location0, -1, $interior, $id);

    /* Load all rooms from database */
    $rooms = DB::GetHouseRooms($this->id);
    foreach ($rooms as $room)
    {
      if ($room['number'] >= $this->maxrooms)
        continue;

      $newroom = new Room($room['id'], $this, $room['number'], $room['fridge'], new Ownership($room['owner'], $room['fowner']));
      if ($room['owner'] == null && $room['fowner'] == null)
      {
        /* If both owner and fowner are null, this is an unsold room */
        $newroom->SetAvailable(true);
        $newroom->SetPrice($this->default_price);
        $this->availrooms++;
      }
      else
      {
        $newroom->SetPrice($room['price']);
        if ($room['price'] != -1)
        {
          /* If price isnt -1, the owner put it for sale */
          $newroom->SetAvailable(true);
          $this->availrooms++;
        }

        if ($room['owner'] != null)
          $this->prooms[$room['owner']] = $newroom;
        if ($room['fowner'] != null)
          $this->frooms[$room['fowner']] = $newroom;
      }
      $this->rooms[$newroom->Number()] = $newroom;

      /* Update the cheapest room on this building */
      if ($newroom->IsAvailable() && ($this->cheapest == null || $this->cheapest->GetPrice() > $newroom->GetPrice()))
        $this->cheapest = $newroom;

      $newroom->SetChanged(false);
    }
  }

  /**
   ** Destroy
   ** Destroys this house and its location
   **/
  public function Destroy()
  {
    $this->rooms = null;
    $this->prooms = null;
    $this->frooms = null;
    $this->cheapest = null;
    parent::Destroy();
  }

  /**
   ** Save
   ** Saves the rooms info to the database
   **/
  public function Save()
  {
    foreach ($this->rooms as $room)
    {
      if ($room->IsChanged())
      {
        $data = $room->GetData();
        if (!DB::SaveRoom($data))
          break;
      }
    }
  }


  /**
   ** ID
   ** Returns this house unique ID
   **/
  public function ID()
  {
    return $this->id;
  }

  /**
   ** GetName
   ** Returns this house name
   **/
  public function GetName()
  {
    return $this->name;
  }

  /**
   ** GetMaxRooms
   ** Returns the maximum number of rooms that this
   ** house can have
   **/
  public function GetMaxRooms()
  {
    return $this->maxrooms;
  }

  /**
   ** GetDefaultPrice
   ** Returns the default price for rooms in this building
   **/
  public function GetDefaultPrice()
  {
    return $this->default_price;
  }


  /**
   ** UpdateCheapest
   ** Looks through all this house rooms to find the cheapest
   ** room for sale. If it is null, then there arent rooms
   ** for sale.
   **/
  public function UpdateCheapest()
  {
    $cheapest = null;
    foreach ($this->rooms as $room)
    {
      if ($room->IsAvailable() && ($cheapest == null || $cheapest->GetPrice() > $room->GetPrice()))
        $cheapest = $room;
    }

    if ($cheapest == null || $this->cheapest == null)
    {
      $this->cheapest = $cheapest;
      $this->UpdateHouseEntrances();
    }
    else
      $this->cheapest = $cheapest;
  }

  /**
   ** GetRoom
   ** Returns one of this house rooms
   **
   ** Parameters:
   ** - id: 
   **           * If it's a Player object, searchs by account ID
   **           * If it's a Faction object, searchs by faction ID
   **           * Else, search by room number
   **/
  public function GetRoom($id)
  {
    if ($id instanceof Player)
    {
      if ($id->account && isset($this->prooms[$id->account->ID()]))
        return $this->prooms[$id->account->ID()];
    }
    else if ($id instanceof Faction)
    {
      if (isset($this->frooms[$id->ID()]))
        return $this->frooms[$id->ID()];
    }
    else if (isset($this->rooms[(int)$id]))
      return $this->rooms[(int)$id];
    return null;
  }

 /**
  ** GetRoomByDBID
  ** Returns one of thiss house rooms searching by their database ID.
  **
  ** Parameters:
  ** - id:  The database id
  **/
 public function GetRoomByDBID($id)
 {
   foreach ($this->rooms as $room)
   {
     if ($room->DBID() == $id)
       return $room;
   }
   return null;
 }


  /**
   ** AddHouseEntrance
   ** Adds a new entrance for this location, only for use in
   ** children classes (instantiated houses).
   **
   ** Parameters:
   ** - name:     Name for this entrance
   ** - position: Position in Location0 for the entrance
   ** - start:    Set an alternative starting point
   **/
  protected function AddHouseEntrance($name, Position $position, Position $start = null)
  {
    if ($this->cheapest)
      parent::AddEntrance(1273, $name, $position, $start);
    else
      parent::AddEntrance(1272, $name, $position, $start);
  }


  /**
   ** UpdateHouseEntrances
   ** Private function to update the house entrance icons.
   **  - If there are rooms for sale, set a green house icon (1273).
   **  - If not, set a blue house icon (1272).
   **/
  private function UpdateHouseEntrances()
  {
    if ($this->cheapest)
    {
      foreach ($this->entrances as $entrance)
        $entrance->ChangeIcon(1273);
    }
    else
    {
      foreach ($this->entrances as $entrance)
        $entrance->ChangeIcon(1272);
    }
  }


  /**
   ** SetFridgeSize
   ** Change the default fridge size for the rooms in this building.
   **
   ** Parameters:
   ** - amount: The new fridge size
   **/
  public function SetFridgeSize($amount)
  {
    $this->fridgesize = $amount;
  }

  /**
   ** GetFridgeSize
   ** Returns the default fridge size for the rooms in this building.
   **/
  public function GetFridgeSize()
  {
    return $this->fridgesize;
  }


  /**
   ** GetPlayerRoom
   ** If a player is inside one of this house rooms, detect
   ** in what room number he is using the virtual world offset.
   **
   ** Parameters:
   ** - player: The player to check
   **/
  public function GetPlayerRoom(Player $player)
  {
    $number = $player->vworld - $this->id;
    if ($number < 0 || $number >= $this->maxrooms)
      return null;
    return $this->rooms[$number];
  }


  /**
   ** GetCheapest
   ** Returns the cheapest room for sale, null if there isn't.
   **/
  public function GetCheapest()
  {
    return $this->cheapest;
  }


  /**
   ** UpdateOwnership
   ** Updates the ownership of one of the rooms
   **
   ** Parameters:
   ** - room:   The room to update
   ** - owner:  The new owner
   **/
  public function UpdateOwnership(Room $room, Ownership $owner)
  {
    $oldowner = $room->GetOwnership();

    switch ($oldowner->GetType())
    {
      case OWNERSHIP_ACCOUNT:
        unset($this->prooms[$oldowner->GetOwner()]);
        break;
      case OWNERSHIP_FACTION:
        unset($this->frooms[$oldowner->GetOwner()]);
        break;
    }

    switch ($owner->GetType())
    {
      case OWNERSHIP_ACCOUNT:
        $this->prooms[$owner->GetOwner()] = $room;
        break;
      case OWNERSHIP_FACTION:
        $this->frooms[$owner->GetOwner()] = $room;
        break;
    }

    $room->UpdateOwnership($owner);
    if ($owner->GetType() == OWNERSHIP_NULL)
    {
      $room->SetPrice($this->default_price);
      $room->SetAvailable(true);
      $this->UpdateCheapest();
    }
  }

  /**
   ** SetKitchen
   ** Sets this house kitchen position
   **
   ** Parameters:
   ** - position: The kitchen position
   **/
  public function SetKitchen(Position $position)
  {
    $this->kitchen = $position;
  }

  /**
   ** GetKitchen
   ** Returns the kitchen position
   **/
  public function GetKitchen()
  {
    return $this->kitchen;
  }

  /**
   ** SetFridgePos
   ** Sets this house fridge position
   **
   ** Parameters:
   ** - position: The fridge position
   **/
  public function SetFridgePos(Position $position)
  {
    $this->fridgepos = $position;
  }

  /**
   ** GetFridgePos
   ** Returns the fridge position
   **/
  public function GetFridgePos()
  {
    return $this->fridgepos;
  }



  /**
   ** Inherited abstract functions
   **/
  protected function OnEnter(Player $player, $numparams, $params)
  {
    if ($numparams > 0)
    {
      $number = (int)$params[1];
      if ($number >= $this->maxrooms || $number < 0)
      {
        GameTextForPlayer($player->id, '~r~Closed', 3000, 4);
        return LOCATION_DISALLOW;
      }

      if (!$this->rooms[$number]->IsOpened() && !$this->rooms[$number]->GetOwnership()->Allowed($player))
      {
        GameTextForPlayer($player->id, '~r~Closed', 3000, 4);
        return LOCATION_DISALLOW;
      }

      return LOCATION_ALLOW | Locations::VWorldOffset($number);
    }
    else
    {
      if ($this->maxrooms > 1)
      {
        $faction = $player->GetFaction();

        $room = $this->GetRoom($player);
        if (!$room && $faction)
          $room = $this->GetRoom($faction);

        if (!$room)
        {
          $player->Send(COLOR_HOUSE_CANTENTER, '[ERROR] You dont own any apartment here, specify the apartment to enter');
          return LOCATION_DISALLOW;
        }
      }
      else
      {
        $room = $this->GetRoom(0);
        if (!$room->IsOpened() && !$room->GetOwnership()->Allowed($player))
        {
          GameTextForPlayer($player->id, '~r~Closed', 3000, 4);
          return LOCATION_DISALLOW;
        }
      }
      return LOCATION_ALLOW | Locations::VWorldOffset($room->Number());
    }
  }

  protected function OnExit(Player $player, $numparams, $params)
  {
    return LOCATION_ALLOW;
  }

  protected function OnWalkin(Player $player, LocationEntrance $entrance)
  {
    $str = "~r~{$this->name}";

    if ($this->cheapest)
    {
      $price = Core::FixIntegerDots($this->cheapest->GetPrice());
      $str .= "~n~~y~Price:~w~ {$this->cheapest->GetPrice()}$";
      if ($this->maxrooms > 1)
        $str .= " ~y~(Apartment~w~ {$this->cheapest->Number()}~y~)";
    }

    if ($this->maxrooms > 1)
    {
      if ($room = $this->GetRoom($player))
        $str .= "~n~~y~Your apartment:~w~ {$room->Number()}";

      $faction = $player->GetFaction();
      if ($faction && ($room = $this->GetRoom($faction)))
        $str .= "~n~~y~Your faction apartment:~w~ {$room->Number()}";
    }
    else
    {
      if ($room = $this->GetRoom($player))
        $str .= "~n~~y~You own this house";
      else if (($faction = $player->GetFaction()) && ($room = $this->GetRoom($faction)))
        $str .= "~n~~y~Your faction owns this house";
    }

    GameTextForPlayer($player->id, $str, 5000, 4);
  }

  protected function OnWalkout(Player $player, LocationEntrance $entrance)
  {
  }

  public function StayAfterLogout()
  {
    return true;
  }
}
?>

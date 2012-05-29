<?
/*+
 ** class Room
 ** Manages each room of a house
 **/
class Room
{
  /**
   ** Attributes:
   ** - id:       Database ID
   ** - house:    The house that this rooms belongs to
   ** - number:   The room number in its house
   ** - owner:    Ownership for this room, only if they are online
   ** - changed:  Specifies if some data of this room was modified,
   **             to reduce database traffic in datasaves.
   **/
  private $id;
  private $house;
  private $number;
  private $owner;
  private $fridge = 0;
  private $open = false;
  private $available = false;
  private $price = 0;
  private $changed = false;

  public function __construct($id, House $house, $number, $fridge, Ownership $owner)
  {
    $this->id     = $id;
    $this->house  = $house;
    $this->number = $number;
    $this->owner  = $owner;
    $this->fridge = $fridge;
  }

  public function __destruct()
  {
    $this->house = null;
    $this->owner = null;
  }

  public function DBID()
  {
    return $this->id;
  }

  public function SetFridge($amount)
  {
    $this->fridge  = $amount;
    $this->changed = true;
  }

  public function GetFridge()
  {
    return $this->fridge;
  }

  public function GetHouse()
  {
    return $this->house;
  }

  public function Number()
  {
    return $this->number;
  }

  public function GetOwnership()
  {
    return $this->owner;
  }

  public function UpdateOwnership(Ownership $owner)
  {
    $this->owner   = $owner;
    $this->changed = true;
  }

  public function Open()
  {
    $this->open = true;
  }

  public function Close()
  {
    $this->open = false;
  }

  public function IsOpened()
  {
    return $this->open;
  }

  public function SetAvailable($state)
  {
    $this->available = $state;
  }

  public function IsAvailable()
  {
    return $this->available;
  }

  public function SetPrice($price)
  {
    $this->price = $price;
    $this->changed = true;
  }

  public function GetPrice()
  {
    return $this->price;
  }

  public function IsChanged()
  {
    return $this->changed;
  }

  public function SetChanged($state)
  {
    $this->changed = $state;
  }

  public function GetData()
  {
    $data = array();
    $data['id']     = $this->id;
    $data['house']  = $this->house->ID();
    $data['number'] = $this->number;
    $data['fridge'] = $this->fridge;
    $data['price']  = $this->price;

    $data['owner']  = null;
    $data['fowner'] = null;
    switch ($this->owner->GetType())
    {
      case OWNERSHIP_ACCOUNT:
        $data['owner'] = $this->owner->GetOwner();
        break;
      case OWNERSHIP_FACTION:
        $data['fowner'] = $this->owner->GetOwner();
        break;
    }

    $this->changed = false;
    return $data;
  }
}
?>

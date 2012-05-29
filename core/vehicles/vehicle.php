<?
/**
 ** class Vehicle
 ** Intended to create a common interface to all vehicles, stored in database.
 **/
class Vehicle
{
  /**
   ** Attributes
   ** - id:         This vehicle samp id
   ** - type:       The vehicle type (player, faction, shop...)
   ** - dbid:       This vehicle id in database
   ** - model:      The vehicle model id
   ** - ownerid:    Owner database id
   ** - ownername:  Owner name
   ** - locked:     The vehicle is locked
   ** - color:      the vehicle color
   ** - parking:    Default spawn point of the vehicle
   ** - health:     Vehicle health
   ** - fuel:       Vehicle fuel
   ** - fuelusage:  Vehicle fuel usage per second
   ** - trunk:      Vehicle trunk items
   ** - age:        Vehicle age
   ** - attributes: This vehicle model specific attributes
   ** - class:      Vehicle class by age: 48h class1, 40h class2, 35h class3, 30h class 4
   ** - windows:    The vehicle windows state
   ** - lockwin:    Sets if the passengers can roll the windows
   ** - respawn:    Respawn time
   **/
  private $id;
  private $type;
  private $dbid;
  private $model;
  private $owner;
  private $locked;
  private $color = array(0, 0);
  private $parking;
  private $health;
  private $fuel;
  private $fuelusage;
  private $trunk;
  private $age;
  private $attributes;
  private $class;
  private $windows = WINDOWS_ROLLED_UP;
  private $lockwin = false;
  private $respawn;

  public function __construct($id, $type, $dbid, $model, $color, Position $parking, $health = 100, $fuel = 1, $age = 0, Account $owner = null)
  {
    $this->type = $type;
    $this->color[0] = $color[0];
    $this->color[1] = $color[1];
    $this->dbid = $dbid;
    $this->health = $health;
    $this->fuel = $fuel;
    $this->age = $age;
    $this->model = $model;

    if ($this->type == VEHICLE_SHOP)
      $this->respawn = 10;
    else
      $this->respawn = 7200;

    if ($id == -1)
    { /* We must create the vehicle in SAMP */
      if ($parking->x != 0 && $parking->y != 0 && $parking->z != 0 && $parking->angle != 0)
      { /* Make sure that it has a spawn position, if not the player must do /callcar */
        $this->id = CreateVehicle($this->model,
                                  $parking->x, $parking->y, $parking->z, $parking->angle,
                                  $this->color[0], $this->color[1], $this->respawn);
      }
      else
        $this->id = -1;
    }
    else
      $this->id = $id;

    $this->attributes = Vehicles::GetAttributes($model);

    if ($this->age < 48*3600)
    {
      $this->class = 1;
      $this->fuelusage = $this->attributes->GetFuelUsage();
    }
    else if ($this->age < (48+40)*3600)
    {
      $this->class = 2;
      $this->fuelusage = $this->attributes->GetFuelSpace() / (DEFAULT_FUELTIME - 300); /* Penalty of 5 minutes of fuel */
    }
    else if ($this->age < (48+40+35)*3600)
    {
      $this->class = 3;
      $this->fuelusage = $this->attributes->GetFuelSpace() / (DEFAULT_FUELTIME - 600); /* Penalty of 10 minutes of fuel */
    }
    else
    {
      $this->class = 4;
      $this->fuelusage = $this->attributes->GetfuelSpace() / (DEFAULT_FUELTIME - 900); /* Penalty of 15 minutes of fuel */
    }

    /* Add this vehicle to the vehicles list */
    Vehicles::Add($this);
  }

  /* Container retreival functions */
  public function ID() { return $this->id; }
  public function Type() { return $this->type; }
  public function DBID() { return $this->dbid; }
  public function Model() { return $this->model; }
  public function Name() { return $this->attributes->GetName(); }
  public function Owner() { return $this->owner; }
  public function Locked() { return $this->locked; }
  public function Color() { return $this->color; }
  public function Health() { return $this->health; }
  public function Fuel() { return $this->fuel; }
  public function FuelUsage() { return $this->fuelusage; }
  public function FuelSpace() { return $this->attributes->GetFuelSpace(); }
  public function TrunkSpace() { return $this->attributes->GetTrunkSpace(); }
  public function Age() { return $this->age; }
  public function Windows() { return $this->windows; }
  public function LockedWindows() { return $this->lockwin; }


  /**
   ** HasWindows
   ** Checks if this vehicle model has windows
   **/
  public function HasWindows()
  {
    return !!($this->attributes->GetAttribs() & VEHICLE_WINDOWS);
  }

  /**
   ** HasRollableWindows
   ** Checks if this vehicle model has rollable windows
   **/
  public function HasRollableWindows()
  {
    return !!($this->attributes->GetAttribs() & VEHICLE_ROLLABLEW);
  }


  /**
   ** SetHealth
   ** Sets the vehicle health
   **/
  public function SetHealth($health)
  {
    $this->health = $health;
    SetVehicleHealth($this->id, $health);
  }

  /**
   ** SetWindows
   ** Sets the windows rolled up/down
   **/
  public function SetWindows($state)
  {
    if ($state == WINDOWS_ROLLED_UP)
      $this->windows = WINDOWS_ROLLED_UP;
    else
      $this->windows = WINDOWS_ROLLED_DOWN;
  }

  /**
   ** LockWindows
   ** Sets the windows locked or not locked
   **/
  public function LockWindows($state)
  {
    $this->lockwin = $state;
  }
}
?>

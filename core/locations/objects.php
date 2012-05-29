<?
/**
 ** class Obj
 ** Defines an object information. Since objects don't really exist when they are created, but streamed to the players,
 ** we need a data structure to store them.
 **/
class Obj
{
  /**
   ** Attributes
   ** - id:       Internal unique id
   ** - model:    The object model id
   ** - position: The position of this object
   ** - rotation: The rotation of this object
   ** - ids:      The identifiers created for streamed players
   ** - sight:    The sight for this object
   **/
  private $id;
  private $model;
  public  $position;
  private $rotation;
  private $ids;
  private $sight;

  /**
   ** Relations of this object with the other objects in the location sight
   **/
  private $nearest = array();

  public function __construct($model, Position $position, Position $rotation, $sight)
  {
    $this->id = Obj::MakeID();
    $this->model = $model;
    $this->position = $position;
    $this->rotation = $rotation;
    $this->ids = array_fill(0, MAX_PLAYERS, 0);
    $this->sight = $sight;
  }

  public function Destroy()
  {
    foreach ($this->ids as $id => $state)
    { 
      if ($state)
        $this->DestroyForPlayer($id);
    }
    $this->nearest = null;
  }

  /**
   ** ID
   ** Returns the internal identifier of this object
   **/
  public function ID()
  {
    return $this->id;
  }

  /**
   ** MakeID
   ** Returns a unique id for this object, for interla purpouses
   **/
  private static function MakeID()
  {
    static $id = 0;
    $id++;
    return $id;
  }

  /**
   ** Sight
   ** Returns this object sight
   **/
  public function Sight()
  {
    return $this->sight;
  }

  /**
   ** SetNear
   ** Creates a relation with other near object
   **/
  public function SetNear(Obj $obj)
  {
    $this->nearest[] = $obj;
  }


  /**
   ** CreateForPlayer
   ** Creates this object for a specific player
   **/
  public function CreateForPlayer(Player $player)
  {
    if ($this->ids[$player->id] == 0)
    {
      $this->ids[$player->id] = CreatePlayerObject($player->id, $this->model,
                                                   $this->position->x, $this->position->y, $this->position->z,
                                                   $this->rotation->x, $this->rotation->y, $this->rotation->z);
    }
  }

  /**
   ** DestroyForPlayer
   ** Destroys this object for a specific player
   **/
  public function DestroyForPlayer(Player $player)
  {
    if ($this->ids[$player->id] != 0)
    {
      DestroyPlayerObject($player->id, $this->ids[$player->id]);
      $this->ids[$player->id] = 0;
    }
  }

  /**
   ** ExistsForPlayer
   ** Checks if this object is created for a player
   **/
  public function ExistsForPlayer(Player $player)
  {
    if ($this->ids[$player->id] == 0)
      return false;
    return true;
  }


  /**
   ** IsNear
   ** Checks if an object is in the near objects list
   **/
  public function IsNear(Obj $object)
  {
    if ($object->id == $this->id)
      return true;

    foreach ($this->nearest as $o)
    {
      if ($o->id == $object->id)
        return true;
    }

    return false;
  }


  /*+
   ** Nearest
   ** Returns the list of the nearest objects
   **/
  public function Nearest()
  {
    return $this->nearest;
  }


  /**
   ** Equals
   ** Checks if a given object is this object
   **/
  public function Equals(Obj $object)
  {
    if ($object->id == $this->id)
      return true;
    return false;
  }
}

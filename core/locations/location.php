<?
/**
 ** Location
 **
 ** Location defines a place in the game maps, that is not just a position but a place with its
 ** coordinates, vitual world, interior identifier, entrance, exit, nested locations, objects
 ** on it, world boundaries, etc.
 **
 ** A Location can have nested locations and a location can be ONLY in one position in the network.
 ** Duplicating locations can cause the manager to don't work properly.
 ** The root location in what all other locations are nested is Location0, and is the common public
 ** street world. All main locations must be linked to this one or they will be not available for
 ** the players since they all start the game in the Location0.
 **
 ** An interior or coordinates can be used for multiple locations so using pickups as entrances is
 ** a bad idea. For this there must be a --->checkpoint streamer<--- colaborative working with the locations
 ** manager and its areas. These checkpoints will do the same work as in ItalyMafia 2 did the pickups,
 ** but they have a big advantage because you can also detect when a player is leaving a checkpoint
 ** to stop sending him the location entrance info and disallowing him the enter / exit commands.
 **
 ** Each location created must inherit from the class Location, including the Location0, to have a
 ** common interface so any location can be created on the fly by modules, without having the core
 ** to have specific information about them.
 **/


/**
 ** Constants for locations callbacks
 ** LOCATION_ALLOW     Allows the given player to enter / exit the location
 ** LOCATION_DISALLOW  Disallows it
 **/
define ('LOCATION_ALLOW',     0x0001);
define ('LOCATION_DISALLOW',  0x0002);


abstract class Location
{
  /**
   ** Relations of this location with the other locations.
   ** 'children' is that list of locations under the 'parent'.
   **/
  private $children = array();
  private $parent   = null;

  /**
   ** Attributes.
   **
   ** - id:       Unique identifier for this location
   ** - area:     Area manager for this location
   ** - interior: Interior id
   ** - start:    Starting position of this location
   ** - bounds:   World boundaries
   ** - entrance: Entrance data for this location
   ** - objects:  List of this location objects
   ** - sight:    Distance from what objects can be seen (-1 means objects always visible)
   ** - vworld:   Virtual world for this location
   **/
  private   $id;
  private   $area;
  private   $interior = 0;
  private   $start;
  private   $bounds = array('min' => null, 'max' => null);
  protected $entrances = array();
  private   $objects = array();
  private   $sight;
  private   $vworld;


  /**
   ** __construct The default constructor
   **
   ** Parameters:
   ** - id:       This location UNIQUE identifier
   ** - corner1:  Top-left corner of this location in the map
   ** - corner2:  Bottom-right corner of this location in the map
   ** - edge:     Size of each area sectors edge
   ** - parent:   Parent location of this location (usually Location0)
   ** - start:    Initial position for players moved to this location
   ** - interior: Interior ID for this location
   ** - vworld:   Virtual world for this location
   **/
  public function __construct($id,
                              Position $corner1,
                              Position $corner2,
                              $edge,
                              Position $start,
                              Location $parent = null,
                              $sight = -1,
                              $interior = 0,
                              $vworld = 0
                             )
  {
    $this->id = (int)$id;
    $this->parent = $parent;
    if ($parent)
      $parent->AddChild($this);
    Locations::Add($this);

    $this->area = new Area($this, $corner1, $corner2, $edge);

    if ($start == null)
      $this->start = new Position(0, 0, 0, 0);
    else
      $this->start = $start;

    $this->interior = $interior;
    $this->vworld = $vworld;
    $this->sight = $sight;

    $this->bounds['min'] = new Position(-40000, -40000);
    $this->bounds['max'] = new Position(40000, 40000);
  }

  public function Destroy()
  {
    foreach ($this->children as $child)
      $child->Destroy();

    if ($this->parent)
    {
      /* Move players to the parent location */
      foreach (Players::Get() as $player)
      {
        if ($player->location->id == $this->id)
        {
          /* Move to the parent location */
          $player->SetLocation($this->parent);
          $entrance = $this->GetEntrance();
          $player->SetPosition($entrance->position);
        }
      }

      $this->parent->RemoveChild($this);
    }

    foreach ($this->entrances as $entrance)
    {
      if ($entrance->id != -1)
        DestroyPickup($entrance->id);
      $entrance->location = null;
    }
    $this->entrances = null;

    foreach ($this->objects as $obj)
      $obj->Destroy();
    $this->objects = null;

    $this->children = null;
    $this->parent = null;
    Locations::Del($this);
    $this->area->Destroy();
    $this->area = null;
  }


  /**
   ** ID
   ** Returns this location unique identifier
   **/
  public function ID()
  {
    return $this->id;
  }


  /**
   ** GetParent
   ** Returns this location parent
   **/
  public function GetParent()
  {
    return $this->parent;
  }

  /**
   ** GetInteriorID
   ** Returns this location interior ID
   **/
  public function GetInteriorID()
  {
    return $this->interior;
  }


  /**
   ** MovePlayer
   ** Moves a player to this location. Must also change the spawn information of the player to 
   **
   ** Parameters:
   ** - player: The player to move
   **/
  public function MovePlayer(Player $player, $vworld_offset, LocationEntrance $entrance = null)
  {
    if ($entrance == null)
    {
      $player->SetSpawnPosition($this->start);
      $player->SetPosition($this->start);
    }
    else
    {
      $player->SetSpawnPosition($entrance->start);
      $player->SetPosition($entrance->start);
    }
    $player->SetVirtualWorld($this->vworld + $vworld_offset);
    SetPlayerInterior($player->id, $this->interior);
    $player->SetWorldBounds($this->bounds['min'], $this->bounds['max']);

    $player->Update();

    /* Stream the objects for this player if the sight is -1 */
    if ($this->sight == -1)
    {
      foreach ($this->objects as $o)
      {
        $o->CreateForPlayer($player);
      }
    }
  }


  /**
   ** SetBounds
   ** Sets the world boundaries for this location
   **
   ** Parameters:
   ** - min: Position of the top left corner
   ** - max: Position of the bottom right corner
   **/
  public function SetBounds(Position $min, Position $max)
  {
    $this->bounds['min'] = $min;
    $this->bounds['max'] = $max;
  }


  /**
   ** GetBounds
   ** Returns this location world boundaries
   **/
  public function GetBounds()
  {
    return $this->bounds;
  }


  /**
   ** Locate
   ** Locates a player inside this location area
   **
   ** Parameters:
   ** - player: The player to locate
   **
   ** Return value:
   ** - The sector if this location area in what the player is
   **/
  public function Locate(Position $position)
  {
    return $this->area->Locate($position);
  }


  /**
   ** AddEntrance
   ** Sets the entrance for this location to be shown in the parent location
   **
   ** Parameters:
   ** - modelid:  Entrance pickup id (won't take effect if this is not a Location0 child)
   ** - position: Entrance coordinates
   **/
  public function AddEntrance($modelid, $name, Position $position, Position $start = null)
  {
    $id = 0;

    if ($this->parent == Locations::Find(0))
    {
      if ($modelid != -1)
        $id = CreatePickup($modelid, 23, $position->x, $position->y, $position->z);
      else
        $id = -1;
    }
    else
    {
      $id = -1;
      if ($this->parent != null)
        $this->parent->area->AddCheckpoint($position, 1.15);
    }

    if ($start == null)
      $start = $this->start;

    $entrance = new LocationEntrance($id, $name, $position, $start, $this);
    $this->entrances[] = $entrance;
    $this->area->AddCheckpoint($start, 0.7);

    return $entrance;
  }


  /**
   ** GetEntrance
   ** Returns this location entrance position
   **/
  public function GetEntrance()
  {
    if (count($this->entrances))
      return $this->entrances[0];
    return null;
  }


  /**
   ** AddOBject
   ** Adds a dynamic object to this location. If sight is -1, then objects are always shown.
   **
   ** Parameters:
   ** - modelid:  Model of the object
   ** - position: Position of the object
   ** - rotation: Rotation of the object
   **/
  public function AddObject($modelid, Position $position, Position $rotation = null, $sight = 0)
  {
    /* Create the object */
    if ($sight == 0)
      $sight = $this->sight;
    if ($rotation == null)
      $rotation = new Position(0, 0, 0, 0);
    $obj = new Obj($modelid, $position, $rotation, $sight);

    /* Find this object nearest objects */
    if  ($sight != -1)
    {
      foreach ($this->objects as $o)
      {
        if ($position->DistanceTo($o->position) < $sight)
        {
          $obj->SetNear($o);
          $o->SetNear($obj);
        }
      }
    }
    /* This object is also near itself */
    $obj->SetNear($obj);

    /* Add the object to this location objects list */
    $this->objects[] = $obj;

    /* Add the object to its sector objects */
    $sector = $this->area->Locate($position);
    if ($sight != -1)
    {
      $sectors = $sector->FindSectors($position, $sight);
      foreach ($sectors as $sector)
        $sector->AddObject($obj);
    }
    else
      $sector->AddObject($obj);
  }

  /**
   ** LoadObjectsFromFile
   ** Reads the location objects from a file
   ** Parameters:
   ** - path: The path of the objects file
   **/
  public function LoadObjectsFromFile($path)
  {
    $data = file(getcwd() . '/gamemodes/objects/' . $path);

    foreach ($data as $line)
    {
      if (empty($line) || $line[0] == '#')
        continue;
      $info = explode(' ', $line);
      $this->AddObject((int)$info[0], new Position((float)$info[1], (float)$info[2], (float)$info[3]),
                                      new Position((float)$info[4], (float)$info[5], (float)$info[6]),
                                      (int)$info[7]);
    }
  }


  /**
   ** StreamObjects
   ** Streams objects for a player.
   **
   ** Parameters:
   ** - player: The player who needs to get the objects streamed
   **/
  public function StreamObjects(Player $player, Position $position, Sector $sector)
  {
    if ($this->sight != -1)
    {
      $last_object = $player->object;
      $nearest = null;
      $distance = 0;
      $sector->FindNearestObject($position, &$nearest, &$distance);

      $player->object = $nearest;

      if ($last_object != null && $player->object != null && $last_object->Equals($player->object))
        return;

      if ($last_object == null)
      {
        if ($player->object != null)
        {
          foreach ($player->object->Nearest() as $o)
            $o->CreateForPlayer($player);
        }
      }

      else if ($player->object == null)
      {
        if ($last_object != null)
        {
          foreach ($last_object->Nearest() as $o)
            $o->DestroyForPlayer($player);
        }
      }

      else
      {
        $oldobjs = $last_object->Nearest();
        $newobjs = $player->object->Nearest();

        reset($oldobjs);
        reset($newobjs);
        $cur = current($newobjs);

        do
        {
          for ($old = current($oldobjs); $old && $old->ID() < $cur->ID(); $old = next($oldobjs))
            $old->DestroyForPlayer($player);

          if ($old && $old->ID() == $cur->ID())
            next($oldobjs);

          $cur->CreateForPlayer($player);
          $cur = next($newobjs);
        }
        while ($cur);

        for ($old = current($oldobjs); $old; $old = next($oldobjs))
          $old->DestroyForPlayer($player);
          
/*          while ($oldobjs != null && $oldobjs->object->ID() < $newobjs->object->ID())
          {
            $oldobjs->object->DestroyForPlayer($player);
            $oldobjs = $oldobjs->next;
          }
          if ($oldobjs != null && $oldobjs->object->ID() == $newobjs->object->ID())
            $oldobjs = $oldobjs->next;

          $newobjs->object->CreateForPlayer($player);
          $newobjs = $newobjs->next;
        } while ($newobjs != null);

        while ($oldobjs != null)
        {
          $oldobjs->object->DestroyForPlayer($player);
          $oldobjs = $oldobjs->next;
        }*/
      }
    }
  }



  /**
   ** UnstreamObjects
   ** Removes all streamed objects for this player.
   **
   ** Parameters:
   ** - player: The player to remove the objects
   **/
  public function UnstreamObjects(Player $player)
  {
    if ($this->sight == -1)
    {
      foreach ($this->objects as $o)
        $o->DestroyForPlayer($player);
    }
    else if ($player->object)
    {
      foreach ($player->object->Nearest() as $o)
        $o->DestroyForPlayer($player);
      $player->object = null;
    }
  }


  /**
   ** WalksInPickup
   ** Callback to be used only by Locations manager. Notifies that a player walked in
   ** some child location entrance marked with pickup
   **
   ** Parameters:
   ** - player:   The player who is walking in
   ** - pickupid: The pickup id of the location entrance
   **/
  public function WalksInPickup(Player $player, $pickupid)
  {
    foreach ($this->children as $l)
    {
      foreach ($l->entrances as $entrance)
      {
        if ($entrance->id == $pickupid)
        {
          $l->OnWalkin($player, $entrance);
          return true;
        }
      }
    }

    return false;
  }


  /**
   ** WalksInCheckpoint
   ** Same as WalksInPickup, but for sub-locations.
   **/
  public function WalksInCheckpoint(Player $player)
  {
    foreach ($this->children as $l)
    {
      foreach ($l->entrances as $entrance)
      {
        if ($entrance->position->IsInSphere($player->Position(), 3.5))
        {
          $l->OnWalkin($player, $entrance);
          return true;
        }
      }
    }

    foreach ($this->entrances as $entrance)
    {
      if ($entrance->start->IsInSphere($player->Position(), 3.5))
      {
        $this->OnWalkout($player, $entrance);
        return true;
      }
    }

    return false;
  }


  /**
   ** WantsEnter
   ** Callback to be used only by Locations manager. Notifies that a player wants
   ** to enter a child location (typed /enter)
   **
   ** Parameters:
   ** - player:    The player who wants to enter
   ** - numparams: Number of parameters of his command
   ** - params:    Parameters of his command
   **/
  public function WantsEnter(Player $player, $numparams, $params)
  {
    if ($entrance = $this->IsInEntrance($player))
    {
      if (($response = $entrance->location->OnEnter($player, $numparams, $params)) & LOCATION_ALLOW)
        $player->SetLocation($entrance->location, ($response >> 16) & 0x0000FFFF, $entrance);
      return true;
    }

    return false;
  }

  /**
   ** IsInEntrance
   ** Checks if a player is in some sublocation entrance. If true, return the entrance.
   **
   ** Parameters:
   ** - player:  The player to check
   **/
  public function IsInEntrance(Player $player)
  {
    foreach ($this->children as $l)
    {
      foreach ($l->entrances as $entrance)
      {
        if ($entrance->position->IsInSphere($player->Position(), 2.5))
          return $entrance;
      }
    }

    return null;
  }


  /**
   ** IsInMyEntrance
   ** Checks if a player is in some of this location entrances. If true, returns the entrance.
   **
   ** Parameters:
   ** - player:  The player to check
   **/
  public function IsInMyEntrance(Player $player)
  {
    foreach ($this->entrances as $entrance)
    {
      if ($entrance->position->IsInSphere($player->Position(), 2.5))
        return $entrance;
    }

    return null;
  }


  /**
   ** WantsExit
   ** Callback to be used only by Locations manager. Notifies that a player wants
   ** to exit his current location (typed /exit). Don't allow it if the player
   ** location is Location0
   **
   ** Parameters:
   ** - player:    The player who wants to exit
   ** - numparams: Number of parameters of his command
   ** - params:    Parameters of his command
   **/
  public function WantsExit(Player $player, $numparams, $params)
  {
    if ($entrance = $this->IsInExit($player))
    {
      if (($response = $this->OnExit($player, $numparams, $params)) & LOCATION_ALLOW)
      {
        $player->SetLocation($this->parent, ($response >> 16) & 0x0000FFFF);
        $player->SetPosition($entrance->position);
      }
      return true;
    }

    return false;
  }

  /**
   ** IsInExit
   ** Checks if a player is in some of this location exits. If true, return the entrance.
   **
   ** Parameters:
   ** - player:  The player to check
   **/
  public function IsInExit(Player $player)
  {
    foreach ($this->entrances as $entrance)
    {
      if ($entrance->start->IsInSphere($player->Position(), 2.5))
        return $entrance;
    }

    return null;
  }


  /**
   ** EnterExitKeybind
   ** Callback to be used only by Locations manager. Notifies that a player
   ** pressed tab twice to enter or exit a location.
   **
   ** Parameters:
   ** - player: The player that activated this event
   **/
  public function EnterExitKeybind(Player $player)
  {
    if (!$this->WantsExit($player, 0, array()))
    {
      if (!$this->WantsEnter($player, 0, array()))
        return false;
    }
    return true;
  }


  /**
   ** Callbacks for abstract extensions
   **/
  abstract protected function OnEnter(Player $player, $numparams, $params);
  abstract protected function OnExit(Player $player, $numparams, $params);
  abstract protected function OnWalkin(Player $player, LocationEntrance $entrance);
  abstract protected function OnWalkout(Player $player, LocationEntrance $entrance);
  abstract public function StayAfterLogout();


  /** Private methods **/
  private function AddChild(Location $child)
  {
    $this->children[$child->id] = $child;
  }

  private function RemoveChild(Location $child)
  {
    if (isset($this->children[$child->id]))
      unset($this->children[$child->id]);
  }
}


/**
 ** class LocationEntrance
 **
 ** Defines the entrance of a location to be identified in the parent location
 ** Attributes are:
 ** - id:       If it's a pickup, then it's the samp pickup id. If it's a checkpoint, then it's the streamer checkpoint id.
 ** - name:     The name of this entrance
 ** - position: Position of the entrance
 ** - start:    The starting position for this entrance
 ** - location: Location in what the player enters using this entrance
 **/
class LocationEntrance
{
  public $id;
  public $name;
  public $position;
  public $start;
  public $location;

  public function __construct($id, $name, Position $position, Position $start, Location $location)
  {
    $this->id = $id;
    $this->name = $name;
    $this->position = $position;
    $this->start = $start;
    $this->location = $location;
  }

  public function ChangeIcon($newmodel)
  {
    if ($this->id != -1)
    {
      DestroyPickup($this->id);
      $this->id = CreatePickup($newmodel, 23, $this->position->x, $this->position->y, $this->position->z);
    }
  }
}
?>

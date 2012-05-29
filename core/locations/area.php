<?

class SectorCheckpoint
{
  public $position;
  public $next;
  public $size;

  public function __construct(Position $position, $size)
  {
    $this->position = $position;
    $this->size = $size;
    $this->next = null;
  }
}

class Sector
{
  public $area;
  public $objects = array();
  public $checkpoints = null;
  public $i;
  public $j;
  public $id;
  public $players = array();
  private $oob = false;

  public function __construct(Area $area = null, $i = 0, $j = 0)
  {
    $this->area = $area;
    $this->i = $i;
    $this->j = $j;
    $this->id = Sector::MakeID();
    if ($this->i == -2 && $this->j == -2)
      $this->oob = true;
  }

  public static function MakeID()
  {
    static $id = 0;
    $id++;
    return $id;
  }

  public function AddPlayer(Player $player)
  {
    $this->players[$player->id] = $player;
    ksort($this->players);
  }

  public function RemovePlayer(Player $player)
  {
    unset($this->players[$player->id]);
  }

  public function GetPlayers()
  {
    return $this->players;
  }

  public function OOB()
  {
    return $this->oob;
  }

  public function DistanceTo(Sector $sector)
  {
    if ($this->area == null || $sector->area == null || $sector->area->location != $this->area->location)
      return 99999999; /* Should be enough distance ... */
    return max(abs($this->i - $sector->i), abs($this->j - $sector->j));
  }

  public function AddObject(Obj $object)
  {
    $this->objects[] = $object;
  }

  /**
   ** FindSectors
   **
   ** Finds the adyacent sectors of this sector if the given
   ** position is near an edge, at at least $sight distance.
   **
   ** Parameters:
   ** - position:  The position to check near the edges
   ** - sight:     The max distance to the edges
   **/
  public function FindSectors(Position $position, $sight)
  {
    $sectors = array();

    if ($this->OOB())
    {
      $sectors[] = $this;
      return $sectors;
    }

    $x = $position->x - $this->area->Base()->x;
    $y = $position->y - $this->area->Base()->y;
    $restx = $x % $this->area->Edge();
    $resty = $y % $this->area->Edge();
    $adyacent_lx = 0;
    $adyacent_ly = 0;
    $adyacent_hx = 0;
    $adyacent_hy = 0;
    $diff_max = $this->area->Edge() - $sight;

    if ($restx <= $sight)
      $adyacent_lx = -1;
    if ($resty <= $sight)
      $adyacent_ly = -1;
    if ($restx >= $diff_max)
      $adyacent_hx = 1;
    if ($resty >= $diff_max)
      $adyacent_hy = 1;
    $oob = false;

    for ($i = $adyacent_lx; $i <= $adyacent_hx; $i++)
    {
      for ($j = $adyacent_ly; $j <= $adyacent_hy; $j++)
      {
        $sector = $this->area->GetSector($this->i + $i, $this->j + $j);
        if (!$sector || $sector->OOB())
          $oob = true;
        else
          $sectors[] = $sector;
      }
    }

    if ($oob == true)
      $sectors[] = $this->area->GetOOB();

    return $sectors;
  }

  public function FindNearestObject(Position $position, &$nearest, &$distance)
  {
    foreach ($this->objects as $o)
    {
      $tmpdist = $position->DistanceTo($o->position);
      if ($tmpdist > $o->Sight())
        continue;

      if ($tmpdist < $distance || $nearest == null)
      {
        $nearest = $o;
        $distance = $tmpdist;
      }
    }
  }

  public function StreamCheckpoints(Player $player)
  {
    $nearest = null;
    $distance = 0.0;
    $tmpdist = 0.0;

    for ($cp = $this->checkpoints; $cp != null; $cp = $cp->next)
    {
      if (($tmpdist = $cp->position->DistanceTo($player->Position())) < $distance || $nearest == null)
      {
        $nearest = $cp;
        $distance = $tmpdist;
      }
    }

    if ($player->checkpoint != $nearest)
    {
      if ($nearest != null)
        SetPlayerCheckpoint($player->id, $nearest->position->x, $nearest->position->y, $nearest->position->z, $nearest->size);
      else
        DisablePlayerCheckpoint($player->id);
    }
    $player->checkpoint = $nearest;
  }

  public function AddCheckpoint(Position $position, $size)
  {
    $cp = new SectorCheckpoint($position, $size);
    $cp->next = $this->checkpoints;
    $this->checkpoints = $cp;
  }
}

class Area
{
  private $base;
  private $size;
  private $edge;
  private $sectors = array();
  private $oob;
  public $location;
  public $max_i;
  public $max_j;

  public function __construct(Location $location, Position $corner1, Position $corner2, $edge)
  {
    $this->location = $location;
    $min_x = $corner1->x;
    $min_y = $corner2->y;
    $max_x = $corner2->x;
    $max_y = $corner1->y;

    $this->size = new Position();
    $size_x = $max_x - $min_x;
    $size_y = $max_y - $min_y;
    $this->size->x = $size_x;
    $this->size->y = $size_y;

    $this->base = new Position($min_x, $min_y);
    $this->edge = $edge;

    $fields_x = (int)($size_x / $edge);
    $fields_y = (int)($size_y / $edge);
    $this->max_i = $fields_x - 1;
    $this->max_j = $fields_y - 1;

    for ($i = 0; $i < $fields_x; $i++)
    {
      for ($j = 0; $j < $fields_y; $j++)
        $this->sectors[$i][$j] = new Sector($this, $i, $j);
    }

    /* Create the out of bounds special sector */
    $this->oob = new Sector($this, -2, -2);
  }

  public function Destroy()
  {
    $this->sectors = null;
    $this->oob = null;
    $this->location = null;
  }

  public function Edge()
  {
    return $this->edge;
  }

  public function Base()
  {
    return $this->base;
  }

  public function Locate(Position $position)
  {
    $x = $position->x - $this->base->x;
    $y = $position->y - $this->base->y;

    if ($x < 0 || $x > $this->size->x || $y < 0 || $y > $this->size->y)
    {
      /* If player is out of boundaries, return the OOB sector */
      return $this->oob;
    }

    return $this->sectors[(int)($x / $this->edge)][(int)($y / $this->edge)];
  }

  public function GetSector($i, $j)
  {
    if ($i < 0 || $j < 0 || $i > $this->max_i || $i > $this->max_j)
      return $this->oob;
    return $this->sectors[$i][$j];
  }

  public function GetOOB()
  {
    return $this->oob;
  }

  public function AddCheckpoint(Position $position, $size = 1.1)
  {
    $sector = $this->Locate($position);
    $sector->AddCheckpoint($position, $size);
  }
}
?>

<?

class Position
{
  public $x;
  public $y;
  public $z;
  public $angle;

  public function __construct($x = 0.0, $y = 0.0, $z = 0.0, $angle = 0.0)
  {
    $this->x = (double)$x;
    $this->y = (double)$y;
    $this->z = (double)$z;
    $this->angle = (double)$angle;
  }

  public function DistanceTo(Position $position)
  {
    $x = $position->x - $this->x;
    $y = $position->y - $this->y;
    $z = $position->z - $this->z;
    return sqrt(($x * $x) + ($y * $y) + ($z * $z));
  }

  public function DistanceToXY(Position $position)
  {
    $x = $position->x - $this->x;
    $y = $position->y - $this->y;
    return sqrt(($x * $x) + ($y * $y));
  }

  public function IsInSphere(Position $position, $radius_)
  {
    $radius = (double)$radius_;
    if ($position->x >= $this->x - $radius && $position->x <= $this->x + $radius &&
        $position->y >= $this->y - $radius && $position->y <= $this->y + $radius &&
        $position->z >= $this->z - $radius && $position->z <= $this->z + $radius
       )
    {
      return true;
    }
    return false;
  }

  public function IsInSquare(Position $min, Position $max)
  {
    if ($this->x >= $min->x && $this->x <= $max->x &&
        $this->y >= $min->y && $this->y <= $max->y
       )
    {
      return true;
    }
    return false;
  }

  public function IsInCube(Position $min, Position $max)
  {
    if ($this->x >= $min->x && $this->x <= $max->x &&
        $this->y >= $min->y && $this->y <= $max->y &&
        $this->z >= $min->z && $this->z <= $max->z
       )
    {
      return true;
    }
    return false;
  }
}

?>

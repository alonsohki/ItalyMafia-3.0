<?
class House_Beach_apartments extends House
{
  public function __construct($name, $maxrooms, $default_price)
  {
    $id = Houses::MakeID($name);
    parent::__construct($id,        /* Location ID */
                        $name,      /* House name */
                        $maxrooms,  /* Max rooms */
                        $default_price,
                        new Position(2485, -1690), /* Top-left corner */
                        new Position(2505, -1710), /* Bottom-right corner */
                        20, /* Edge size */
                        new Position(2496.0500, -1692.9301, 1014.7422, 180), /* Starting position */
                        3); /* Interior ID */
    parent::AddHouseEntrance('street', new Position(893.5016, -1637.2668, 14.9297, 180));
  }
}
?>

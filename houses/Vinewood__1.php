<?
class House_Vinewood__1 extends House
{
  public function __construct($name, $maxrooms, $default_price)
  {
    $id = Houses::MakeID($name);
    parent::__construct($id,        /* Location ID */
                        $name,      /* House name */
                        $maxrooms,  /* Max rooms */
                        $default_price,
                        new Position(1220, -730), /* Top-left corner */
                        new Position(1320, -830), /* Bottom-right corner */
                        100, /* Edge size */
                        new Position(1258.2936, -838.6871, 1084.0078, 0), /* Starting position */
                        5); /* Interior ID */
    $this->AddHouseEntrance('main', new Position(1298.5369, -798.8372, 83.8, 180));
    $this->AddHouseEntrance('roof', new Position(1287.4863, -788.7426, 92.0313, 90),
                                    new Position(1261.4065, -785.6282, 1091.9063, 270));
    $this->SetKitchen(new Position(1276.9720, -813.8871, 1085.6328));
    $this->SetFridgePos(new Position(1275.4027, -822.4887, 1085.6328));
    $this->LoadObjectsFromFile('Madd_House.txt');
  }
}
?>

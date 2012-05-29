<?
/**
 ** class VehicleShop
 ** Creates the vehicles for sale in the map and allow modules to reject buyers.
 **/

define ('VEHICLESHOP_ALLOW', 0x001);
define ('VEHICLESHOP_DENY',  0x002);

class VehicleShop
{
  private static $prices = array();

  public static function Init()
  {
    Callbacks::Instance()->Register(cOnPlayerEnterVehicle, null, array('VehicleShop', 'OnPlayerEnterVehicle'), -1000);

    echo ">>>>> Adding vehicle shops ...\n";
    /* Register vehicles */
    VehicleShop::Add('Bullet', 200000, array(6, 6), new Position(1116.5715, 2261.9004, 10.4453, 179.8618));
    VehicleShop::Add('Perenniel', 35000, array(0, 0), new Position(1106.2313, 2262.6445, 10.5530, 358.8151));
    VehicleShop::Add('NRG 500', 50000, array(5, 6), new Position(1109.7905, 2262.1455, 10.3839, 176.2969));
    VehicleShop::Add('Maverick', 150000, array(0, 0), new Position(1076.9761, 2311.6589, 10.9968, 179.3844));
  }

  private static function Add($name, $price, $color, Position $position)
  {
    $modelid = Vehicles::FindModelidByName($name);
    if ($modelid != -1)
    {
      $v = new Vehicle(-1, VEHICLE_SHOP, -1, $modelid, $color, $position);
      VehicleShop::$prices[$v->ID()] = $price;
    }
  }

  public static function OnPlayerEnterVehicle(Player $player, Vehicle $vehicle, $ispassenger)
  {
    if (!$ispassenger && $vehicle->Type() == VEHICLE_SHOP)
    {
      $price = VehicleShop::$prices[$vehicle->ID()];
      $name = $vehicle->Name();
      $capacity = $vehicle->FuelSpace();
      $usage = sprintf('%.2f', $vehicle->FuelUsage() * 60);
      $trunk = $vehicle->TrunkSpace();
      $player->Send(COLOR_CARSHOP_HEADER, '');
      $player->Send(COLOR_CARSHOP_HEADER, '.:: Vehicle shop ::.');
      $player->Send(COLOR_CARSHOP_INFO, "This '{$name}' is for sale for {$price}$");
      $player->Send(COLOR_CARSHOP_INFO, 'If you want to buy it, type /buy');
      $player->Send(COLOR_CARSHOP_INFO, 'Vehicle details:');
      $player->Send(COLOR_CARSHOP_INFO, "*  Gas tank capacity: {$capacity}lt");
      $player->Send(COLOR_CARSHOP_INFO, "*  Fuel usage: {$usage}lt / minute");
      $player->Send(COLOR_CARSHOP_INFO, "*  Trunk space: {$trunk} units");
      $vehicle->SetHealth(1000);
      return CALLBACK_BREAK;
    }
    return CALLBACK_OK;
  }
}
?>

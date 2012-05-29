<?
/**
 ** Constants for vehicle attributes
 **/
define ('VEHICLE_CAR',       0x00001);  /* Cars, with standard mods of NOS and hydraulics */
define ('VEHICLE_BOAT',      0x00002);  /* Boats */
define ('VEHICLE_PLANE',     0x00004);  /* Planes (including choppers) */
define ('VEHICLE_BIKE',      0x00008);  /* Bikes */
define ('VEHICLE_BICYCLE',   0x00010);  /* Bicycles, no need for license to drive them */
define ('VEHICLE_TRAIN',     0x00020);  /* Trains and train trailers */
define ('VEHICLE_TRUCK',     0x00040);  /* Trucks, allow to attach a trailer */
define ('VEHICLE_TRAILER',   0x00080);  /* Trailers */
define ('VEHICLE_OTHERS',    0x00100);  /* Others */
define ('VEHICLE_TYPES',     VEHICLE_CAR|VEHICLE_BOAT|VEHICLE_PLANE|VEHICLE_BIKE|VEHICLE_BICYCLE|VEHICLE_TRAIN|VEHICLE_TRUCK|VEHICLE_TRAILER|VEHICLE_OTHERS);
define ('VEHICLE_WINDOWS',   0x01000);
define ('VEHICLE_ROLLABLE',  0x02000|VEHICLE_WINDOWS);
define ('VEHICLE_ROLLABLEW', 0x02000);
define ('VEHICLE_ATTRIBS',   VEHICLE_WINDOWS|VEHICLE_ROLLABLE);
define ('VEHICLE_MODDABLE',  0x10000);  /* Car moddable with something more than just NOS and hydraulics */
define ('VEHICLE_LOWRIDER',  0x20000);  /* Special moddable vehicle */
define ('VEHICLE_ARCHANG',   0x40000);  /* Special moddable vehicle */
define ('VEHICLE_MODS',      VEHICLE_MODDABLE|VEHICLE_LOWRIDER|VEHICLE_ARCHANG);

define ('VEHICLE_FUELTIME',  1800); /* Time that a deposit of any car lasts before refilling it */

/* Vehicle types */ 
define ('VEHICLE_PLAYER',  0x001);
define ('VEHICLE_FACTION', 0x002);
define ('VEHICLE_SHOP',    0x003);
define ('VEHICLE_STATIC',  0x004);

define ('WINDOWS_ROLLED_UP',   0x001);
define ('WINDOWS_ROLLED_DOWN', 0x002);


require_once('vehicle.php');
require_once('shop.php');


/**
 ** class VehicleAttributes
 ** Defines the attributes of each vehicle model
 **/
class VehicleAttributes
{
  private $attributes = 0;
  private $trunkspace = 0;
  private $fuelusage = 0;
  private $fuelspace = 0;
  private $name = null;

  public function __construct($name = null, $trunkspace = 0, $fuelspace = 0, $attributes = 0)
  {
    $this->name = $name;
    $this->attributes = $attributes;
    $this->trunkspace = $trunkspace;
    $this->fuelusage = (double)($fuelspace / VEHICLE_FUELTIME);
    $this->fuelspace = $fuelspace;
  }

  public function GetName() { return $this->name; }
  public function GetType() { return $this->attributes & VEHICLE_TYPES; }
  public function GetAttribs() { return $this->attributes & VEHICLE_ATTRIBS; }
  public function GetMods() { return $this->attributes & VEHICLE_MODS; }
  public function GetTrunkspace() { return $this->trunkspace; }
  public function GetFuelusage() { return $this->fuelusage; }
  public function GetFuelspace() { return $this->fuelspace; }
}

/**
 ** class Vehicles
 ** The vehicles manager
 **/
class Vehicles
{
  private static $vehicles;
  private static $vehicle_attributes = array();
  private static $vehicle_names = array();

  public static function Init()
  {
    CommandHandler::Register('rolldown',      0, null, array('Vehicles', 'cmdRolldown'), '', 1);
    CommandHandler::Register('rollup',        0, null, array('Vehicles', 'cmdRollup'),   '', 1);
    CommandHandler::Register('lockwindows',   0, null, array('Vehicles', 'cmdLockwindows'), '', 1);
    CommandHandler::Register('unlockwindows', 0, null, array('Vehicles', 'cmdUnlockwindows'), '', 1);
    CommandHandler::Register('makecar',       1, null, array('Vehicles', 'cmdCreateVehicle'), '[VehicleID/VehicleName]', 1);

    Keybinds::Register(array(KEY_LOOK_LEFT, KEY_LOOK_LEFT), null, array('Vehicles', 'RolldownupKeybind'));

    Vehicles::$vehicles = array_fill(0, 700, null);

    echo ">>>>> Categorizing vehicle models ...\n";
    for ($i = 0; $i < 400; $i++)
      Vehicles::Describe($i, new VehicleAttributes(null));
    /*            model                       name     trunk size  deposit size              flags                     */
    Vehicles::Describe(400, new VehicleAttributes('Landstalker',  4000, 50, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(401, new VehicleAttributes('Bravura',      3000, 30, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(402, new VehicleAttributes('Buffalo',      2000, 50, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(403, new VehicleAttributes('Linerunner',      0, 60, VEHICLE_TRUCK|VEHICLE_ROLLABLE));
    Vehicles::Describe(404, new VehicleAttributes('Perenniel',    5000, 25, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(405, new VehicleAttributes('Sentinel',     5000, 40, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(406, new VehicleAttributes('Dumper',      22000, 90, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(407, new VehicleAttributes('Fire truck',      0, 50, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(408, new VehicleAttributes('Trashmaster',     0, 40, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(409, new VehicleAttributes('Stretch',      3000, 55, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(410, new VehicleAttributes('Manana',       3000, 30, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(411, new VehicleAttributes('Infernus',     3000, 85, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(412, new VehicleAttributes('Voodoo',       6000, 40, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(413, new VehicleAttributes('Pony',        12000, 45, VEHICLE_VAN|VEHICLE_ROLLABLE));
    Vehicles::Describe(414, new VehicleAttributes('Mule',        18000, 50, VEHICLE_VAN|VEHICLE_ROLLABLE));
    Vehicles::Describe(415, new VehicleAttributes('Cheetah',      2000, 85, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(416, new VehicleAttributes('Ambulance',       0, 25, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(417, new VehicleAttributes('Leviathan',       0,100, VEHICLE_PLANE|VEHICLE_WINDOWS));
    Vehicles::Describe(418, new VehicleAttributes('Moonbeam',     6000, 35, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(419, new VehicleAttributes('Esperanto',    4000, 40, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(420, new VehicleAttributes('Taxi',            0, 20, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(421, new VehicleAttributes('Washington',   4000, 45, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(422, new VehicleAttributes('Bobcat',       8000, 40, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(423, new VehicleAttributes('Mr Whoopee',      0, 30, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(424, new VehicleAttributes('BF Injection', 2000, 40, VEHICLE_OTHERS));
    Vehicles::Describe(425, new VehicleAttributes('Hunter',          0,120, VEHICLE_PLANE|VEHICLE_WINDOWS));
    Vehicles::Describe(426, new VehicleAttributes('Premier',      3000, 35, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(427, new VehicleAttributes('Enforcer',        0, 30, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(428, new VehicleAttributes('Securicar',   12000, 60, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(429, new VehicleAttributes('Banshee',      2000, 80, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(430, new VehicleAttributes('Predator',        0, 50, VEHICLE_BOAT));
    Vehicles::Describe(431, new VehicleAttributes('Bus',             0, 25, VEHICLE_OTHERS|VEHICLE_WINDOWS));
    Vehicles::Describe(432, new VehicleAttributes('Rhino',           0, 80, VEHICLE_OTHERS|VEHICLE_WINDOWS));
    Vehicles::Describe(433, new VehicleAttributes('Barracks',        0, 60, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(434, new VehicleAttributes('Hotknife',     2000, 50, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(435, new VehicleAttributes('Article1',    30000,  0, VEHICLE_TRAILER));
    Vehicles::Describe(436, new VehicleAttributes('Previon',      4000, 35, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(437, new VehicleAttributes('Coach',           0, 25, VEHICLE_OTHERS|VEHICLE_WINDOWS));
    Vehicles::Describe(438, new VehicleAttributes('Cabbie',          0, 25, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(439, new VehicleAttributes('Stallion',     2000, 70, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(440, new VehicleAttributes('Rumpo',       12000, 40, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(441, new VehicleAttributes('RC Bandit',       0,  0, VEHICLE_OTHERS));
    Vehicles::Describe(442, new VehicleAttributes('Romero',          0, 40, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(443, new VehicleAttributes('Packet',          0, 60, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(444, new VehicleAttributes('Monster',         0, 65, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(445, new VehicleAttributes('Admiral',      3000, 35, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(446, new VehicleAttributes('Squallo',     10000, 60, VEHICLE_BOAT));
    Vehicles::Describe(447, new VehicleAttributes('Sea sparrow',     0, 80, VEHICLE_PLANE|VEHICLE_WINDOWS));
    Vehicles::Describe(448, new VehicleAttributes('Pizza boy',       0, 15, VEHICLE_BIKE));
    Vehicles::Describe(449, new VehicleAttributes('Tram',            0,  0, VEHICLE_TRAIN|VEHICLE_WINDOWS));

    Vehicles::Describe(450, new VehicleAttributes('Article2',    30000 , 0, VEHICLE_TRAILER));
    Vehicles::Describe(451, new VehicleAttributes('Turismo',      2000, 85, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(452, new VehicleAttributes('Speeder',      4000, 60, VEHICLE_BOAT));
    Vehicles::Describe(453, new VehicleAttributes('Reefer',      12000, 50, VEHICLE_BOAT));
    Vehicles::Describe(454, new VehicleAttributes('Tropic',      15000, 50, VEHICLE_BOAT));
    Vehicles::Describe(455, new VehicleAttributes('Flatbed',     22000, 80, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(456, new VehicleAttributes('Yankee',      20000, 75, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(457, new VehicleAttributes('Caddy',           0, 15, VEHICLE_OTHERS));
    Vehicles::Describe(458, new VehicleAttributes('Solair',       4000, 35, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(459, new VehicleAttributes('Topfun',      12000, 45, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(460, new VehicleAttributes('Skimmer',         0, 40, VEHICLE_PLANE|VEHICLE_WINDOWS));
    Vehicles::Describe(461, new VehicleAttributes('PCJ-600',         0, 20, VEHICLE_BIKE));
    Vehicles::Describe(462, new VehicleAttributes('Faggio',          0, 15, VEHICLE_BIKE));
    Vehicles::Describe(463, new VehicleAttributes('Freeway',         0, 35, VEHICLE_BIKE));
    Vehicles::Describe(464, new VehicleAttributes('RC Baron',        0,  0, VEHICLE_OTHERS));
    Vehicles::Describe(465, new VehicleAttributes('RC Raider',       0,  0, VEHICLE_OTHERS));
    Vehicles::Describe(466, new VehicleAttributes('Glendale',     3000, 45, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(467, new VehicleAttributes('Oceanic',      3000, 47, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(468, new VehicleAttributes('Sanchez',         0, 15, VEHICLE_BIKE));
    Vehicles::Describe(469, new VehicleAttributes('Sparrow',         0, 35, VEHICLE_PLANE|VEHICLE_WINDOWS));
    Vehicles::Describe(470, new VehicleAttributes('Patriot',      2000, 50, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(471, new VehicleAttributes('Quad',            0, 25, VEHICLE_BIKE));
    Vehicles::Describe(472, new VehicleAttributes('Coastguard',   2000, 25, VEHICLE_BOAT));
    Vehicles::Describe(473, new VehicleAttributes('Dinghy',          0, 15, VEHICLE_BOAT));
    Vehicles::Describe(474, new VehicleAttributes('Hermes',       4000, 55, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(475, new VehicleAttributes('Sabre',        3000, 65, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(476, new VehicleAttributes('Rustler',         0, 75, VEHICLE_PLANE|VEHICLE_WINDOWS));
    Vehicles::Describe(477, new VehicleAttributes('ZR-350',       2000, 70, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(478, new VehicleAttributes('Walton',       8000, 50, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(479, new VehicleAttributes('Regina',       4000, 40, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(480, new VehicleAttributes('Comet',        2000, 65, VEHICLE_CAR));
    Vehicles::Describe(481, new VehicleAttributes('BMX',             0,  0, VEHICLE_BICYCLE));
    Vehicles::Describe(482, new VehicleAttributes('Burrito',     12000, 50, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(483, new VehicleAttributes('Camper',       6000, 40, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(484, new VehicleAttributes('Marquis',     18000, 25, VEHICLE_BOAT));
    Vehicles::Describe(485, new VehicleAttributes('Baggage',         0, 15, VEHICLE_OTHERS));
    Vehicles::Describe(486, new VehicleAttributes('Dozer',       18000, 60, VEHICLE_OTHERS));
    Vehicles::Describe(487, new VehicleAttributes('Maverick',        0, 90, VEHICLE_PLANE|VEHICLE_WINDOWS));
    Vehicles::Describe(488, new VehicleAttributes('News Maverick',   0, 90, VEHICLE_PLANE|VEHICLE_WINDOWS));
    Vehicles::Describe(489, new VehicleAttributes('Rancher',      6000, 55, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(490, new VehicleAttributes('FBI Rancher',     0, 55, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(491, new VehicleAttributes('Virgo',        3000, 35, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(492, new VehicleAttributes('Greenwood',    3000, 35, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(493, new VehicleAttributes('Jetmax',       5000, 60, VEHICLE_BOAT));
    Vehicles::Describe(494, new VehicleAttributes('Hotring C',       0, 80, VEHICLE_OTHERS));
    Vehicles::Describe(495, new VehicleAttributes('Sandking',     6000, 80, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(496, new VehicleAttributes('Blista',       3000, 25, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(497, new VehicleAttributes('Police Maverick', 0, 40, VEHICLE_PLANE|VEHICLE_WINDOWS));
    Vehicles::Describe(498, new VehicleAttributes('Boxville',    18000, 60, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(499, new VehicleAttributes('Benson',      19000, 60, VEHICLE_OTHERS|VEHICLE_ROLLABLE));

    Vehicles::Describe(500, new VehicleAttributes('Mesa',         2000, 40, VEHICLE_CAR));
    Vehicles::Describe(501, new VehicleAttributes('RC Goblin',       0,  0, VEHICLE_OTHERS));
    Vehicles::Describe(502, new VehicleAttributes('Hotring A',    4000, 80, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(503, new VehicleAttributes('Hotring B',    4000, 80, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(504, new VehicleAttributes('Bloodra',      5000, 70, VEHICLE_OTHERS));
    Vehicles::Describe(505, new VehicleAttributes('Rancher',      8000, 50, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(506, new VehicleAttributes('Super GT',     2000, 85, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(507, new VehicleAttributes('Elegant',      6000, 55, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(508, new VehicleAttributes('Camper',          0, 40, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(509, new VehicleAttributes('Bike',            0,  0, VEHICLE_BICYCLE));
    Vehicles::Describe(510, new VehicleAttributes('Mountain Bike',   0,  0, VEHICLE_BICYCLE));
    Vehicles::Describe(511, new VehicleAttributes('Beagle',          0, 80, VEHICLE_PLANE|VEHICLE_WINDOWS));
    Vehicles::Describe(512, new VehicleAttributes('Cropduster',      0, 75, VEHICLE_PLANE|VEHICLE_WINDOWS));
    Vehicles::Describe(513, new VehicleAttributes('Stunt plane',     0, 75, VEHICLE_PLANE|VEHICLE_WINDOWS));
    Vehicles::Describe(514, new VehicleAttributes('Petrol tanker',   0, 65, VEHICLE_TRUCK|VEHICLE_ROLLABLE));
    Vehicles::Describe(515, new VehicleAttributes('Road train',      0, 70, VEHICLE_TRUCK|VEHICLE_ROLLABLE));
    Vehicles::Describe(516, new VehicleAttributes('Nebula',       4000, 40, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(517, new VehicleAttributes('Majestic',     6000, 45, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(518, new VehicleAttributes('Buccaneer',    4000, 40, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(519, new VehicleAttributes('Shamal',          0,400, VEHICLE_PLANE|VEHICLE_WINDOWS));
    Vehicles::Describe(520, new VehicleAttributes('Hydra',           0,600, VEHICLE_PLANE|VEHICLE_WINDOWS));
    Vehicles::Describe(521, new VehicleAttributes('FCR 900',         0, 25, VEHICLE_BIKE));
    Vehicles::Describe(522, new VehicleAttributes('NRG 500',         0, 30, VEHICLE_BIKE));
    Vehicles::Describe(523, new VehicleAttributes('Cop bike',        0, 20, VEHICLE_BIKE));
    Vehicles::Describe(524, new VehicleAttributes('Cement truck',    0, 40, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(525, new VehicleAttributes('Tow truck',       0, 30, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(526, new VehicleAttributes('Fortune',      3000, 40, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(527, new VehicleAttributes('Cadrona',      3000, 45, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(528, new VehicleAttributes('FBI truck',       0, 40, VEHICLE_OTHERS|VEHICLE_WINDOWS));
    Vehicles::Describe(529, new VehicleAttributes('Willard',      4000, 30, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(530, new VehicleAttributes('Forklift',        0,  5, VEHICLE_OTHERS));
    Vehicles::Describe(531, new VehicleAttributes('Tractor',         0, 15, VEHICLE_OTHERS));
    Vehicles::Describe(532, new VehicleAttributes('Harvester',       0, 15, VEHICLE_OTHERS));
    Vehicles::Describe(533, new VehicleAttributes('Feltzer',      6000, 45, VEHICLE_CAR));
    Vehicles::Describe(534, new VehicleAttributes('Remington',    8000, 45, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_LOWRIDER));
    Vehicles::Describe(535, new VehicleAttributes('Slamvan',      8000, 50, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_LOWRIDER));
    Vehicles::Describe(536, new VehicleAttributes('Blade',        6000, 45, VEHICLE_CAR|VEHICLE_LOWRIDER));
    Vehicles::Describe(537, new VehicleAttributes('Freight',         0,  0, VEHICLE_TRAIN|VEHICLE_WINDOWS));
    Vehicles::Describe(538, new VehicleAttributes('Browstreak',      0,  0, VEHICLE_TRAIN|VEHICLE_WINDOWS));
    Vehicles::Describe(539, new VehicleAttributes('Vortex',          0, 30, VEHICLE_OTHERS));
    Vehicles::Describe(540, new VehicleAttributes('Vincent',      3000, 35, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(541, new VehicleAttributes('Bullet',       2000, 85, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(542, new VehicleAttributes('Clover',       4000, 45, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(543, new VehicleAttributes('Sadler',      10000, 50, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(544, new VehicleAttributes('Fire truck 2',    0, 50, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(545, new VehicleAttributes('Hustler',      4000, 25, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(546, new VehicleAttributes('Intruder',     3000, 40, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(547, new VehicleAttributes('Primo',        3000, 40, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(548, new VehicleAttributes('Cargobob',        0, 90, VEHICLE_PLANE|VEHICLE_WINDOWS));
    Vehicles::Describe(549, new VehicleAttributes('Tampa',        3000, 65, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));

    Vehicles::Describe(550, new VehicleAttributes('Sunrise',      3000,   45, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(551, new VehicleAttributes('Merit',        3000,   45, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(552, new VehicleAttributes('Utility Van',  3000,   60, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(553, new VehicleAttributes('Nevada',          0,  200, VEHICLE_PLANE|VEHICLE_WINDOWS));
    Vehicles::Describe(554, new VehicleAttributes('Yosemite',     8000,   65, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(555, new VehicleAttributes('Windsor',      2000,   55, VEHICLE_CAR));
    Vehicles::Describe(556, new VehicleAttributes('Monster A',   12000,   90, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(557, new VehicleAttributes('Monster B',   12000,   90, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(558, new VehicleAttributes('Uranus',       3000,   65, VEHICLE_CAR|VEHICLE_ARCHANG|VEHICLE_ROLLABLE));
    Vehicles::Describe(559, new VehicleAttributes('Jester',       3000,   70, VEHICLE_CAR|VEHICLE_ARCHANG|VEHICLE_ROLLABLE));
    Vehicles::Describe(560, new VehicleAttributes('Sultan',       3000,   70, VEHICLE_CAR|VEHICLE_ARCHANG|VEHICLE_ROLLABLE));
    Vehicles::Describe(561, new VehicleAttributes('Stratum',      4000,   45, VEHICLE_CAR|VEHICLE_ARCHANG|VEHICLE_ROLLABLE));
    Vehicles::Describe(562, new VehicleAttributes('Elegy',        3000,   75, VEHICLE_CAR|VEHICLE_ARCHANG|VEHICLE_ROLLABLE));
    Vehicles::Describe(563, new VehicleAttributes('Raindance',       0,  120, VEHICLE_PLANE|VEHICLE_WINDOWS));
    Vehicles::Describe(564, new VehicleAttributes('RC Tiger',        0,    0, VEHICLE_OTHERS));//special
    Vehicles::Describe(565, new VehicleAttributes('Flash',        4000,   40, VEHICLE_CAR|VEHICLE_ARCHANG|VEHICLE_ROLLABLE));
    Vehicles::Describe(566, new VehicleAttributes('Tahoma',       5000,   60, VEHICLE_CAR|VEHICLE_LOWRIDER|VEHICLE_ROLLABLE));
    Vehicles::Describe(567, new VehicleAttributes('Savanna',      5000,   60, VEHICLE_CAR|VEHICLE_LOWRIDER|VEHICLE_ROLLABLE));
    Vehicles::Describe(568, new VehicleAttributes('Bandito',         0,   25, VEHICLE_OTHERS));
    Vehicles::Describe(569, new VehicleAttributes('Freight Flat',45000,    0, VEHICLE_TRAILER));
    Vehicles::Describe(570, new VehicleAttributes('Streak',          0,    0, VEHICLE_TRAILER|VEHICLE_WINDOWS));
    Vehicles::Describe(571, new VehicleAttributes('Kart',            0,   15, VEHICLE_OTHERS));
    Vehicles::Describe(572, new VehicleAttributes('Mower',           0,   70, VEHICLE_OTHERS));
    Vehicles::Describe(573, new VehicleAttributes('Dune',        15000,   50, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(574, new VehicleAttributes('Sweeper',         0,   35, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(575, new VehicleAttributes('Broadway',     3000,   55, VEHICLE_CAR|VEHICLE_LOWRIDER|VEHICLE_ROLLABLE));
    Vehicles::Describe(576, new VehicleAttributes('Tornado',      3000,   55, VEHICLE_CAR|VEHICLE_LOWRIDER|VEHICLE_ROLLABLE));
    Vehicles::Describe(577, new VehicleAttributes('AT 400',           0, 1000, VEHICLE_PLANE|VEHICLE_WINDOWS));
    Vehicles::Describe(578, new VehicleAttributes('DFT-30',      18000,   65, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(579, new VehicleAttributes('Huntley',      4000,   60, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(580, new VehicleAttributes('Stafford',     3000,   50, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(581, new VehicleAttributes('BF-400',          0,   30, VEHICLE_BIKE));
    Vehicles::Describe(582, new VehicleAttributes('Newsvan',     11000,   48, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(583, new VehicleAttributes('Tug',             0,   25, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(584, new VehicleAttributes('Petrol',      30000,    0, VEHICLE_TRAILER));
    Vehicles::Describe(585, new VehicleAttributes('Emperor',      3000,   35, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(586, new VehicleAttributes('Wayfarer',        0,   25, VEHICLE_BIKE));
    Vehicles::Describe(587, new VehicleAttributes('Euros',        2000,   75, VEHICLE_CAR|VEHICLE_ARCHANG|VEHICLE_ROLLABLE));
    Vehicles::Describe(588, new VehicleAttributes('Hotdog',          0,   40, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(589, new VehicleAttributes('Club',         3000,   35, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(590, new VehicleAttributes('Freight Box', 48000,    0, VEHICLE_TRAILER));
    Vehicles::Describe(591, new VehicleAttributes('Article 3',   30000,    0, VEHICLE_TRAILER));
    Vehicles::Describe(592, new VehicleAttributes('Andromada',       0,  800, VEHICLE_PLANE|VEHICLE_WINDOWS));
    Vehicles::Describe(593, new VehicleAttributes('Dodo',            0,   90, VEHICLE_PLANE|VEHICLE_WINDOWS));
    Vehicles::Describe(594, new VehicleAttributes('RC Cam',          0,    0, VEHICLE_OTHERS));//special
    Vehicles::Describe(595, new VehicleAttributes('Launch',       8000,   60, VEHICLE_BOAT));
    Vehicles::Describe(596, new VehicleAttributes('Police Car',   3000,   50, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(597, new VehicleAttributes('Police Car',   3000,   50, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(598, new VehicleAttributes('Police Car',   3000,   50, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(599, new VehicleAttributes('Police Ranger',6000,   60, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(600, new VehicleAttributes('Picador',      8000,   55, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(601, new VehicleAttributes('S.W.A.T.',        0,   60, VEHICLE_OTHERS|VEHICLE_WINDOWS));
    Vehicles::Describe(602, new VehicleAttributes('Alpha',        3000,   65, VEHICLE_CAR|VEHICLE_ROLLABLE));
    Vehicles::Describe(603, new VehicleAttributes('Phoenix',      3000,   70, VEHICLE_CAR|VEHICLE_ROLLABLE|VEHICLE_MODDABLE));
    Vehicles::Describe(604, new VehicleAttributes('Glendale S',   3000,   45, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(605, new VehicleAttributes('Sadler S',     8000,   55, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(606, new VehicleAttributes('Baggage A',    8000,    0, VEHICLE_TRAILER));
    Vehicles::Describe(607, new VehicleAttributes('Baggage B',    6000,    0, VEHICLE_TRAILER));
    Vehicles::Describe(608, new VehicleAttributes('Tug Stairs',      0,    0, VEHICLE_TRAILER));
    Vehicles::Describe(609, new VehicleAttributes('Boxburg',     18000,   65, VEHICLE_OTHERS|VEHICLE_ROLLABLE));
    Vehicles::Describe(610, new VehicleAttributes('Farm',            0,    0, VEHICLE_TRAILER));
    Vehicles::Describe(611, new VehicleAttributes('Utility',         0,    0, VEHICLE_TRAILER));
    /* Now it's 01:53 and I only want to say: "Fuck you Ryden!" -- Crazy <-- quote --- red */

    VehicleShop::Init();
  }

  private static function Describe($modelid, VehicleAttributes $vehicle_attribs)
  {
    Vehicles::$vehicle_attributes[(int)$modelid] = $vehicle_attribs;
    Vehicles::$vehicle_names[strtolower($vehicle_attribs->GetName())] = $modelid;
  }

  public static function FindModelidByName($name_)
  {
    $name = strtolower($name_);
    if (isset(Vehicles::$vehicle_names[$name]))
    {
      return Vehicles::$vehicle_names[$name];
    }
    return -1;
  }

  public static function IsValidModelid($id)
  { /* me coughs */
    if ($id >= 400 && $id <= 611)
      return true;
    return false;
  }

  public static function Add(Vehicle $vehicle)
  {
    Vehicles::$vehicles[$vehicle->ID()] = $vehicle;
  }

  public static function FindByID($id)
  {
    if (isset(Vehicles::$vehicles[$id]) && Vehicles::$vehicles[$id] != null)
      return Vehicles::$vehicles[$id];
    return null;
  }

  public static function GetAttributes($modelid)
  {
    return Vehicles::$vehicle_attributes[(int)$modelid];
  }


  /**
   ** Keybinds
   **/
  public static function RolldownupKeybind(Player $player)
  {
    $vehicle = $player->GetVehicle();

    if ($vehicle != null)
    {
      if ($vehicle->Windows() == WINDOWS_ROLLED_DOWN)
        Vehicles::cmdRollup($player, 0, array());
      else
        Vehicles::cmdRolldown($player, 0, array());
      return KEYBIND_BREAK;
    }
    return KEYBIND_OK;
  }

  /**
   ** Commands
   **/
  public static function cmdRolldown(Player $player, $numparams, $params)
  {
    $vehicle = $player->GetVehicle();

    if ($vehicle != null)
    {
      if ($vehicle->LockedWindows() && !$player->IsDriver())
        return COMMAND_OK;

      if ($vehicle->HasRollableWindows() && $vehicle->Windows() != WINDOWS_ROLLED_DOWN)
      {
        $vehicle->SetWindows(WINDOWS_ROLLED_DOWN);
        Messages::SendNear($player, COLOR_ROLLING_WINDOWS, "{$player->name} is rolling down the vehicle windows");
      }
    }
    return COMMAND_OK;
  }

  public static function cmdRollup(Player $player, $numparams, $params)
  {
    $vehicle = $player->GetVehicle();

    if ($vehicle != null)
    {
      if ($vehicle->LockedWindows() && !$player->IsDriver())
        return COMMAND_OK;

      if ($vehicle->HasRollableWindows() && $vehicle->Windows() != WINDOWS_ROLLED_UP)
      {
        $vehicle->SetWindows(WINDOWS_ROLLED_UP);
        Messages::SendNear($player, COLOR_ROLLING_WINDOWS, "{$player->name} is rolling up the vehicle windows");
      }
    }
    return COMMAND_OK;
  }

  public static function cmdLockwindows(Player $player, $numparams, $params)
  {
    $vehicle = $player->GetVehicle();

    if ($player->IsDriver() && $vehicle != null && $vehicle->HasRollableWindows())
    {
      if (!$vehicle->LockedWindows())
      {
        $vehicle->LockWindows(true);
        Messages::SendNear($player, COLOR_LOCKING_WINDOWS, "{$player->name} has locked the vehicle windows");
      }
    }

    return COMMAND_OK;
  }

  public static function cmdUnlockwindows(Player $player, $numparams, $params)
  {
    $vehicle = $player->GetVehicle();

    if ($player->IsDriver() && $vehicle != null && $vehicle->HasRollableWindows())
    {
      if ($vehicle->LockedWindows())
      {
        $vehicle->LockWindows(false);
        Messages::SendNear($player, COLOR_LOCKING_WINDOWS, "{$player->name} has unlocked the vehicle windows");
      }
    }

    return COMMAND_OK;
  }

  public static function cmdCreateVehicle(Player $player, $numparams, $params)
  {
    $vehicle = implode(' ', array_slice($params, 1));
    $model = -1;

    if (!ctype_digit($vehicle))
      $model = Vehicles::FindModelidByName($vehicle);
    else if (Vehicles::IsValidModelid($vehicle))
      $model = (int)$vehicle;
    else
      return COMMAND_OK;

    if ($model != -1)
    {
      $pos = clone $player->Position();
      $pos->x++;
      $pos->y++;
      $pos->z++;
      new Vehicle(-1, VEHICLE_STATIC, -1, $model, array(1, 0), $pos, 100, 1.1);
    }
    else
      $player->Send(COLOR_ERROR, '.:: Enter a valid vehicle model ID or name ::.');
    return COMMAND_OK;
  }

}
?>

<?

class Animation
{
  public $lib;
  public $anim;
  public $loop;
  public $movex;
  public $movey;
  public $continue;
  public $time;
  public $forced;
  public $stop;
  public $child;
  public $steptime;
  public $stop_cbk;
  public $stop_data;

  public function __construct($lib, $anim, $loop, $movex, $movey, $continue, $time, $forced,
                              $steptime = -1, Animation $stop = null, Animation $child = null,
                              $stop_cbk = null, $stop_data = null)
  {
    $this->lib       = $lib;
    $this->anim      = $anim;
    $this->loop      = $loop;
    $this->movex     = $movex;
    $this->movey     = $movey;
    $this->continue  = $continue;
    $this->time      = $time;
    $this->forced    = $forced;
    $this->stop      = $stop;
    $this->child     = $child;
    $this->steptime  = $steptime;
    $this->stop_cbk  = $stop_cbk;
    $this->stop_data = $stop_data;
  }
}

class Animations
{
  private static $stdanims = array();
  private static $stopping_draw;
  private static $death_animation;
  private static $seat_animation;
  private static $stalk_animation;

  public static function Init()
  {
    /* Register callbacks */
    Callbacks::Instance()->Register(cOnPlayerKeyStateChange, null, array('Animations', 'OnPlayerKeyStateChange'));
    Callbacks::Instance()->Register(cOnPlayerText, null, array('Animations', 'OnPlayerText'), -10);

    /* Register commands */
    CommandHandler::Register('stopanim', 0, null, array('Animations', 'cmdStopanim'), '', 1);

    /*                         lib    anim              loop    movex   movey   continue  time  forced */
    $seat_up   = new Animation('PED', 'seat_up',        false,  false,  false,  false,    -1,   false);
    $seat_idle = new Animation('PED', 'seat_idle',      true,   false,  false,  false,    -1,   false, -1,   $seat_up);
    $seat_down = new Animation('PED', 'seat_down',      false,  false,  false,  false,    1350, false, 1200, $seat_up, $seat_idle);
    Animations::Register('seat', $seat_down);
    Animations::$seat_animation = $seat_idle;
    Animations::$stalk_animation = new Animation('MISC', 'seat_talk_01', false, false, false, false, 1800, false, 1600, $seat_up, $seat_idle);

    $drunkstop = new Animation('PED', 'walk_drunk',     true,   true,   true,   false,  100,  false);
    $drunk     = new Animation('PED', 'walk_drunk',     true,   true,   true,   true,     1,  false, -1, $drunkstop);
    Animations::Register('drunk', $drunk);

    $fdrunkstop = new Animation('FOOD', 'eat_vomit_p',   true,   true,   true,   false, -1, true, 7200);
    $fdrunk     = new Animation('PED',  'walk_drunk',    true,   true,   true,   true,  1,  true, 5000, null, $fdrunkstop);
    Animations::Register('fdrunk', $fdrunk);

    $lay_up    = new Animation('SUNBATHE', 'lay_bac_out',false, false, false, false, -1, false);
    $lay_idle  = new Animation('BEACH',    'bather',     true,  false, false, false, -1, false, -1, $lay_up);
    $lay_down  = new Animation('SUNBATHE', 'lay_bac_in', false, false, false, false, 2100, false, 2000, $lay_up, $lay_idle);
    Animations::Register('lay', $lay_down);


    /* Create the textdraw for stopping animations */
    Animations::$stopping_draw = TextDrawCreate(214.000000,421.000000,'Use ~r~~k~~PED_SPRINT~~w~ to stop the animation');
    TextDrawUseBox(Animations::$stopping_draw, 1);
    TextDrawBoxColor(Animations::$stopping_draw, 0x00000066);
    TextDrawTextSize(Animations::$stopping_draw, 460.000000, 0.000000);
    TextDrawAlignment(Animations::$stopping_draw, 0);
    TextDrawBackgroundColor(Animations::$stopping_draw, 0x000000ff);
    TextDrawFont(Animations::$stopping_draw, 1);
    TextDrawLetterSize(Animations::$stopping_draw, 0.399999, 1.300000);
    TextDrawColor(Animations::$stopping_draw, 0xffffffff);
    TextDrawSetOutline(Animations::$stopping_draw, 1);
    TextDrawSetProportional(Animations::$stopping_draw, 1);

    /* Create global animations */
    Animations::$death_animation = new Animation('WUZI', 'CS_Dead_Guy', true, false, false, false, -1, true);
  }

  public static function Register($name, Animation $animation)
  {
    Animations::$stdanims[strtolower($name)] = $animation;
    CommandHandler::Register($name, 0, null, array('Animations', 'cmdStdanims'), '', 1, -1000);
  }

  public static function cmdStdanims(Player $player, $numparams, $params)
  {
    if ($player->GetState() == PLAYER_STATE_ONFOOT)
    {
      if (isset(Animations::$stdanims[strtolower($params[0])]))
        $player->Animate(Animations::$stdanims[strtolower($params[0])]);
    }
    return COMMAND_OK;
  }

  public static function GetStoppingDraw()
  {
    return Animations::$stopping_draw;
  }

  public static function GetDeathAnim()
  {
    return Animations::$death_animation;
  }


  /**
   ** Commands
   **/
  public static function cmdStopanim(Player $player, $numparams, $params)
  {
    $player->StopAnimation();
  }


  /**
   ** Callbacks
   **/
  public static function OnPlayerKeyStateChange(Player $player, $newkeys, $oldkeys)
  {
    if ($newkeys & KEY_SPRINT)
      $player->StopAnimation();
    return CALLBACK_OK;
  }

  public static function OnPlayerText(Player $player, $text)
  {
    if ($player->GetAnimation() == Animations::$seat_animation)
    {
      $player->ClearAnimations(false);
      $player->Animate(Animations::$stalk_animation);
    }
    return CALLBACK_OK;
  }
}

?>

<?
class Gym
{
  private static $anims = array();
  private static $machines = array();
  private static $draws;
  private static $players_key;
  private static $players_gained;
  private static $keys = array();
  private static $keystr = array();

  public static function Init()
  {
    /* Register commands and callbacks */
    CommandHandler::Register('exercise', 0, null, array('Gym', 'cmdExercise'), '', 1);
    CommandHandler::Register('stopexercise', 0, null, array('Gym', 'cmdStopexercise'), '', 1);
    Callbacks::Instance()->Register(cOnPlayerDisconnect, null, array('Gym', 'OnPlayerDisconnect'));
    Callbacks::Instance()->Register(cOnPlayerKeyStateChange, null, array('Gym', 'OnPlayerKeyStateChange'));
    Callbacks::Instance()->Register(cOnPlayerSpawn, null, array('Gym', 'OnPlayerSpawn'));

    /* Create the animations */
    $benchpress_getoff    = new Animation('benchpress', 'gym_bp_getoff', false, false, false, false, 8100, true);
    $benchpress_exercise  = new Animation('benchpress', 'gym_bp_up_smooth', true, false, false, false, -1, true, -1,
                                          $benchpress_getoff);
    $benchpress_anim      = new Animation('benchpress', 'gym_bp_geton', false, false, false, false, 5200, true, 5100,
                                          $benchpress_getoff, $benchpress_exercise, array('Gym', 'startExercise'));
    Gym::$anims['benchpress'] = $benchpress_anim;

    $bike_getoff   = new Animation('gymnasium', 'gym_bike_getoff', false, false, false, false, 1350, true);
    $bike_exercise = new Animation('gymnasium', 'gym_bike_pedal', true, false, false, false, -1, true, -1,
                                   $bike_getoff);
    $bike_anim     = new Animation('gymnasium', 'gym_bike_geton', false, false, false, false, 1500, true, 1400,
                                   $bike_getoff, $bike_exercise, array('Gym', 'startExercise'));
    Gym::$anims['bike'] = $bike_anim;

    $tread_getoff   = new Animation('gymnasium', 'gym_tread_getoff', false, false, false, false, 3000, true);
    $tread_exercise = new Animation('gymnasium', 'gym_tread_jog', true, false, false, false, -1, true, -1,
                                    $tread_getoff);
    $tread_anim     = new Animation('gymnasium', 'gym_tread_geton', false, false, false, false, 2250, true, 2200,
                                    $tread_getoff, $tread_exercise, array('Gym', 'startExercise'));
    Gym::$anims['tread'] = $tread_anim;

    /* Make the list of keys */
    Gym::RegisterKey(KEY_ACTION, 'TAB');
    Gym::RegisterKey(KEY_CROUCH, 'CROUCH');
    Gym::RegisterKey(KEY_SPRINT, 'SPRINT');
    Gym::RegisterKey(KEY_JUMP,   'JUMP');
    Gym::RegisterKey(KEY_WALK,   'WALK');
    Gym::RegisterKey(KEY_SECONDARY_ATTACK, 'ENTER V.');

    /* Initialize arrays */
    Gym::$players_key = array_fill(0, MAX_PLAYERS, -1);
    Gym::$players_gained = array_fill(0, MAX_PLAYERS, -1);
    Gym::$draws = array_fill(0, MAX_PLAYERS, null);

    /* Register all exercise machines */
    echo ">>>>> Registering all exercise machines locations ...\n";
    /* Madd doggs */
    Gym::Register('benchpress', new Position(1233.6235, -769.3942, 1084.0000, 180), new Position(1233.6235, -767.3942, 1086));
    Gym::Register('bike', new Position(1242.4896, -767.7238, 1084.0121, 90), new Position(1239, -766.7238, 1086));
    Gym::Register('tread', new Position(1226.5122, -762.5506, 1084.0055, 180), new Position(1226.5352, -768.6758, 1087));
  }

  public static function Register($type, Position $position, Position $camera)
  {
    Gym::$machines[] = new ExerciseMachine($type, $position, $camera);
  }

  public static function RegisterKey($key, $name)
  {
    Gym::$keys[] = $key;
    Gym::$keystr[] = $name;
  }

  public static function CreateDraw()
  {
    $draw = TextDrawCreate(465.000000, 385.000000, '...');
    TextDrawUseBox($draw, 1);
    TextDrawBoxColor($draw, 0x00000099);
    TextDrawTextSize($draw, 633.000000, 43.000000);
    TextDrawAlignment($draw, 0);
    TextDrawBackgroundColor($draw, 0x000000ff);
    TextDrawFont($draw, 3);
    TextDrawLetterSize($draw, 0.599999, 1.200000);
    TextDrawColor($draw, 0xffffffff);
    TextDrawSetOutline($draw, 1);
    TextDrawSetProportional($draw, 1);
    return $draw;
  }

  public static function ExerciseStep(Player $player, $correct_key)
  {
    if ($correct_key)
      Gym::$players_gained[$player->id]++;
    else if (Gym::$players_gained[$player->id] > 0)
      Gym::$players_gained[$player->id]--;

    do
    {
      $oldkey = Gym::$players_key[$player->id];
      $key = rand(0, count(Gym::$keys) - 1);
      Gym::$players_key[$player->id] = Gym::$keys[$key];
    } while ($oldkey == Gym::$players_key[$player->id]);

    $str = '';
    if (!$correct_key)
      $str = '~r~Wrong key!~n~';
    $str .= '~w~Press ~r~' . Gym::$keystr[$key] . '~n~~w~-~n~Strength~n~gained:~g~ ' . (int)(Gym::$players_gained[$player->id] / 1);
    TextDrawSetString(Gym::$draws[$player->id], $str);
  }

  private static function UnregisterPlayer(Player $player)
  {
    if (Gym::$players_key[$player->id] != -1)
    {
      Gym::$players_key[$player->id] = -1;
      Gym::$players_gained[$player->id] = -1;
      if (Gym::$draws[$player->id] !== null)
      {
        TextDrawDestroy(Gym::$draws[$player->id]);
        Gym::$draws[$player->id] = null;
      }

      foreach (Gym::$machines as $machine)
      {
        if ($machine->player && $machine->player->id == $player->id)
        {
          $machine->player = null;
          break;
        }
      }
    }
  }

  /*+
   ** Callbacks
   **/
  public static function OnPlayerDisconnect(Player $player, $reason)
  {
    Gym::UnregisterPlayer($player);
    return CALLBACK_OK;
  }

  public static function OnPlayerSpawn(Player $player)
  {
    Gym::UnregisterPlayer($player);
    return CALLBACK_OK;
  }

  public static function OnPlayerKeyStateChange(Player $player, $newkeys, $oldkeys)
  {
    if ($oldkeys == 0 && Gym::$players_key[$player->id] != -1)
    {
      if (Gym::$players_key[$player->id] == $newkeys)
        Gym::ExerciseStep($player, true);
      else
        Gym::ExerciseStep($player, false);
    }

    return CALLBACK_OK;
  }


  /**
   ** Commands
   **/
  public static function cmdExercise(Player $player, $numparams, $params)
  {
    foreach (Gym::$machines as $machine)
    {
      if (isset(Gym::$anims[$machine->type]) && $machine->position->IsInSphere($player->Position(), 1.5))
      {
        if ($machine->player)
        {
          $player->Send(COLOR_GYM_MACHINE_USED, '[ERROR] This exercise machine is already being used');
          return COMMAND_OK;
        }
        $player->SetPosition($machine->position);
        $player->SetCamera($machine->camera);
        $player->CameraLookAt($machine->position);
        $player->Animate(Gym::$anims[$machine->type]);
        $machine->player = $player;
      }
    }
    return COMMAND_OK;
  }

  public static function cmdStopexercise(Player $player, $numparams, $params)
  {
    if (Gym::$players_key[$player->id] != -1)
    {
      SetCameraBehindPlayer($player->id);
      $player->ClearAnimations();
      $player->SetStrength($player->GetStrength() + (int)(Gym::$players_gained[$player->id] / 1));
      $player->Send(COLOR_GYM_EARNT, '[GYM] You have earned a total of ' . (int)(Gym::$players_gained[$player->id] / 1) . ' strength points');

      Gym::UnregisterPlayer($player);
    }
    return COMMAND_OK;
  }


  /**
   ** Animation callbacks
   **/
  public static function startExercise(Player $player)
  {
    $draw = Gym::CreateDraw();
    TextDrawShowForPlayer($player->id, $draw);
    Gym::$draws[$player->id] = $draw;
    Gym::ExerciseStep($player, true);
  }
}

class ExerciseMachine
{
  public $type;
  public $position;
  public $camera;
  public $player;

  public function __construct($type, Position $position, Position $camera)
  {
    $this->type = $type;
    $this->position = $position;
    $this->camera = $camera;
    $this->player = null;
  }
}
?>

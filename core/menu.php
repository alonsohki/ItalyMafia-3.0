<?
/**
 ** Constants for menus
 **/
define ('ROW_NOACTION',   0x0000);
define ('ROW_DISABLED',   0x0001);
define ('ROW_ACTION',     0x0002);
define ('ROW_NAVIGATE',   0x0004);

class Menu
{
  /**
   ** Static methods
   **/
  public static function Init()
  {
    Callbacks::Instance()->Register(cOnPlayerExitedMenu, null, array('Menu', 'OnPlayerExitedMenu'));
    Callbacks::Instance()->Register(cOnPlayerSelectedMenuRow, null, array('Menu', 'OnPlayerSelectedMenuRow'));
  }

  public static function OnPlayerExitedMenu(Player $player)
  {
    if ($menu = $player->GetMenu())
      $player->SetMenu($menu->parent);

    return CALLBACK_OK;
  }

  public static function OnPlayerSelectedMenuRow(Player $player, $row)
  {
    if ($menu = $player->GetMenu())
    {
      if (isset($menu->rows[$row]))
      {
        switch ($menu->rows[$row])
        {
          case ROW_ACTION:
            $player->SetMenu(null);
            call_user_func($menu->actions[$row], $player, $menu->rownames[$row]);
            break;
          case ROW_NOACTION:
            $player->SetMenu(null);
            break;
          case ROW_NAVIGATE:
            $player->SetMenu($menu->actions[$row], $player);
            break;
        }
      }
    }
    return CALLBACK_OK;
  }



  /**
   ** Non static methods
   **/
  private $id;
  private $title;
  private $parent;
  private $entercallback;
  private $exitcallback;
  private $rows = array();
  private $rownames = array();
  private $actions = array();

  public function __construct(Menu $parent = null, $title, $entercallback, $exitcallback, $columns, Position $pos, $width1, $width2 = 0.0)
  {
    $this->id = CreateMenu($title, $columns, $pos->x, $pos->y, $width1, $width2);
    $this->title = $title;
    $this->entercallback = $entercallback;
    $this->exitcallback = $exitcallback;
    $this->parent = $parent;
  }

  public function Add($text, $column, $flags = ROW_NOACTION, $action = null)
  {
    $row = AddMenuItem($this->id, $column, $text);
    if ($column == 0)
    {
      if ($flags & ROW_DISABLED)
        DisableMenuRow($this->id, $row);
      if ($flags & ROW_NAVIGATE && !($action instanceof Menu))
      {
        echo "WARNING! Adding a navigation row '{$text}' for menu '{$this->title}' referencing to a non menu. Setting to ROW_NOACTION";
        $flags = ROW_NOACTION;
      }
      if ($flags & ROW_ACTION && $action == null)
      {
        echo "WARNING! Adding an action row '{$text}' for menu '{$this->title}' with a null action. Setting to ROW_NOACTION";
        $flags = ROW_NOACTION;
      }
      $this->rows[$row] = $flags & ~ROW_DISABLED;
      $this->rownames[$row] = $text;
      $this->actions[$row] = $action;
    }
  }

  public function SetHeader($text, $column = 0)
  {
    SetMenuColumnHeader($this->id, $column, $text);
  }

  public function ShowForPlayer(Player $player)
  {
    if (!($menu = $player->GetMenu()) || $menu->id != $this->id)
    {
      if ($this->entercallback)
        call_user_func($this->entercallback, $player);
      ShowMenuForPlayer($this->id, $player->id);
    }
  }

  public function HideForPlayer(Player $player)
  {
    if (($menu = $player->GetMenu()) && $menu->id == $this->id)
    {
      if ($this->exitcallback)
        call_user_func($this->exitcallback, $player);
      HideMenuForPlayer($this->id, $player->id);
    }
  }
}
?>

<?
class Bitmap
{
  private $data = array();

  public function __construct($size = -1)
  {
    if ($size != -1)
    {
      $this->data = array_fill(0, (int)($size / 32) + 1, 0);
    }
  }

  public function SetArrayData($data)
  {
    $this->data = clone $data;
  }

  public function SetHexData($data)
  {
    $len = strlen($data);
    
    for ($i = 0; $i < $len; $i += 8)
    {
      $this->data[] = hexdec(substr($data, $i, 8));
    }
  }

  public function GetArrayData()
  {
    return $this->data;
  }

  public function GetHexData()
  {
    $str = '';

    foreach ($this->data as $block)
    {
      $str .= sprintf('%08x', $block);
    }

    return $str;
  }

  public function Set($pos, $value)
  {
    $block = (int)($pos / 32);
    $offset = $pos % 32;

    if ($value)
      $this->data[$block] |= 1 << $offset;
    else
      $this->data[$block] &= ~(1 << $offset);
  }

  public function Get($pos)
  {
    $block = (int)($pos / 32);
    $offset = $pos % 32;

    return ($this->data[$block] >> $offset & 0x1);
  }
}
?>

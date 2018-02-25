<?php

namespace youconix\core\helpers;

class OnOff extends \youconix\core\helpers\Helper
{

  protected $a_values = [0 => '0', 1 => '1'];
  protected $bo_default = false;
  protected $s_name = 'on_off';

  /**
   * Sets the enabled and disabled values
   *
   * @param string $selected
   * @param string $notSelected
   */
  public function setValues($selected, $notSelected)
  {
    $this->a_values[0] = $selected;
    $this->a_values[1] = $notSelected;
  }

  /**
   * Sets the slider as selected
   *
   * @param boolean $bo_selected
   */
  public function setSelected($bo_selected)
  {
    $this->bo_default = $bo_selected;
  }

  /**
   * Sets the field name
   *
   * @param string $s_name
   */
  public function setName($s_name)
  {
    $this->s_name = $s_name;
  }

  /**
   * Adds the javascript and css files
   * 
   * @param \OutputInterface $output
   */
  public function addHead(\OutputInterface $output)
  {
    $output->append('head', '<script src="/js/widgets/on_off.js" type="text/javascript"></script>');
    $output->append('head', '<link rel="stylesheet" href="/styles/shared/css/widgets/on_off.css">');
  }

  /**
   * Generates the slider
   *
   * @return string
   */
  public function generate()
  {
    $s_active = '';
    $s_value = $this->a_values[0];
    if ($this->bo_default) {
      $s_active = ' on_off_active';
      $s_value = $this->a_values[1];
    }

    return '<div class="on_off' . $s_active . '" id="' . $this->s_name . '_slider" data-off="' . $this->a_values[0] . '" data-on="' . $this->a_values[1] . '">
      </div>
      <input type="hidden" name="' . $this->s_name . '" value="' . $s_value . '">';
  }
}

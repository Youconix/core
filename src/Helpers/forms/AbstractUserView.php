<?php

namespace youconix\core\helpers\forms;

abstract class AbstractUserView {
  /**
   * 
   * @var \OutputInterface
   */
  protected $template;
  
  /**
   *
   * @var \youconix\core\entities\User
   */
  protected $user;
  
  /**
   *
   * @var \youconix\core\Input
   */
  protected $input;
  
  /**
   *
   * @var \LanguageInterface
   */
  protected $language;

  /**
   *
   * @var \youconix\core\repositories\Groups
   */
  protected $groups;
  
  /**
   *
   * @var \youconix\core\helpers\Localisation
   */
  protected $localization;
  
  protected $a_formData = [];  
  
  /**
   * Sets the user
   *
   * @param \youconix\core\Input $input
   * @param \youconix\core\entities\User $user
   */
  public function init(\youconix\core\Input $input, \youconix\core\entities\User $user)
  {
    $this->input = $input;
    $this->user = $user;
  }
  
  protected function setData($s_name, $value)
  {
    if (!array_key_exists($s_name, $this->a_formData)) {
      $this->a_formData[$s_name] = $value;
    } else if (is_array($this->a_formData[$s_name])) {
      $this->a_formData[$s_name][] = $value;
    } else {
      $this->a_formData[$s_name] = [$this->a_formData[$s_name]];
      $this->a_formData[$s_name][] = $value;
    }
  }
  
  public function generate($s_template)
  {
    $template = $this->createView($s_template, $this->a_formData);

    return $template;
  }

  /**
   * Loads the template
   *
   * @param string $s_view
   * @param array $a_data
   * @return \OutputInterface
   */
  protected function createView($s_view, $a_data)
  {
    $s_templateDir = 'admin';

    $this->template->load('admin/modules/general/users/' . $s_view,
			  $s_templateDir);
    $this->template->setArray($a_data);

    return $this->template;
  }
}
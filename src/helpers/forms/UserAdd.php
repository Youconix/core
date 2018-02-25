<?php

namespace youconix\core\helpers\forms;

class UserAdd extends AbstractUserView
{

  /**
   * \youconix\Core\helpers\forms\UserView
   */
  private $editView;

  /**
   *
   * @var \youconix\core\helpers\OnOff
   */
  protected $slider;

  /**
   *
   * @var \youconix\core\repositories\Groups
   */
  protected $groups;
  
  /**
   *
   * @var \youconix\core\helpers\PasswordForm
   */
  protected $passwordForm;

  /**
   * 
   * @param \youconix\core\helpers\forms\UserView $view
   * @param \youconix\core\helpers\OnOff $slider
   * @param \Output $template
   * @param \Language $language
   * @param \youconix\core\repositories\Groups $groups
   * @param \youconix\core\helpers\Localisation $localization
   * @param \youconix\core\helpers\PasswordForm $passwordForm
   */
  public function __construct(\youconix\core\helpers\forms\UserView $view,
			      \youconix\core\helpers\OnOff $slider, \Output $template,
			      \Language $language, \youconix\core\repositories\Groups $groups,
			      \youconix\core\helpers\Localisation $localization,
			      \youconix\core\helpers\PasswordForm $passwordForm)
  {
    $this->editView = $view;
    $this->slider = $slider;
    $this->template = $template;
    $this->language = $language;
    $this->groups = $groups;
    $this->localization = $localization;
    $this->passwordForm = $passwordForm;
  }

  /**
   * Sets the user
   *
   * @param \youconix\core\Input $input
   * @param \youconix\core\entities\User $user
   */
  public function init(\youconix\core\Input $input,
		       \youconix\core\entities\User $user)
  {
    parent::init($input, $user);

    $this->editView->init($input, $user);
  }

  protected function add()
  {
    $this->setData('usernameError',
		   $this->language->get('system/admin/users/js/usernameEmpty'));
    $this->setData('headerText',
		   $this->language->get('system/admin/users/headerAdd'));
    $this->setData('saveButton', $this->language->get('system/buttons/save'));
    $this->setData('passwordHeader',
		   $this->language->get('system/admin/users/password'));
    $this->setData('passwordRepeatHeader',
		   $this->language->get('system/admin/users/passwordAgain'));
    $this->setData('passwordError',
		   $this->language->get('system/admin/users/js/passwordEmpty'));
    $this->setData('emailError',
		   $this->language->get('system/admin/users/js/emailInvalid'));
    
    $bot = clone $this->slider;
    $bot->setName('bot');
    if ($this->input->getDefault('bot', false)) {
      $bot->setSelected(true);
    }
    
    $this->setData('bot', $bot);
    $this->setData('username', $this->input->getDefault('username'));
    $this->setData('email', $this->input->getDefault('email'));
    $this->setData('passwordForm', $this->passwordForm);
  }

  /**
   * Displays the groups
   *
   * @param boolean $includeAll
   */
  protected function getGroups($includeAll = false)
  {
    $userGroups = [];
    $a_groups = $this->groups->getGroups();

    if ($includeAll) {
      foreach ($a_groups AS $group) {
	$userGroups[$group->getId()] = [
	    'name' => $group->getName(),
	    'level' => $this->language->get('system/rights/level_-1'),
	    'blocked' => false,
	    'levelNr' => -1
	];
      }
    }

    foreach ($this->user->getGroups() AS $id => $group) {
      $userGroups[$id] = [
	  'name' => $a_groups[$id]->getName(),
	  'level' => $this->language->get('system/rights/level_' . $group->getLevel()),
	  'blocked' => false,
	  'levelNr' => $group->getLevel()
      ];
    }

    foreach ($a_groups AS $group) {
      if (in_array($group->getId(), $this->user->getGroups())) {
	continue;
      }
      $this->setData('newGroups',
		     ['value' => $group->getId(), 'text' => $group->getName()]);
    }

    foreach ($userGroups AS $id => $group) {
      if (($id == 0) && ($this->user->getId() == USERID)) {
	$userGroups[$id]['blocked'] = true;
      }
    }

    for ($i = -1; $i <= 2; $i++) {
      $this->setData('levels',
		     ['value' => $i, 'text' => $this->language->get('system/rights/level_' . $i)]);
    }
    $this->setData('groups', $userGroups);
  }

  public function generate($s_template)
  {
    $template = $this->editView->generate($s_template);

    $this->add();

    foreach ($this->a_formData as $s_name => $value) {
      $template->set($s_name, $value);
    }
    
    $this->passwordForm->addHead($template);

    return $template;
  }
}

<?php

namespace youconix\core\helpers\forms;

class UserView extends AbstractUserView
{
  /**
   * 
   * @param \Output $template
   * @param \Language $language
   * @param \youconix\core\repositories\Groups $groups
   * @param \youconix\core\helpers\Localisation $localization
   */
  public function __construct(\Output $template, \Language $language,
			      \youconix\core\repositories\Groups $groups,
			      \youconix\core\helpers\Localisation $localization)
  {
    $this->template = $template;
    $this->language = $language;
    $this->groups = $groups;
    $this->localization = $localization;
  }
  

  /**
   * Sets the user
   *
   * @param \youconix\core\Input $input
   * @param \youconix\core\entities\User $user
   */
  public function init(\youconix\core\Input $input, \youconix\core\entities\User $user)
  {
    parent::init($input, $user);

    $this->setData('userid', USERID);
    $this->setData('user', $user);
    $this->setData('localisation', $this->localization);
  }

  protected function viewHeaders()
  {
    $this->setData('usernameHeader', $this->language->get('system/admin/users/username'));
    $this->setData('emailHeader', $this->language->get('system/admin/users/email'));
    $this->setData('headerText', $this->language->get('system/admin/users/headerView'));
    $this->setData('botHeader', $this->language->get('system/admin/users/bot'));
    $this->setData('buttonBack', $this->language->get('system/buttons/back'));
    $this->setData('no', $this->language->get('system/admin/users/no'));
    $this->setData('yes', $this->language->get('system/admin/users/yes'));
  }
  
  protected function view()
  {
    $this->setData('blockedHeader', $this->language->get('system/admin/users/blocked'));
    $this->setData('loggedinHeader', $this->language->get('system/admin/users/loggedIn'));
    $this->setData('registratedHeader', $this->language->get('system/admin/users/registrated'));
    $this->setData('activeHeader', $this->language->get('system/admin/users/activated'));
    $this->setData('edit', $this->language->get('system/buttons/edit'));
    $this->setData('loginAss', 'Inloggen als');
  }
  
  /**
   * Displays the groups
   */
  protected function getGroups(){
    $userGroups = [];
    $a_groups = $this->groups->getGroups();
    
    foreach ($this->user->getGroups() AS $id => $group) {
      $userGroups[$id] = [
	  'name' => $a_groups[$id]->getName(),
	  'level' => $this->language->get('system/rights/level_' . $group->getLevel()),
	  'blocked' => false,
	  'levelNr' => $group->getLevel()
      ];
    }
    
    foreach($userGroups AS $id => $group){
      if( ($id == 0) && ($this->user->getId() == USERID) ){
        $userGroups[$id]['blocked'] = true;
      }
    }    
    $this->setData('groups', $userGroups);
  }
  
  /**
   * Checks de delete option
   */
  protected function checkDeleteOption()
  {
    $s_deleteRejected = ((USERID == $this->user->getId()) ? 'style="color:grey; text-decoration: line-through; cursor:auto"' : '');

    $this->setData('deleteRejected', $s_deleteRejected);
    $this->setData('delete', $this->language->get('system/buttons/delete'));
  }

  public function generate($s_template)
  {
    $this->viewHeaders();
    $this->view();
    $this->getGroups();

    $this->checkDeleteOption();

    return parent::generate($s_template);
  }
}

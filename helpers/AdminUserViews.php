<?php

namespace youconix\core\helpers;

/**
 * Admin user view
 *
 * @since 2.0
 */
class AdminUserViews extends \youconix\core\helpers\Helper
{
  /**
   * @var \youconix\core\helpers\forms\UserView
   */
  private $userView;
  
  /**
   *
   * @var \youconix\core\helpers\forms\UserAdd
   */
  private $addView;
  
  public function __construct(\youconix\core\helpers\forms\UserView $userView, \youconix\core\helpers\forms\UserAdd $addView)
  {
    $this->userView = $userView;
    $this->addView = $addView;
  }
  
  /**
   * Generates the add screen
   *
   * @param \youconix\core\Input $input
   * @param \youconix\core\entities\User $user
   * @return \Output
   */
  public function addScreen(\youconix\core\Input $input,\youconix\core\entities\User $user)
  {
    $this->addView->init($input, $user);
    return $this->addView->generate('addScreen');
  }

  /**
   * Generates the edit screen
   *
   * @param \youconix\core\entities\User $user
   * @param \youconix\core\Input $post
   * @return \Output
   */
  public function editScreen(\youconix\core\entities\User $user,
                             \youconix\core\Input $post)
  {
    $this->headersGeneral();
    $this->setData($post, $user);
    $this->runView();
    $this->add();
    $this->edit();
    $this->checkDeleteOption();
    $this->getGroups($user);

    $template = $this->createView('editScreen', $this->a_data);
    return $template;
  }

  /**
   * Generates the view screen
   *
   * @param \youconix\core\Input $input
   * @param \youconix\core\entities\User $user
   * @return \Output
   */
  public function viewScreen(\youconix\core\Input $input,\youconix\core\entities\User $user){
    $this->userView->init($input, $user);
    return $this->userView->generate('view');
  }

  /**
   * Loads the template
   *
   * @param string $s_view
   * @param array $a_data
   * @return \Output
   */
  protected function createView($s_view, $a_data)
  {
    $s_templateDir = 'admin';

    $this->template->load('admin/modules/general/users/'.$s_view, $s_templateDir);
    $this->template->setArray($a_data);

    return $this->template;
  }

  /**
   * Shows the user data
   */
  protected function runView()
  {
    $this->a_data = array_merge($this->a_data,
        [
        'blockedHeader' => $this->language->get('system/admin/users/blocked'),
        'loggedinHeader' => $this->language->get('system/admin/users/loggedIn'),
        'registratedHeader' => $this->language->get('system/admin/users/registrated'),
        'activeHeader' => $this->language->get('system/admin/users/activated'),
        'edit' => $this->language->get('system/buttons/edit'),
        'loginAss' => 'Inloggen als'
    ]);
  }

  /**
   * Checks de delete option
   */
  protected function checkDeleteOption()
  {
    (USERID == $this->a_data['user']->getId()) ? $s_deleteRejected = 'style="color:grey; text-decoration: line-through; cursor:auto"'
              : $s_deleteRejected = '';

    $this->a_data['deleteRejected'] = $s_deleteRejected;
    $this->a_data['delete'] = $this->language->get('system/buttons/delete');
  }

  /**
   * Displays the groups
   *
   * @param \youconix\core\entities\User $user
   * @param boolean $includeAll
   */
  protected function getGroups(\youconix\core\entities\User $user,$includeAll = false){
    $userGroups = [];
    $a_groups = $this->groups->getGroups();

    $this->a_data['newGroups'] = [];
    foreach($userGroups AS $group){
      $this->a_data['newGroups'][] = ['value'=>$group->getId(),'text'=>$group->getName()];
    }

    if( $includeAll ){
        foreach($userGroups AS $group){
          $userGroups[$group->getId()] = [
              'name'=>$group->getName(),
              'level'=> $this->language->get('system/rights/level_-1'),
              'blocked' => false,
              'levelNr' => -1
          ];
        }

        foreach($user->getGroups() AS $id => $group){
          $userGroups[$id]['level'] = $this->language->get('system/rights/level_'.$group);
          $userGroups[$id]['levelNr'] = $group;
        }
    }
    else {
      foreach($user->getGroups() AS $id => $group){
        $userGroups[$id] = [
            'name' => $a_groups[$id]->getName(),
            'level' => $this->language->get('system/rights/level_'.$group->getLevel()),
            'blocked' => false,
            'levelNr' => $group->getLevel()
        ];
      }
    }

    foreach($userGroups AS $id => $group){
      if( ($id == 0) && ($user->getId() == USERID) ){
        $userGroups[$id]['blocked'] = true;
      }
    }

    $this->a_data['levels'] = [];
    for($i=-1; $i<=2; $i++){
      $this->a_data['levels'][] = ['value'=>$i,'text'=>$this->language->get('system/rights/level_'.$i)];
    }

    $this->a_data['groups'] = $userGroups;
  }

  /**
   * Sets the groups names, permissions in edit modus
   */
  protected function setGroupsEdit()
  {
    $a_groups = $this->obj_User->getGroups();

    $a_currentGroups = array();
    foreach ($a_groups as $i_id => $i_level) {
      $obj_group = $this->groups->getGroup($i_id);

      $a_data = array(
          'name' => $obj_group->getName(),
          'level' => $this->language->get('system/rights/level_'.$i_level),
          'levelNr' => $i_level,
          'id' => $obj_group->getID()
      );

      if (($obj_group->getID() == 0) && ($this->obj_User->getID() == USERID)) {
        $this->template->setBlock('userGroupBlocked', $a_data);
      } else {
        $this->template->setBlock('userGroup', $a_data);
      }

      $a_currentGroups[] = $obj_group->getID();
    }

    $a_groups = $this->groups->getGroups();
    foreach ($a_groups as $obj_group) {
      if (in_array($obj_group->getID(), $a_currentGroups)) {
        continue;
      }

      $this->template->setBlock('newGroup',
          array(
          'value' => $obj_group->getID(),
          'text' => $obj_group->getName()
      ));
    }

    for ($i = 0; $i <= 2; $i ++) {
      $this->template->setBlock('newLevel',
          array(
          'value' => $i,
          'text' => $this->language->get('system/rights/level_'.$i)
      ));
    }
  }

  /**
   * Sets the general text
   * 
   * @return array
   */
  protected function headersGeneral()
  {
    $this->a_data = array_merge($this->a_data,
        [
        'usernameHeader' => $this->language->get('system/admin/users/username'),
        'emailHeader' => $this->language->get('system/admin/users/email'),
        'headerText' => $this->language->get('system/admin/users/headerView'),
        'botHeader' => $this->language->get('system/admin/users/bot'),
        'buttonBack' => $this->language->get('system/buttons/back'),
        'no' => $this->language->get('system/admin/users/no'),
        'yes' => $this->language->get('system/admin/users/yes')
    ]);
  }

  /**
   * Sets the add view text
   */
  protected function add()
  {
    $this->a_data = array_merge($this->a_data,
        [
        'usernameError' => $this->language->get('system/admin/users/js/usernameEmpty'),
         'headerText' => $this->language->get('system/admin/users/headerAdd'),
        'saveButton' => $this->language->get('system/buttons/save'),
        'passwordHeader' => $this->language->get('system/admin/users/password'),
        'passwordRepeatHeader' => $this->language->get('system/admin/users/passwordAgain'),
        'passwordError' => $this->language->get('system/admin/users/js/passwordEmpty'),
        'emailError' => $this->language->get('system/admin/users/js/emailInvalid')
    ]);
  }

  /**
   * Shows the edit view text
   */
  protected function edit()
  {
    $this->a_data = array_merge($this->a_data,
        [
        'passwordChangeHeader' => $this->language->get('system/admin/users/headerPassword'),
        'passwordChangeText' => $this->language->get('system/admin/users/passwordChangeText'),
        'passwordHeader' => $this->language->get('system/admin/users/password'),
        'passwordRepeatHeader' => $this->language->get('system/admin/users/passwordAgain'),
        'passwordError' => $this->language->get('system/admin/users/js/passwordEmpty'),
        'emailError' => $this->language->get('system/admin/users/js/emailInvalid'),
        'updateButton' => $this->language->get('system/buttons/edit'),
        'headerText' => $this->language->get('system/admin/users/headerEdit')
    ]);
  }

  /**
   * Sets the user
   *
   * @param \youconix\core\Input $input
   * @param \youconix\core\entities\User $user  The  user
   */
  public function setData(\youconix\core\Input $input, $user)
  {
    $a_fields = ['username', 'email', 'active', 'blocked', 'bot'];

    foreach ($a_fields AS $s_field) {
      if ($input->has($s_field)) {
        $user->$s_field = $input->get($s_field);
      }
    }

    $this->a_data['userid'] = USERID;
    $this->a_data['user'] = $user;
    $this->a_data['localisation'] = $this->localization;
  }
}
<?php

namespace youconix\core\classes;

/**
 * Site menu
 * @since 1.0
 */
class Menu implements \Menu
{

  /**
   *
   * @var \youconix\core\services\Template
   */
  protected $template;

  /**
   *
   * @var \Language
   */
  protected $language;

  /**
   *
   * @var \youconix\core\auth\Auth
   */
  protected $auth;

  /**
   * Starts the class menu
   *        
   * @param \Language $language            
   * @param \youconix\core\auth\Auth $auth
   */
  public function __construct(\Language $language,
			      \youconix\core\auth\Auth $auth)
  {
    $this->language = $language;
    $this->auth = $auth;
  }

  /**
   * Generates the menu
   * 
   * @param \Output $template
   */
  public function generateMenu(\Output $template)
  {
    $this->template = $template;
    $this->template->set('home', $this->language->get('menu/home'));

    $this->template->set('menuAdmin', false);
    $this->template->set('menuLoggedIn', false);

    $user = $this->auth->getUser();

    if (!is_null($user)) {
      $this->template->set('menuLoggedIn', true, true);

      $this->loggedIn($user);
    } else {
      $this->loggedout();
    }
  }

  /**
   * Displays the logged out items
   */
  protected function loggedout()
  {
    $this->template->set('login', $this->language->get('menu/login'));
    $this->template->set('registration',
			 $this->language->get('menu/registration'));
  }

  /**
   * @param \youconix\core\entities\User $user
   * 
   * Displays the logged in items
   */
  protected function loggedIn(\youconix\core\entities\User $user)
  {
    $this->template->set('logout', $this->language->get('menu/logout'));

    $group = $user->getGroup(GROUP_ADMIN);
    if ($group->isAdmin()) {
      $this->template->set('menuAdmin', true, true);

      $this->template->set('adminPanel',
			   $this->language->get('system/menu/adminPanel'));
    }
  }
}

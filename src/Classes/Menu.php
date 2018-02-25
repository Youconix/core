<?php

namespace youconix\Core\Classes;

/**
 * Site menu
 * @since 1.0
 */
class Menu implements \MenuInterface
{

  /**
   *
   * @var \OutputInterface
   */
  protected $template;

  /**
   *
   * @var \LanguageInterface
   */
  protected $language;

  /**
   *
   * @var \youconix\Core\Auth\Auth
   */
  protected $auth;

  /**
   * Starts the class menu
   *        
   * @param \LanguageInterface $language            
   * @param \youconix\Core\Auth\Auth $auth
   */
  public function __construct(\LanguageInterface $language,
			      \youconix\Core\Auth\Auth $auth)
  {
    $this->language = $language;
    $this->auth = $auth;
  }

  /**
   * Generates the menu
   * 
   * @param \OutputInterface $template
   */
  public function generateMenu(\OutputInterface $template)
  {
    $this->template = $template;
    $this->template->set('home', $this->language->get('menu/home'));

    $this->template->set('menuAdmin', false);
    $this->template->set('menuLoggedIn', false);

    $user = $this->auth->getUser();

    if (!is_null($user->getUserId())) {
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

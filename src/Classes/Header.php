<?php

namespace youconix\Core\Classes;

use \youconix\Core\Auth\Auth AS Auth;

/**
 * Site header
 * @since 1.0
 */
class Header implements \HeaderInterface
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
   * @var \youconix\core\auth\Auth
   */
  protected $auth;

  /**
   *
   * @var \ConfigInterface
   */
  protected $config;

  /**
   * Starts the class header
   * 
   * @param \LanguageInterface $language
   * @param \ConfigInterface $config
   * @param \youconix\core\auth\Auth $auth
   */
  public function __construct(\LanguageInterface $language, \ConfigInterface $config, Auth $auth)
  {
    $this->language = $language;
    $this->auth = $auth;
    $this->config = $config;
  }

  /**
   * Generates the header
   * 
   * @param \OutputInterface $template
   */
  public function createHeader(\OutputInterface $template)
  {
    $this->template = $template;

    $this->displayLanguageFlags();

    $this->template->set('welcomeHeader', '');

    $user = $this->auth->getUser();
    if (is_null($user->getUserId())) {
      return;
    }

    $group = $user->getGroup(GROUP_SITE);
    if ($group->isAdmin()) {
      $s_welcome = $this->language->get('system/header/adminWelcome');
    } else {
      $s_welcome = $this->language->get('system/header/userWelcome');
    }

    $s_color = $group->getColor();
    $header = '<span style="color:' . $s_color . '">' . $s_welcome . ' ' . $user->getUsername() . '</span>';
    $this->template->set('welcomeHeader', $header, true);
  }

  /**
   * Displays the language change flags
   */
  protected function displayLanguageFlags()
  {
    $a_languages = $this->config->getLanguages();
    $a_languagesCodes = $this->language->getLanguageCodes();

    foreach ($a_languages as $s_code) {
      $s_language = (array_key_exists($s_code, $a_languagesCodes)) ? $a_languagesCodes[$s_code] : $s_code;

      $this->template->append('headerLanguage',
			      [['code' => $s_code, 'LanguageInterface' => $s_language]]);
    }
  }
}

<?php

namespace youconix\core\classes;

use \youconix\core\auth\Auth AS Auth;

/**
 * Site header
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class Header implements \Header {

  /**
   *
   * @var \Output
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
   *
   * @var \Config
   */
  protected $config;

  /**
   * Starts the class header
   * 
   * @param \Language $language
   * @param \Config $config
   * @param \youconix\core\auth\Auth $auth
   */
  public function __construct(\Language $language, \Config $config,Auth $auth) {
    $this->language = $language;
    $this->auth = $auth;
    $this->config = $config;
  }

  /**
   * Generates the header
   * 
   * @param \Output $template
   */
  public function createHeader(\Output $template) {
    $this->template = $template;

    $this->displayLanguageFlags();
    
    $this->template->set('welcomeHeader','');    

    $obj_User = $this->auth->getUser();
    if (is_null($obj_User)) {
      return;
    }

    if ($obj_User->isAdmin(GROUP_SITE)) {
      $s_welcome = $this->language->get('system/header/adminWelcome');
    } else {
      $s_welcome = $this->language->get('system/header/userWelcome');
    }

    $this->template->set('welcomeHeader', '<a href="{NIV}profile/view/details/id=' . $obj_User->getID() . '" style="color:' . $obj_User->getColor() . '">' . $s_welcome . ' ' . $obj_User->getUsername() . '</a>',true);
  }

  /**
   * Displays the language change flags
   */
  protected function displayLanguageFlags() {
    $a_languages = $this->config->getLanguages();
    $a_languagesCodes = $this->language->getLanguageCodes();

    foreach ($a_languages as $s_code) {
      $s_language = (array_key_exists($s_code, $a_languagesCodes)) ? $a_languagesCodes[$s_code] : $s_code;

      $this->template->append('headerLanguage', [['code'=>$s_code,'language'=>$s_language]]);
    }
  }

}

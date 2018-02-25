<?php

namespace youconix\core\helpers;

/**
 * Contains the password form with a password strength test
 * @since 2.0
 */
class PasswordForm extends Helper
{

  /**
   *
   * @var \LanguageInterface
   */
  protected $language;

  /**
   *
   * @var \ConfigInterface
   */
  protected $config;

  /**
   * PHP 5 constructor
   *
   * @param \LanguageInterface $language
   * @param \ConfigInterface $config
   */
  public function __construct(\LanguageInterface $language, \ConfigInterface $config)
  {
    $this->language = $language;
    $this->config = $config;
  }

  /**
   * 
   * @param \OutputInterface $template
   */
  public function addHead(\OutputInterface $template)
  {
    $s_head = '<script id="password_check_script" src="/js/widgets/password_check.js"></script>
    <script src="/js/validation.js"></script>
    <link rel="stylesheet" href="/' . $this->config->getSharedStylesDir() . 'css/HTML5_validation.css">
    <link rel="stylesheet" href="/' . $this->config->getSharedStylesDir() . 'css/widgets/password_form.css">';

    $template->append('head', $s_head);
  }

  /**
   * Generates the form
   * 
   * @return string The form
   */
  public function generate()
  {
    $settings = $this->config->getPasswordSettings();

    $s_html = '<section id="passwordForm">
      <fieldset id="capslock_warning">
	<span class="validation-error-message">Capslock is enabled</span>
      </fieldset>
      <fieldset>
	<label class="label">' . $this->language->get('widgets/passwordForm/password') . ' *</label>
	<input type="password" name="password" id="password1" required>
      </fieldset>
      <fieldset>
	<label class="label" for="password2">' . $this->language->get('widgets/passwordForm/passwordAgain') . ' *</label>
	<input type="password" name="password2" id="password2" required>
      </fieldset>
    </section>
    <article id="passwordStrength">
      <div id="passwordIndicator"></div>
      <div id="passwordStrengthText"></div>
    </article>
    <script>
    <!--
    passwordCheckSettings = {
	level: ' . $settings->level . ',
	minimunLength : ' . $settings->mimimunLength . ',
	language : {
	  passwordform_invalid : "' . $this->language->get('widgets/passwordForm/invalid') . '",
	  passwordform_toShort : "' . $this->language->get('widgets/passwordForm/toShort') . '",
	  passwordform_veryStrongPassword : "' . $this->language->get('widgets/passwordForm/veryStrongPassword') . '",
	  passwordform_strongPassword : "' . $this->language->get('widgets/passwordForm/strongPassword') . '",
	  passwordform_fairPassword : "' . $this->language->get('widgets/passwordForm/fairPassword') . '",
	  passwordform_weakPassword : "' . $this->language->get('widgets/passwordForm/weakPassword') . '",
	  requirements : {
	    1 : "Uw wachtwoord moet minimaal uit [length] tekens bestaan",
	    2 : "Uw wachtwoord moet minimaal uit [length] tekens inclusief 1 cijfer bestaan",
	    3 : "Uw wachtwoord moet minimaal uit [length] tekens inclusief 1 cijfer en 1 speciaal teken bestaan"
	  }
      }
    };
    //-->
    </script>';

    return $s_html;
  }
}

?>

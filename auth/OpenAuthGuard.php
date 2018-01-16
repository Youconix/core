<?php

namespace youconix\core\auth;

abstract class OpenAuthGuard extends GuardParent
{

  protected function loadConfig()
  {
    $this->guardConfig = [
	'enabled' => false,
	'appId' => '',
	'appSecret' => ''
    ];

    parent::loadConfig();
  }

  /**
   * 
   * @return boolean
   */
  public function hasReset()
  {
    return false;
  }

  /**
   * 
   * @return boolean
   */
  public function hasActivation()
  {
    return false;
  }

  /**
   * 
   * @return boolean
   */
  public function hasConfig()
  {
    return true;
  }

  /**
   * 
   * @return string
   */
  public function getLogo()
  {
    return '';
  }

  /**
   * 
   * @return string
   */
  public function getConfigForm()
  {
    $name = $this->getName();

    $form = '<fieldset>
	<label class="label">'.$this->language->get('system/settings/login/appID').' *</label>
	<input type="text" name="' . $name . '_appId" value="' . $this->guardConfig['appId'] . '" required
	  data-validation="'.$this->language->get('system/settings/login/facebookAppError').'">
    </fieldset>
    <fieldset>
	<label class="label">'.$this->language->get('system/settings/login/appSecret').' *</label>
	<input type="text" name="' . $name . '_appSecret" value="' . $this->guardConfig['appSecret'] . '" required
	  data-validation="'.$this->language->get('system/settings/login/facebookAppSecretError').'">
    </fieldset>';

    return $form;
  }
}

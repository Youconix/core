<?php

namespace youconix\Core\Auth;

abstract class AbstractOpenAuthGuard extends AbstractGuard
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
	<label class="label">'.$this->language->get('system/admin/settings/login/appID').' *</label>
	<input type="text" name="' . $name . '_appId" value="' . $this->guardConfig['appId'] . '" required
	  data-validation="'.$this->language->get('system/admin/settings/login/facebookAppError').'">
    </fieldset>
    <fieldset>
	<label class="label">'.$this->language->get('system/admin/settings/login/appSecret').' *</label>
	<input type="text" name="' . $name . '_appSecret" value="' . $this->guardConfig['appSecret'] . '" required
	  data-validation="'.$this->language->get('system/admin/settings/login/facebookAppSecretError').'">
    </fieldset>';

    return $form;
  }
}

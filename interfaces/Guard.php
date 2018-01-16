<?php
interface Guard {
  const FORM_INVALID = "FORM_INVALID";
  const INVALID_LOGIN = "INVALID LOGIN";
  const LOGIN_EXPIRED = "EXPIRED LOGIN";
  const FORM_OKE = "ALL OK";
  const USERNAME_TAKEN = "USERNAME TAKEN";
  const EMAIL_TAKEN = "EMAIL TAKEN";
  
  /**
   * 
   * @return boolean
   */
  public function hasReset();
  
  /**
   * 
   * @return boolean
   */
  public function hasActivation();
  
  /**
   * 
   * @return boolean
   */
  public function isRegistrationEnabled();
  
  /**
   * 
   * @return boolean
   */
  public function hasConfig();
  
  /**
   * 
   * @param \youconix\core\Input $config
   */
  public function validate(\youconix\core\Input $config);
  
  /**
   * 
   * @param \youconix\core\Input $config
   */
  public function setConfig(\youconix\core\Input $config);
  
  /**
   * 
   * @return array
   */
  public function isEnabled();
  
  /**
   * 
   * @param boolean $enabled
   */
  public function setEnabled($enabled);
  
  public function loginForm(\Output $output,\Request $request);
  
  public function do_login(\Request $request);
  
  public function registrationForm(\Output $output,\Request $request);
  
  public function do_registration(\Request $request);
  
  public function setAuth(\youconix\core\auth\Auth $auth);
  
  public function usernameAvailable($s_username);
  
  public function emailAvailable($s_email);
  
  /**
   * 
   * @return string
   */
  public function getLogo();
  
  /**
   * 
   * @return string
   */
  public function getName();
  
  /**
   * 
   * @return string
   */
  public function getDisplayName();
}
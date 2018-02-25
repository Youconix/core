<?php

use youconix\Core\Input;
use youconix\Core\Auth\Auth;

interface GuardInterface {
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
   * @param Input $config
   */
  public function validate(Input $config);
  
  /**
   * 
   * @param Input $config
   */
  public function setConfig(Input $config);
  
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
  
  public function loginForm(\OutputInterface $output, \RequestInterface $request);
  
  public function do_login(\RequestInterface $request);
  
  public function registrationForm(\OutputInterface $output, \RequestInterface $request);
  
  public function do_registration(\RequestInterface $request);
  
  public function setAuth(Auth $auth);
  
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
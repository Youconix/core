<?php
interface Guard {
  const FORM_INVALID = "FORM_INVALID";
  const INVALID_LOGIN = "INVALID LOGIN";
  const LOGIN_EXPIRED = "EXPIRED LOGIN";
  const FORM_OKE = "ALL OK";
  const USERNAME_TAKEN = "USERNAME TAKEN";
  const EMAIL_TAKEN = "EMAIL TAKEN";
  
  public function hasReset();
  
  public function hasRegistration();
  
  public function loginForm(\Output $output,\Request $request);
  
  public function do_login(\Request $request);
  
  public function expiredForm(\Output $output);
  
  public function updatePassword(\Request $request);
  
  public function resetForm(\Output $output,\Request $request);
  
  public function sendResetEmail(\Request $request);
  
  public function do_reset($hash);
  
  public function registrationForm(\Output $output,\Request $request);
  
  public function do_registration(\Request $request);
  
  public function email_confirm();
  
  public function setAuth(\youconix\core\auth\Auth $auth);
  
  public function usernameAvailable($s_username);
  
  public function emailAvailable($s_email);
  
  public function getLogo();
  
  public function getName();
}
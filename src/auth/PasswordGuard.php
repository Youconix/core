<?php

namespace youconix\core\auth;

abstract class PasswordGuard extends GuardParent
{

  /**
   * 
   * @return boolean
   */
  public function hasReset()
  {
    return true;
  }

  /**
   * 
   * @return boolean
   */
  public function hasActivation()
  {
    return true;
  }

  /**
   * 
   * @return boolean
   */
  public function hasConfig()
  {
    return false;
  }

  /**
   * 
   * @return string
   */
  public function getLogo()
  {
    return '';
  }

  abstract public function email_confirm();

  abstract public function updatePassword(\Request $request);

  abstract public function expiredForm(\Output $output);
}

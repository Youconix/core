<?php

namespace youconix\Core\Auth;

abstract class PasswordGuard extends AbstractGuard
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

  abstract public function updatePassword(\RequestInterface $request);

  abstract public function expiredForm(\OutputInterface $output);
}

<?php

namespace youconix\Core\Auth\Guards;

class Facebook extends \youconix\Core\Auth\AbstractOpenAuthGuard
{

  public function do_login(\RequestInterface $request)
  {
    
  }

  public function do_registration(\RequestInterface $request)
  {
    
  }

  public function do_reset($hash)
  {
    
  }

  /**
   * 
   * @return string
   */
  public function getName()
  {
    return 'facebook';
  }

  /**
   * 
   * @return string
   */
  public function getDisplayName()
  {
    return 'Facebook';
  }

  public function loginForm(\OutputInterface $output, \RequestInterface $request)
  {
    
  }

  public function registrationForm(\OutputInterface $output, \RequestInterface $request)
  {
    
  }
}

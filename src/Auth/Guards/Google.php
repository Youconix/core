<?php

namespace youconix\core\auth\guards;

class Google extends \youconix\Core\Auth\AbstractOpenAuthGuard
{

  public function do_login(\RequestInterface $request)
  {
    
  }

  public function do_registration(\RequestInterface $request)
  {
    
  }

  /**
   * 
   * @return string
   */
  public function getName()
  {
    return 'google';
  }

  /**
   * 
   * @return string
   */
  public function getDisplayName()
  {
    return 'Google';
  }

  public function loginForm(\OutputInterface $output, \RequestInterface $request)
  {
    
  }

  public function registrationForm(\OutputInterface $output, \RequestInterface $request)
  {
    
  }
}

<?php

namespace youconix\core\auth\guards;

class Google extends \youconix\core\auth\OpenAuthGuard
{

  public function do_login(\Request $request)
  {
    
  }

  public function do_registration(\Request $request)
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

  public function loginForm(\OutputInterface $output, \Request $request)
  {
    
  }

  public function registrationForm(\OutputInterface $output, \Request $request)
  {
    
  }
}

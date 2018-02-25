<?php

namespace youconix\core\auth\guards;

class Facebook extends \youconix\core\auth\OpenAuthGuard
{

  public function do_login(\Request $request)
  {
    
  }

  public function do_registration(\Request $request)
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

  public function loginForm(\Output $output, \Request $request)
  {
    
  }

  public function registrationForm(\Output $output, \Request $request)
  {
    
  }
}

<?php

namespace youconix\core\services\session;

/**
 * Native session class
 * 
 * @since 2.0
 */
class Native extends \youconix\core\services\session\Session
{
  /**
   * PHP 5 constructor
   *
   * @param \Settings $settings            
   * @param \Builder $builder            
   */
  public function __construct(\Settings $settings)
  {
    $s_sessionSetName = $settings->get('settings/session/sessionName');
    $s_sessionSetPath = $settings->get('settings/session/sessionPath');
    $s_sessionExpire = $settings->get('settings/session/sessionExpire');

    if ($s_sessionSetName != '') {
      @session_name($s_sessionSetName);
    }
    if ($s_sessionSetPath != '') {
      @session_save_path($s_sessionSetPath);
    }
    if ($s_sessionExpire != '') {
      @ini_set("session.gc_maxlifetime", $s_sessionExpire);
    }

    @session_start();

    $this->a_data = $_SESSION;
  }

  /**
   * Destructor
   */
  public function __destruct()
  {
    $this->writeSession();
    session_write_close();
  }

  /**
   * Destroys all sessions currently set
   */
  public function destroy()
  {
    session_destroy();
    $_SESSION = array();
    $this->a_data = [];
  }

  /**
   * Regenerates the session ID
   */
  public function regenerate()
  {
    session_regenerate_id();
  }

  /**
   * Writes the session memory to storage
   */
  public function writeSession()
  {
    $_SESSION = $this->a_data;
  }
}

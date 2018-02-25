<?php

interface SessionInterface
{

  const FORBIDDEN = - 1;
  // Stil here for backwards compatibility
  const ANONYMOUS = - 1;
  const USER = 0;
  const MODERATOR = 1;
  const ADMIN = 2;
  const FORBIDDEN_COLOR = 'grey';
  // Stil here for backwards compatibility
  const ANONYMOUS_COLOR = 'grey';
  const USER_COLOR = 'black';
  const MODERATOR_COLOR = 'green';
  const ADMIN_COLOR = 'red';

  /**
   * Sets the session with the given name and content
   *
   * @param string $s_sessionName            
   * @param string $s_sessionData            
   */
  public function set($s_sessionName, $s_sessionData);

  /**
   * Deletes the session with the given name
   *
   * @param string $s_sessionName
   *            of the session
   * @throws \IOException if the session does not exist
   */
  public function delete($s_sessionName);

  /**
   * Collects the content of the given session
   *
   * @param string $s_sessionName
   *            name of the session
   * @return string asked session
   * @throws \IOException if the session does not exist
   */
  public function get($s_sessionName);

  /**
   * Checks or the given session exists
   *
   * @param string $s_sessionName
   *            name of the session
   * @return boolean True if the session exists, false if it does not
   */
  public function exists($s_sessionName);

  /**
   * Destroys all sessions currently set
   */
  public function destroy();

  /**
   * Regenerates the session ID
   */
  public function regenerate();
  
  /**
   * Writes the session memory to storage
   */
  public function writeSession();
}

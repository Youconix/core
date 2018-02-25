<?php

namespace youconix\Core\Services\Session;

abstract class AbstractSession implements \SessionInterface {
  protected $a_data = [];
  
  /**
   * Returns if the object should be treated as singleton
   *
   * @return boolean
   */
  public static function isSingleton()
  {
    return true;
  }
  
  /**
   * Sets the session with the given name and content
   *
   * @param string $s_sessionName
   *            of the session
   * @param string $s_sessionData
   *            of the session
   */
  public function set($s_sessionName, $s_sessionData)
  {
    \youconix\core\Memory::type('string', $s_sessionName);

    /* Set session */
    $this->a_data[$s_sessionName] = $s_sessionData;
  }

  /**
   * Deletes the session with the given name
   *
   * @param string $s_sessionName
   *            of the session
   * @throws \IOException if the session does not exist
   */
  public function delete($s_sessionName)
  {
    \youconix\core\Memory::type('string', $s_sessionName);

    if (!$this->exists($s_sessionName)) {
      throw new \IOException('Session ' . $s_sessionName . ' does not exist');
    }

    unset($this->a_data[$s_sessionName]);
  }

  /**
   * Collects the content of the given session
   *
   * @param string $s_sessionName
   *            name of the session
   * @return string asked session
   * @throws \IOException if the session does not exist
   */
  public function get($s_sessionName)
  {
    \youconix\core\Memory::type('string', $s_sessionName);

    if (!$this->exists($s_sessionName)) {
      throw new \IOException('Session ' . $s_sessionName . ' does not exist');
    }

    $s_data = $this->a_data[$s_sessionName];

    return $s_data;
  }

  /**
   * Checks or the given session exists
   *
   * @param string $s_sessionName
   *            name of the session
   * @return boolean True if the session exists, false if it does not
   */
  public function exists($s_sessionName)
  {
    \youconix\core\Memory::type('string', $s_sessionName);

    if (array_key_exists($s_sessionName, $this->a_data)) {
      return true;
    }

    return false;
  }
  
  /**
   * Returns the visitors browser fingerprint
   *
   * @param boolean $bo_bindToIp
   * @return String fingerprint
   */
  public function getFingerprint($bo_bindToIp)
  {
    $s_encoding = str_replace(', sdch', '', $_SERVER['HTTP_ACCEPT_ENCODING']);
    if ($bo_bindToIp) {
      return sha1($_SERVER['REMOTE_ADDR'] . '-' . $_SERVER['HTTP_USER_AGENT'] . '-' . $_SERVER['HTTP_HOST'] . '-' . $_SERVER['SERVER_SIGNATURE'] . '-' . strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']) . '-' . $s_encoding);
    } else {
      return sha1($_SERVER['HTTP_USER_AGENT'] . '-' . $_SERVER['HTTP_HOST'] . '-' . $_SERVER['SERVER_SIGNATURE'] . '-' . strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']) . '-' . $s_encoding);
    }
  }
}


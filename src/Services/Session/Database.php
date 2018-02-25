<?php

namespace youconix\Core\Services\Session;

/**
 * Native session class
 * 
 * @since 2.0
 */
class Database extends \youconix\Core\Services\Session\AbstractSession
{

  /**
   *
   * @var \Builder
   */
  protected $builder;

  /**
   *
   * @var \cookie
   */
  protected $cookie;  
  protected $s_sessionId = null;
  protected $s_table = 'sessions';
  protected $s_sessionExpire;

  /**
   * PHP 5 constructor
   *
   * @param \Builder $builder           
   */
  public function __construct(\BuilderInterface $builder, \Cookie $cookie,
			      \SettingsInterface $settings)
  {
    $this->builder = $builder;
    $this->cookie = $cookie;

    $this->s_sessionExpire = $settings->get('settings/session/sessionExpire');

    $timeout = (time() - $this->s_sessionExpire + 30); //session expires in the next 30 sec
    $this->builder->delete($this->s_table)
	->getWhere()
	->bindInt('last_access_time', $timeout, 'AND', '<=')
	->getResult();

    $this->readSession();
  }

  /**
   * Destructor
   */
  public function __destruct()
  {
    $this->writeSession();
  }

  /**
   * Destroys all sessions currently set
   */
  public function destroy()
  {
    $this->builder->delete($this->s_table)
	->getWhere()
	->bindString('session_id', $this->s_sessionId)
	->getResult();

    $this->s_sessionId = null;
    $this->a_data = [];
  }

  /**
   * Regenerates the session ID
   */
  public function regenerate()
  {
    $newSessionId = $this->createSessionId();
    $this->builder->update($this->s_table)
	->bindString('session_id', $newSessionId)
	->getWhere()
	->bindString('session_id', $this->s_sessionId)
	->getResult();

    $this->s_sessionId = $newSessionId;
    $this->cookie->get('MH_session_id', $this->s_sessionId);
  }

  /**
   * 
   * @return string
   */
  protected function createSessionId()
  {
    return sha1(time() . ' | ' . getmypid() . ' | ' . $this->getFingerprint());
  }

  protected function readSession()
  {
    if (!$this->cookie->exists('MH_session_id')) {
      $this->s_sessionId = $this->createSessionId();
      $this->cookie->set('MH_session_id', $this->s_sessionId);
    } else {
      $this->s_sessionId = $this->cookie->get('MH_session_id');
    }

    $this->builder->insert($this->s_table, true)
	->bindString('session_id', $this->s_sessionId)
	->bindString('fingerprint', $this->getFingerprint())
	->bindInt('last_access_time', time())
	->bindBlob('session_data', '')
	->bindInt('lock_time', 0)
	->bindString('lock_id', '')
	->getResult();

    $start = time();
    $this->getLock($start);

    $database = $this->builder->select($this->s_table, 'session_data')
	->order('id', 'DESC')
	->limit(1)
	->getWhere()
	->bindString('session_id', $this->s_sessionId)
	->getResult();

    if ($database->num_rows() == 0) {
      $this->s_sessionId = null;
      return;
    }
    $this->a_data = unserialize($database->fetch_column(1));
  }

  protected function getLock($i_startTime)
  {
    if ((time() - $i_startTime) > 40) {
      throw new \RuntimeException('Could not get session lock in 40 sec.');
    }

    $database = $this->builder->update($this->s_table)
	->bindInt('last_access_time', time())
	->bindInt('lock_time', time())
	->bindString('lock_id', getmypid())
	->getWhere()
	->bindString('session_id', $this->s_sessionId)
	->startSubWhere()
	->bindInt('lock_time', 0)
	->bindInt('lock_time', (time() - 30), 'OR', '<=')
	->getResult();

    if ($database->affected_rows() == 0) {
      usleep(30000); //wait 0.3 sec
      $this->getLock($i_startTime);
    }
  }

  protected function createSession()
  {
    $this->regenerate();

    $this->builder->insert($this->s_table)
	->bindString('session_id', $this->s_sessionId)
	->bindString('fingerprint', $this->getFingerprint())
	->bindInt('timeout', (time() + $this->s_sessionExpire))
	->bindBlob('session_data', serialize($this->a_data))
	->getResult();
  }

  /**
   * Writes the session memory to storage
   */
  public function writeSession()
  {
    $this->builder->update($this->s_table)
	->bindBlob('session_data', serialize($this->a_data))
	->bindInt('lock_time', 0)
	->bindString('lock_id', '')
	->getWhere()
	->bindString('session_id', $this->s_sessionId)
	->getResult();
  }
}

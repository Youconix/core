<?php
namespace youconix\core\services\logger;

/**
 * Log-handler for manipulating log files
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 2.0
 */
abstract class LoggerParent extends \youconix\core\services\Service implements \Logger
{

    protected $service_Mailer;

    protected $a_address;

    protected $obj_loglevel;

    protected $s_host;

    public function __construct(\youconix\core\services\Mailer $mailer, \Config $config, \Psr\Log\LogLevel $loglevel)
    {
        $this->service_Mailer = $mailer;
        $this->a_address = $config->getAdminAddress();
        $this->obj_loglevel = $loglevel;
        $this->s_host = $config->getHost();
    }

    /**
     * Returns if the object schould be treated as singleton
     *
     * @return boolean True if the object is a singleton
     */
    public static function isSingleton()
    {
        return true;
    }

    /**
     * System is unusable.
     *
     * @param string $message            
     * @param array $context            
     * @return null
     */
    public function emergency($message, array $context = array())
    {
        $obj_loglevel = $this->obj_loglevel;
        
        $this->log($obj_loglevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message            
     * @param array $context            
     * @return null
     */
    public function alert($message, array $context = array())
    {
        $obj_loglevel = $this->obj_loglevel;
        
        $this->log($obj_loglevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message            
     * @param array $context            
     * @return null
     */
    public function critical($message, array $context = array())
    {
        $obj_loglevel = $this->obj_loglevel;
        
        $this->log($obj_loglevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message            
     * @param array $context            
     * @return null
     */
    public function error($message, array $context = array())
    {
        $obj_loglevel = $this->obj_loglevel;
        
        $this->log($obj_loglevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message            
     * @param array $context            
     * @return null
     */
    public function warning($message, array $context = array())
    {
        $obj_loglevel = $this->obj_loglevel;
        
        $this->log($obj_loglevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message            
     * @param array $context            
     * @return null
     */
    public function notice($message, array $context = array())
    {
        $obj_loglevel = $this->obj_loglevel;
        
        $this->log($obj_loglevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message            
     * @param array $context            
     * @return null
     */
    public function info($message, array $context = array())
    {
        $obj_loglevel = $this->obj_loglevel;
        
        $this->log($obj_loglevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message            
     * @param array $context            
     * @return null
     */
    public function debug($message, array $context = array())
    {
        $obj_loglevel = $this->obj_loglevel;
        
        $this->log($obj_loglevel::DEBUG, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level            
     * @param string $message            
     * @param array $context            
     * @return null
     */
    abstract public function log($level, $message, array $context = array());

    /**
     * Parses the level, message and context
     *
     * @param \Psr\Log\LogLevel $level
     *            The log level
     * @param string $message
     *            The message
     * @param array $context
     *            The context
     * @return string The parsed message
     */
    protected function parseContext($level, $message, $context)
    {
        $message = $level . ' ' . $message;
        $s_exception = '';
        
        foreach ($context as $key => $value) {
            if ($key == 'exception') {
                $s_exception = $value->getMessage() . "\n" . $value->getTraceAsString();
            } else {
                $message .= "\n" . $key . ' : ' . $value;
            }
        }
        
        if (! empty($s_exception)) {
            $message .= "\n" . $s_exception;
        }
        
        return $message;
    }

    /**
     * Warns the admin trough email is the level is high enough
     *
     * @param \Psr\Log\LogLevel $level
     *            The log level
     * @param string $message
     *            The message
     */
    protected function warnAdmin($level, $message)
    {
        $obj_loglevel = $this->obj_loglevel;
        
        if (($level == $obj_loglevel::EMERGENCY) || ($level == $obj_loglevel::ALERT) || ($level == $obj_loglevel::CRITICAL)) {
            $this->service_Mailer->logDeamon($message, $this->a_address, $this->s_host);
        }
    }
}
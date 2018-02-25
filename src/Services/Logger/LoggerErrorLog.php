<?php
namespace youconix\Core\Services\Logger;

class LoggerErrorLog extends \youconix\Core\Services\Logger\AbstractLogger
{

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level            
     * @param string $message            
     * @param array $context            
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $message = $this->parseContext($level, $message, $context);
        
        if (! error_log($message)) {
            $message = $this->parseContext($obj_loglevel::CRITICAL, 'Could not write to ' . ini_get('error_log') . '.');
            $this->warnAdmin($obj_loglevel::CRITICAL, $message);
        } else {
            $this->warnAdmin($level, $message);
        }
    }
}
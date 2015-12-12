<?php
namespace youconix\core\services\logger;

class LoggerSysLog extends \youconix\core\services\logger\LoggerParent
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
        $obj_loglevel = $this->obj_loglevel;
        
        (array_key_exists('type', $context)) ? $s_name = $context['type'] : $s_name = 'default';
        
        $message = $this->parseContext($level, $message, $context);
        
        $s_ident = $this->s_host . ' : ' . $s_name;
        if (openlog($s_ident, LOG_PID | LOG_PERROR, LOG_USER)) {
            if ($s_name == 'error') {
                $type = LOG_ERR;
            } else {
                $type = LOG_INFO;
            }
            
            syslog($type, $s_log);
            
            closelog();
            
            $this->warnAdmin($level, $message);
        } else {
            $message = $this->parseContext($obj_loglevel::CRITICAL, 'Could not write to syslog.');
            $this->warnAdmin($obj_loglevel::CRITICAL, $message);
        }
    }
}
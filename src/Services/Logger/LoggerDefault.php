<?php
namespace youconix\Core\Services\Logger;

class LoggerDefault extends \youconix\Core\Services\Logger\AbstractLogger
{

    protected $service_File;

    protected $service_FileData;

    protected $s_target;

    protected $i_maxSize;

    protected $s_errorLog;

    public function __construct(\youconix\Core\Services\File $service_File, \youconix\Core\Services\FileData $service_FileData, \youconix\Core\Services\Mailer $mailer, \ConfigInterface $config, \Psr\Log\LogLevel $loglevel)
    {
        parent::__construct($mailer, $config, $loglevel);
        
        $this->service_File = $service_File;
        $this->service_FileData = $service_FileData;
        $this->s_target = $config->getLogLocation();
        $this->i_maxSize = $config->getLogfileMaxSize();
        
        $s_errorLogPath = $this->s_target . 'error.log';
        $this->s_errorLog = realpath($s_errorLogPath);
        if (! $this->s_errorLog) {
            touch($s_errorLogPath);
            $this->s_errorLog = realpath($s_errorLogPath);
        }
    }

    protected function logRotate($s_name)
    {
        if (! $this->service_File->exists($this->s_target . $s_name . '.log')) {
            return;
        }
        
        if ($this->service_FileData->getFileSize($this->s_target . $s_name . '.log') >= $this->i_maxSize) {
            if ($this->service_File->exists($this->s_target . $s_name . '.1.log')) {
                $this->service_File->moveFile($this->s_target . $s_name . '.1.log', $this->s_target . $s_name . '.2.log');
            }
            
            $this->service_File->moveFile($this->s_target . $s_name . '.log', $this->s_target . $s_name . '.1.log');
        }
    }

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
        $message = '[' . date('d-m-Y H:i:s') . "]\t" . $level . ' ' . $message;
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
        
        $message .= "\n";
        
        return $message;
    }

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
        (array_key_exists('type', $context)) ? $s_name = $context['type'] : $s_name = 'default';
        
        $obj_loglevel = $this->obj_loglevel;
        
        $message = $this->parseContext($level, $message, $context);
        
        try {
            $this->logRotate($s_name);
        } catch (\IOException $exception) {
            $this->emergency('Can not rotate log ' . $s_name . '.log', array(
                'previous' => $message,
                'exception' => $exception
            ));
            return;
        }
        
        $obj_loglevel = $this->obj_loglevel;
        
        try {
            if ($s_name == 'error' || in_array($level, array(
                $obj_loglevel::EMERGENCY
            ))) {
                $this->service_File->writeLastFile($this->s_errorLog, $message);
            } else {
                $this->service_File->writeLastFile($this->s_target . $s_name . '.log', $message);
            }
            
            $this->warnAdmin($level, $message);
        } catch (\IOException $exception) {
            if ($s_name != 'error' && ! in_array($level, array(
                $obj_loglevel::EMERGENCY
            ))) {
                $this->emergency('Error writing to log ' . $s_name . '.log', array(
                    'previous' => $message,
                    'exception' => $exception
                ));
            } else {
                $message = $this->parseContext($obj_loglevel::CRITICAL, 'Can not write to errorlog', array(
                    'exception' => $exception
                ));
                $this->warnAdmin($obj_loglevel::CRITICAL, $message);
            }
        }
    }
}

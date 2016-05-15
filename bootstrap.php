<?php

function reportException(\Exception $exception, $bo_caught = true)
{
    $s_error = $exception->getMessage() . PHP_EOL;
    $s_error .= $exception->getTraceAsString();
    
    try {
        $logs = \Loader::Inject('\Logger');
        
        if ($bo_caught) {
            $logs->critical($s_error);
        } else {
            $logs->alert($s_error);
        }
    } catch (Exception $e) {
        if (defined('DEBUG')) {
            echo ($s_error);
        }
    }
}

/* Set error catcher */
function exception_handler($exception)
{
    if (defined('DEBUG') || ($exception instanceof \CoreException)) {
        header('HTTP/1.1 500 Internal Server Error');
        
        echo ('<!DOCTYPE html>
		<html>
		<head>
			<title>500 Internal Server Error</title>
		</head>
		<body>
		<section id="container">
  			<h1>500 Internal Server Error</h1>
  	
  			<h3>Whoops something went wrong</h3>
  	
  			<h5>Whoops? What whoops?<br/> Computer deactivate the [fill in what you want]</h5>
  	
		    <p>' . nl2br($exception->__toString()) . '</p>
		</section>
		</body>
		</html>
  	');
        exit();
    }
    
    include (WEB_ROOT . 'errors/Error500.php');
    exit();
}

set_exception_handler('exception_handler');

/**
 * Start framework
 */
define('CORE','vendor'.DIRECTORY_SEPARATOR.'youconix'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR);
$a_files = explode('vendor'.DIRECTORY_SEPARATOR.'youconix',__FILE__);
define('WEB_ROOT',$a_files[0]);

require_once (WEB_ROOT.DIRECTORY_SEPARATOR.CORE.'Profiler.php');
Profiler::reset();

require_once (WEB_ROOT.DIRECTORY_SEPARATOR.CORE. 'Memory.php');
\youconix\core\Memory::startUp();

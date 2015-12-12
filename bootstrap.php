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
    if (defined('DEBUG')) {
        $headers = \Loader::Inject('\Headers');
        $headers->http500();
        $headers->printHeaders();
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
    
    print_r($exception);
    
    include (WEBSITE_ROOT . 'errors/Error500.php');
    exit();
}

set_exception_handler('exception_handler');

interface Routable
{

    public function route($s_command);
}

/**
 * Start framework
 */
define('CORE','vendor'.DIRECTORY_SEPARATOR.'youconix'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR);
require_once (NIV.CORE.'Profiler.php');
Profiler::reset();

require_once (NIV.CORE. 'Memory.php');
\core\Memory::startUp();

/* Check login */
\Profiler::profileSystem('core/models/Provileges', 'Checking access level');
\Loader::inject('\core\models\Privileges')->checkLogin();
\Profiler::profileSystem('core/models/Provileges', 'Checking access level completed');

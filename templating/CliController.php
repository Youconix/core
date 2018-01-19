<?php

namespace youconix\core\templating;

abstract class CliController
{

  protected $cliParameters = [];
  protected $cliFlags = [];

  /**
   * Base class constructor
   *
   * @param \Headers $headers
   */
  public function __construct()
  {

    $this->readCliParameters();
  }

  protected function readCliParameters()
  {
    for ($i = 1; $i < count($_SERVER['argv']); $i++) {
      $parameter = $_SERVER['argv'][$i];

      if (substr($parameter, 0, 1) !== '-') {
	$this->cliParameters[] = $parameter;
	continue;
      }

      if (substr($parameter, 0, 2) == '--') {
	$flag = substr($parameter, 2);
	if (strpos($parameter, '=') !== false) {
	  $parts = explode('=', $flag);
	  $this->cliFlags[$parts[0]] = $parts[1];
	  continue;
	}

	$this->cliFlags[] = $flag;
      }

      $flag = substr($parameter, 1);
      if (strpos($parameter, '=') !== false) {
	$parts = explode('=', $flag);
	$this->cliFlags[$parts[0]] = $parts[1];
	continue;
      }
      $this->cliFlags[] = $flag;
    }
  }
  
  /**
   * 
   * @param string $message
   */
  protected function message($message)
  {
    echo($message.PHP_EOL);
    flush();
  }
}

<?php

namespace youconix\core\Templating;

use \youconix\core\Templating\Gui\AdminLogicClass AS Layout;

/**
 * @author Rachelle Scheijen
 * @since 2.0
 */
final class AdminControllerWrapper extends ControllerWrapper
{

  /**
   *
   * @param \Request $request
   * @param \youconix\core\templating\gui\AdminLogicClass $layout
   * @param \OutputInterface $output
   * @param \Headers $headers
   * @param \LoggerInterface $logger
   * @param \LanguageInterface $language
   */
  public function __construct(\Request $request, Layout $layout, \OutputInterface $output, \Headers $headers, \LoggerInterface $logger, \LanguageInterface $language)
  {
    parent::__construct($request, $layout, $output, $headers, $logger, $language);
  }
}

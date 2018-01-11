<?php

namespace youconix\core\templating;

use \youconix\core\templating\gui\AdminLogicClass AS Layout;

/**
 * @author Rachelle Scheijen
 * @since 2.0
 */
final class AdminControllerWrapper extends ControllerWrapper {
    
    /**
     * 
     * @param \Request $request
     * @param \youconix\core\templating\gui\AdminLogicClass $layout
     * @param \Output $output
     * @param \Headers $headers
     * @param \Logger $logger
     * @param \Language $language
     */
    public function __construct(\Request $request,Layout $layout,\Output $output, \Headers $headers, \Logger $logger, \Language $language)
    {
        parent::__construct($request, $layout, $output, $headers, $logger, $language);
    }
}

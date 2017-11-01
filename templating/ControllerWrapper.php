<?php

namespace youconix\core\templating;

/**
 * @author Rachelle Scheijen
 * @since 2.0
 */
final class ControllerWrapper {
    /**
     *
     * @var \Request
     */
    private $request;
    
    /**
     *
     * @var \Layout 
     */
    private $layout;
    
    /**
     *
     * @var \Output
     */
    private $output;
    
    /**
     *
     * @var \Headers
     */
    private $headers;
    
    /**
     *
     * @var \Logger
     */
    private $logger;
    
    public function __construct(\Request $request,\Layout $layout,\Output $output, \Headers $headers, \Logger $logger)
    {
        $this->request = $request;
        $this->layout  = $layout;
        $this->output = $output;
        $this->headers = $headers;
        $this->logger = $logger;
    }
    
    public function acceptAllRequestInput()
    {
        $this->request->acceptAllInput();
    }
    
    /**
     * 
     * @param array $initGet
     * @param array $initPost
     * @param array $initPut
     * @param array $initDelete
     */
    public function initRequest(array $initGet, array $initPost, array $initPut, array $initDelete)
    {
        $this->request->initGet($initGet);
        $this->request->initPost($initPost);
        $this->request->initPut($initPut);
        $this->request->initDelete($initDelete);
    }
    
    /**
     * 
     * @return \Request
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     * 
     * @return \Layout
     */
    public function getLayout()
    {
        return $this->layout;
    }
    
    /**
     * 
     * @return \Output
     */
    public function getOutput()
    {
        return $this->output;
    }
    
    /**
     * 
     * @return \Headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * 
     * @return \Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }
}

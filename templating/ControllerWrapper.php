<?php

namespace youconix\core\templating;

/**
 * @author Rachelle Scheijen
 * @since 2.0
 */
class ControllerWrapper {
    /**
     *
     * @var \Request
     */
    protected $request;
    
    /**
     *
     * @var \Layout 
     */
    protected $layout;
    
    /**
     *
     * @var \Output
     */
    protected $output;
    
    /**
     *
     * @var \Headers
     */
    protected $headers;
    
    /**
     *
     * @var \Logger
     */
    protected $logger;
    
    /**
     *
     * @var \Language
     */
    protected $language;
    
    /**
     * 
     * @param \Request $request
     * @param \Layout $layout
     * @param \Output $output
     * @param \Headers $headers
     * @param \Logger $logger
     * @param \Language $language
     */
    public function __construct(\Request $request,\Layout $layout,\Output $output, \Headers $headers, \Logger $logger, \Language $language)
    {
        $this->request = $request;
        $this->layout  = $layout;
        $this->output = $output;
        $this->headers = $headers;
        $this->logger = $logger;
        $this->language = $language;
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
    
    /**
     * 
     * @return \Language
     */
    public function getLanguage()
    {
        return $this->language;
    }
}

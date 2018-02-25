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
     * @var \OutputInterface
     */
    protected $output;
    
    /**
     *
     * @var \Headers
     */
    protected $headers;
    
    /**
     *
     * @var \LoggerInterface
     */
    protected $logger;
    
    /**
     *
     * @var \LanguageInterface
     */
    protected $language;
    
    /**
     * 
     * @param \Request $request
     * @param \Layout $layout
     * @param \OutputInterface $output
     * @param \Headers $headers
     * @param \LoggerInterface $logger
     * @param \LanguageInterface $language
     */
    public function __construct(\RequestInterface $request, \Layout $layout, \OutputInterface $output, \HeadersInterface $headers, \LoggerInterface $logger, \LanguageInterface $language)
    {
        $this->request = $request;
        $this->layout  = $layout;
        $this->output = $output;
        $this->headers = $headers;
        $this->logger = $logger;
        $this->language = $language;
    }

    public function __debugInfo()
    {
      return [];
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
     * @return \OutputInterface
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
     * @return \LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }
    
    /**
     * 
     * @return \LanguageInterface
     */
    public function getLanguage()
    {
        return $this->language;
    }
}

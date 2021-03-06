<?php
namespace youconix\Core\Templating;

class Request implements \RequestInterface
{

    /**
     *
     * @var \youconix\core\Input
     */
    protected $input;

    /**
     *
     * @var \youconix\core\services\Headers
     */
    protected $headers;

    /**
     *
     * @var \ConfigInterface
     */
    protected $config;

    /**
     *
     * @var \LoggerInterface
     */
    protected $logs;

    /**
     *
     * @var \youconix\core\services\Session
     */
    protected $session;

    /**
     *
     * @var \youconix\core\Input
     */
    protected $get;

    /**
     *
     * @var \youconix\core\Input
     */
    protected $post;

    /**
     *
     * @var \youconix\core\Input
     */
    protected $put;

    /**
     *
     * @var \youconix\core\Input
     */
    protected $delete;

    /**
     *
     * @param \youconix\Core\Input $input
     * @param \youconix\Core\Services\Headers $headers
     * @param \ConfigInterface $config            
     * @param \LoggerInterface $logs            
     * @param \SessionInterface $session
     */
    public function __construct(\youconix\Core\Input $input, \youconix\Core\Services\Headers $headers, \ConfigInterface $config, \LoggerInterface $logs, \SessionInterface $session)
    {
        $this->headers = $headers;
        $this->config = $config;
        $this->logs = $logs;
        $this->session = $session;
        $this->input = $input;
        
        $this->get = clone $this->input;
        $this->post = clone $this->input;
        $this->put = clone $this->input;
        $this->delete = clone $this->input;
        
        $this->parseRedirect();
    }

    /**
     * Parses the redirect values
     */
    protected function parseRedirect()
    {
        $a_fields = array(
            'get',
            'post',
            'put',
            'delete'
        );
        
        foreach ($a_fields as $s_field) {
            if ($this->session->exists('redirect_' . $s_field)) {
                $this->$s_field->setPrevious(unserialize($this->session->get('redirect_' . $s_field)));
                
                $this->session->delete('redirect_' . $s_field);
            }
        }
    }
    
    public function acceptAllInput()
    {
        $this->get->getAll('GET');
        $this->post->getAll('POST');
        $this->put->getAll('PUT');
        $this->delete->getAll('DELETE');
    }

    /**
     * Inits the GET values
     *
     * @param array $a_initGet
     *            The declarations
     * @return \youconix\core\Input The GET values
     */
    public function initGet($a_initGet)
    {
        $this->get->parse('GET', $a_initGet);
        
        return $this->get();
    }

    /**
     * Returns the GET values
     *
     * @return \youconix\core\Input The GET values
     */
    public function get()
    {
        return $this->get;
    }

    /**
     * Inits the POST values
     *
     * @param array $a_initPost
     *            The declarations
     * @return \youconix\core\Input The POST values
     */
    public function initPost($a_initPost)
    {
        $this->post->parse('POST', $a_initPost);
        
        return $this->post();
    }

    /**
     * Returns the POST values
     *
     * @return \youconix\core\Input The POST values
     */
    public function post()
    {
        return $this->post;
    }

    /**
     * Inits the PUT values
     *
     * @param array $a_initPut
     *            The declarations
     * @return \youconix\core\Input The PUT values
     */
    public function initPut($a_initPut)
    {
        $this->put->parse('PUT', $a_initPut);
        
        return $this->put();
    }

    /**
     * Returns the PUT values
     *
     * @return \youconix\core\Input The PUT values
     */
    public function put()
    {
        return $this->put;
    }

    /**
     * Inits the DELETE values
     *
     * @param array $a_initDelete
     *            The declarations
     * @return \youconix\core\Input The DELETE values
     */
    public function initDelete($a_initDelete)
    {
        $this->delete->parse('DELETE', $a_initDelete);
        
        return $this->delete();
    }

    /**
     * Returns the DELETE values
     *
     * @return \youconix\core\Input The DELETE values
     */
    public function delete()
    {
        return $this->delete;
    }

    /**
     * Redirects to the given location
     *
     * @param string $s_location
     */
    public function redirect($s_location)
    {
        $this->headers->redirect($s_location);
    }

    /**
     * Redirects to the given location while saving the current form values
     *
     * @param string $s_location
     */
    public function redirectWithInput($s_location)
    {
        $this->session->set('redirect_get', serialize($this->get->toArray()));
        $this->session->set('redirect_post', serialize($this->post->toArray()));
        $this->session->set('redirect_put', serialize($this->put->toArray()));
        $this->session->set('redirect_delete', serialize($this->delete->toArray()));
        
        $this->headers->redirect($s_location);
    }

    /**
     *
     * @return \youconix\core\services\Headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     *
     * @return \ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     *
     * @return \LoggerInterface
     */
    public function getLogger()
    {
        return $this->logs;
    }

    /**
     *
     * @return \youconix\core\services\Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     *
     * @return \youconix\core\services\Validation;
     */
    public function getValidation()
    {
        return $this->request->getValidation();
    }
}
<?php

interface RequestInterface
{

    /**
     *
     * @param \youconix\Core\Input $input
     * @param \youconix\Core\Services\Headers $headers
     * @param \ConfigInterface $config            
     * @param \LoggerInterface $logs            
     * @param \SessionInterface $session
     */
    public function __construct(\youconix\Core\Input $input, \youconix\Core\Services\Headers $headers, \ConfigInterface $config, \LoggerInterface $logs, \SessionInterface $session);

    /**
     * Inits the GET values
     *
     * @param array $a_initGet
     *            The declarations
     * @return \youconix\core\Input The GET values
     */
    public function initGet($a_initGet);

    /**
     * Returns the GET values
     *
     * @return \youconix\core\Input The GET values
     */
    public function get();

    /**
     * Inits the POST values
     *
     * @param array $a_initPost
     *            The declarations
     * @return \youconix\core\Input The POST values
     */
    public function initPost($a_initPost);

    /**
     * Returns the POST values
     *
     * @return \youconix\core\Input The POST values
     */
    public function post();

    /**
     * Inits the PUT values
     *
     * @param array $a_initPut
     *            The declarations
     * @return \youconix\core\Input The PUT values
     */
    public function initPut($a_initPut);

    /**
     * Returns the PUT values
     *
     * @return \youconix\core\Input The PUT values
     */
    public function put();

    /**
     * Inits the DELETE values
     *
     * @param array $a_initDelete
     *            The declarations
     * @return \youconix\core\Input The DELETE values
     */
    public function initDelete($a_initDelete);

    /**
     * Returns the DELETE values
     *
     * @return \youconix\core\Input The DELETE values
     */
    public function delete();

    /**
     * Redirects to the given location
     *
     * @param string $s_location
     *            The loczation
     */
    public function redirect($s_location);

    /**
     * Redirects to the given location while saving the current form values
     *
     * @param string $s_location
     *            The loczation
     */
    public function redirectWithInput($s_location);

    /**
     *
     * @return \Headers
     */
    public function getHeaders();

    /**
     *
     * @return \ConfigInterface
     */
    public function getConfig();

    /**
     *
     * @return \LoggerInterface
     */
    public function getLogger();

    /**
     *
     * @return \Session
     */
    public function getSession();

    /**
     *
     * @return \Validation;
     */
    public function getValidation();
}
?>
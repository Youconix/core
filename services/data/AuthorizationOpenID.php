<?php
class AuthorizationOpenID extends \core\services\Service implements \core\interfaces\Authorization {
	private $service_Cookie;
	private $service_Session;
	private $builder;
	private $service_Logs;
	private $model_User;
	
	/**
	 * PHP 5 constructor
	 *
	 * @param \Builder $builder
	 *        	The query builder
	 * @param \Logger $service_Logs
	 *        	The log service
	 *        	@oaram \Session $service_Session The session handler
	 * @param \core\models\User $model_User
	 *        	The user model
	 */
	public function __construct(\Builder $builder, \Logger $service_Logs, \Session $service_Session, \core\models\User $model_User) {
		$this->builder = $builder;
		$this->service_Database = $this->builder->getDatabase ();
		$this->service_Logs = $service_Logs;
		$this->service_Session = $service_Session;
		$this->model_User = $model_User;
	}
	
	/**
	 * Registers the user
	 *
	 * @param array $a_data
	 *        	data
	 * @param bool $bo_skipActivation
	 *        	true to skip sending the activation email (auto activation)
	 * @return bool if the user is registrated
	 */
	public function register($a_data, $bo_skipActivation = false) {
		$obj_openID = $this->getOpenID ( $a_data ['type'] );
		
		/* Temp save data */
		$this->service_Cookie->set ( 'openID', $a_data ['type'], '/' );
		$this->service_Session->set ( 'forname', $a_data ['forname'] );
		$this->service_Session->set ( 'nameBetween', $a_data ['nameBetween'] );
		$this->service_Session->set ( 'surname', $a_data ['surname'] );
		$this->service_Session->set ( 'nationality', $a_data ['nationality'] );
		$this->service_Session->set ( 'telephone', $a_data ['telephone'] );
		
		$this->service_Cookie->set ( 'redirectOpenID', 'registration.php', '/' );
		
		$obj_openID->registration ();
	}
	
	/**
	 * Registers the user trough open ID
	 * User gets auto logged in and redirect to index.php
	 *
	 * @param String $s_code
	 *        	The activation code
	 * @param Boolean $bo_redirect
	 *        	to true for auto redirect to home.php
	 * @return int fault code
	 *         -1 Session timeout or communication error
	 *         0 Username allready taken with openID server
	 *         1 Email adres is taken
	 *         2 Registration complete
	 */
	public function activateUser($s_code) {
		if (! $this->service_Cookie->exists ( 'openID' )) {
			/* Timeout */
			return - 1;
		}
		$s_loginType = $this->service_Cookie->get ( 'openID' );
		$this->service_Cookie->delete ( 'redirectOpenID', '/' );
		
		if (! $this->service_Cookie->exists ( 'openID_username' )) {
			return - 1;
		}
		$s_username = $this->service_Cookie->get ( 'openID_username' );
		$s_email = $this->service_Cookie->get ( 'openID_email' );
		
		/* Check username */
		if (! $this->model_User->checkUsername ( $s_username, - 1, 'OpenID' )) {
			return 0;
		}
		
		/* Check email */
		if (! $this->model_User->checkEmail ( $s_email )) {
			return 1;
		}
		
		$s_forname = $this->service_Session->get ( 'forname' );
		$s_nameBetween = $this->service_Session->get ( 'nameBetween' );
		$s_surname = $this->service_Session->get ( 'surname' );
		
		$obj_User = $this->model_User->createUser ();
		$obj_User->setUsername ( $s_username );
		$obj_User->setName ( $s_forname );
		$obj_User->setNameBetween ( $s_nameBetween );
		$obj_User->setSurname ( $s_surname );
		$obj_User->setEmail ( $s_email );
		$obj_User->enableAccount ();
		$obj_User->setLoginType ( 'OpenID' );
		$obj_User->setBot ( false );
		$obj_User->save ();
		
		/* Auto login */
		$this->service_Session->setLogin ( $obj_User->getID (), $$s_username );
		
		$this->service_Cookie->delete ( 'openID', '/' );
		$this->service_Cookie->delete ( 'openID_username' );
		$this->service_Cookie->delete ( 'openID_email' );
		
		return 2;
	}
	
	/**
	 * Prepares the login
	 *
	 * Only implemented for openID
	 *
	 * @param string $s_server
	 *        	The server address
	 */
	public function loginStart($s_server) {
		$this->service_Cookie->set ( 'openID', 'openID', '/' );
		$this->service_Cookie->set ( 'redirectOpenID', 'login.php', '/' );
	}
	
	/**
	 * Logs the user in
	 *
	 * @param String $s_username        	
	 * @param String $s_password
	 *        	text password
	 * @param Boolean $bo_autologin
	 *        	true for auto login
	 * @return array id, username and password_expired if the login is correct, otherwise null
	 */
	public function login($s_username, $s_password, $bo_autologin = false) {
		if (! $this->service_Cookie->exists ( 'openID' )) {
			/* Timeout */
			return null;
		}
		$s_type = $this->service_Cookie->get ( 'openID' );
		$this->service_Cookie->delete ( 'redirectOpenID', '/' );
		
		$obj_openID = $this->getOpenID ( $s_type );
		$s_username = $obj_openID->loginConfirm ( $s_username );
		
		$this->builder->select ( 'users', 'id,nick,lastLogin,userType' );
		$this->builder->getWhere ()->addAnd ( array (
				'nick',
				'active',
				'blocked',
				'loginType' 
		), array (
				's',
				's',
				's',
				's' 
		), array (
				$s_username,
				'1',
				'0',
				$s_type 
		) );
		
		$service_Database = $this->builder->getResult ();
		if ($service_Database->num_rows () == 0) {
			$this->service_Logs->loginLog ( $s_username, 'failed', - 1, $s_type );
			return null;
		}
		
		$this->service_Logs->loginLog ( $s_username, 'success', - 1, $s_type );
		$a_data = $service_Database->fetch_assoc ();
		
		$this->service_Session->set ( 'openID', 'true' );
		return $a_data [0];
	}
	
	/**
	 * Logs the user out
	 *
	 * @param String $s_url
	 *        	The redirectUrl
	 */
	public function logout($s_url) {
		$this->service_Session->delete ( 'openID' );
		
		header ( 'location: ' . $s_url );
	}
}
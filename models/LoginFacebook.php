<?php
namespace youconix\core\models;
/**
 * Authorisation class for Facebook logins
 *
 * @copyright Youconix
 * @author Roxanna Lugtigheid
 * @since 2.0
 */
class LoginFacebook extends \youconix\core\models\LoginParent {
    /**
     * (non-PHPdoc)
     * @see \youconix\core\models\LoginParent::do_login()
     */
    public function do_login(\youconix\core\models\data\User $user) {
        
    }
    
    /**
     * 
     * @param \youconix\core\models\data\User $user The data of the User in question
     */
    public function register(\youconix\core\models\data\User $user){
        
    }
}
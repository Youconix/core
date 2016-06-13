<?php
namespace youconix\core\helpers\form;

class User extends \youconix\core\helpers\form\FormGenerator {
    protected function init()
    {
        $user = $this->createItem('username', 'username');
        $user->label = $this->language->get('system/admin/users/username');
        $user->error_text = $this->language->get('system/admin/users/js/usernameEmpty');
        
        /*
        $username = clone $this->item;
        $username->setName('username');
        $username->setRequired();
        $username->
        $username->
        $this->a_items['username'] = $username;
        
        $email = clone $this->item;
        $email->setName('email');
        $email->setRequired();
        $email->setLabel($this->language->get('system/admin/users/email'));
        $username->setErrorMessages($this->language->get('system/admin/users/js/emailInvalid'));
        $this->a_items['email'] = $email;
        
        $bot = clone $this->item;
        $bot->setName('bot');
        $bot->setType('checkbox');
        $bot->setDefault(0);
        $bot->setRequired();
        $bot->setLabel($this->language->get('system/admin/users/bot'));
        $this->a_items['bot'] = $bot;
        
        $registrated = clone $this->item;
        $registrated->setName('registrated');
        $registrated->setType('number');
        $registrated->setlabel($this->language->get('system/admin/users/registrated'));
        $this->a_items['registrated'] = $registrated;
        
        $loggedIn = clone $this->item;
        $loggedIn->setName('loggedIn');
        $loggedIn->setType('number');
        $loggedIn->setLabel($this->language->get('system/admin/users/loggedIn'));
        $this->a_items['loggedIn'] = $loggedIn;
        
        $active = clone $this->item;
        $active->setName('active');
        $active->setType('checkbox');
        $active->setDefault(1);
        $active->setLabel($this->language->get('system/admin/users/active'));
        $this->a_items['active'] = $active;
        
        $blocked = clone $this->item;
        $blocked->setName('blocked');
        $blocked->setType('checkbox');
        $blocked->setDefault(0);
        $blocked->setLabel($this->language->get('system/admin/users/blocked'));
        $this->a_items['blocked'] = $blocked;
        
        $passwordExpired = clone $this->item;
        $passwordExpired->setName('passwordExpired');
        $passwordExpired->setType('checkbox');
        $passwordExpired->setDefault(0);
        $passwordExpired->setLabel($this->language->get('system/admin/users/passwordExpired'));
        $this->a_items['passwordExpired'] = $passwordExpired;
        
        $password = clone $this->item;
        $password->setName('password');
        $password->setType('password');
        $password->setRequired();
        $password->setLabel($this->language->get('system/admin/users/password'));
        $this->a_items['password'] = $password;
        
        $password = clone $this->item;
        $password->setName('password_repeat');
        $password->setType('password');
        $password->setRequired();
        $password->setLabel($this->language->get('system/admin/users/passwordAgain'));
        $this->a_items['password_repeat'] = $password;
        
        $bindToIp = clone $this->item;
        $bindToIp->setName('bindToIp');
        $bindToIp->setType('checkbox');
        $bindToIp->setDefault(0);
        $bindToIp->setLabel($this->language->get('system/admin/users/bindToIp'));
        $this->a_items['bindToIp'] = $bindToIp;
         * 
         */
        
        
    }
}
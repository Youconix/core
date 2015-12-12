<?php
namespace core\helpers\form;

class User extends \core\helpers\form\FormGenerator {
    protected function init()
    {
        // username
        $username = clone $this->item;
        $username->setName('username');
        $username->setRequired();
        $username->setLabel($this->language->get('system/admin/users/username'));
        $username->setErrorMessages($this->language->get('system/admin/users/js/usernameEmpty'));
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
        $bot->setDefault(1);
        $bot->setRequired();
        $bot->setLabel($this->language->get('system/admin/users/bot'));
        $this->a_items['bot'] = $bot;
        /*
        
        protected $i_bot = 0;
        
        protected $i_registrated = 0;
        
        protected $i_loggedIn = 0;
        
        protected $i_active = 0;
        
        protected $i_blocked = 0;
        
        protected $i_passwordExpired = 0;
        
        protected $s_password;
        
        protected $s_profile = '';
        
        protected $s_loginType */
    }
}
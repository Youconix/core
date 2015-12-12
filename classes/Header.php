<?php
namespace youconix\core\classes;

/**
 * Site header
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class Header implements \Header
{

    /**
     *
     * @var \Output
     */
    protected $template;

    /**
     *
     * @var \Language
     */
    protected $language;

    /**
     *
     * @var \youconix\core\models\User
     */
    protected $user;

    /**
     *
     * @var \Config
     */
    protected $config;

    /**
     * Starts the class header
     * 
     * @param \Output $template
     * @param \Language $language
     * @param \youconix\core\models\User $user
     * @param \Config $config
     */
    public function __construct(\Output $template, \Language $language, \youconix\core\models\User $user, \Config $config)
    {
        $this->template = $template;
        $this->language = $language;
        $this->user = $user;
        $this->config = $config;
    }

    /**
     * Generates the header
     */
    public function createHeader()
    {
        $this->displayLanguageFlags();
        
        $obj_User = $this->user->get();
        if (is_null($obj_User->getID())) {
            return;
        }
        
        if ($obj_User->isAdmin(GROUP_SITE)) {
            $s_welcome = $this->language->get('system/header/adminWelcome');
        } else {
            $s_welcome = $this->language->get('system/header/userWelcome');
        }
        
        $this->template->set('welcomeHeader', '<a href="{NIV}profile/view/details/id=' . $obj_User->getID() . '" style="color:' . $obj_User->getColor() . '">' . $s_welcome . ' ' . $obj_User->getUsername() . '</a>');
    }

    /**
     * Displays the language change flags
     */
    protected function displayLanguageFlags()
    {
        $a_languages = $this->config->getLanguages();
        $a_languagesCodes = $this->language->getLanguageCodes();
        
        foreach ($a_languages as $s_code) {
            $s_language = (array_key_exists($s_code, $a_languagesCodes)) ? $a_languagesCodes[$s_code] : $s_code;
            
            $this->template->setBlock('headerLanguage', array(
                'code' => $s_code,
                'language' => $s_language
            ));
        }
    }
}
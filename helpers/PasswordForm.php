<?php
namespace youconix\core\helpers;

/**
 * Contains the password form with a password strength test
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 2.0
 */
class PasswordForm extends Helper
{

    /**
     *
     * @var \Language
     */
    protected $language;
    
    /**
     *
     * @var \Config
     */
    protected $config;

    /**
     * PHP 5 constructor
     *
     * @param \Language $language
     * @param \Config $config
     */
    public function __construct(\Language $language,\Config $config)
    {
        $this->language = $language;
	$this->config = $config;
    }

    /**
     * Generates the form
     * 
     * @param \Output $template
     * @return string The form
     */
    public function generate(\Output $template)
    {
        $template->append('head','<script src="/js/widgets/password_check.js"></script>');
        $template->append('head','<script src="/js/validation.js"></script>');
        $template->append('head','<link rel="stylesheet" href="/'.$this->config->getSharedStylesDir().'css/HTML5_validation.css">');
	$template->append('head','<link rel="stylesheet" href="/'.$this->config->getSharedStylesDir().'css/widgets/password_form.css">');
	
      
        $s_passwordError = $this->language->get('widgets/passwordForm/passwordMissing');
        
        $a_language = array(
            'passwordform_invalid' => $this->language->get('widgets/passwordForm/invalid'),
            'passwordform_toShort' => $this->language->get('widgets/passwordForm/toShort'),
            'passwordform_veryStrongPassword' => $this->language->get('widgets/passwordForm/veryStrongPassword'),
            'passwordform_strongPassword' => $this->language->get('widgets/passwordForm/strongPassword'),
            'passwordform_fairPassword' => $this->language->get('widgets/passwordForm/fairPassword'),
            'passwordform_weakPassword' => $this->language->get('widgets/passwordForm/weakPassword')
        );
        
        $s_html = '<section id="passwordForm">
		<fieldset>
		  <label class="label">' . $this->language->get('widgets/passwordForm/password') . ' *</label>
		  <span><input type="password" name="password" id="password1" data-validation="' . $s_passwordError . '" data-validation-pattern="' . $a_language['passwordform_toShort'] . '" pattern=".{8,}" required></span>
		</fieldset>
		<fieldset>
			<label class="label" for="password2">' . $this->language->get('widgets/passwordForm/passwordAgain') . ' *</label>
			<span><input type="password" name="password2" id="password2" data-validation="' . $s_passwordError . '" data-validation-pattern="' . $a_language['passwordform_toShort'] . '" pattern=".{8,}" required></span>			
		</fieldset>
		</section>
		<article id="passwordStrength">
		    <section id="passwordIndicator"></section>
		    <section id="passwordStrengthText"></section>
		</article>
		<script>
		<!--
        $(document).ready(function(){
            validation.bind(["password1","password2"]);
				    
    		passwordCheck = new PasswordCheck();
		    passwordCheck.setLanguage(' . json_encode($a_language) . ');
    		passwordCheck.init();
    	});
		//-->
		</script>';
        
        return $s_html;
    }
}
?>

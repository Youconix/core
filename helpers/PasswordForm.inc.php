<?php
namespace core\helpers;

/**
 * Miniature-happiness is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Miniature-happiness is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Miniature-happiness. If not, see <http://www.gnu.org/licenses/>.
 *
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

    private $language;

    /**
     * PHP 5 constructor 
     * 
     * @param \Language $language     The language service
     * @param \Output $template     The template service
     */
    public function __construct(\Language $language, \Output $template)
    {
        $this->language = $language;
        
        $s_link = '<script src="{NIV}js/widgets/password_check.js"></script>';
        $template->setJavascriptLink($s_link);
        $s_link = '<script src="{NIV}js/validation.js"></script>';
        $template->setJavascriptLink($s_link);
        $s_link = '<link rel="stylesheet" href="{NIV}{shared_style_dir}css/HTML5_validation.css">';
        $template->setCssLink($s_link);
    }

    /**
     * Generates the form
     * 
     * @return string   The form
     */
    public function generate()
    {
        $s_passwordError = $this->language->get('widgets/passwordForm/passwordMissing');
        
        $a_language = array(
            'passwordform_invalid' => $this->language->get('widgets/passwordForm/invalid'),
            'passwordform_toShort' => $this->language->get('widgets/passwordForm/toShort'),
            'passwordform_veryStrongPassword' =>  $this->language->get('widgets/passwordForm/veryStrongPassword'),
            'passwordform_strongPassword' => $this->language->get('widgets/passwordForm/strongPassword'),
            'passwordform_fairPassword' => $this->language->get('widgets/passwordForm/fairPassword'),
            'passwordform_weakPassword' => $this->language->get('widgets/passwordForm/weakPassword')
        );
        
        $s_html = '<section id="passwordForm">
		<fieldset>
				<label class="label">' . $this->language->get('widgets/passwordForm/password') . '</label>
				<span><input type="password" name="password" id="password1" data-validation="'.$s_passwordError.'" data-validation-pattern="'.$a_language['passwordform_toShort'].'" pattern=".{8,}" required></span>
		</fieldset>
        <fieldset>
			<label class="label" for="password2">' . $this->language->get('widgets/passwordForm/passwordAgain') . '</label>
			<span><input type="password" name="password2" id="password2" data-validation="'.$s_passwordError.'" data-validation-pattern="'.$a_language['passwordform_toShort'].'" pattern=".{8,}" required></span>			
		</fieldset>
		</section>
        <article id="passwordStrength">
		  <section id="passwordIndicator">
		  </section>
				        <section id="passwordStrengthText">
				    
				        </section>
				</article>
		<style>
		<!--
		#passwordIndicator div {	height:12px; width:12px; float:left; margin-right:2px; }
        #passwordStrength { min-width:250px; max-width:300px; width:auto; padding:4px; border:1px solid #ece9e9;
		   border-left:1px solid #dedbdb; border-top:1px solid #dedbdb;
	       background-color:#FFF;
	       color:#111;
	       ms-border-radius:5px;
	       webkit-border-radius:5px;
	       border-radius:5px;
	       moz-box-shadow: 10px 10px 5px #888888;
	       webkit-box-shadow: 10px 10px 5px #888888;
	       ms-box-shadow: 10px 10px 5px #888888;
	       box-shadow: 10px 10px 5px #888888;
           display:none;
		   position:absolute;
        }
        #passwordIndicator { width:99.99%; height:15px; }  
		.passwordRed	{ background-color:red; }	
		.passwordYellow { background-color:yellow; }
		.passwordGreen {	background-color:green; }
		//-->
		</style>
		<script>
		<!--
        $(document).ready(function(){
            validation.bind(["password1","password2"]);
				    
    		passwordCheck = new PasswordCheck();
		    passwordCheck.setLanguage('.json_encode($a_language).');
    		passwordCheck.init();
    	});
		//-->
		</script>';
        
        return $s_html;
    }
}
?>

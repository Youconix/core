<?php
namespace\core\helpers;

class OpenAuthLogin extends Helper
{

    private $service_Security;

    private $service_Language;

    private $service_Template;

    private $service_Builder;

    private $a_settings = array();

    /**
     * PHP 5 constructor
     *
     * @param \core\services\Security $service_Security
     *            The security handler
     * @param \core\services\Language $service_Language
     *            The language service
     * @param \core\services\Template $service_Template
     *            The template service
     * @param \Builder $service_Builder
     *            The query builder
     */
    public function __construct(\core\services\Security $service_Security, \core\services\Language $service_Language, \core\services\Template $service_Template, \Builder $service_Builder)
    {
        $this->service_Language = $service_Language;
        $this->service_Template = $service_Template;
        $this->service_Security = $service_Security;
        $this->service_Builder = $service_Builder();
        
        $this->loadSettings();
    }

    private function loadSettings()
    {
        $this->service_Builder->select('openID', '*')
            ->getWhere()
            ->addAnd('active', 's', '1');
        $service_Database = $this->service_Builder->getResult();
        if ($service_Database->num_rows() > 0) {
            $this->a_settings = $service_Database->fetch_assoc_key('name');
        }
    }

    public function editScreen()
    {
        $s_form = '<section>
      <script>
      <!--
      function openIDSave(id){
       alert(id);
      }
      //-->
      </script>

      <h2>Open ID settings</h2>

      <table>
      <thead>
      <tr>
        <td>Active</td>
        <td>Type</td>
        <td>Settings seperated with a ,</td>
      </tr>
      </thead>
      <tbody>
      ';
        
        $i = 1;
        foreach ($this->a_settings as $a_setting) {
            ($a_setting['active'] == 1) ? $s_checked = 'checked="checked"' : $s_checked = '';
            
            $s_form .= '<tr>
       <td><input type="checkbox" id="openid_' . $i . '" data-name="' . $a_setting['name'] . '" ' . $s_checked . ' onclick="openIDSave(' . $i . ')"></td>
       <td><label>' . $a_setting['name'] . '</label></td>       
       <td><input type="text" id="openid_settings_' . $i . '" value="' . $a_setting['settings'] . '" onblur="openIDSave(' . $i . ')"></td> 
     </tr>';
            
            $i ++;
        }
        
        $s_form . '</tbody>
      </table>
    </section>';
        
        return $s_form;
    }

    public function edit($s_name, $s_active, $s_settings)
    {
        \Memory::type('string', $s_name);
        \Memory::type('string', $s_active);
        \Memory::type('string', $s_settings);
        
        if (! in_array($s_active, array(
            '0',
            '1'
        ))) {
            $s_active = '0';
        }
        
        $this->service_Builder->update('openID', array(
            'active',
            'settings'
        ), array(
            's',
            's'
        ), array(
            $s_active,
            $s_settings
        ))
            ->getWhere()
            ->addAnd('name', 's', $s_name);
        $this->service_Builder->getResult();
    }

    public function formRegistration()
    {
        return $this->form('Registrate with', 'registration');
    }

    public function formLogin()
    {
        return $this->form('Login with', 'login');
    }

    private function form($s_text, $s_action)
    {
        $s_form = '<section>
     <script>
     <!--
     function checkHeader(url){
      if ($(\'script[src*="\'+url+\'"]\').length === 0 ){
        $("head").append(\'<script src="\'+url+\'"></script>\');
      }
     }
     //-->
    </script>
     ';
        
        $i_active = 0;
        foreach ($this->a_settings as $a_setting) {
            if ($a_setting['active'] == '0') {
                continue;
            }
            
            $i_active ++;
            
            switch (strtolower($a_setting['name'])) {
                case 'facebook':
                    $s_form .= $this->formFacebook($a_setting, $s_text, $s_action);
                    break;
                
                default:
                    $i_active --;
                    break;
            }
        }
        
        $s_form .= '</section>';
        
        if ($i_active == 0) {
            return '';
        }
        return $s_form;
    }

    private function formFacebook($a_setting, $s_text, $s_action)
    {
        $a_settings = $this->getSettingsArray($a_setting['settings']);
        $a_settings['action'] = $s_action;
        $a_settings['channelUrl'] = 'https://' . $_SERVER['HTTP_HOST'] . '/' . \core\Memory::getBase() . '/openID/channelFacebook.php';
        $a_settings['locale'] = $this->service_Language->get('locale');
        if (! array_key_exists('loggedin', $a_settings)) {
            $a_settings['loggedin'] = array();
        }
        if (! array_key_exists('loggedout', $a_settings)) {
            $a_settings['loggedout'] = array();
        }
        if (! array_key_exists('unautorized', $a_settings)) {
            $a_settings['unautorized'] = array();
        }
        
        if ($s_action == 'login') {
            $a_settings['loggedin'][] = 'this.setLogin(response.authResponse.accessToken,response.authResponse.userID)';
        } else {
            $a_settings['loggedin'][] = 'this.setLogin(response.authResponse.accessToken,response.authResponse.userID)';
        }
        
        $a_settings['loggedin'] = implode(";\n", $a_settings['loggedin']);
        $a_settings['loggedout'] = implode("\n", $a_settings['loggedout']);
        $a_settings['unautorized'] = implode("\n", $a_settings['unautorized']);
        
        $s_facebook = '<script><!-- if( typeof facebook_settings === "undefined" ){ facebook_settings = ' . json_encode($a_settings) . ';  checkHeader("' . LEVEL . 'js/openid/facebook.js"); } --></script>
    <img src="{style_dir}images/openid/facebook.png" alt="' . $s_text . ' ' . $a_setting['name'] . '" title="' . $s_text . ' ' . $a_setting['name'] . '" onclick="facebook.' . $s_action . '();"> ';
        
        return $s_facebook;
    }

    private function getSettingsArray($s_settings)
    {
        $a_settingsRaw = explode(',', $s_settings);
        $a_settings = array();
        foreach ($a_settingsRaw as $s_item) {
            $a_item = explode('=', $s_item);
            if (count($a_item) == 1) {
                $a_settings[$a_item[0]] = '';
            } else {
                $a_settings[$a_item[0]] = $a_item[1];
            }
        }
        
        return $a_settings;
    }
}
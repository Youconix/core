<?php
namespace youconix\Core\Services;

/**
 * Mailer service
 * Wraps the class PHPMailer (GPL)
 *
 * @author Rachelle Scheijen
 * @since 1.0
 */
class Mailer extends AbstractService
{

    /**
     *
     * @var \Mailer
     */
    protected $obj_phpMailer;

    /**
     *
     * @var \LanguageInterface
     */
    protected $language;

    protected $s_language;

    protected $service_File;

    protected $s_domain;

    protected $s_domainUrl;

    /**
     * Inits the class Mailer
     *
     * @param \LanguageInterface $language
     * @param \youconix\Core\Services\File $service_File
     * @param \ConfigInterface $config
     */
    public function __construct(\LanguageInterface $language, \youconix\Core\Services\File $service_File, \ConfigInterface $config, \MailerInterface $mailer)
    {
        $this->obj_phpMailer = $mailer;
        
        $this->language = $language;
        $this->s_language = $this->language->getLanguage();
        $this->service_File = $service_File;
        
        $this->s_domain = $_SERVER['HTTP_HOST'];
        $this->s_domainUrl = $config->getProtocol() . $this->s_domain . $config->getBase();
    }

    /**
     * Returns the PHPMailer
     *
     * @param boolean $bo_html
     *            true for html mail, default true
     * @return PHPMailer mailer
     */
    public function getMailer($bo_html = true)
    {
        $this->obj_phpMailer->clearAll();
        
        $this->obj_phpMailer->useHTML($bo_html);
        
        return $this->obj_phpMailer;
    }

    /**
     * Sends the registration activation email
     *
     * @param string $s_username
     *            username
     * @param string $s_email
     *            email address
     * @param string $s_url
     *            activation url
     * @return boolean if the email is send
     */
    public function registrationMail($s_username, $s_email, $s_url)
    {
        $a_mail = $this->getMail('registration');
        $s_body = $this->language->insert($a_mail['body'], array(
            'username',
            'url'
        ), array(
            $s_username,
            $s_url
        ));
        $s_bodyAlt = $this->language->insert($a_mail['bodyAlt'], array(
            'username',
            'url'
        ), array(
            $s_username,
            $s_url
        ));
        
        $obj_mailer = $this->getMailer();
        $obj_mailer->addAddress($s_email, $s_username);
        $obj_mailer->setSubject($a_mail['subject']);
        $obj_mailer->setBody($s_body);
        $obj_mailer->setAltBody($s_bodyAlt);
        
        return $this->sendMail($obj_mailer);
    }

    /**
     * Sends the registration confirm email triggerd by a admin
     *
     * @param string $s_username
     *            username
     * @param string $s_password
     *            plain text password
     * @param string $s_email
     *            email address
     * @return boolean if the email is send
     */
    public function adminAdd($s_username, $s_password, $s_email)
    {
        $a_mail = $this->getMail('registrationAdmin');
        $s_body = $this->language->insert($a_mail['body'], array(
            'username',
            'password'
        ), array(
            $s_username,
            $s_password
        ));
        $s_bodyAlt = $this->language->insert($a_mail['bodyAlt'], array(
            'username',
            'password'
        ), array(
            $s_username,
            $s_password
        ));
        
        $obj_mailer = $this->getMailer();
        $obj_mailer->addAddress($s_email, $s_username);
        $obj_mailer->setSubject($a_mail['subject']);
        $obj_mailer->setBody($s_body);
        $obj_mailer->setAltBody($s_bodyAlt);
        
        return $this->sendMail($obj_mailer);
    }

    /**
     * Sends the password reset email
     *
     * @param string $s_username
     *            username
     * @param string $s_email
     *            email address
     * @param string $s_newPassword
     *            new plain text password
     * @param string $s_hash
     *            reset confirm code
     * @param string $s_expire
     * @return boolean if the email is send
     */
    public function passwordResetMail($s_username, $s_email, $s_newPassword, $s_url,$s_expire)
    {
        $a_mail = $this->getMail('passwordReset');
        $s_body = $this->language->insert($a_mail['body'], array(
            'username',
            'password',
            'url',
	    'expire'
        ), array(
            $s_username,
            $s_newPassword,
            $s_url,
	    $s_expire
        ));
        $s_bodyAlt = $this->language->insert($a_mail['bodyAlt'], array(
            'username',
            'password',
            'url',
	    'expire'
        ), array(
            $s_username,
            $s_newPassword,
            $s_url,
	    $s_expire
        ));
        
        $obj_mailer = $this->getMailer();
        $obj_mailer->addAddress($s_email, $s_username);
        $obj_mailer->setSubject($a_mail['subject']);
        $obj_mailer->setBody($s_body);
        $obj_mailer->setAltBody($s_bodyAlt);
        
        return $this->sendMail($obj_mailer);
    }

    /**
     * Sends the password reset email triggerd by a admin
     *
     * @param string $s_username
     *            username
     * @param string $s_email
     *            email address
     * @param string $s_newPassword
     *            new plain text password
     * @return boolean if the email is send
     */
    public function adminPasswordReset($s_username, $s_email, $s_newPassword)
    {
        $a_mail = $this->getMail('passwordResetAdmin');
        $s_body = $this->language->insert($a_mail['body'], array(
            'username',
            'password'
        ), array(
            $s_username,
            $s_newPassword
        ));
        $s_bodyAlt = $this->language->insert($a_mail['bodyAlt'], array(
            'username',
            'password'
        ), array(
            $s_username,
            $s_newPassword
        ));
        
        $obj_mailer = $this->getMailer();
        $obj_mailer->addAddress($s_email, $s_username);
        $obj_mailer->setSubject($a_mail['subject']);
        $obj_mailer->setBody($s_body);
        $obj_mailer->setAltBody($s_bodyAlt);
        
        return $this->sendMail($obj_mailer);
    }

    /**
     * Sends the account disable notification email
     *
     * @param string $s_username
     *            username
     * @param string $s_email
     *            email address
     * @return boolean if the email is send
     */
    public function accountDisableMail($s_username, $s_email)
    {
        $a_mail = $this->getMail('accountDisabled');
        $s_body = $this->language->insert($a_mail['body'], array(
            'username'
        ), array(
            $s_username
        ));
        $s_bodyAlt = $this->language->insert($a_mail['bodyAlt'], array(
            'username'
        ), array(
            $s_username
        ));
        
        $obj_mailer = $this->getMailer();
        $obj_mailer->addAddress($s_email, $s_username);
        $obj_mailer->setSubject($a_mail['subject']);
        $obj_mailer->setBody($s_body);
        $obj_mailer->setAltBody($s_bodyAlt);
        
        return $this->sendMail($obj_mailer);
    }

    /**
     * Sends the personal message notification email
     *
     * @param \youconix\core\models\data\User $obj_receiver
     *            The receiver
     * @return boolean True if the email is send
     */
    public function PM(\youconix\core\models\data\User $obj_receiver)
    {
        $s_email = $obj_receiver->getEmail();
        $s_username = $obj_receiver->getUsername();
        
        $a_mail = $this->getMail('PM');
        $s_body = $this->language->insert($a_mail['body'], array(
            'username'
        ), array(
            $s_username
        ));
        $s_bodyAlt = $this->language->insert($a_mail['bodyAlt'], array(
            'username'
        ), array(
            $s_username
        ));
        
        $obj_mailer = $this->getMailer();
        $obj_mailer->addAddress($s_email, $s_username);
        $obj_mailer->setSubject($a_mail['subject']);
        $obj_mailer->setBody($s_body);
        $obj_mailer->setAltBody($s_bodyAlt);
        
        return $this->sendMail($obj_mailer);
    }

    /**
     * Sends the log alert email to the administrator
     *
     * @param string $s_message
     *            The log message
     * @param array $a_address
     *            The name and emailaddress
     * @param string $s_domain
     *            The domain
     * @return boolean True if the email is send
     */
    public function logDeamon($s_message, $a_address, $s_domain)
    {
        $s_email = $a_address['email'];
        $s_name = $a_address['name'];
        
        $a_mail = $this->getMail('log');
        $s_body = $this->language->insert($a_mail['body'], array(
            'name',
            'message',
            'domain'
        ), array(
            $s_name,
            nl2br($s_message),
            $s_domain
        ));
        $s_bodyAlt = $this->language->insert($a_mail['bodyAlt'], array(
            'name',
            'message',
            'domain'
        ), array(
            $s_name,
            $s_message,
            $s_domain
        ));
        
        $obj_mailer = $this->getMailer();
        $obj_mailer->addAddress($s_email, $s_name);
        $obj_mailer->setSubject($a_mail['subject']);
        $obj_mailer->setBody($s_body);
        $obj_mailer->setAltBody($s_bodyAlt);
        
        return $this->sendMail($obj_mailer);
    }

    /**
     * Collects the email template
     *
     * @param string $s_code
     *            template code
     * @param string $s_language
     *            The language, optional
     * @throws \Exception the template does not exist
     * @return array templates
     */
    protected function getMail($s_code, $s_language = '')
    {
        if (empty($s_language))
            $s_language = $this->s_language;
        
        if (! $this->service_File->exists(WEBSITE_ROOT . 'emails/' . $s_language . '/' . $s_code . '.tpl')) {
            throw new \Exception("Can not find the email template " . $s_code . " for language " . $this->s_language);
        }
        
        $a_file = explode('<==========>', $this->service_File->readFile(WEBSITE_ROOT . 'emails/' . $s_language . '/' . $s_code . '.tpl'));
        $a_filePlain = explode('<==========>', $this->service_File->readFile(WEBSITE_ROOT . 'emails/' . $s_language . '/' . $s_code . '_plain.tpl'));
        
        $s_htmlBody = $this->service_File->readFile(WEBSITE_ROOT . 'emails/main.tpl');
        $s_plainBody = $this->service_File->readFile(WEBSITE_ROOT . 'emails/main_plain.tpl');
        $a_filePlain[1] = str_replace('[content]', $a_filePlain[1], $s_plainBody);
        $a_file[1] = str_replace('[content]', $a_file[1], $s_htmlBody);
        
        $a_file[0] = $this->setMainMail($a_file[0]);
        $a_file[1] = $this->setMainMail($a_file[1]);
        $a_filePlain[1] = $this->setMainMail($a_filePlain[1]);
        
        return array(
            'subject' => trim($a_file[0]),
            'body' => $a_file[1],
            'bodyAlt' => $a_filePlain[1]
        );
    }

    /**
     * Sets the main email data
     *
     * @param string $s_mail
     *            email
     * @return string processed email
     */
    protected function setMainMail($s_mail)
    {
        $s_domain = $this->s_domain;
        $s_domainUrl = $this->s_domainUrl;
        
        $s_mail = $this->language->insert($s_mail, array(
            'domain',
            'domainUrl',
	    'subject'
        ), array(
            $s_domain,
            $s_domainUrl,
	    $s_domain
        ));
        
        return trim($s_mail);
    }

    /**
     * Sends the email if testing mode is not active
     *
     * @param PHPMailer $obj_mailer
     *            mailer
     */
    protected function sendMail($obj_mailer)
    {
        return $obj_mailer->send();
    }
}
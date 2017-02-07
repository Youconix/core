<?php
namespace youconix\core\services;

/**
 * Mailer service
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class Mailer extends Service implements \Mailer
{

    /**
     *
     * @var \MailerLib
     */
    protected $obj_phpMailer;

    /**
     *
     * @var \Language
     */
    protected $language;

    protected $s_language;

    /**
     *
     * @var \youconix\core\services\FileHandler
     */
    protected $file;

    protected $s_domain;

    protected $s_domainUrl;

    /**
     * Inits the class Mailer
     *
     * @param \Language $language
     * @param \youconix\core\services\FileHandler $file
     * @param \Config $config
     * @param \MailerLib $mailer
     */
    public function __construct(\Language $language, \youconix\core\services\FileHandler $file, \Config $config, \MailerLib $mailer)
    {
        $this->obj_phpMailer = $mailer;
        
        $this->language = $language;
        $this->s_language = $this->language->getLanguage();
        $this->file = $file;
        
        $this->s_domain = $_SERVER['HTTP_HOST'];
        $this->s_domainUrl = $config->getProtocol() . $this->s_domain . $config->getBase();
    }

    /**
     * Returns the PHPMailer
     *
     * @param boolean $bo_html
     *            true for html mail, default true
     * @return \MailerLib mailer
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
     * @param string $s_email
     * @param string $s_activationUrl
     * @return boolean if the email is send
     */
    public function registrationMail($s_username, $s_email, $s_activationUrl)
    {
        $mail = $this->getMail('registration');
        $mail->set('username',$s_username);
        $mail->set('url',$s_activationUrl);
        $mail->render();

        $obj_mailer = $this->getMailer();
        $obj_mailer->addAddress($s_email, $s_username);
        $obj_mailer->setSubject($mail->getSubject() );
        $obj_mailer->setBody($mail->getText());
        $obj_mailer->setAltBody($mail->getAltText());
        
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
        $mail = $this->getMail('registrationAdmin');
        $mail->set('username',$s_username);
        $mail->set('password',$s_password);
        $mail->render();
        
        $obj_mailer = $this->getMailer();
        $obj_mailer->addAddress($s_email, $s_username);
        $obj_mailer->setSubject($mail->getSubject());
        $obj_mailer->setBody($mail->getText());
        $obj_mailer->setAltBody($mail->getAltText());
        
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
     * @param string $s_url
     * @param string $s_expire
     * @return boolean if the email is send
     */
    public function passwordResetMail($s_username, $s_email, $s_newPassword, $s_url,$s_expire)
    {
        $mail = $this->getMail('passwordReset');
        $mail->set('username',$s_username);
        $mail->set('password',$s_newPassword);
        $mail->set('url',$s_url);
        $mail->set('expire',$s_expire);
        $mail->render();
        
        $obj_mailer = $this->getMailer();
        $obj_mailer->addAddress($s_email, $s_username);
        $obj_mailer->setSubject($mail->getSubject());
        $obj_mailer->setBody($mail->getText());
        $obj_mailer->setAltBody($mail->getAltText());
        
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
        $mail = $this->getMail('passwordResetAdmin');
        $mail->set('username',$s_username);
        $mail->set('password',$s_newPassword);
        $mail->render();
        
        $obj_mailer = $this->getMailer();
        $obj_mailer->addAddress($s_email, $s_username);
        $obj_mailer->setSubject($mail->getSubject());
        $obj_mailer->setBody($mail->getText());
        $obj_mailer->setAltBody($mail->getAltText());
        
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
        $mail = $this->getMail('accountDisabled');
        $mail->set('username',$s_username);
        $mail->render();
        
        $obj_mailer = $this->getMailer();
        $obj_mailer->addAddress($s_email, $s_username);
        $obj_mailer->setSubject($mail->getSubject());
        $obj_mailer->setBody($mail->getText());
        $obj_mailer->setAltBody($mail->getAltText());
        
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
        
        $mail = $this->getMail('PM');
        $mail->set('username',$s_username);
        $mail->render();
        
        $obj_mailer = $this->getMailer();
        $obj_mailer->addAddress($s_email, $s_username);
        $obj_mailer->setSubject($mail->getSubject());
        $obj_mailer->setBody($mail->getText());
        $obj_mailer->setAltBody($mail->getAltText());
        
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
        
        $mail = $this->getMail('log');
        $mail->set('name',$s_name);
        $mail->set('message',nl2br($s_message));
        $mail->set('domain',$s_domain);
        $mail->render();
        
        $obj_mailer = $this->getMailer();
        $obj_mailer->addAddress($s_email, $s_name);
        $obj_mailer->setSubject($mail->getSubject());
        $obj_mailer->setBody($mail->getText());
        $obj_mailer->setAltBody($mail->getAltText());
        
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
     * @return \MailMessage
     */
    protected function getMail($s_code, $s_language = '')
    {
        if (empty($s_language) ){
            $s_language = $this->s_language;
        }

        $message = new MailMessage();
        $message->loadEmail($this->file, $s_language, $s_code);
        
        $message->set('domain', $this->s_domain);
        $message->set('domainUrl',$this->s_domainUrl);

        return $message;
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

class MailMessage implements \MailMessage {
  /**
   *
   * @var \youconix\core\services\FileHandler
   */
  protected $file;

  protected $a_entries = [];

  protected $s_text;
  protected $s_altText;
  protected $s_subject;

  /**
   * Loads the email
   *
   * @param \youconix\core\services\FileHandler $file
   * @param string $s_language
   * @param string $s_code
   * @throws \Exception the template does not exist
   */
  public function loadEmail(\youconix\core\services\FileHandler $file,$s_language,$s_code){
    $this->file = $file;

    if (! $this->file->exists(WEBSITE_ROOT . 'emails/' . $s_language . '/' . $s_code . '.tpl')) {
      throw new \Exception("Can not find the email template " . $s_code . " for language " . $s_language);
    }

    $a_file = explode('<==========>', $this->file->readFile(WEBSITE_ROOT . 'emails/' . $s_language . '/' . $s_code . '.tpl'));
    $a_filePlain = explode('<==========>', $this->file->readFile(WEBSITE_ROOT . 'emails/' . $s_language . '/' . $s_code . '_plain.tpl'));

    $s_htmlBody = $this->file->readFile(WEBSITE_ROOT . 'emails/main.tpl');
    $s_plainBody = $this->file->readFile(WEBSITE_ROOT . 'emails/main_plain.tpl');
    $a_filePlain[1] = str_replace('[content]', $a_filePlain[1], $s_plainBody);
    $a_file[1] = str_replace('[content]', $a_file[1], $s_htmlBody);

    $this->s_subject = trim($a_file[0]);
    $this->s_text = $a_file[1];
    $this->s_altText = $a_filePlain[1];
  }

  /**
   * Adds text to the email
   *
   * @param string $s_key
   * @param string $s_text
   */
  public function set($s_key,$s_text){
    $this->a_entries[$s_key] = $s_text;
  }

  /**
   * Renders the emails
   */
  public function render(){
    foreach($this->a_entries AS $s_key => $s_value){
      $this->s_text = str_replace('['.$s_key.']',$s_value,$this->s_text);
      $this->s_altText = str_replace('['.$s_key.']',$s_value,$this->s_altText);
    }
    $this->s_text = trim($this->s_text);
    $this->s_altText = trim($this->s_altText);
  }

  /**
   *
   * @return string
   */
  public function getSubject(){
    return $this->s_subject;
  }

  /**
   *
   * @return string
   */
  public function getText(){
    return $this->s_text;
  }

  /**
   *
   * @return string
   */
  public function getAltText(){
    return $this->s_altText;
  }
}
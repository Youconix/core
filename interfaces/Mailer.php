<?php

interface Mailer
{

    /**
     * Returns the PHPMailer
     *
     * @param boolean $bo_html
     *            true for html mail, default true
     * @return \MailerLib mailer
     */
    public function getMailer($bo_html = true);

    /**
     * Sends the registration activation email
     *
     * @param string $s_username
     * @param string $s_email
     * @param string $s_activationUrl
     * @return boolean if the email is send
     */
    public function registrationMail($s_username, $s_email, $s_activationUrl);

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
    public function adminAdd($s_username, $s_password, $s_email);

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
    public function passwordResetMail($s_username, $s_email, $s_newPassword, $s_url,$s_expire);

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
    public function adminPasswordReset($s_username, $s_email, $s_newPassword);

    /**
     * Sends the account disable notification email
     *
     * @param string $s_username
     *            username
     * @param string $s_email
     *            email address
     * @return boolean if the email is send
     */
    public function accountDisableMail($s_username, $s_email);

    /**
     * Sends the personal message notification email
     *
     * @param \youconix\core\models\data\User $obj_receiver
     *            The receiver
     * @return boolean True if the email is send
     */
    public function PM(\youconix\core\models\data\User $obj_receiver);

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
    public function logDeamon($s_message, $a_address, $s_domain);
}

interface MailMessage {
  /**
   * Loads the email
   *
   * @param \youconix\core\services\FileHandler $file
   * @param string $s_language
   * @param string $s_code
   * @throws \Exception the template does not exist
   */
  public function loadEmail(\youconix\core\services\FileHandler $file, $s_language,$s_code);

  /**
   * Adds text to the email
   *
   * @param string $s_key
   * @param string $s_text
   */
  public function set($s_key,$s_text);

  /**
   * Renders the emails
   */
  public function render();

  /**
   *
   * @return string
   */
  public function getSubject();

  /**
   *
   * @return string
   */
  public function getText();

  /**
   *
   * @return string
   */
  public function getAltText();
}
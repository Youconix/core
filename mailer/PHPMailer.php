<?php

namespace youconix\core\mailer;

class PHPMailer extends \youconix\core\services\Service implements \MailerLib
{
  private $obj_phpMailer;

  /**
   * Initializes the wrapper
   * 
   * @param \Settings	$settings
   */
  public function __construct(\Settings $settings)
  {
    require_once(NIV.'vendor'.DS.'phpmailer'.DS.'phpmailer'.DS.'PHPMailerAutoload.php');

    $this->obj_phpMailer = new \PHPMailer();

    $sender = $settings->get('mail/senderName').' <'.$settings->get('mail/senderEmail').'>';
    $this->obj_phpMailer->Sender = $sender;

    if ($settings->get('mail/SMTP') == '1') {
      $this->obj_phpMailer->SMTPAuth = true;
      $this->obj_phpMailer->Username = $settings->get('mail/username');
      $this->obj_phpMailer->Password = $settings->get('mail/password');
      $this->obj_phpMailer->Host = $settings->get('mail/host');
      $this->obj_phpMailer->Port = $settings->get('mail/port');
      $this->obj_phpMailer->Timeout = $settings->get('mail/timeout');

      if ($settings->get('mail/ssl') == '1') {
        $this->obj_phpMailer->SMTPSecure = 'tls';
      }
    }
  }

  /**
   * Checks the SMTP details
   *
   * @param string $s_host        The host name
   * @param int $i_port           The port number
   * @param string $s_username    The username
   * @param string $s_password    The password
   * @return booleanean      True if the connection is valid
   * @throws LogicException If the arguments are invalid
   */
  public function checkSmtpDetails($s_host, $i_port, $s_username, $s_password)
  {
    \youconix\core\Memory::type('string', $s_host, true);
    \youconix\core\Memory::type('int', $i_port, true);
    \youconix\core\Memory::type('string', $s_username, true);
    \youconix\core\Memory::type('password', $s_password, true);

    if ($i_port < 1) {
      throw new \RangeException('SMTP port number must be a whole number greater then 0.');
    }

    $obj_SMTP = new SMTP();

    if (!$obj_SMTP->Connect($s_host, $i_port)) {
      return false;
    }
    return $obj_SMTP->Authenticate($s_username, $s_password);
  }
  // ///////////////////////////////////////////////
  // MESSAGE FUNCTIONS
  // ///////////////////////////////////////////////

  /**
   * Sets the Subject of the message.
   *
   * @param string $s_subject
   * @throws LogicException If the subject is invalid
   */
  public function setSubject($s_subject)
  {
    \youconix\core\Memory::type('string', $s_subject, true);

    $this->obj_phpMailer->Subject = $s_subject;
  }

  /**
   * Sets the Body of the message.
   * This can be either an HTML or text body.
   * If HTML then run useHTML(true).
   *
   * @param
   *            string	The body
   * @throws LogicException If the body is invalid
   */
  public function setBody($s_body)
  {
    \youconix\core\Memory::type('string', $s_body, true);

    $this->obj_phpMailer->Body = $s_body;
  }

  /**
   * Sets the text-only body of the message.
   * This automatically sets the
   * email to multipart/alternative. This body can be read by mail
   * clients that do not have HTML email capability such as mutt. Clients
   * that can read HTML will view the normal Body.
   *
   * @param string $s_body
   *            body
   * @throws LogicException If the body is invalid
   */
  public function setAltBody($s_body)
  {
    \youconix\core\Memory::type('string', $s_body, true);

    $this->obj_phpMailer->AltBody = $s_body;

    $this->setContentType('multipart/alternative');
  }

  /**
   * Sets word wrapping on the body of the message to a given number of
   * characters.
   * This value is default 0
   *
   * @param int $i_wrap
   *            limit
   * @throws LogicException If the wrap is invalid   
   */
  public function setWordWrap($i_wrap)
  {
    \youconix\core\Memory::type('int', $i_wrap);
    if ($i_wrap < 0) {
      throw new InvalidArgumentException('Wrap must be a whole number greater of equal to 0.');
    }

    $this->obj_phpMailer->WordWrap = $i_wrap;
  }

  // ///////////////////////////////////////////////
  // MAILER SETTINGS
  // ///////////////////////////////////////////////
  /**
   * Sets the email priority
   * This value is default 3
   *
   * @param int $i_priority
   *            priority (1 = High, 3 = Normal, 5 = low)
   * @throws \InvalidArgumentException If the priority is invalid
   */
  public function setPriority($i_priority)
  {
    if (!in_array($i_priority,
            [
            1,
            3,
            5
        ])) {
      throw new \InvalidArgumentException('Setting illegal priority '.$i_priority.'. Only 1, 3 and 5 are allowed.');
    }

    $this->obj_phpMailer->Priority = $i_priority;
  }

  /**
   * Sets the CharSet of the message.
   * This value is default iso-8859-1
   *
   * @param string $s_charset
   * @throws LogicException If the charset is invalid
   */
  public function setCharset($s_charset)
  {
    \youconix\core\Memory::type('string', $s_charset, true);

    $this->obj_phpMailer->CharSet = $s_charset;
  }

  /**
   * Sets the Content-type of the message.
   * This value is default text/plain
   *
   * @param string $s_contentType
   *            type
   * @throws LogicException If the content type is invalid
   */
  public function setContentType($s_contentType)
  {
    \youconix\core\Memory::type('string', $s_contentType, true);

    $this->obj_phpMailer->ContentType = $s_contentType;
  }

  /**
   * Sets the Encoding of the message.
   * Options for this are
   * "8bit", "7bit", "binary", "base64", and "quoted-printable".
   * This value is default 8bit
   *
   * @param string $s_encoding
   * @throws \InvalidArgumentException    If the encoding is invalid
   */
  public function setEncoding($s_encoding)
  {
    if (!in_array($s_encoding,
            [
            "8bit",
            "7bit",
            "binary",
            "base64",
            "quoted-printable"
        ])) {
      throw new \InvalidArgumentException("Setting illegal encoding ".$s_encoding.'. Only "8bit", "7bit", "binary", "base64" and "quoted-printable" are allowed.');
    }

    $this->obj_phpMailer->Encoding = $s_encoding;
  }

  /**
   * Returns the most recent mailer error message.
   *
   * @return string error message
   */
  public function getErrorMessage()
  {
    return $this->obj_phpMailer->ErrorInfo;
  }

  /**
   * Sets the email address that a reading confirmation will be sent.
   *
   * @param string $s_address
   * @throws LogicException If the address is invalid
   */
  public function setReadingConfirmation($s_address)
  {
    \youconix\core\Memory::type('string', $s_address, true);

    $this->obj_phpMailer->ConfirmReadingTo = $s_address;
  }

  /**
   * Sets the hostname to use in Message-Id and Received headers
   * and as default HELO string.
   * If empty, the value returned
   * by SERVER_NAME is used or 'localhost.localdomain'.
   *
   * @param string $s_hostname
   *            name
   * @throws LogicException If the hostname is invalid
   */
  public function setHostName($s_hostname)
  {
    \youconix\core\Memory::type('string', $s_hostname, true);

    $this->obj_phpMailer->Hostname = $s_hostname;
  }

  /**
   * Sets the message ID to be used in the Message-Id header.
   * If empty, a unique id will be generated.
   *
   * @param string $s_id
   *            ID
   * @throws LogicException If the message  id is invalid
   */
  public function setMessageID($s_id)
  {
    \youconix\core\Memory::type('string', $s_id, true);

    $this->obj_phpMailer->MessageID = $s_id;
  }

  /**
   * Provides the ability to have the TO field process individual
   * emails, instead of sending to entire TO addresses
   *
   * @param boolean $bo_singleTo
   *            true for individual to field
   * @throws InvalidArgumentException if the single to is invalid
   */
  public function setSingleTo($bo_singleTo)
  {
    if (!is_boolean($bo_singleTo)) {
      throw new \InvalidArgumentException('Setting illegal single to '.$bo_singleTo.'. Only booleaneans are allowed.');
    }

    $this->obj_phpMailer->SingleTo = $bo_singleTo;
  }

  /**
   * If SingleTo is true, this provides the array to hold the email addresses
   *
   * @return array addresses
   */
  public function getAddresses()
  {
    return $this->obj_phpMailer->SingleToArray;
  }

  /**
   * Provides the ability to change the line ending
   *
   * @param string $s_ending
   *            ending
   */
  public function setLineEnding($s_ending)
  {
    $this->obj_phpMailer->LE = $s_ending;
  }

  /**
   * Used with DKIM DNS Resource Record
   *
   * @param string $s_selector            
   * @param string $s_indentity
   *            optional
   * @param string $s_domain
   *            optional
   * @param string $s_private
   *            private value, optional
   * @throws LogicException   If the arguments are invalid
   */
  public function setDKIM($s_selector, $s_indentity = '', $s_domain = '',
                          $s_private = '')
  {
    \youconix\core\Memory::type('string', $s_selector, true);
    \youconix\core\Memory::type('string', $s_indentity);
    \youconix\core\Memory::type('string', $s_domain);
    \youconix\core\Memory::type('string', $s_private);

    $this->obj_phpMailer->DKIM_selector = $s_selector;
    $this->obj_phpMailer->DKIM_identity = $s_indentity;
    $this->obj_phpMailer->DKIM_domain = $s_domain;
    $this->obj_phpMailer->DKIM_private = $s_private;
  }

  /**
   * Callback Action function name
   * the function that handles the result of the send email action.
   * Parameters:
   * boolean $result result of the send action
   * string $to email address of the recipient
   * string $cc cc email addresses
   * string $bcc bcc email addresses
   * string $subject the subject
   * string $body the email body
   *
   * @param string $s_callback
   */
  public function setCallback($s_callback)
  {
    $this->obj_phpMailer->action_function = $s_callback;
  }

  /**
   * Sets message type to HTML.
   *
   * @param
   *            boolean html		Set to true for HTML mail
   * @throws LogicException   If the html setting is invalid
   */
  public function useHTML($bo_html = true)
  {
    \youconix\core\Memory::type('bool', $bo_html);

    return $this->obj_phpMailer->IsHTML($bo_html);
  }

  /**
   * Sets Mailer to send message using SMTP.
   */
  public function useSMTP()
  {
    $this->obj_phpmailer->IsSMTP();
  }

  /**
   * Sets Mailer to send message using PHP mail() function.
   */
  public function useMail()
  {
    $this->obj_phpMailer->IsMail();
  }

  /**
   * Sets Mailer to send message using the $Sendmail program.
   */
  public function useSendmail()
  {
    $this->obj_phpMailer->IsSendmail();
  }

  /**
   * Sets Mailer to send message using the qmail MTA.
   */
  public function useQmail()
  {
    $this->obj_phpMailer->IsQmail();
  }
  // ///////////////////////////////////////////////
  // METHODS, RECIPIENTS
  // ///////////////////////////////////////////////

  /**
   * Adds a "To" address.
   *
   * @param string $s_address
   *            email
   * @param string $s_name
   *            name
   * @return boolean True on success, false if the address already used
   */
  public function addAddress($s_address, $s_name = '')
  {
    return $this->obj_phpMailer->AddAddress($s_address, $s_name);
  }

  /**
   * Adds a "Cc" address.
   * Note: this function works with the SMTP mailer on win32, not with the "mail" mailer.
   *
   * @param string $s_address
   *            email
   * @param string $s_name
   *            name
   * @return boolean True on success, false if the address already used
   */
  public function addCC($s_address, $s_name = '')
  {
    return $this->obj_phpMailer->AddCC($s_address, $s_name);
  }

  /**
   * Adds a "Bcc" address.
   * Note: this function works with the SMTP mailer on win32, not with the "mail" mailer.
   *
   * @param string $s_address
   *            email
   * @param string $s_name
   *            name
   * @return boolean True on success, false if the address already used
   */
  public function addBCC($s_address, $s_name = '')
  {
    return $this->obj_phpMailer->AddBCC($s_address, $s_name);
  }

  /**
   * Adds a "Reply-to" address.
   *
   * @param string $s_address
   *            email
   * @param string $s_name
   *            name
   * @return boolean True on success, false if the address already used
   */
  public function addReplyTo($s_address, $s_name = '')
  {
    return $this->obj_phpMailer->AddReplyTo($s_address, $s_name);
  }

  /**
   * Set the From and FromName properties
   *
   * @param string $s_address
   *            email
   * @param string $s_name
   *            name
   * @return boolean True on success, false if the address is invalid
   */
  public function setFrom($s_address, $s_name = '', $auto = 1)
  {
    $this->obj_phpMailer->SetFrom($s_address, $s_name, $auto);
  }

  /**
   * Check that a string looks roughly like an email address should
   * Static so it can be used without instantiation
   * Tries to use PHP built-in validator in the filter extension (from PHP 5.2), falls back to a reasonably competent regex validator
   * Conforms approximately to RFC2822
   *
   * @link http://www.hexillion.com/samples/#Regex Original pattern found here
   *      
   * @param string $s_address
   *            The email address to check
   * @return boolean if the email address is valid, otherwise false
   * @static
   *
   */
  public static function validateAddress($s_address)
  {
    return PHPMailer::ValidateAddress($s_address);
  }
  // ///////////////////////////////////////////////
  // METHODS, MAIL SENDING
  // ///////////////////////////////////////////////

  /**
   * Creates message and assigns Mailer.
   * If the message is
   * not sent successfully then it returns false. Use the ErrorInfo
   * variable to view description of the error.
   *
   * @return boolean if the email has been send
   */
  public function send()
  {
    return $this->obj_phpMailer->Send();
  }

  /**
   * Initiates a connection to an SMTP server.
   * Returns false if the operation failed.
   *
   * @uses SMTP
   * @return boolean if the connection has made
   */
  public function smtpConnect()
  {
    return $this->obj_phpMailer->SmtpConnect();
  }

  /**
   * Closes the active SMTP session if one exists.
   */
  public function smtpClose()
  {
    return $this->obj_phpMailer->SmtpClose();
  }

  /**
   * Sets the language for all class error messages.
   * Returns false if it cannot load the language file. The default language is English.
   *
   * @param string $langcode
   *            ISO 639-1 2-character language code (e.g. Portuguese: "br")
   * @param string $lang_path
   *            Path to the language file directory
   * @return boolean if the language has been changed
   */
  public function setLanguage($langcode = 'en', $lang_path = 'language/')
  {
    return $this->obj_phpMailer->SetLanguage($langcode, $lang_path);
  }

  /**
   * Return the current array of language strings
   *
   * @return array language strings
   */
  public function getTranslations()
  {
    return $this->obj_phpMailer->GetTranslations();
  }
  // ///////////////////////////////////////////////
  // METHODS, MESSAGE CREATION
  // ///////////////////////////////////////////////

  /**
   * Creates recipient headers.
   *
   * @param string $s_type            
   * @param string $s_address            
   * @return string address
   */
  public function addressAppend($s_type, $s_address)
  {
    return $this->obj_phpMailer->AddrAppend($s_type, $s_address);
  }

  /**
   * Formats an address correctly.
   *
   * @param string $s_address            
   * @return string address
   */
  public function addressFormat($s_address)
  {
    return $this->obj_phpMailer->AddrFormat($s_address);
  }

  /**
   * Wraps message for use with mailers that do not
   * automatically perform wrapping and for quoted-printable.
   *
   * @param string $s_message
   *            to wrap
   * @param int $i_length
   *            length to wrap to
   * @param boolean $bo_qp_mode
   *            run in Quoted-Printable mode
   * @return string wrapped text
   */
  public function wrapText($s_message, $i_length, $bo_qp_mode = false)
  {
    return $this->obj_phpMailer->WrapText($s_message, $i_length, $bo_qp_mode);
  }

  /**
   * Finds last character boundary prior to maxLength in a utf-8
   * quoted (printable) encoded string.
   *
   * @param string $s_encodedText
   *            QP text
   * @param int $i_maxLength
   *            last character boundary prior to this length
   * @return int max length
   */
  public function getUTF8CharBoundary($s_encodedText, $i_maxLength)
  {
    return $this->obj_phpMailer->UTF8CharBoundary($s_encodedText, $i_maxLength);
  }

  /**
   * Performs the body wrapping.
   */
  public function performWordWrap()
  {
    $this->obj_phpMailer->SetWordWrap();
  }

  /**
   * Assembles message header.
   *
   * @return string The assembled header
   */
  public function createHeader()
  {
    return $this->obj_phpMailer->CreateHeader();
  }

  /**
   * Returns the message MIME.
   *
   * @return string message
   */
  public function getMailMIME()
  {
    return $this->obj_phpMailer->GetMailMIME();
  }

  /**
   * Assembles the message body.
   * Returns an empty string on failure.
   *
   * @return string The assembled message body
   */
  public function createBody()
  {
    return $this->obj_phpMailer->CreateBody();
  }

  /**
   * Returns a formatted header line.
   *
   * @param string $s_name            
   * @param string $s_value            
   * @return string line
   */
  public function getHeaderLine($s_name, $s_value)
  {
    return $this->obj_phpMailer->HeaderLine($s_name, $s_value);
  }

  /**
   * Returns a formatted mail line.
   *
   * @param string $s_value            
   * @return string line
   */
  public function getTextLine($s_value)
  {
    return $this->obj_phpMailer->TextLine($s_value);
  }
  // ///////////////////////////////////////////////
  // CLASS METHODS, ATTACHMENTS
  // ///////////////////////////////////////////////

  /**
   * Adds an attachment from a path on the filesystem.
   * Returns false if the file could not be found
   * or accessed.
   *
   * @param string $s_path
   *            to the attachment.
   * @param string $s_name
   *            the attachment name.
   * @param string $s_encoding
   *            encoding (see setEncoding).
   * @param string $s_type
   *            extension (MIME) type.
   * @return boolean if the attachment is attached
   */
  public function addAttachment($s_path, $s_name = '', $s_encoding = 'base64',
                                $s_type = 'application/octet-stream')
  {
    return $this->obj_phpMailer->AddAttachment($s_path, $s_name, $s_encoding,
            $s_type);
  }

  /**
   * Return the current array of attachments
   *
   * @return array attachments
   */
  public function getAttachments()
  {
    return $this->obj_phpMailer->GetAttachments();
  }

  /**
   * Encodes string to requested format.
   * Returns an empty string on failure.
   *
   * @param string $s_text
   *            The text to encode
   * @param string $s_encoding
   *            encoding to use; one of 'base64', '7bit', '8bit', 'binary', 'quoted-printable'
   * @return string encoded string
   */
  public function encodeString($s_text, $s_encoding = 'base64')
  {
    if (!in_array($s_encoding,
            [
            'base64',
            '7bit',
            '8bit',
            'binary',
            'quoted-printable'
        ])) {
      throw new \InvalidArgumentException('Setting illegal encoding '.$s_encoding.'. Only "base64","7bit", "8bit", "binary" and "quoted-printable" are allowed.');
    }

    return $this->obj_phpMailer->EncodeString($s_text, $s_encoding);
  }

  /**
   * Encode a header string to best (shortest) of Q, B, quoted or none.
   *
   * @param string $s_text
   * @param string $s_position
   *            (phrase,comment,text), default text
   * @return string
   */
  public function encodeHeader($s_text, $s_position = 'text')
  {
    if (!in_array($s_position,
            [
            'phrase',
            'comment',
            'text'
        ])) {
      throw new \InvalidArgumentException('Setting illegal position '.$s_position.'. Only "phrase", "comment" and "text" are allowed.');
    }

    return $this->obj_phpMailer->EncodeHeader($s_text, $s_position);
  }

  /**
   * Checks if a string contains multibyte characters.
   *
   * @param string $s_text
   *            multi-byte text to wrap encode
   * @return boolean if the string contains multibyte characters
   */
  public function hasMultiBytes($s_text)
  {
    return $this->obj_phpMailer->HasMultiBytes($s_text);
  }

  /**
   * Correctly encodes and wraps long multibyte strings for mail headers
   * without breaking lines within a character.
   * Adapted from a function by paravoid at http://uk.php.net/manual/en/function.mb-encode-mimeheader.php
   *
   * @param string $s_text
   *            multi-byte text to wrap encode
   * @return string encoded and wrapped text
   */
  public function getBase64EncodeWrapMB($s_text)
  {
    return $this->obj_phpMailer->Base64EncodeWrapMB($s_text);
  }

  /**
   * Encode string to quoted-printable.
   * Only uses standard PHP, slow, but will always work
   *
   * @param string $s_input
   *            text to encode
   * @param int $i_line_max
   *            chars allowed on a line before wrapping
   * @param boolean $bo_space_conv
   *            true for space conversion
   * @return string encoded text
   */
  public function getEncodeQPphp($s_input = '', $i_line_max = 76,
                                 $bo_space_conv = false)
  {
    return $this->obj_phpMailer->EncodeQPphp($s_input, $i_line_max,
            $bo_space_conv);
  }

  /**
   * Encode string to RFC2045 (6.7) quoted-printable format
   * Uses a PHP5 stream filter to do the encoding about 64x faster than the old version
   * Also results in same content as you started with after decoding
   *
   * @see EncodeQPphp()
   *
   * @param string $s_text
   *            text to encode
   * @param int $i_line_max
   *            Number of chars allowed on a line before wrapping
   * @param boolean $bo_space_conv
   *            for compatibility with existing EncodeQP function
   * @return string encoded text
   */
  public function getEncodeQP($s_text, $i_line_max = 76, $bo_space_conv = false)
  {
    return $this->obj_phpMailer->EncodeQP($s_text, $i_line_max, $bo_space_conv);
  }

  /**
   * Encode string to q encoding.
   *
   * @link http://tools.ietf.org/html/rfc2047
   *
   * @param string $s_text
   *            text to encode
   * @param string $s_position
   *            the text is going to be used, see the RFC for what that means : (phrase,comment,text), default text
   * @return string encoded text
   */
  public function getEncodeQ($s_text, $s_position = 'text')
  {
    if (!in_array($s_position,
            [
            'phrase',
            'comment',
            'text'
        ])) {
      throw new \InvalidArgumentException('Setting illegal position '.$s_position.'. Only "phrase", "comment" and "text" are allowed.');
    }

    return $this->obj_phpMailer->EncodeQ($s_text, $s_position);
  }

  /**
   * Adds a string or binary attachment (non-filesystem) to the list.
   * This method can be used to attach ascii or binary data,
   * such as a BLOB record from a database.
   *
   * @param string $s_data
   *            attachment data.
   * @param string $s_filename
   *            of the attachment.
   * @param string $s_encoding
   *            encoding (see setEncoding).
   * @param string $s_type
   *            extension (MIME) type.
   */
  public function addStringAttachment($s_data, $s_filename,
                                      $s_encoding = 'base64',
                                      $s_type = 'application/octet-stream')
  {
    $this->obj_phpMailer->AddStringAttachment($s_data, $s_filename, $s_encoding,
        $s_type);
  }

  /**
   * Adds an embedded attachment.
   * This can include images, sounds, and
   * just about any other document. Make sure to set the $type to an
   * image type. For JPEG images use "image/jpeg" and for GIF images
   * use "image/gif".
   *
   * @param string $s_path
   *            to the attachment.
   * @param string $s_cid
   *            ID of the attachment. Use this to identify	the Id for accessing the image in an HTML form.
   * @param string $s_name
   *            the attachment name.
   * @param string $s_encoding
   *            encoding (see setEncoding).
   * @param string $s_type
   *            File extension (MIME) type.
   * @return boolean if the attachment is attached
   */
  public function addEmbeddedImage($s_path, $s_cid, $s_name = '',
                                   $s_encoding = 'base64',
                                   $s_type = 'application/octet-stream')
  {
    return $this->obj_phpMailer->AddEmbeddedImage($s_path, $s_cid, $s_name,
            $s_encoding, $s_type);
  }

  /**
   * Returns true if an inline attachment is present.
   *
   * @return boolean if an inline image exists
   */
  public function inlineImageExists()
  {
    return $this->obj_phpMailer->InlineImageExists();
  }
  // ///////////////////////////////////////////////
  // CLASS METHODS, MESSAGE RESET
  // ///////////////////////////////////////////////

  /**
   * Clears all recipients assigned in the TO array.
   */
  public function clearAddresses()
  {
    $this->obj_phpMailer->ClearAddresses();
  }

  /**
   * Clears all recipients assigned in the CC array.
   */
  public function clearCCs()
  {
    $this->obj_phpMailer->ClearCCs();
  }

  /**
   * Clears all recipients assigned in the BCC array.
   */
  public function clearBCCs()
  {
    $this->obj_phpMailer->ClearBCCs();
  }

  /**
   * Clears all recipients assigned in the ReplyTo array.
   */
  public function clearReplyTos()
  {
    $this->obj_phpMailer->ClearReplyTos();
  }

  /**
   * Clears all recipients assigned in the TO, CC and BCC array.
   */
  public function clearAllRecipients()
  {
    $this->obj_phpMailer->ClearAllRecipients();
  }

  /**
   * Clears all previously set filesystem, string, and binary attachments.
   */
  public function clearAttachments()
  {
    $this->obj_phpMailer->ClearAttachments();
  }

  /**
   * Clears all custom headers.
   */
  public function clearCustomHeaders()
  {
    $this->obj_phpMailer->ClearCustomHeaders();
  }

  /**
   * Clears all the recipients, headers, headers and reply tos
   */
  public function clearAll()
  {
    $this->obj_phpMailer->ClearAllRecipients();
    $this->obj_phpMailer->ClearAttachments();
    $this->obj_phpMailer->ClearCustomHeaders();
    $this->obj_phpMailer->ClearReplyTos();
  }
  // ///////////////////////////////////////////////
  // CLASS METHODS, MISCELLANEOUS
  // ///////////////////////////////////////////////

  /**
   * Returns the proper RFC 822 formatted date.
   *
   * @return string formatted date
   * @static
   *
   */
  public static function getRFCDate()
  {
    return PHPMailer::RFCDate();
  }

  /**
   * Returns true if an error occurred.
   *
   * @return boolean
   */
  public function hasError()
  {
    return $this->obj_phpMailer->IsError();
  }

  /**
   * Adds a custom header.
   *
   * @param string $s_customHeader            
   */
  public function addCustomHeader($s_customHeader)
  {
    $this->obj_phpMailer->AddCustomHeader($s_customHeader);
  }

  /**
   * Evaluates the message and returns modifications for inline images and backgrounds
   *
   * @param string $s_message            
   * @param string $s_basedir
   *            directory
   */
  public function performMsgHTML($s_message, $s_basedir = '')
  {
    $this->obj_phpMailer->MsgHTML($s_message, $s_basedir);
  }

  /**
   * Gets the MIME type of the embedded or inline image
   *
   * @param string $s_ext
   *            extension
   * @return string MIME type of ext
   * @static
   *
   */
  public static function _mime_types($s_ext = '')
  {
    return PHPMailer::_mime_types($s_ext);
  }

  /**
   * Strips newlines to prevent header injection.
   *
   * @param string $s_header
   *            String
   * @return string header
   */
  public function secureHeader($s_header)
  {
    return $this->obj_phpMailer->SecureHeader($s_header);
  }

  /**
   * Set the private key file and password to sign the message.
   *
   * @param string $s_certFilename
   *            file
   * @param string $s_keyFilename
   *            Name
   * @param string $s_keyPass
   *            private key
   */
  public function sign($s_certFilename, $s_keyFilename, $s_keyPass)
  {
    $this->obj_phpMailer->Sign($s_certFilename, $s_keyFilename, $s_keyPass);
  }

  /**
   * Set the private key file and password to sign the message.
   *
   * @access public
   * @param string $key_filename
   *            Parameter File Name
   * @param string $key_pass
   *            Password for private key
   */
  public function DKIM_QP($txt)
  {
    $tmp = "";
    $line = "";
    for ($i = 0; $i < strlen($txt); $i ++) {
      $ord = ord($txt[$i]);
      if (((0x21 <= $ord) && ($ord <= 0x3A)) || $ord == 0x3C || ((0x3E <= $ord) && ($ord
          <= 0x7E))) {
        $line .= $txt[$i];
      } else {
        $line .= "=".sprintf("%02X", $ord);
      }
    }
    return $line;
  }

  /**
   * Generate DKIM signature
   *
   * @param string $s_header
   *            Header
   * @return string signature
   */
  public function performDKIM_Sign($s_header)
  {
    return $this->obj_phpMailer->DKIM_Sign($s_header);
  }

  /**
   * Generate DKIM Canonicalization Header
   *
   * @param string $s_header
   *            Header
   * @return string header
   */
  public function performDKIM_HeaderC($s_header)
  {
    return $this->obj_phpMailer->DKIM_HeaderC($s_header);
  }

  /**
   * Generate DKIM Canonicalization Body
   *
   * @param string $s_body
   *            Message Body
   * @return string body
   */
  public function performDKIM_BodyC($s_body)
  {
    return $this->obj_phpMailer->DKIM_BodyC($s_body);
  }

  /**
   * Create the DKIM header, body, as new header
   *
   * @access public
   * @param string $s_headerLines
   *            lines
   * @param string $s_subject
   *            Subject
   * @param string $s_body
   *            Body
   * @return string header
   */
  public function performDKIM_Add($s_headerLines, $s_subject, $s_body)
  {
    return $this->obj_phpMailer->DKIM_Add($s_headerLines, $s_subject, $s_body);
  }
}
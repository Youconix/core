<?php
namespace core\mailer;

class PHPMailerDebug extends \core\services\Service implements \Mailer
{
    private $s_sender;
    private $s_subject;
    private $s_body;
    private $s_altBody;
    private $i_wordwrap;
    private $i_priority;
    private $s_charset;
    private $s_contentType;
    private $s_encoding;
    private $s_ConfirmReadingTo =  0;
    private $i_messageID;
    private $b_singleTo = false;
    private $a_singleToArray = array();
    private $LE  = "\n";
    private $b_html = false;
    private $to = array();
    private $cc = array();
    private $bcc = array();
    private $ReplyTo = array();
    private $all_recipients = array();
    private $s_from;
    private $s_fromName;
    
    /**
     * 
     * @var \core\services\FileHandler
     */
    private $file;
    

    /**
     * Initializes the wrapper
     */
    public function __construct(\core\services\FileHandler $file)
    {
        $this->file = $file;
        
        if( !$file->exists(DATA_DIR.'/tmp') ){
        	mkdir(DATA_DIR.'/tmp',0700);
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
        return true;
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
        \core\Memory::type('string',$s_subject,true);
        
        $this->s_subject = $s_subject;
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
        \core\Memory::type('string', $s_body,true);
        
        $this->s_body = $s_body;
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
        \core\Memory::type('string', $s_body,true);
        
        $this->s_altBody;
        
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
        \core\Memory::type('int', $i_wrap);
        if( $i_wrap < 0 ){
            throw new InvalidArgumentException('Wrap must be a whole number greater of equal to 0.');
        }
        
        $this->i_wordwrap = $i_wrap;
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
        if (! in_array($i_priority, array(
            1,
            3,
            5
        ))) {
            throw new \InvalidArgumentException('Setting illegal priority ' . $i_priority . '. Only 1, 3 and 5 are allowed.');
        }
        
        $this->i_priority = $i_priority;
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
        \core\Memory::type('string',$s_charset,true);
        
       	$this->s_charset = $s_charset;
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
        \core\Memory::type('string', $s_contentType,true);
        
        $this->s_contentType = $s_contentType;
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
        if (! in_array($s_encoding, array(
            "8bit",
            "7bit",
            "binary",
            "base64",
            "quoted-printable"
        ))) {
            throw new \InvalidArgumentException("Setting illegal encoding " . $s_encoding . '. Only "8bit", "7bit", "binary", "base64" and "quoted-printable" are allowed.');
        }
        
        $this->s_encoding = $s_encoding;
    }

    /**
     * Returns the most recent mailer error message.
     *
     * @return string error message
     */
    public function getErrorMessage()
    {
        return '';
    }

    /**
     * Sets the email address that a reading confirmation will be sent.
     *
     * @param string $s_address
     * @throws LogicException If the address is invalid                
     */
    public function setReadingConfirmation($s_address)
    {
        \core\Memory::type('string',$s_address,true);
        
        $this->s_ConfirmReadingTo = $s_address;
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
        \core\Memory::type('string',$s_id,true);
        
        $this->i_messageID = $s_id;
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
        if (! is_boolean($bo_singleTo)) {
            throw new \InvalidArgumentException('Setting illegal single to ' . $bo_singleTo . '. Only booleaneans are allowed.');
        }
        
        $this->b_singleTo = $bo_singleTo;
    }

    /**
     * If SingleTo is true, this provides the array to hold the email addresses
     *
     * @return array addresses
     */
    public function getAddresses()
    {
        return $this->a_singleToArray;
    }

    /**
     * Provides the ability to change the line ending
     *
     * @param string $s_ending
     *            ending
     */
    public function setLineEnding($e_ending)
    {
        $this->LE = $s_ending;
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
        \core\Memory::type('bool',$bo_html);
        
        return $this->b_html = $bo_html;
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
        return $this->AddAnAddress('to', $s_address, $s_name);
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
        return $this->AddAnAddress('cc', $s_address, $s_name);
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
        return $this->AddAnAddress('bcc', $s_address, $s_name);
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
        return $this->AddAnAddress('ReplyTo', $s_address, $s_name);
    }
    
    /**
     * Adds an address to one of the recipient arrays
     * Addresses that have been added already return false, but do not throw exceptions
     *
     * @param string $kind
     *            One of 'to', 'cc', 'bcc', 'ReplyTo'
     * @param string $address
     *            The email address to send to
     * @param string $name
     * @return boolean true on success, false if address already used or invalid in some way
     * @access private
     */
    private function AddAnAddress($kind, $address, $name = '')
    {
    	if (! preg_match('/^(to|cc|bcc|ReplyTo)$/', $kind)) {
    		throw new \InvalidArgumentException('Invalid recipient array: ' . kind);
    	}
    	$address = trim($address);
    	$name = trim(preg_replace('/[\r\n]+/', '', $name)); // Strip breaks and trim
    
    	if ($kind != 'ReplyTo') {
    		if (! isset($this->all_recipients[strtolower($address)])) {
    			array_push($this->$kind, array(
    					$address,
    					$name
    			));
    			$this->all_recipients[strtolower($address)] = true;
    			return true;
    		}
    	} else {
    		if (! array_key_exists(strtolower($address), $this->ReplyTo)) {
    			$this->ReplyTo[strtolower($address)] = array(
    					$address,
    					$name
    			);
    			return true;
    		}
    	}
    	return false;
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
        $this->s_from = $address;
        $this->s_fromName = $name;
        if ($auto) {
            if (empty($this->ReplyTo)) {
                $this->AddAnAddress('ReplyTo', $address, $name);
            }
            if (empty($this->s_sender)) {
                $this->s_sender = $address;
            }
        }
        return true;
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
    	/* Creating "email" */
    	$a_email = array();
    	$a_email[] = 'Sender : '.$this->s_sender;
    	$a_email[] = 'Subject : '.$this->s_subject;
		$a_email[] = 'From : '.$this->s_fromName.' <'.$this->s_from.'>';
    	$a_email[] = 'Body : ';
    	$a_email[] = $this->s_body;
    	$a_email[] = 'Alternative body : ';
    	$a_email[] = $this->s_altBody;
    	$a_email[] = '';
    	$a_email[] = 'Word wrap : '.$this->i_wordwrap;
    	$a_email[] = 'Priority : '.$this->i_priority;
    	$a_email[] = 'Charset : '.$this->s_charset;
    	$a_email[] =  'Content type : '.$this->s_contentType;
    	$a_email[] = 'Encoding : '.$this->s_encoding;
    	$a_email[] = 'Confirm reading : '.$this->s_ConfirmReadingTo;
    	$a_email[] = 'To : '.implode(', ',$this->prepareAddress($this->to));
    	$a_email[] = 'CC : '.implode(', ',$this->prepareAddress($this->cc));
    	$a_email[] = 'BCC : '.implode(', ',$this->prepareAddress($this->bcc));
    	$a_email[] = 'Reply to : '.implode(', ',$this->prepareAddress($this->ReplyTo));
    		
    	$s_email = implode($this->LE,$a_email);
    	
    	 
    	
    	$filename = DIRECTORY_SEPARATOR.date('d-m-Y H:i:s').' email - '.$this->s_subject.'.txt';
    	$file = new \SplFileInfo(DATA_DIR.DIRECTORY_SEPARATOR.'tmp'.$filename);
    	$dir = new \SplFileInfo(DATA_DIR.DIRECTORY_SEPARATOR.'tmp');
    	if( !$dir->isWritable() ){
    		throw new \IOException('Can not dump email to '.DATA_DIR.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$filename);
    	}
    	$pointer = $file->openFile('w');
    	$pointer->fwrite($s_email);
    	$pointer  = null;
    	$file = null;
    }
    
    private function prepareAddress($a_address){
        $a_address2 = array();

        foreach($a_address AS $item ){
            $a_address2[] = $item[0].' <'.$item[1].'>';
        }
        
        return $a_address2;
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
    public function addAttachment($s_path, $s_name = '', $s_encoding = 'base64', $s_type = 'application/octet-stream')
    {
    }

    /**
     * Return the current array of attachments
     *
     * @return array attachments
     */
    public function getAttachments()
    {
        return array();
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
    public function addStringAttachment($s_data, $s_filename, $s_encoding = 'base64', $s_type = 'application/octet-stream')
    {
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
    public function addEmbeddedImage($s_path, $s_cid, $s_name = '', $s_encoding = 'base64', $s_type = 'application/octet-stream')
    {
    }

    /**
     * Clears all recipients assigned in the CC array.
     */
    public function clearCCs()
    {
        $this->cc = array();
    }

    /**
     * Clears all recipients assigned in the BCC array.
     */
    public function clearBCCs()
    {
        $this->bcc = array();
    }

    /**
     * Clears all recipients assigned in the ReplyTo array.
     */
    public function clearReplyTos()
    {
        $this->ReplyTo = array();
    }

    /**
     * Clears all recipients assigned in the TO, CC and BCC array.
     */
    public function clearAllRecipients()
    {
        $this->clearCCs();
        $this->clearBCCs();
        $this->to = array();
    }

    /**
     * Clears all previously set filesystem, string, and binary attachments.
     */
    public function clearAttachments()
    {
    }

    /**
     * Clears all custom headers.
     */
    public function clearCustomHeaders()
    {
    }

    /**
     * Clears all the recipients, headers, headers and reply tos
     */
    public function clearAll()
    {
        $this->ClearAllRecipients();
        $this->ClearAttachments();
        $this->ClearCustomHeaders();
        $this->ClearReplyTos();
    }
}
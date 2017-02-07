<?php

interface MailerLib
{

    /**
     * Sets the Subject of the message.
     *
     * @param string $s_subject            
     * @throws \LogicException If the subject is invalid
     */
    public function setSubject($s_subject);

    /**
     * Sets the Body of the message.
     * This can be either an HTML or text body.
     * If HTML then run useHTML(true).
     *
     * @param
     *            string The body
     * @throws \LogicException If the body is invalid
     */
    public function setBody($s_body);

    /**
     * Sets the text-only body of the message.
     * This automatically sets the
     * email to multipart/alternative. This body can be read by mail
     * clients that do not have HTML email capability such as mutt. Clients
     * that can read HTML will view the normal Body.
     *
     * @param string $s_body
     *            body
     * @throws \LogicException If the body is invalid
     */
    public function setAltBody($s_body);

    /**
     * Sets word wrapping on the body of the message to a given number of
     * characters.
     * This value is default 0
     *
     * @param int $i_wrap
     *            limit
     * @throws \LogicException If the wrap is invalid
     */
    public function setWordWrap($i_wrap);

    /**
     * Sets the email priority
     * This value is default 3
     *
     * @param int $i_priority
     *            priority (1 = High, 3 = Normal, 5 = low)
     * @throws \InvalidArgumentException If the priority is invalid
     */
    public function setPriority($i_priority);

    /**
     * Sets the CharSet of the message.
     * This value is default iso-8859-1
     *
     * @param string $s_charset            
     * @throws \LogicException If the charset is invalid
     */
    public function setCharset($s_charset);

    /**
     * Sets the Content-type of the message.
     * This value is default text/plain
     *
     * @param string $s_contentType
     *            type
     * @throws \LogicException If the content type is invalid
     */
    public function setContentType($s_contentType);

    /**
     * Sets the Encoding of the message.
     * Options for this are
     * "8bit", "7bit", "binary", "base64", and "quoted-printable".
     * This value is default 8bit
     *
     * @param string $s_encoding            
     * @throws \InvalidArgumentException If the encoding is invalid
     */
    public function setEncoding($s_encoding);

    /**
     * Returns the most recent mailer error message.
     *
     * @return string error message
     */
    public function getErrorMessage();

    /**
     * Sets the email address that a reading confirmation will be sent.
     *
     * @param string $s_address            
     * @throws \LogicException If the address is invalid
     */
    public function setReadingConfirmation($s_address);

    /**
     * Sets the message ID to be used in the Message-Id header.
     * If empty, a unique id will be generated.
     *
     * @param string $s_id
     *            ID
     * @throws \LogicException If the message id is invalid
     */
    public function setMessageID($s_id);

    /**
     * Provides the ability to have the TO field process individual
     * emails, instead of sending to entire TO addresses
     *
     * @param boolean $bo_singleTo
     *            true for individual to field
     * @throws \InvalidArgumentException if the single to is invalid
     */
    public function setSingleTo($bo_singleTo);

    /**
     * If SingleTo is true, this provides the array to hold the email addresses
     *
     * @return array addresses
     */
    public function getAddresses();

    /**
     * Provides the ability to change the line ending
     *
     * @param string $s_ending
     *            ending
     */
    public function setLineEnding($e_ending);

    /**
     * Sets message type to HTML.
     *
     * @param
     *            boolean html Set to true for HTML mail
     * @throws \LogicException If the html setting is invalid
     */
    public function useHTML($bo_html = true);

    /**
     * Adds a "To" address.
     *
     * @param string $s_address
     *            email
     * @param string $s_name
     *            name
     * @return boolean True on success, false if the address already used
     */
    public function addAddress($s_address, $s_name = '');

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
    public function addCC($s_address, $s_name = '');

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
    public function addBCC($s_address, $s_name = '');

    /**
     * Adds a "Reply-to" address.
     *
     * @param string $s_address
     *            email
     * @param string $s_name
     *            name
     * @return boolean True on success, false if the address already used
     */
    public function addReplyTo($s_address, $s_name = '');

    /**
     * Set the From and FromName properties
     *
     * @param string $s_address
     *            email
     * @param string $s_name
     *            name
     * @return boolean True on success, false if the address is invalid
     */
    public function setFrom($s_address, $s_name = '', $auto = 1);

    /**
     * Creates message and assigns Mailer.
     * If the message is
     * not sent successfully then it returns false. Use the ErrorInfo
     * variable to view description of the error.
     *
     * @return boolean if the email has been send
     */
    public function send();

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
    public function addAttachment($s_path, $s_name = '', $s_encoding = 'base64', $s_type = 'application/octet-stream');

    /**
     * Return the current array of attachments
     *
     * @return array attachments
     */
    public function getAttachments();

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
    public function addStringAttachment($s_data, $s_filename, $s_encoding = 'base64', $s_type = 'application/octet-stream');

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
     *            ID of the attachment. Use this to identify the Id for accessing the image in an HTML form.
     * @param string $s_name
     *            the attachment name.
     * @param string $s_encoding
     *            encoding (see setEncoding).
     * @param string $s_type
     *            File extension (MIME) type.
     * @return boolean if the attachment is attached
     */
    public function addEmbeddedImage($s_path, $s_cid, $s_name = '', $s_encoding = 'base64', $s_type = 'application/octet-stream');

    /**
     * Clears all recipients assigned in the CC array.
     */
    public function clearCCs();

    /**
     * Clears all recipients assigned in the BCC array.
     */
    public function clearBCCs();

    /**
     * Clears all recipients assigned in the ReplyTo array.
     */
    public function clearReplyTos();

    /**
     * Clears all previously set filesystem, string, and binary attachments.
     */
    public function clearAttachments();

    /**
     * Clears all custom headers.
     */
    public function clearCustomHeaders();

    /**
     * Clears all the recipients, headers, headers and reply tos
     */
    public function clearAll();
}
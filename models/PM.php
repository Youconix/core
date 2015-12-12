<?php
namespace youconix\core\models;

/**
 * PM controller model.
 * Contains the PM models
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class PM extends \youconix\core\models\Model
{

    /**
     *
     * @var \Mailer
     */
    private $mailer;

    /**
     *
     * @var \youconix\core\models\data\PM
     */
    private $pm;

    private $a_messages = array();

    /**
     * PHP5 constructor
     *
     * @param \Builder $builder            
     * @param \Validation $validation            
     * @param \youconix\core\models\data\PM $pm            
     * @param \Mailer $mailer            
     */
    public function __construct(\Builder $builder, \Validation $validation, \youconix\core\models\data\PM $pm, \Mailer $mailer)
    {
        parent::__construct(builder, $validation);
        $this->pm = $pm;
        $this->mailer = $mailer;
    }

    /**
     * Sends a message from system
     *
     * @param \youconix\core\models\data\User $obj_receiver            
     * @param string $s_title
     *            The title of the message
     * @param string $s_message
     *            The content of the message
     * @return int The new message ID
     */
    public function sendSystemMessage(\youconix\core\models\data\User $obj_receiver, $s_title, $s_message)
    {
        \youconix\core\Memory::type('string', $s_title);
        \youconix\core\Memory::type('string', $s_message);
        
        $i_receiver = $obj_receiver->getID();
        
        $obj_message = $this->pm->cloneModel();
        $obj_message->setSender(0); // system as sender
        $obj_message->setReceiver($i_receiver);
        $obj_message->setTitle($s_title);
        $obj_message->setMessage($s_message);
        $obj_message->save();
        
        $this->mailer->PM($obj_receiver);
        
        $this->a_messages[$obj_message->getID()] = $obj_message;
        
        return $obj_message->getID();
    }

    /**
     * Sends a message
     *
     * @param \youconix\core\models\data\User $obj_receiver            
     * @param string $s_title
     *            The title of the message
     * @param string $s_message
     *            The content of the message
     * @param int $i_sender
     *            ID, default current user
     * @return int The new message ID
     */
    public function sendMessage(\youconix\core\models\data\User $obj_receiver, $s_title, $s_message, $i_sender = -1)
    {
        \youconix\core\Memory::type('string', $s_title);
        \youconix\core\Memory::type('string', $s_message);
        
        if ($i_sender == - 1) {
            $i_sender = USERID;
        }
        
        $i_receiver = $obj_receiver->getID();
        
        $obj_message = $this->pm->cloneModel();
        $obj_message->setSender($i_sender);
        $obj_message->setReceiver($i_receiver);
        $obj_message->setTitle($s_title);
        $obj_message->setMessage($s_message);
        $obj_message->save();
        
        if ($i_receiver == USERID) {
            $this->a_messages[$obj_message->getID()] = $obj_message;
        } else {
            $this->mailer->PM($obj_receiver);
        }
        
        $this->a_messages[$obj_message->getID()] = $obj_message;
        
        return $obj_message->getID();
    }

    /**
     * Gets all the messages send to the logged in user
     *
     * @param int $i_receiver
     *            ID, default current user
     * @return \youconix\core\models\data\PM[] The messages
     */
    public function getMessages($i_receiver = -1)
    {
        if ($i_receiver == - 1) {
            $i_receiver = USERID;
        }
        
        /* Get messages send to the logged in user */
        $this->a_messages = array();
        $this->builder->select('pm', '*')
            ->order('send', 'DESC')
            ->getWhere()
            ->addAnd('toUserid', 'i', $i_receiver);
        $service_Database = $this->builder->getResult();
        
        $a_messages = array();
        if ($service_Database->num_rows() != 0) {
            $a_preMessages = $service_Database->fetch_assoc();
            foreach ($a_preMessages as $a_message) {
                $obj_message = $this->pm->cloneModel();
                $obj_message->setData($a_message);
                $a_messages[$a_message['id']] = $obj_message;
                
                $this->a_messages[$a_message['id']] = $obj_message;
            }
        }
        
        return $a_messages;
    }

    /**
     * Gets the message with the given ID
     *
     * @param int $i_id
     *            The ID of the message
     * @return \youconix\core\models\data\PM The message
     * @throws \DBException if the message does not exists
     */
    public function getMessage($i_id)
    {
        \youconix\core\Memory::type('int', $i_id);
        
        if (array_key_exists($i_id, $this->a_messages)) {
            return $this->a_messages[$i_id];
        }
        
        $obj_message = $this->pm->cloneModel();
        $obj_message->loadData($i_id);
        $this->a_messages[$i_id] = $obj_message;
        
        return $this->a_messages[$i_id];
    }

    /**
     * Deletes the message with the given ID
     *
     * @param int $i_id
     *            The ID of the message
     * @throws \DBException if the message does not exists
     */
    public function deleteMessage($i_id)
    {
        \youconix\core\Memory::type('int', $i_id);
        
        $obj_message = $this->getMessage($i_id);
        $obj_message->deleteMessage();
        
        unset($this->a_messages[$i_id]);
    }
}
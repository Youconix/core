<?php
namespace youconix\core\entities;

/**
 * Personal message data model.
 * Contains the personal message data
 * 
 * @Table(name="pm")
 */
class PM extends \youconix\core\ORM\Entity
{

    /**
     *
     * @var \youconix\core\repositories\User
     */
    protected $user;

    /**
     *
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id = null;

    /**
     *
     * @Column(type="integer")
     */
    protected $fromUserid;

    /**
     *
     * @Column(type="integer")
     */
    protected $toUserid;

    /**
     *
     * @Column(type="string")
     */
    protected $title;

    /**
     *
     * @Column(type="string")
     */
    protected $message;

    /**
     *
     * @Column(type="datetime")
     */
    protected $sendTime;

    /**
     *
     * @Column(type="boolean")
     */
    protected $unread = 1;

    /**
     * PHP5 constructor
     *
     * @param \Builder $builder            
     * @param \Validation $validation            
     * @param \youconix\core\repositories\User $user            
     */
    public function __construct(\Builder $builder, \Validation $validation, \youconix\core\repositories\User $user)
    {
        parent::__construct($builder, $validation);
        
        $this->user = $user;
        
        $this->a_validation['sendTime'] .= '|min-value:' . time();
    }

    /**
     * Sets the data
     *
     * @param array $a_data
     *            The data
     */
    public function setData($a_data)
    {
        $this->id = $a_data['id'];
        $this->fromUserid = $a_data['fromUserid'];
        $this->toUserid = $a_data['toUserid'];
        $this->title = $a_data['title'];
        $this->message = $a_data['message'];
        $this->sendTime = $a_data['send'];
        $this->unread = $a_data['unread'];
    }

    /**
     * Returns the message ID
     *
     * @return int
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * Returns the sender
     *
     * @return \youconix\core\models\data\User The sender
     */
    public function getSender()
    {
        return $this->user->get($this->fromUserid);
    }

    /**
     * Sets the sender
     *
     * @param \youconix\core\entities\User $sender The sender
     */
    public function setSender(\youconix\core\entities\User $sender)
    {        
        $this->fromUserid = $sender->getID();
    }

    /**
     * Sets the receiver ID
     * For new messages only
     *
     * @param int $i_receiver
     *            The receiver ID
     */
    public function setReceiver($i_receiver)
    {
        \youconix\core\Memory::type('int', $i_receiver);
        
        $this->toUserid = $i_receiver;
    }

    /**
     * Returns the receiver ID
     *
     * @return int ID
     */
    public function getReceiver()
    {
        return $this->toUserid;
    }

    /**
     * Returns the message title
     *
     * @return string The message title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the message title
     *
     * @param string $s_title
     *            The message title
     */
    public function setTitle($s_title)
    {
        \youconix\core\Memory::type('string', $s_title);
        
        $this->title = $s_title;
    }

    /**
     * Returns the message content
     *
     * @return string The message content
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets the message content
     *
     * @param string $s_message
     *            The message content
     */
    public function setMessage($s_message)
    {
        \youconix\core\Memory::type('string', $s_message);
        
        $this->message = $s_message;
    }

    /**
     * Returns if the message is already read
     *
     * @return boolean True if the message is unread, otherwise false
     */
    public function isUnread()
    {
        return ($this->unread == 1);
    }

    /**
     * Sets the message as read
     */
    public function setRead()
    {
        if ($this->unread == 1) {
            $this->unread = 0;
            $this->builder->update('pm')
                ->bindString('unread', 0)
                ->getWhere()
                ->bindInt('id', $this->id);
            $this->builder->getResult();
        }
    }

    /**
     * Returns the send time as a timestamp
     *
     * @return int The send time
     */
    public function getTime()
    {
        return $this->sendTime;
    }

    /**
     * Saves the item
     */
    public function save()
    {
        if (is_null($this->id)) {
            $this->add();
        }
    }
}
<?php

namespace youconix\Core\Entities;

/**
 * Personal message data model.
 * Contains the personal message data
 *
 * @Table(name="pm")
 * @ORM\Entity(repositoryClass="youconix\Core\Repositories\PM")
 */
class PM extends \youconix\Core\ORM\AbstractEntity
{

  /**
   *
   * @var \youconix\Core\Repositories\User
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
   * @Column(type="integer", name="fromUserid")
   */
  protected $sender;

  /**
   *
   * @Column(type="integer", name="toUserid")
   */
  protected $receiver;

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
   * @Column(type="datetime", name="sendTime")
   */
  protected $time;

  /**
   *
   * @Column(type="boolean")
   */
  protected $unread = 1;

  /**
   * PHP5 constructor
   *
   * @param \BuilderInterface $builder
   * @param \ValidationInterface $validation
   * @param \youconix\Core\Repositories\User $user
   */
  public function __construct(\BuilderInterface $builder, \ValidationInterface $validation, \youconix\Core\Repositories\User $user)
  {
    parent::__construct($builder, $validation);

    $this->user = $user;

    $this->a_validation['sendTime'] .= '|min-value:' . time();
  }

  /**
   * Sets the data
   *
   * @param array $data
   *            The data
   */
  public function setData($data)
  {
    $this->id = $data['id'];
    $this->fromUserid = $data['fromUserid'];
    $this->toUserid = $data['toUserid'];
    $this->title = $data['title'];
    $this->message = $data['message'];
    $this->sendTime = $data['send'];
    $this->unread = $data['unread'];
  }

  /**
   * Returns the message ID
   *
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Returns the sender
   *
   * @return \youconix\Core\Entities\User The sender
   */
  public function getSender()
  {
    return $this->user->get($this->fromUserid);
  }

  /**
   * Sets the sender
   *
   * @param \youconix\Core\Entities\User $sender The sender
   */
  public function setSender(\youconix\Core\Entities\User $sender)
  {
    $this->fromUserid = $sender->getID();
  }

  /**
   * Sets the receiver ID
   * For new messages only
   *
   * @param int $receiver
   *            The receiver ID
   */
  public function setReceiver($receiver)
  {
    \youconix\core\Memory::type('int', $receiver);

    $this->toUserid = $receiver;
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
   * @param string $title
   *            The message title
   */
  public function setTitle($title)
  {
    \youconix\core\Memory::type('string', $title);

    $this->title = $title;
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
   * @param string $message
   *            The message content
   */
  public function setMessage($message)
  {
    \youconix\core\Memory::type('string', $message);

    $this->message = $message;
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
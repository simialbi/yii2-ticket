<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2020 Simon Karlen
 */

namespace simialbi\yii2\ticket;

use simialbi\yii2\models\UserInterface;
use simialbi\yii2\ticket\models\Comment;
use simialbi\yii2\ticket\models\Ticket;
use yii\base\Event;

/**
 * CommentEvent represents the event parameter used for an ticket got a new comment event.
 */
class CommentEvent extends Event
{
    /**
     * @var Comment The comment which triggered the event
     */
    public $comment;

    /**
     * @var Ticket The ticket which received the comment
     */
    public $ticket;

    /**
     * @var UserInterface|null The user which is important with this event
     */
    public $user;

    /**
     * @var boolean If the ticket got closed
     */
    public $gotClosed = false;
}

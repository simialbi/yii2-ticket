<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket;

use simialbi\yii2\models\UserInterface;
use simialbi\yii2\ticket\models\Ticket;
use yii\base\Event;

/**
 * TaskEvent represents the event parameter used for an ticket event.
 */
class TicketEvent extends Event
{
    /**
     * @var Ticket The board which triggered the event
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

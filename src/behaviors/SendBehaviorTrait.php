<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@gmail.com>
 */

namespace simialbi\yii2\ticket\behaviors;

/**
 * Trait SendBehaviorTrait
 * @package simialbi\yii2\ticket\behaviors
 */
trait SendBehaviorTrait
{
    /**
     * @var string|array|\Closure The ticket's agent's name property (ticket as base model)
     */
    public $agentNameProperty = ['agent', 'name'];
    /**
     * @var string|array|\Closure The ticket's author's name property (ticket as base model)
     */
    public $authorNameProperty = ['author', 'name'];
    /**
     * @var \Closure|array An array or function which returns an array of agents to inform after a ticket was created
     */
    public $agentsToInform;
}

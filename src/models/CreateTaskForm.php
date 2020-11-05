<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket\models;

use simialbi\yii2\kanban\models\Bucket;
use simialbi\yii2\kanban\models\Task;
use simialbi\yii2\kanban\models\TaskUserAssignment;
use Yii;
use yii\base\Model;

/**
 * Class CreateTaskForm
 * @package simialbi\yii2\ticket\models
 */
class CreateTaskForm extends Model
{
    /**
     * @var integer Bucket
     */
    public $bucket_id;

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            [['bucket_id'], 'integer'],

            [
                'bucket_id',
                'exist',
                'skipOnError' => true,
                'targetClass' => Bucket::class,
                'targetAttribute' => ['bucket_id' => 'id']
            ],

            [['bucket_id'], 'required']
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function attributeLabels()
    {
        return [
            'bucket_id' => Yii::t('simialbi/ticket/model/create-task-form', 'Bucket')
        ];
    }

    /**
     * Create task from data
     *
     * @param Ticket $ticket
     *
     * @return boolean
     */
    public function createTask($ticket)
    {
        if (!$this->validate()) {
            return false;
        }

        switch ($ticket->status) {
            case Ticket::STATUS_IN_PROGRESS:
            case Ticket::STATUS_ASSIGNED:
                $status = Task::STATUS_IN_PROGRESS;
                break;
            case Ticket::STATUS_OPEN:
            default:
                $status = Task::STATUS_NOT_BEGUN;
                break;
            case Ticket::STATUS_RESOLVED:
                $status = Task::STATUS_DONE;
                break;
        }

        $task = new Task([
            'bucket_id' => $this->bucket_id,
            'ticket_id' => $ticket->id,
            'subject' => $ticket->subject,
            'description' => $ticket->description,
            'status' => $status
        ]);
        if ($task->save()) {
            $assignment = new TaskUserAssignment([
                'task_id' => $task->id,
                'user_id' => $ticket->assigned_to
            ]);
            $assignment->save();
            foreach ($ticket->comments as $comment) {
                $taskComment = new \simialbi\yii2\kanban\models\Comment([
                    'task_id' => $task->id,
                    'created_at' => $comment->created_at,
                    'created_by' => $comment->created_by,
                    'text' => $comment->text
                ]);
                $taskComment->detachBehavior('timestamp');
                $taskComment->detachBehavior('blameable');
                $taskComment->save();
            }

            return true;
        }

        return false;
    }
}

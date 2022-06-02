<?php

use rmrevin\yii\fontawesome\FAS;
use simialbi\yii2\ticket\models\Ticket;
use yii\bootstrap4\Html;
use yii\helpers\Url;

/** @var string $icon */
/** @var Ticket $ticket */

// If user has created the ticket then show the agents name
// If user is the agent, show the creators name
$name = $name = $ticket->author->getName();
if ($ticket->agent) {
    $name = $ticket->agent->getName();
    if ($ticket->agent->id == Yii::$app->user->id) {
        $name = $ticket->author->getName();
    }
}

?>

<div class="ticket" data-id="<?= $ticket->id ?>" title="<?= $ticket->subject ?>"
     data-toggle="tooltip">
    <div class="d-flex flex-row justify-content-between">
        <div class="icon priority-<?= $ticket->priority ?> d-flex align-items-center justify-content-center">
            <?= FAS::i($icon) ?>
        </div>
        <div class="description flex-grow-1 position-relative">
            <div class="creator">
                <small><?= $name ?></small>
            </div>
            <div class="title text-truncate font-weight-bold mb-1">
                <?= $ticket->subject ?>
            </div>
            <div class="date <?= ($ticket->due_date && $ticket->due_date <= time()) ? 'text-danger' : '' ?>">
                <small>
                    <?php
                    echo Yii::$app->formatter->asDate($ticket->created_at, 'php:d.m.Y');
                    if ($ticket->due_date) {
                        echo ' - ' . Yii::$app->formatter->asDate($ticket->due_date, 'php:d.m.Y');
                    }
                    ?>
                </small>
            </div>
            <a href="<?= Url::to(['/ticket/ticket/view', 'id' => $ticket->id]) ?>" class="stretched-link"></a>
        </div>
        <?php
        if (!$ticket->assigned_to):
            ?>
            <div class="actions d-flex flex-column px-2">
                <?php
                if (Yii::$app->user->can('takeTicket', ['ticket' => $ticket])):
                    ?>
                    <div class="take flex-grow-1 d-flex align-items-center">
                        <?= Html::a(FAS::i('hand-rock'), Url::to(['/ticket/ticket/take', 'id' => $ticket->id]), [
                            'title' => Yii::t('simialbi/ticket', 'Take ticket'),
                            'aria-label' => Yii::t('simialbi/ticket', 'Take ticket')
                        ]);
                        ?>
                    </div>
                    <?php
                endif;
                if (Yii::$app->user->can('assignTicket')):
                    ?>
                    <div class="assign flex-grow-1 d-flex align-items-center">
                        <?= Html::a(FAS::i('hand-point-right'), Url::to(['/ticket/ticket/assign', 'id' => $ticket->id]), [
                            'title' => Yii::t('simialbi/ticket', 'Assign ticket'),
                            'aria' => [
                                'label' => Yii::t('simialbi/ticket', 'Assign ticket')
                            ],
                            'data' => [
                                'pjax' => '0',
                                'toggle' => 'modal',
                                'target' => '#ticketModal'
                            ]
                        ]);
                        ?>
                    </div>
                    <?php
                endif;
                ?>
            </div>
            <?php
        endif
        ?>
    </div>
</div>

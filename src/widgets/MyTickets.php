<?php

namespace simialbi\yii2\ticket\widgets;

use simialbi\yii2\ticket\models\Ticket;
use simialbi\yii2\ticket\Module;
use simialbi\yii2\ticket\TicketAsset;
use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap4\Html;
use yii\bootstrap4\Widget;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class MyTickets extends Widget
{
    /** @var string Module id */
    public $module = 'ticket';

    /** @var boolean Show tickets created by themselves for agents */
    public $showCreatedTicketsForAgents = true;

    /** @var array Options for the container */
    public $containerOptions = [];

    /** @var array Options for the ticket-type container (assigned, my tickets) */
    public $typeOptions = ['class' => ['type', 'text-center', 'font-weight-bold', 'p-2']];

    /** @var array|false Options for the tooltip plugin */
    public $toolTipOptions = [
        'boundary' => 'body',
        'placement' => 'left',
        'html' => true,
        'trigger' => 'hover',
        'delay' => [
            'show' => 750,
            'hide' => 0
        ]
    ];

    /** @var boolean whether the header for own tickets has been appended. Only used for agents */
    protected $changedFromAssignedToOwn = false;

    /**
     * {@inheritDoc}
     * @throws InvalidConfigException
     */
    public function run()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        $this->view->registerAssetBundle(TicketAsset::class);

        /** @var Module $module */
        $module = Yii::$app->getModule($this->module);

        // Base Query
        $query = Ticket::find()
            ->where([
                '!=',
                'status',
                Ticket::STATUS_RESOLVED
            ]);


        if (Yii::$app->user->can('ticketAgent')) {
            $query1 = clone $query;
            $query2 = clone $query;

            // Show assigned tickets, and created_by if enabled
            $condition = [
                'or',
                ['assigned_to' => Yii::$app->user->id]
            ];
            if ($this->showCreatedTicketsForAgents) {
                $condition[] = ['created_by' => Yii::$app->user->id];
            }
            $query1->andWhere($condition);

            // Not assigned tickets whose topic this user is an agent
            $query2
                ->alias('ti')
                ->innerJoinWith('topic to')
                ->innerJoin('{{%ticket__topic_agent}} ta', ['ta.agent_id' => Yii::$app->user->id])
                ->where([
                    'and',
                    ['!=', '{{ti}}.[[status]]', Ticket::STATUS_RESOLVED],
                    ['assigned_to' => null]
                ]);

            $query = (new Query())->from($query1->union($query2));

        } elseif ($module->canAssignTicketsToNonAgents) {
            $query->andWhere([
                'or',
                ['assigned_to' => Yii::$app->user->id],
                ['created_by' => Yii::$app->user->id]
            ]);
        } else {
            $query->andWhere([
                'created_by' => Yii::$app->user->id
            ]);
        }

        // Order by
        $orderBy = 'priority DESC, CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date ASC, id DESC';
        if (Yii::$app->user->can('ticketAgent') || $module->canAssignTicketsToNonAgents) {
            $orderBy = 'CASE WHEN created_by = ' . Yii::$app->user->id . ' THEN 1 ELSE 0 END, ' . $orderBy;
        }
        $query->orderBy(new Expression($orderBy));


        // Load from array into models
        $ticketsArr = $query->all();
        $tickets = [];
        for ($i = 0; $i < count($ticketsArr); $i++) {
            $tickets[] = new Ticket($ticketsArr[$i]);
        }


        $cnt_assigned = count(array_filter($tickets, function ($item) {
            return $item->assigned_to == Yii::$app->user->id;
        }));
        $cnt_created = count(array_filter($tickets, function ($item) {
            return $item->created_by == Yii::$app->user->id;
        }));

        $containerOptions = $this->containerOptions;
        $containerOptions = ArrayHelper::merge($containerOptions, ['class' => ['my-tickets']]);


        echo Html::beginTag('div', $containerOptions);

        if ($cnt_assigned > 0 && $cnt_created > 0) {
            echo Html::tag('div', Yii::t('simialbi/ticket', 'Assigned to me'), $this->typeOptions);
        }

        foreach ($tickets as $ticket) {
            // Check if label should be shown
            if ($cnt_assigned > 0 &&
                $cnt_created > 0 &&
                $ticket->created_by == Yii::$app->user->id &&
                !$this->changedFromAssignedToOwn
            ) {
                echo Html::tag('div', Yii::t('simialbi/ticket', 'My tickets'), $this->typeOptions);
                $this->changedFromAssignedToOwn = true;
            }

            switch ($ticket->priority) {
                case Ticket::PRIORITY_LOW:
                    $icon = 'angle-down';
                    break;
                case Ticket::PRIORITY_NORMAL:
                    $icon = 'angle-up';
                    break;
                case Ticket::PRIORITY_HIGH:
                    $icon = 'angle-double-up';
                    break;
                case Ticket::PRIORITY_EMERGENCY:
                    $icon = 'exclamation-triangle';
                    break;
                default:
                    $icon = 'angle-up';
            }

            echo $this->render('ticket', [
                'icon' => $icon,
                'ticket' => $ticket
            ]);
        }
        echo Html::endTag('div');


        $options = ($this->toolTipOptions === false) ? 'false' : Json::encode($this->toolTipOptions);
        $js = <<<JS
var cnt = $cnt_assigned;
if (cnt > 0) {
    $('.tickets-icon-count').text(cnt).show();    
}

var options = $options;
if (options != false) {
    $('.my-tickets [data-toggle="tooltip"]').tooltip(options);    
}
JS;
        $this->view->registerJs($js);

        if (Yii::$app->request->pathInfo != 'ticket/ticket') {
            echo $this->render('@simialbi/yii2/ticket/views/ticket/_modal');
        }
    }
}

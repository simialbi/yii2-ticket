<?php

use kartik\grid\GridView;
use kartik\select2\Select2;
use rmrevin\yii\fontawesome\FAS;
use yii\bootstrap4\Html;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;

/* @var $this \yii\web\View */
/* @var $searchModel \simialbi\yii2\ticket\models\SearchTicket */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $topics array */
/* @var $users array */
/* @var $hasKanban boolean */
/* @var $statuses array */
/* @var $priorities array */

$this->title = Yii::t('simialbi/ticket', 'My tickets');
$this->params['breadcrumbs'] = [$this->title];

?>
<div class="sa-ticket-ticket-index">
    <?= GridView::widget([
        'bsVersion' => 4,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax' => true,
        'export' => false,
        'bordered' => false,
        'panel' => [
            'heading' => $this->title,
            'headingOptions' => [
                'class' => [
                    'card-header',
                    'd-flex',
                    'align-items-center',
                    'justify-content-between',
                    'bg-white'
                ]
            ],
            'titleOptions' => [
                'class' => ['card-title', 'm-0']
            ],
            'summaryOptions' => [
                'class' => []
            ],
            'beforeOptions' => [
                'class' => [
                    'card-body',
                    'py-2',
                    'border-bottom',
                    'd-flex',
                    'justify-content-between',
                    'align-items-center'
                ]
            ],
            'footerOptions' => [
                'class' => ['card-footer', 'bg-white']
            ],
            'options' => [
                'class' => ['card']
            ]
        ],
        'panelTemplate' => '
            {panelHeading}
            {panelBefore}
            {items}
            {panelFooter}
        ',
        'panelHeadingTemplate' => '
            {title}
            {toolbar}
        ',
        'panelFooterTemplate' => '{pager}{footer}',
        'panelBeforeTemplate' => '{pager}{summary}',
        'panelAfterTemplate' => '',
        'containerOptions' => [],
        'toolbar' => [
            [
                'content' => Html::a(FAS::i('plus'), ['ticket/create'], [
                    'class' => ['btn', 'btn-primary'],
                    'data' => [
                        'pjax' => '0'
                    ]
                ])
            ]
        ],
        'columns' => [
            [
                'class' => 'kartik\grid\DataColumn',
                'attribute' => 'id',
                'header' => '#',
                'hAlign' => GridView::ALIGN_CENTER,
                'vAlign' => GridView::ALIGN_MIDDLE,
                'width' => '70px'
            ],
            [
                'class' => 'kartik\grid\DataColumn',
                'attribute' => 'created_by',
                'value' => 'author.image',
                'format' => [
                    'image',
                    [
                        'class' => ['rounded-circle'],
                        'style' => [
                            'height' => '50px',
                            'width' => '50px',
                            'object-position' => 'center',
                            'object-fit' => 'cover'
                        ]
                    ]
                ],
                'filter' => false,
                'enableSorting' => false,
                'headerOptions' => ['style' => ['display' => 'none']],
                'filterOptions' => ['style' => ['display' => 'none']],
                'vAlign' => GridView::ALIGN_MIDDLE,
                'width' => '50px'
            ],
            [
                'class' => 'kartik\grid\DataColumn',
                'attribute' => 'created_by',
                'value' => 'author.name',
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $users,
                'filterWidgetOptions' => [
                    'theme' => Select2::THEME_KRAJEE_BS4,
                    'bsVersion' => 4,
                    'options' => [
                        'placeholder' => ''
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ]
                ],
                'headerOptions' => ['colspan' => 2],
                'filterOptions' => ['colspan' => 2],
                'vAlign' => GridView::ALIGN_MIDDLE
            ],
            [
                'class' => 'kartik\grid\DataColumn',
                'attribute' => 'updated_at',
                'format' => 'datetime',
                'vAlign' => GridView::ALIGN_MIDDLE
            ],
            [
                'class' => 'kartik\grid\DataColumn',
                'attribute' => 'subject',
                'vAlign' => GridView::ALIGN_MIDDLE
            ],
            [
                'class' => 'kartik\grid\DataColumn',
                'attribute' => 'priority',
                'value' => function ($model, $key, $index, $column) use ($priorities) {
                    /* @var $column \kartik\grid\DataColumn */
                    return ArrayHelper::getValue($priorities, $model->{$column->attribute});
                },
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $priorities,
                'filterWidgetOptions' => [
                    'theme' => Select2::THEME_KRAJEE_BS4,
                    'bsVersion' => 4,
                    'options' => [
                        'placeholder' => ''
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ]
                ],
                'vAlign' => GridView::ALIGN_MIDDLE
            ],
            [
                'class' => 'kartik\grid\DataColumn',
                'attribute' => 'topic_id',
                'value' => function ($model, $key, $index, $column) use ($topics) {
                    /* @var $column \kartik\grid\DataColumn */
                    return ArrayHelper::getValue($topics, $model->{$column->attribute});
                },
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $topics,
                'filterWidgetOptions' => [
                    'theme' => Select2::THEME_KRAJEE_BS4,
                    'bsVersion' => 4,
                    'options' => [
                        'placeholder' => '',
                        'multiple' => true
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ]
                ],
                'vAlign' => GridView::ALIGN_MIDDLE
            ],
            [
                'class' => 'kartik\grid\DataColumn',
                'attribute' => 'assigned_to',
                'value' => 'agent.name',
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $users,
                'filterWidgetOptions' => [
                    'theme' => Select2::THEME_KRAJEE_BS4,
                    'bsVersion' => 4,
                    'options' => [
                        'placeholder' => ''
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ]
                ],
                'vAlign' => GridView::ALIGN_MIDDLE
            ],
            [
                'class' => 'kartik\grid\DataColumn',
                'attribute' => 'status',
                'value' => function ($model, $key, $index, $column) use ($statuses) {
                    /* @var $column \kartik\grid\DataColumn */
                    return ArrayHelper::getValue($statuses, $model->{$column->attribute});
                },
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $statuses,
                'filterWidgetOptions' => [
                    'theme' => Select2::THEME_KRAJEE_BS4,
                    'bsVersion' => 4,
                    'options' => [
                        'placeholder' => '',
                        'multiple' => true
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ]
                ],
                'vAlign' => GridView::ALIGN_MIDDLE
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{view} {update} {assign} {take} {create-task} {close}',
                'buttons' => [
                    'assign' => function ($url) {
                        return Html::a(FAS::i('hand-point-right'), $url, [
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
                    },
                    'take' => function ($url) {
                        return Html::a(FAS::i('hand-rock'), $url, [
                            'title' => Yii::t('simialbi/ticket', 'Take ticket'),
                            'aria-label' => Yii::t('simialbi/ticket', 'Take ticket'),
                            'data-pjax' => '0'
                        ]);
                    },
                    'create-task' => function ($url) {
                        return Html::a(FAS::s([
                            'style' => [
                                'height' => '1em',
                                'vertical-align' => '-.125em',
                                'width' => '1em'
                            ]
                        ])->icon(FAS::i('check', [
                            'style' => [
                                'height' => '.5em',
                                'width' => '.5em'
                            ],
                            'data' => [
                                'fa-transform' => 'right-4 up-4'
                            ]
                        ]))->on(FAS::i('clone', [
                            'style' => [
                                'height' => '1em',
                                'width' => '1em'
                            ]
                        ])), $url, [
                            'title' => Yii::t('simialbi/ticket', 'Create Kanban task'),
                            'aria-label' => Yii::t('simialbi/ticket', 'Create Kanban task'),
                            'data' => [
                                'pjax' => '0',
                                'toggle' => 'modal',
                                'target' => '#ticketModal'
                            ]
                        ]);
                    },
                    'close' => function ($url) {
                        return Html::a(FAS::i('check-square'), $url, [
                            'title' => Yii::t('simialbi/ticket', 'Close ticket'),
                            'aria-label' => Yii::t('simialbi/ticket', 'Close ticket'),
                            'data-pjax' => '0'
                        ]);
                    }
                ],
                'visibleButtons' => [
                    'view' => function ($model) {
                        return Yii::$app->user->can('viewTicket', ['ticket' => $model]);
                    },
                    'update' => function ($model) {
                        return Yii::$app->user->can('administrateTicket', ['ticket' => $model]);
                    },
                    'assign' => function () {
                        return Yii::$app->user->can('assignTicket');
                    },
                    'take' => function ($model) {
                        /* @var $model \simialbi\yii2\ticket\models\Ticket */
                        return empty($model->assigned_to) && Yii::$app->user->can('takeTicket', ['ticket' => $model]);
                    },
                    'create-task' => function ($model) use ($hasKanban) {
                        /* @var $model \simialbi\yii2\ticket\models\Ticket */
                        return $hasKanban && !$model->getTask()->count() && Yii::$app->user->can('ticketAgent');
                    },
                    'close' => function ($model) {
                        /* @var $model \simialbi\yii2\ticket\models\Ticket */
                        return Yii::$app->user->can('closeTicket', ['ticket' => $model]) && $model->status !== $model::STATUS_RESOLVED;
                    }
                ],
                'width' => '120px'
            ]
        ],
    ]); ?>
</div>
<?php
Modal::begin([
    'id' => 'ticketModal',
    'options' => [
        'class' => ['modal', 'remote', 'fade'],
        'tabindex' => ''
    ],
    'clientOptions' => [
        'backdrop' => 'static',
        'keyboard' => false
    ],
    'size' => Modal::SIZE_LARGE,
    'title' => null,
    'closeButton' => false
]);
Modal::end();

$this->registerJs("jQuery('#ticketModal').on('show.bs.modal', function (evt) {
    var link = jQuery(evt.relatedTarget);
    var href = link.prop('href');

    var modal = jQuery(this);
    modal.find('.modal-content').load(href);
});");
?>

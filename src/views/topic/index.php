<?php

use kartik\grid\GridView;
use kartik\select2\Select2;
use rmrevin\yii\fontawesome\FAS;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;

/** @var $this \yii\web\View */
/** @var $searchModel \simialbi\yii2\ticket\models\SearchTopic */
/** @var $dataProvider \yii\data\ActiveDataProvider */
/** @var $users array */
/** @var $statuses array */
/** @var $priorities array */

$this->title = Yii::t('simialbi/ticket/topic', 'Topics');
$this->params['breadcrumbs'] = [$this->title];

?>
<div class="sa-ticket-topic-index">
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
                'content' => Html::a(FAS::i('plus'), ['topic/create'], [
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
                'attribute' => 'name',
                'vAlign' => GridView::ALIGN_MIDDLE
            ],
            [
                'class' => 'kartik\grid\DataColumn',
                'attribute' => 'new_ticket_assign_to',
                'value' => 'newTicketAgent.name',
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
                'attribute' => 'new_ticket_status',
                'value' => function ($model, $key, $index, $column) use ($statuses) {
                    /** @var $column \kartik\grid\DataColumn */
                    return ArrayHelper::getValue($statuses, $model->{$column->attribute});
                },
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $statuses,
                'filterWidgetOptions' => [
                    'theme' => Select2::THEME_KRAJEE_BS4,
                    'bsVersion' => 4,
                    'options' => [
                        'placeholder' => '',
                        'multiple' => false
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ]
                ],
                'vAlign' => GridView::ALIGN_MIDDLE
            ],
            [
                'class' => 'kartik\grid\BooleanColumn',
                'attribute' => 'status',
                'vAlign' => GridView::ALIGN_MIDDLE
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'template' => '{update} {delete}'
            ]
        ],
    ]); ?>
</div>

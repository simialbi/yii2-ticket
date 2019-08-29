<?php

use kartik\grid\GridView;
use rmrevin\yii\fontawesome\CdnFreeAssetBundle;
use rmrevin\yii\fontawesome\FAS;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $searchModel \simialbi\yii2\ticket\models\SearchTicket */
/* @var $dataProvider \yii\data\ActiveDataProvider */

$this->title = Yii::t('simialbi/ticket', 'My tickets');
$this->params['breadcrumbs'] = [$this->title];

CdnFreeAssetBundle::register($this);

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
                    'class' => ['btn', 'btn-primary']
                ])
            ]
        ],
        'columns' => [
            [
                'class' => 'kartik\grid\SerialColumn'
            ],
            'updated_at:datetime',
            'subject',
            [
                'attribute' => 'created_by',
                'value' => 'author.name'
            ],
            'priority',
            [
                'attribute' => 'assigned_to',
                'value' => 'agent.name'
            ],
            [
                'class' => 'kartik\grid\ActionColumn'
            ]
        ],
    ]); ?>
</div>

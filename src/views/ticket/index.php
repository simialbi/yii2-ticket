<?php

/* @var $this \yii\web\View */

use kartik\grid\GridView;

/* @var $searchModel \simialbi\yii2\ticket\models\SearchTicket */
/* @var $dataProvider \yii\data\ActiveDataProvider */

$this->title = Yii::t('simialbi/ticket', 'My tickets');
$this->params['breadcrumbs'] = [$this->title];

?>
<div class="sa-ticket-ticket-index">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax' => true,
        'export' => false,
        'bordered' => false,
        'columns' => [
            'class' => 'kartik\grid\SerialColumn'
        ]
    ]); ?>
</div>

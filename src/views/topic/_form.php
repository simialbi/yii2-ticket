<?php

use kartik\select2\Select2;
use marqu3s\summernote\Summernote;
use simialbi\yii2\ticket\models\Topic;
use simialbi\yii2\ticket\Module;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;

/** @var $this \yii\web\View */
/** @var $form \yii\bootstrap4\ActiveForm */
/** @var $model \simialbi\yii2\ticket\models\Topic */
/** @var $agents array */
/** @var $users array */
/** @var $statuses array */
/** @var $richTextFields boolean */
/** @var $canAssignTicketsToNonAgents boolean */
/** @var $notifications array */

?>

    <div class="form-row align-items-center">
        <?= $form->field($model, 'name', [
            'options' => [
                'class' => ['form-group', 'col-12', 'col-sm-9']
            ]
        ])->textInput(); ?>
        <?= $form->field($model, 'status', [
            'options' => [
                'class' => ['form-group', 'col-12', 'col-sm-3', 'col-md-2', 'offset-md-1']
            ]
        ])->checkbox(); ?>
    </div>
    <div class="form-row">
        <?= $form->field($model, 'new_ticket_assign_to', [
            'options' => [
                'class' => ['form-group', 'col-12', 'col-md-6', 'col-lg-4']
            ]
        ])->widget(Select2::class, [
            'data' => $users,
            'theme' => Select2::THEME_KRAJEE_BS4,
            'bsVersion' => 4,
            'options' => [
                'placeholder' => Yii::t('simialbi/ticket', 'Select user(s)')
            ],
            'pluginOptions' => [
                'allowClear' => true
            ]
        ]); ?>
        <?= $form->field($model, 'new_ticket_status', [
            'options' => [
                'class' => ['form-group', 'col-12', 'col-md-6', 'col-lg-4']
            ]
        ])->widget(Select2::class, [
            'data' => $statuses,
            'theme' => Select2::THEME_KRAJEE_BS4,
            'bsVersion' => 4,
            'options' => [
                'placeholder' => ''
            ],
            'pluginOptions' => [
                'allowClear' => false
            ]
        ]); ?>
        <div class="form-group col-12 col-md-6 col-lg-4">
            <?= Html::label(Yii::t('simialbi/ticket', 'Responsible agents'), 'responsible-agents'); ?>
            <?= Select2::widget([
                'name' => 'agents',
                'value' => ArrayHelper::getColumn($model->getAgents(), 'id'),
                'id' => 'responsible-agents',
                'data' => $agents,
                'options' => [
                    'placeholder' => Yii::t('simialbi/ticket', 'Select user(s)'),
                    'multiple' => true
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ]
            ]); ?>
        </div>
    </div>
    <div class="form-row">
        <?php if ($richTextFields): ?>
            <?= $form->field($model, 'template', [
                'options' => [
                    'class' => ['form-group', 'col-12']
                ]
            ])->widget(Summernote::class, [
                'clientOptions' => [
                    'styleTags' => [
                        'p',
                        [
                            'title' => 'blockquote',
                            'tag' => 'blockquote',
                            'className' => 'blockquote',
                            'value' => 'blockquote'
                        ],
                        'pre'
                    ],
                    'toolbar' => new \yii\helpers\ReplaceArrayValue([
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'strikethrough']],
                        ['script', ['subscript', 'superscript']],
                        ['list', ['ol', 'ul']],
                        ['clear', ['clear']]
                    ])
                ]
            ]); ?>
        <?php else: ?>
            <?= $form->field($model, 'template', [
                'options' => [
                    'class' => ['form-group', 'col-12']
                ]
            ])->textarea(['rows' => 5]); ?>
        <?php endif; ?>
    </div>
    <div class="form-row">
        <div class="form-group col-12 col-md-6 col-lg">
            <?= Html::label(Yii::t('simialbi/ticket/model/topic', 'Notification on {event}', [
                'event' => Yii::t('simialbi/ticket/model/topic', Module::EVENT_TICKET_CREATED)
            ])); ?>
            <?= Html::checkboxList(
                'Notification[' . Module::EVENT_TICKET_CREATED . ']',
                isset($notifications[Module::EVENT_TICKET_CREATED]) ? $notifications[Module::EVENT_TICKET_CREATED] : [],
                [
                    Topic::BEHAVIOR_SMS => Yii::t('simialbi/ticket', 'Send SMS'),
                    Topic::BEHAVIOR_MAIL => Yii::t('simialbi/ticket', 'Send mail')
                ]
            ); ?>
        </div>
        <div class="form-group col-12 col-md-6 col-lg">
            <?= Html::label(Yii::t('simialbi/ticket/model/topic', 'Notification on {event}', [
                'event' => Yii::t('simialbi/ticket/model/topic', Module::EVENT_TICKET_UPDATED)
            ])); ?>
            <?= Html::checkboxList(
                'Notification[' . Module::EVENT_TICKET_UPDATED . ']',
                isset($notifications[Module::EVENT_TICKET_UPDATED]) ? $notifications[Module::EVENT_TICKET_UPDATED] : [],
                [
                    Topic::BEHAVIOR_SMS => Yii::t('simialbi/ticket', 'Send SMS'),
                    Topic::BEHAVIOR_MAIL => Yii::t('simialbi/ticket', 'Send mail')
                ]
            ); ?>
        </div>
        <div class="form-group col-12 col-md-6 col-lg">
            <?= Html::label(Yii::t('simialbi/ticket/model/topic', 'Notification on {event}', [
                'event' => Yii::t('simialbi/ticket/model/topic', Module::EVENT_TICKET_ASSIGNED)
            ])); ?>
            <?= Html::checkboxList(
                'Notification[' . Module::EVENT_TICKET_ASSIGNED . ']',
                isset($notifications[Module::EVENT_TICKET_ASSIGNED]) ? $notifications[Module::EVENT_TICKET_ASSIGNED] : [],
                [
                    Topic::BEHAVIOR_SMS => Yii::t('simialbi/ticket', 'Send SMS'),
                    Topic::BEHAVIOR_MAIL => Yii::t('simialbi/ticket', 'Send mail')
                ]
            ); ?>
        </div>
        <div class="form-group col-12 col-md-6 col-lg">
            <?= Html::label(Yii::t('simialbi/ticket/model/topic', 'Notification on {event}', [
                'event' => Yii::t('simialbi/ticket/model/topic', Module::EVENT_TICKET_RESOLVED)
            ])); ?>
            <?= Html::checkboxList(
                'Notification[' . Module::EVENT_TICKET_RESOLVED . ']',
                isset($notifications[Module::EVENT_TICKET_RESOLVED]) ? $notifications[Module::EVENT_TICKET_RESOLVED] : [],
                [
                    Topic::BEHAVIOR_SMS => Yii::t('simialbi/ticket', 'Send SMS'),
                    Topic::BEHAVIOR_MAIL => Yii::t('simialbi/ticket', 'Send mail')
                ]
            ); ?>
        </div>
        <div class="form-group col-12 col-md-6 col-lg">
            <?= Html::label(Yii::t('simialbi/ticket/model/topic', 'Notification on {event}', [
                'event' => Yii::t('simialbi/ticket/model/topic', Module::EVENT_TICKET_COMMENTED)
            ])); ?>
            <?= Html::checkboxList(
                'Notification[' . Module::EVENT_TICKET_COMMENTED . ']',
                isset($notifications[Module::EVENT_TICKET_COMMENTED]) ? $notifications[Module::EVENT_TICKET_COMMENTED] : [],
                [
                    Topic::BEHAVIOR_SMS => Yii::t('simialbi/ticket', 'Send SMS'),
                    Topic::BEHAVIOR_MAIL => Yii::t('simialbi/ticket', 'Send mail')
                ]
            ); ?>
        </div>
    </div>

<?php
$idAssign = '#' . Html::getInputId($model, 'new_ticket_assign_to');
$idAgents = '#responsible-agents';
$js = <<<JS
    jQuery('$idAgents').on('change', function() {
        var elem = jQuery('$idAssign');
        var _this = jQuery(this);

        // Add missing agents
        var arrIds = _this.val();
        jQuery.each(arrIds, function(k,v) {
            if (elem.find('option[value="' + v + '"]').length === 0) {
                var option = _this.find('option[value="' + v + '"]');
                var newOption = new Option(option.text(), option.attr('value'), false, false);
                elem.append(newOption).trigger('change');
            }
        });

        // Remove agents
        jQuery.each(elem.find('option[value]'), function() {
            if (jQuery(this).attr('value') !== '') {
                if (_this.find('option[value="' + jQuery(this).attr('value') + '"]:selected').length === 0) {
                    jQuery(this).remove();
                }
            }
        });
    });
JS;

if (!$canAssignTicketsToNonAgents) {
    $this->registerJs($js);
}

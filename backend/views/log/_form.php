<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Log */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="log-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

<?php  
                $ar1= (new \yii\db\Query())
                  ->from('user')
                  ->all();
                $ar2=ArrayHelper::map($ar1,'id','name');
                echo $form->field($model, 'user_id')->
                  listBox($ar2)->label('User');
        
                 ?>    <?= $form->field($model, 'level')->textInput() ?>

    <?= $form->field($model, 'category')->textarea(['rows' => 6]) ?>

<?=            $form->field($model,'log_time')->
              widget(\kartik\datecontrol\DateControl::className(), 
              ['type'=>'datetime']) ?>
            <?= $form->field($model, 'prefix')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'message')->textarea(['rows' => 6]) ?>


    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
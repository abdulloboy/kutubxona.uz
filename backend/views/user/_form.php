<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\UserM */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-m-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'name')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'fullName')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'department')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'position')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'telephone')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'auth_key')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'password_hash')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'password_reset_token')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'email')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'status')->textInput() ?>

<?=            $form->field($model,'created_at')->
              widget(\kartik\datecontrol\DateControl::className(), 
              ['type'=>'datetime']) ?>
        <?=            $form->field($model,'updated_at')->
              widget(\kartik\datecontrol\DateControl::className(), 
              ['type'=>'datetime']) ?>
        
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
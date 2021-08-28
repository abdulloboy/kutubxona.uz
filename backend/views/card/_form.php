<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Card */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="card-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

<?php  
                $ar1= (new \yii\db\Query())
                  ->from('book')
                  ->all();
                $ar2=ArrayHelper::map($ar1,'id','name');
                echo $form->field($model, 'book_id')->
                  listBox($ar2)->label('Book');
        
                 ?><?php  
                $ar1= (new \yii\db\Query())
                  ->from('user')
                  ->all();
                $ar2=ArrayHelper::map($ar1,'id','name');
                echo $form->field($model, 'user_id')->
                  listBox($ar2)->label('User');
        
                 ?><?=            $form->field($model,'got_at')->
              widget(\kartik\datecontrol\DateControl::className(), 
              ['type'=>'datetime']) ?>
        <?=            $form->field($model,'returned_at')->
              widget(\kartik\datecontrol\DateControl::className(), 
              ['type'=>'datetime']) ?>
        
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
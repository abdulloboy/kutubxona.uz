<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\CardSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="card-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

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
        
         ?>    <?= $form->field($model, 'got_at') ?>

    <?= $form->field($model, 'returned_at') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

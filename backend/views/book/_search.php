<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\BookSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="book-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

<?php  
            $ar1= (new \yii\db\Query())
            ->from('language')
            ->all();
            $ar2=ArrayHelper::map($ar1,'id','name');
            echo $form->field($model, 'language_id')->
              listBox($ar2)->label('Language');
        
         ?><?php  
            $ar1= (new \yii\db\Query())
            ->from('topic')
            ->all();
            $ar2=ArrayHelper::map($ar1,'id','name');
            echo $form->field($model, 'topic_id')->
              listBox($ar2)->label('Topic');
        
         ?><?php  
            $ar1= (new \yii\db\Query())
            ->from('user')
            ->all();
            $ar2=ArrayHelper::map($ar1,'id','name');
            echo $form->field($model, 'user_id')->
              listBox($ar2)->label('User');
        
         ?>    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'author') ?>

    <?= $form->field($model, 'imageFileName') ?>

    <?= $form->field($model, 'url') ?>

    <?php // echo $form->field($model, 'description') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

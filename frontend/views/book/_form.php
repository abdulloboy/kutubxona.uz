<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Book */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="book-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

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
        
                 ?>    <?= $form->field($model, 'name')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'author')->textarea(['rows' => 6]) ?>

<?=     
            Html::activeLabel($model,'imageFileName_Fl') ?>
            <br>
            <?= 
                Html::a(Html::img("@web/../../frontend/web/uploads/$model->imageFileName",
                ['width' => 200, 'height'=> 200]),
                "@web/../../frontend/web/uploads/$model->imageFileName"); ?>         
            <?= $form->field($model, 'imageFileName_Fl')->fileInput()->label('Upload '.$model->getAttributeLabel('imageFileName_Fl')) ?>

    <?= $form->field($model, 'url')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>


    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
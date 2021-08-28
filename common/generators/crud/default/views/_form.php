<?php
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */
/* @var $model \yii\db\ActiveRecord */
/* @var $rels array */
/* @var $timeAtts array */
/* @var $uloadAtts array */

$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}

echo "<?php\n";
?>

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form">

    <?= "<?php " ?>$form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

<?php foreach ($generator->getColumnNames() as $attribute) {
    if (in_array($attribute, $safeAttributes)) {

        foreach($rels as $rel):
            if(!$rel[2] && $rel[3] && $rel[3][$attribute]){
                $s1=strtolower($rel[1]);
                echo '<?php ';
                ?>
 
                $ar1= (new \yii\db\Query())
                  ->from('<?=$s1 ?>')
                  ->all();
                $ar2=ArrayHelper::map($ar1,'id','name');
                echo $form->field($model, '<?=$attribute ?>')->
                  listBox($ar2)->label('<?=$rel[1] ?>');
        
                <?php
                echo ' ?>';
                continue 2;
            }
        endforeach;
        
        if($uploadAtts && 
          in_array($attribute.'_Fl',$uploadAtts)):
            echo '<?= '; ?>    
            Html::activeLabel($model,'<?=$attribute ?>_Fl') ?>
            <br>
            <?php $s1="@web/../../frontend/web/uploads/\$model->$attribute";
            if(stristr($attribute,'image')){ 
                echo '<?='; ?> 
                Html::a(Html::img("<?=$s1 ?>",
                ['width' => 200, 'height'=> 200]),
                "<?=$s1 ?>"); ?>         
            <?php } else {
                echo '<?='; ?> 
                Html::a($model-><?=$attribute ?>,
                "<?=$s1 ?>"); ?>    
            <?php }
            
            echo "<?= ".$generator->
              generateActiveField($attribute.'_Fl').
              "->fileInput()->label('Upload '.\$model->getAttributeLabel('{$attribute}_Fl')) ?>\n\n";
            continue;
        endif;
        
        if($timeAtt=$timeAtts[$attribute]): 
            echo '<?='; ?>
            $form->field($model,'<?=$attribute ?>')->
              widget(\kartik\datecontrol\DateControl::className(), 
              ['type'=>'<?=$timeAtt['type'] ?>']) ?>
        <?php continue;
        endif; 
    
        echo "    <?= " . $generator->
          generateActiveField($attribute) . " ?>\n\n";
    }
} ?>

    <div class="form-group">
        <?= "<?= " ?>Html::submitButton($model->isNewRecord ? <?= $generator->generateString('Create') ?> : <?= $generator->generateString('Update') ?>, ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?= "<?php " ?>ActiveForm::end(); ?>

</div>
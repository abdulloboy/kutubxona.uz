<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */
/* @var $model \yii\db\ActiveRecord */
/* @var $rels array */
/* @var $timeAtts array */
/* @var $uloadAtts array */

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */

$this->title = $model-><?= $generator->getNameAttribute() ?>;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-view">

    <h1><?= "<?= " ?>Html::encode($this->title) ?></h1>

    <p>
        <?= "<?= " ?>Html::a(<?= $generator->generateString('Update') ?>, ['update', <?= $urlParams ?>], ['class' => 'btn btn-primary']) ?>
        <?= "<?= " ?>Html::a(<?= $generator->generateString('Delete') ?>, ['delete', <?= $urlParams ?>], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => <?= $generator->generateString('Are you sure you want to delete this item?') ?>,
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= "<?= " ?>DetailView::widget([
        'model' => $model,
        'attributes' => [
<?php
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        echo "            '" . $name . "',\n";
    }
} else {
    foreach ($generator->getTableSchema()->columns as $column) {
    
        foreach($rels as $rel){
            if(!($rel[2]) && ($rel[3]) && 
              ($rel[3][$column->name])){
                echo "            '".strtolower($rel[1]).".name',\n";
                continue 2;
            }
        }
        
        if(in_array($column->name.'_Fl',$uploadAtts))
            continue;
        
        if($timeAtt=$timeAtts[$column->name])
            $format=$timeAtt['type']; else
            $format = $generator->
              generateColumnFormat($column);
        
        echo "            '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
    }
}
?>
        ],
    ]) ?>


    <?php 
    foreach ($generator->getColumnNames() as $attribute):
        if(in_array($attribute.'_Fl',$uploadAtts)):
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
        endif;
    endforeach; ?>

</div>

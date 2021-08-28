<?php
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */
/* @var $model \yii\db\ActiveRecord */
/* @var $rels array */
/* @var $timeAtts array */
/* @var $uloadAtts array */

echo "<?php\n";
?>
use yii\bootstrap\Nav;
use yii\helpers\Html;
use <?=$generator->searchModelClass ?>;
?>
<?php
$searchMC=StringHelper::basename($generator->searchModelClass);
foreach($rels as $rel):
    if($rel[2] || !$rel[3])
        continue;
?>

<div class="panel panel-info">
                <div class="panel-heading">
                  <h3 class="panel-title"><?='<?=' ?> Html::a('<?=$rel[1] ?>',['/<?=$generator->getControllerId() ?>/index']) ?> </h3>
                </div>
                <div class="panel-body">
                  <?='<?php ' ?>
                  $ar1=(new \yii\db\Query())
                    ->from(strtolower(<?=$rel[1] ?>))
                    ->all();
                  $ar2=array_map(function($row_){
                    $ar3['url']=['<?=$generator->getControllerId() ?>/index'];
                    $ar3['url']['<?= $searchMC ?>']=Yii::$app->request->queryParams['<?= $searchMC ?>'];
                    $ar3['url']['<?= $searchMC ?>']['<?=strtolower($rel[1]) ?>.name']=$row_['name'];
                    $ar3['label']=$row_['name'].'<span class="badge">'.(new <?= $searchMC ?>())->search($ar3['url'])->query->count().'</span>';
                    return $ar3;
                  },$ar1);
                  echo Nav::widget([
                    'encodeLabels' => false,
                    'options' => ['class' => 'nav-pills'],
                    'items' => $ar2,
                  ]);
                  ?> 
                </div>
              </div>
              
              
              
<?php
endforeach;
?>
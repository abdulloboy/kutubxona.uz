<?php
use yii\bootstrap\Nav;
use yii\helpers\Html;
use common\models\CardSearch;
?>

<div class="panel panel-info">
                <div class="panel-heading">
                  <h3 class="panel-title"><?= Html::a('User',['/card/index']) ?> </h3>
                </div>
                <div class="panel-body">
                  <?php                   $ar1=(new \yii\db\Query())
                    ->from(strtolower('User'))
                    ->all();
                  $ar2=array_map(function($row_){
                    $ar3['url']=['card/index'];
                    $ar3['url']['CardSearch']=Yii::$app->request->queryParams['CardSearch'];
                    $ar3['url']['CardSearch']['user.name']=$row_['name'];
                    $ar3['label']=$row_['name'].'<span class="badge">'.(new CardSearch())->search($ar3['url'])->query->count().'</span>';
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
              
              
              

<div class="panel panel-info">
                <div class="panel-heading">
                  <h3 class="panel-title"><?= Html::a('Book',['/card/index']) ?> </h3>
                </div>
                <div class="panel-body">
                  <?php                   $ar1=(new \yii\db\Query())
                    ->from(strtolower('Book'))
                    ->all();
                  $ar2=array_map(function($row_){
                    $ar3['url']=['card/index'];
                    $ar3['url']['CardSearch']=Yii::$app->request->queryParams['CardSearch'];
                    $ar3['url']['CardSearch']['book.name']=$row_['name'];
                    $ar3['label']=$row_['name'].'<span class="badge">'.(new CardSearch())->search($ar3['url'])->query->count().'</span>';
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
              
              
              

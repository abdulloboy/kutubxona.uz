<?php
use yii\bootstrap\Nav;
use yii\helpers\Html;
use common\models\BookSearch;
?>

<div class="panel panel-info">
                <div class="panel-heading">
                  <h3 class="panel-title"><?= Html::a('Topic',['/book/index']) ?> </h3>
                </div>
                <div class="panel-body">
                  <?php                   $ar1=(new \yii\db\Query())
                    ->from(strtolower(Topic))
                    ->all();
                  $ar2=array_map(function($row_){
                    $ar3['url']=['book/index'];
                    $ar3['url']['BookSearch']=Yii::$app->request->queryParams['BookSearch'];
                    $ar3['url']['BookSearch']['topic.name']=$row_['name'];
                    $ar3['label']=$row_['name'].'<span class="badge">'.(new BookSearch())->search($ar3['url'])->query->count().'</span>';
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
                  <h3 class="panel-title"><?= Html::a('Language',['/book/index']) ?> </h3>
                </div>
                <div class="panel-body">
                  <?php                   $ar1=(new \yii\db\Query())
                    ->from(strtolower(Language))
                    ->all();
                  $ar2=array_map(function($row_){
                    $ar3['url']=['book/index'];
                    $ar3['url']['BookSearch']=Yii::$app->request->queryParams['BookSearch'];
                    $ar3['url']['BookSearch']['language.name']=$row_['name'];
                    $ar3['label']=$row_['name'].'<span class="badge">'.(new BookSearch())->search($ar3['url'])->query->count().'</span>';
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
                  <h3 class="panel-title"><?= Html::a('User',['/book/index']) ?> </h3>
                </div>
                <div class="panel-body">
                  <?php                   $ar1=(new \yii\db\Query())
                    ->from(strtolower(User))
                    ->all();
                  $ar2=array_map(function($row_){
                    $ar3['url']=['book/index'];
                    $ar3['url']['BookSearch']=Yii::$app->request->queryParams['BookSearch'];
                    $ar3['url']['BookSearch']['user.name']=$row_['name'];
                    $ar3['label']=$row_['name'].'<span class="badge">'.(new BookSearch())->search($ar3['url'])->query->count().'</span>';
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
              
              
              

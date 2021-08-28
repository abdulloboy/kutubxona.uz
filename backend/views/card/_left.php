<?php
use yii\bootstrap\Nav;
use yii\helpers\Html;
use common\models\BookSearch;
?>

<div class="panel panel-info">
                <div class="panel-heading">
                  <h3 class="panel-title"><?= Html::a('User',['/book']) ?> </h3>
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
              
              
              

<div class="panel panel-info">
                <div class="panel-heading">
                  <h3 class="panel-title"><?= Html::a('Book',['/book']) ?> </h3>
                </div>
                <div class="panel-body">
                  <?php                   $ar1=(new \yii\db\Query())
                    ->from(strtolower(Book))
                    ->all();
                  $ar2=array_map(function($row_){
                    $ar3['url']=['book/index'];
                    $ar3['url']['BookSearch']=Yii::$app->request->queryParams['BookSearch'];
                    $ar3['url']['BookSearch']['book.name']=$row_['name'];
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
              
              
              

<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\Book;
?>
<div>
<h2><small><?=$model->topic->name ?> 
&rarr;
</small>
<?=$model->name ?></h2>
<p><?= $model->user->name ?> | 
    <?= $model->language->name ?> 
    <?php $user=Yii::$app->user;
    if(!$user->isGuest): ?> | 
        <?php 
         $cards=$model->getCards()->
           where(['user_id' => $user->id,
           'returned_at' => null])->count();   
        if($cards)
            echo Html::a(Yii::t('app','Give book'),[
               'get-book','id' => $model->id,
               'return' => 1],['class' => 'btn btn-primary']); else
            echo Html::a(Yii::t('app','Get book'),[
               'get-book','id' => $model->id],['class' => 'btn btn-success']); 
    endif; ?>
</p>
<?php $s1="uploads/{$model->imageFileName}";
echo Html::a("<img src='$s1' width='300' height='300'>",
  $s1); ?>
<p><?=$model->description ?></p>
<p><?= Html::a(Yii::t('app', 'Details'),
    ['view','id' => $model->id]) ?></p>
</div>
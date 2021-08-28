<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
?>
<div class="card-view">

    <h1><?= Html::encode($model->book->name) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'book.name',
            'user.name',
            'got_at:datetime',
            'returned_at:datetime',
        ],
    ]) ?>
<p><?= Html::a(Yii::t('app', 'Details'),
    ['view','id' => $model->id]) ?></p>
</div>
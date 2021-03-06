<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\UserMSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'User Ms');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-m-index">


    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', 'Create User M'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name:ntext',
            'fullName:ntext',
            'department:ntext',
            'position:ntext',
            // 'telephone:ntext',
            // 'auth_key:ntext',
            // 'password_hash:ntext',
            // 'password_reset_token:ntext',
            // 'email:ntext',
            // 'status',
            // 'created_at:datetime',
            // 'updated_at:datetime',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>

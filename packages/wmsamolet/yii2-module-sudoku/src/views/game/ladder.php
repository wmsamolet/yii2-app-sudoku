<?php

use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */

$this->title = 'Yii2 Vue Application';

$dataProvider->pagination->pageSize = 2;
?>

<div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'header' => 'Winner player id',
                'value' => function ($data) {
                    return $data['winner_player_id'];
                },
            ],
            [
                'header' => 'Wins',
                'value' => function ($data) {
                    return $data['count_wins'];
                },
            ],
        ],
    ]) ?>
</div>


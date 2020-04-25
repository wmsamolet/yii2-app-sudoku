<?php

/* @var $this yii\web\View */

/* @var $dataProvider \yii\data\ActiveDataProvider */

use wmsamolet\yii2\modules\sudoku\models\SudokuGame;
use wmsamolet\yii2\modules\sudoku\services\GameMatrixService;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Yii2 Vue Application';

$dataProvider->pagination->pageSize = 10;
$dataProvider->sort->defaultOrder = ['id' => SORT_DESC];
?>

<div id="app">
    <div>
        <a href="<?= Url::to(['/sudoku/game/create']) ?>" class="btn btn-success">Create new game</a>
    </div>
    <br>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            [
                'attribute' => 'size',
                'value' => function (SudokuGame $model) {
                    return ArrayHelper::getValue(GameMatrixService::getSizeList(), [$model->size]);
                },
            ],
            [
                'attribute' => 'difficulty',
                'value' => function (SudokuGame $model) {
                    return ArrayHelper::getValue(GameMatrixService::getDifficultyList(), [$model->difficulty]);
                },
            ],
            [
                'header' => 'Creator user id',
                'value' => function (SudokuGame $model) {
                    return $model->owner_player_id;
                },
            ],
            [
                'header' => 'Winner user id',
                'value' => function (SudokuGame $model) {
                    return $model->winner_player_id;
                },
            ],
            [
                'format' => 'raw',
                'value' => function (SudokuGame $model) {
                    return Html::a('Play', ['/sudoku/game/play', 'id' => $model->id], [
                        'class' => 'btn btn-success',
                    ]);
                },
            ],
        ],
    ]) ?>
</div>


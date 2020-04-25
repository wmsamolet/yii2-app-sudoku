<?php

use wmsamolet\yii2\modules\sudoku\services\GameMatrixService;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model wmsamolet\yii2\modules\sudoku\models\SudokuGame */
/* @var $form ActiveForm */
?>
<div class="game-create">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'size')->dropDownList(GameMatrixService::getSizeList()) ?>

    <?= $form->field($model, 'difficulty')->dropDownList(GameMatrixService::getDifficultyList()) ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div><!-- game-create -->

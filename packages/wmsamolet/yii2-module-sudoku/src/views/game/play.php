<?php
/** @noinspection PhpRedundantVariableDocTypeInspection */

use wmsamolet\yii2\modules\sudoku\assets\VueAsset;
use wmsamolet\yii2\modules\sudoku\assets\VueSudokuAsset;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $game \wmsamolet\yii2\modules\sudoku\models\SudokuGame */
/* @var $accessToken string */

$this->title = 'Yii2 Vue Application';

VueAsset::register($this);
VueSudokuAsset::register($this);
?>

<div id="app">
    <Sudoku size="<?= $game->size ?>"
            game-id="<?= $game->id ?>"
            player-id="<?= Yii::$app->user->getId() ?>"
            access-token="<?= $accessToken ?>"
            connection-url="ws://<?= Url::base('') ?>:9090"
    ></Sudoku>
</div>

<?php ob_start() ?>
<!--suppress JSUnfilteredForInLoop -->
<script>
    <?php ob_end_clean(); ob_start() ?>

    new Vue({
        el: "#app",
        name: "App",
        components: {
            Sudoku
        }
    });

    <?php $this->registerJs(ob_get_clean(), $this::POS_END); ob_start(); ?>
</script>
<?php ob_end_clean() ?>

<?php

namespace wmsamolet\yii2\modules\sudoku\models;

use Yii;

/**
 * This is the model class for table "sudoku_game".
 *
 * @property int $id
 * @property int $size
 * @property int $difficulty
 * @property string $matrix
 * @property string $solution
 * @property int $owner_player_id
 * @property int $winner_player_id
 * @property string $started_at
 * @property string|null $finished_at
 */
class SudokuGame extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sudoku_game';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['size', 'difficulty', 'owner_player_id', 'winner_player_id'], 'integer'],
            [['matrix', 'solution'], 'required'],
            [['matrix', 'solution'], 'string'],
            [['started_at', 'finished_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'size' => Yii::t('app', 'Size'),
            'difficulty' => Yii::t('app', 'Difficulty'),
            'matrix' => Yii::t('app', 'Matrix'),
            'solution' => Yii::t('app', 'Solution'),
            'owner_player_id' => Yii::t('app', 'Owner Player ID'),
            'winner_player_id' => Yii::t('app', 'Winner Player ID'),
            'started_at' => Yii::t('app', 'Started At'),
            'finished_at' => Yii::t('app', 'Finished At'),
        ];
    }
}

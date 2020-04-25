<?php

namespace wmsamolet\yii2\modules\sudoku\models;

use Yii;

/**
 * This is the model class for table "sudoku_game_movement".
 *
 * @property int $id
 * @property int $game_id
 * @property int $player_id
 * @property int $cell_id
 * @property int $cell_value
 * @property int $cell_status
 * @property string $created_at
 * @property string $updated_at
 */
class SudokuGameMovement extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sudoku_game_movement';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['game_id', 'player_id', 'cell_id'], 'required'],
            [['game_id', 'player_id', 'cell_id', 'cell_value', 'cell_status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'game_id' => Yii::t('app', 'Game ID'),
            'player_id' => Yii::t('app', 'Player ID'),
            'cell_id' => Yii::t('app', 'Cell ID'),
            'cell_value' => Yii::t('app', 'Cell Value'),
            'cell_status' => Yii::t('app', 'Cell Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}

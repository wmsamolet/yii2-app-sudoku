<?php

use yii\db\Migration;

/**
 * Class m200424_092714_sudoku_init
 */
class m200424_092714_sudoku_init extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%sudoku_game}}', [
            'id' => $this->primaryKey(11),
            'size' => $this->tinyInteger()->notNull()->defaultValue(9),
            'difficulty' => $this->tinyInteger()->notNull()->defaultValue(0),
            'matrix' => $this->text()->notNull(),
            'solution' => $this->text()->notNull(),
            'owner_player_id' => $this->integer(11)->notNull()->defaultValue(0),
            'winner_player_id' => $this->integer(11)->notNull()->defaultValue(0),
            'started_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'finished_at' => $this->timestamp()->null()->defaultValue(null),
        ], $tableOptions);

        $this->createTable($table = '{{%sudoku_game_movement}}', [
            'id' => $this->primaryKey(11),
            'game_id' => $this->integer(11)->notNull(),
            'player_id' => $this->integer(11)->notNull(),
            'cell_id' => $this->smallInteger()->notNull(),
            'cell_value' => $this->tinyInteger()->notNull()->defaultValue(0),
            'cell_status' => $this->tinyInteger()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('{{%sudoku_game_movement}}');
        $this->dropTable('{{%sudoku_game}}');
    }
}

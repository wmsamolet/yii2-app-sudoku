<?php

use yii\db\Migration;

/**
 * Class m200424_082117_create_users
 */
class m200424_082117_create_users extends Migration
{
    public function safeUp()
    {
        $this->insert('{{%user}}', [
            'id' => 1,
            'username' => 'root',
            'auth_key' => Yii::$app->getSecurity()->generateRandomString(),
            'password_hash' => Yii::$app->getSecurity()->generatePasswordHash('root'),
            'email' => 'root@healthylivingco.ru',
            'status' => 10,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        for ($id = 2; $id <= 5; $id++) {
            $this->insert('{{%user}}', [
                'id' => $id,
                'username' => 'player' . ($id - 1),
                'auth_key' => Yii::$app->getSecurity()->generateRandomString(),
                'password_hash' => Yii::$app->getSecurity()->generatePasswordHash('player'),
                'email' => "player{$id}@sudoku.local",
                'status' => 10,
                'created_at' => time(),
                'updated_at' => time(),
            ]);
        }
    }

    public function safeDown()
    {
        $this->delete('{{%user}}', ['id' => [1,2,3,4,5]]);
    }
}

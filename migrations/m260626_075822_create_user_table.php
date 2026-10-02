<?php

use yii\db\Migration;

class m260626_075822_create_user_table extends Migration
{
    public function up()
    {
        $this->createTable('user', [
            'id'            => $this->primaryKey(),
            'role'          => "ENUM('admin','teacher','student') NOT NULL",
            'name'          => $this->string(100)->notNull(),
            'email'         => $this->string(180)->null()->defaultValue(null),
            'username'      => $this->string(50)->null()->defaultValue(null),
            'password_hash' => $this->string()->notNull()->defaultValue(''),
            'auth_key'      => $this->string(32)->notNull()->defaultValue(''),
            'password_reset_token' => $this->string()->null()->defaultValue(null),
            'status'        => "ENUM('pending','active','inactive') NOT NULL DEFAULT 'pending'",
            'created_at'    => $this->integer()->notNull(),
            'updated_at'    => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_user_email',    'user', 'email',    true);
        $this->createIndex('idx_user_username', 'user', 'username', true);
        $this->createIndex('idx_user_password_reset_token', 'user', 'password_reset_token', true);
    }

    public function down()
    {
        $this->dropTable('user');
    }
}
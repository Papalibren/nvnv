<?php

use yii\db\Migration;

class m260626_075926_create_invite_token_table extends Migration
{
    public function up()
    {
        $this->createTable('invite_token', [
            'id'         => $this->primaryKey(),
            'token'      => $this->string(64)->notNull(),
            'student_id' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'expires_at' => $this->integer()->notNull(),
            'used_at'    => $this->integer()->null()->defaultValue(null),
        ]);

        $this->createIndex('idx_invite_token_token', 'invite_token', 'token', true);

        $this->addForeignKey(
            'fk_invite_student',
            'invite_token', 'student_id',
            'user', 'id',
            'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'fk_invite_created_by',
            'invite_token', 'created_by',
            'user', 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('invite_token');
    }
}
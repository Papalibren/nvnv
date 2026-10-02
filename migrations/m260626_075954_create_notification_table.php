<?php

use yii\db\Migration;

class m260626_075954_create_notification_table extends Migration
{
    public function up()
    {
        $this->createTable('notification', [
            'id'           => $this->primaryKey(),
            'user_id'      => $this->integer()->notNull(),
            'type'         => "ENUM(
                                'homework_assigned',
                                'homework_submitted',
                                'deadline_reminder',
                                'exam_assigned',
                                'result_ready'
                              ) NOT NULL",
            'title'        => $this->string(255)->notNull(),
            'body'         => $this->text()->notNull(),
            'is_read'      => $this->boolean()->notNull()->defaultValue(false),
            'related_type' => $this->string(50)->null()->defaultValue(null),
            'related_id'   => $this->integer()->null()->defaultValue(null),
            'created_at'   => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_notification_user_id', 'notification', 'user_id');
        $this->createIndex('idx_notification_is_read', 'notification', ['user_id', 'is_read']);

        $this->addForeignKey(
            'fk_notification_user',
            'notification', 'user_id',
            'user', 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('notification');
    }
}
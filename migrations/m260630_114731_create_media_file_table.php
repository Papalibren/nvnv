<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%media_file}}`.
 */
class m260630_114731_create_media_file_table extends Migration
{
    public function up()
    {
        $this->createTable('media_file', [
            'id'             => $this->primaryKey(),
            'filename'       => $this->string(255)->notNull(),
            'path'           => $this->string(500)->notNull(),
            'mime_type'      => $this->string(100)->notNull()->defaultValue(''),
            'size'           => $this->integer()->notNull()->defaultValue(0),
            'width'          => $this->integer()->null(),
            'height'         => $this->integer()->null(),
            'uploaded_by'    => $this->integer()->notNull(),
            'created_at'     => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_media_file_uploaded_by', 'media_file', 'uploaded_by');

        $this->addForeignKey(
            'fk_media_file_uploaded_by',
            'media_file', 'uploaded_by',
            'user', 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('media_file');
    }
}

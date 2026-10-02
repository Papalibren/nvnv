<?php

use yii\db\Migration;

class m260626_080356_create_landing_tables extends Migration
{
    public function up()
    {
        $this->createTable('landing', [
            'id'               => $this->primaryKey(),
            'title'            => $this->string(255)->notNull(),
            'slug'             => $this->string(255)->notNull(),
            'meta_title'       => $this->string(255)->notNull()->defaultValue(''),
            'meta_description' => $this->string(500)->notNull()->defaultValue(''),
            'content'          => $this->text()->notNull(),
            'status'           => "ENUM('draft','published') NOT NULL DEFAULT 'draft'",
            'created_at'       => $this->integer()->notNull(),
            'updated_at'       => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_landing_slug', 'landing', 'slug', true);

        $this->createTable('lead', [
            'id'           => $this->primaryKey(),
            'landing_id'   => $this->integer()->notNull(),
            'name'         => $this->string(100)->notNull(),
            'phone'        => $this->string(30)->notNull()->defaultValue(''),
            'email'        => $this->string(180)->null(),
            'message'      => $this->text()->null(),
            'utm_source'   => $this->string(100)->null(),
            'utm_medium'   => $this->string(100)->null(),
            'utm_campaign' => $this->string(100)->null(),
            'is_processed' => $this->boolean()->notNull()->defaultValue(false),
            'created_at'   => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_lead_landing_id',   'lead', 'landing_id');
        $this->createIndex('idx_lead_is_processed', 'lead', 'is_processed');

        $this->addForeignKey('fk_lead_landing',
            'lead', 'landing_id',
            'landing', 'id',
            'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('lead');
        $this->dropTable('landing');
    }
}
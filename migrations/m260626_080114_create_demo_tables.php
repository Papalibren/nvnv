<?php

use yii\db\Migration;

class m260626_080114_create_demo_tables extends Migration
{
    public function up()
    {
        $this->createTable('demo_variant', [
            'id'           => $this->primaryKey(),
            'year'         => $this->smallInteger()->notNull(),
            'title'        => $this->string(255)->notNull(),
            'slug'         => $this->string(255)->notNull(),
            'published_at' => $this->integer()->null()->defaultValue(null),
        ]);

        $this->createIndex('idx_demo_variant_slug', 'demo_variant', 'slug', true);
        $this->createIndex('idx_demo_variant_year', 'demo_variant', 'year', true);

        $this->createTable('demo_solution', [
            'id'          => $this->primaryKey(),
            'variant_id'  => $this->integer()->notNull(),
            'task_number' => $this->smallInteger()->notNull(),
            'content'     => $this->text()->notNull(),
        ]);

        $this->createIndex(
            'idx_demo_solution_variant_task',
            'demo_solution',
            ['variant_id', 'task_number'],
            true
        );

        $this->addForeignKey(
            'fk_demo_solution_variant',
            'demo_solution', 'variant_id',
            'demo_variant', 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('demo_solution');
        $this->dropTable('demo_variant');
    }
}
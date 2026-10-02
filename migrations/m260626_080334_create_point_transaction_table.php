<?php

use yii\db\Migration;

class m260626_080334_create_point_transaction_table extends Migration
{
    public function up()
    {
        $this->createTable('point_transaction', [
            'id'          => $this->primaryKey(),
            'student_id'  => $this->integer()->notNull(),
            'source_type' => "ENUM('homework','exam') NOT NULL",
            'source_id'   => $this->integer()->notNull(),
            'points'      => $this->smallInteger()->notNull()->defaultValue(0),
            'description' => $this->string(500)->notNull()->defaultValue(''),
            'created_at'  => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_point_transaction_student',
            'point_transaction', 'student_id');

        $this->addForeignKey('fk_point_transaction_student',
            'point_transaction', 'student_id',
            'user', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('point_transaction');
    }
}
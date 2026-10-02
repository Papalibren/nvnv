<?php

use yii\db\Migration;

class m260626_080200_create_group_tables extends Migration
{
    public function up()
    {
        $this->createTable('group', [
            'id'          => $this->primaryKey(),
            'teacher_id'  => $this->integer()->notNull(),
            'name'        => $this->string(255)->notNull(),
            'description' => $this->text()->null(),
            'created_at'  => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk_group_teacher',
            'group', 'teacher_id',
            'user', 'id',
            'CASCADE', 'CASCADE'
        );

        $this->createTable('group_student', [
            'group_id'   => $this->integer()->notNull(),
            'student_id' => $this->integer()->notNull(),
            'joined_at'  => $this->integer()->notNull(),
        ]);

        $this->addPrimaryKey('pk_group_student', 'group_student', ['group_id', 'student_id']);

        $this->addForeignKey(
            'fk_group_student_group',
            'group_student', 'group_id',
            'group', 'id',
            'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'fk_group_student_student',
            'group_student', 'student_id',
            'user', 'id',
            'CASCADE', 'CASCADE'
        );

        // Привязка учитель-ученик (admin назначает)
        $this->createTable('teacher_student', [
            'teacher_id' => $this->integer()->notNull(),
            'student_id' => $this->integer()->notNull(),
        ]);

        $this->addPrimaryKey('pk_teacher_student', 'teacher_student', ['teacher_id', 'student_id']);

        $this->addForeignKey(
            'fk_teacher_student_teacher',
            'teacher_student', 'teacher_id',
            'user', 'id',
            'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'fk_teacher_student_student',
            'teacher_student', 'student_id',
            'user', 'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('teacher_student');
        $this->dropTable('group_student');
        $this->dropTable('group');
    }
}
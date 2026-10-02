<?php

use yii\db\Migration;

class m260831_165407_create_site_setting extends Migration
{
    public function up()
    {
        $this->createTable('site_setting', [
            'id'         => $this->primaryKey(),
            'ege_date'   => $this->integer()->null(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->insert('site_setting', [
            'ege_date'   => strtotime('next year june 1'),
            'updated_at' => time(),
        ]);
    }

    public function down()
    {
        $this->dropTable('site_setting');
    }
}

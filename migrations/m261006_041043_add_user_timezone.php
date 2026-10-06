<?php

use yii\db\Migration;

class m261006_041043_add_user_timezone extends Migration
{
    public function up()
    {
        $this->addColumn('user', 'timezone', $this->string(50)->notNull()->defaultValue('Europe/Moscow')->after('name'));
    }

    public function down()
    {
        $this->dropColumn('user', 'timezone');
    }
}

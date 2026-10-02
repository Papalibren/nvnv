<?php

use yii\db\Migration;

class m260710_052423_update_point_transaction_source_types extends Migration
{
    public function up()
    {
        $this->alterColumn('point_transaction', 'source_type',
            "ENUM('homework','exam','public_task') NOT NULL");
    }

    public function down()
    {
        $this->alterColumn('point_transaction', 'source_type',
            "ENUM('homework','exam') NOT NULL");
    }
}

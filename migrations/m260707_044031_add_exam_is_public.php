<?php

use yii\db\Migration;

class m260707_044031_add_exam_is_public extends Migration
{
    public function up()
    {
        $this->addColumn('exam', 'is_public',
            $this->boolean()->notNull()->defaultValue(0)->after('is_proctored'));
    }

    public function down()
    {
        $this->dropColumn('exam', 'is_public');
    }
}

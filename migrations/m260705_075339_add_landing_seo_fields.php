<?php

use yii\db\Migration;

class m260705_075339_add_landing_seo_fields extends Migration
{
    public function up()
    {
        $this->addColumn('landing', 'og_image', $this->string(500)->null()->after('meta_description'));
        $this->addColumn('landing', 'is_indexed', $this->boolean()->notNull()->defaultValue(true)->after('og_image'));
        $this->addColumn('landing', 'form_title', $this->string(255)->null()->defaultValue('Оставить заявку'));
    }

    public function down()
    {
        $this->dropColumn('landing', 'og_image');
        $this->dropColumn('landing', 'is_indexed');
        $this->dropColumn('landing', 'form_title');
    }
}

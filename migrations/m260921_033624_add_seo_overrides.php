<?php

use yii\db\Migration;

class m260921_033624_add_seo_overrides extends Migration
{
    public function up()
    {
        $this->addColumn('book_page', 'seo_title', $this->string(255)->null()->after('content'));
        $this->addColumn('book_page', 'seo_description', $this->string(300)->null()->after('seo_title'));

        $this->addColumn('task', 'seo_title', $this->string(255)->null()->after('content'));
        $this->addColumn('task', 'seo_description', $this->string(300)->null()->after('seo_title'));
    }

    public function down()
    {
        $this->dropColumn('task', 'seo_description');
        $this->dropColumn('task', 'seo_title');
        $this->dropColumn('book_page', 'seo_description');
        $this->dropColumn('book_page', 'seo_title');
    }
}

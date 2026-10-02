<?php

use yii\db\Migration;

class m260921_155103_add_visibility_to_book_structure extends Migration
{
    public function up()
    {
        $this->addColumn('book_section', 'is_published', $this->boolean()->notNull()->defaultValue(1)->after('description'));
        $this->addColumn('book_chapter', 'is_published', $this->boolean()->notNull()->defaultValue(1)->after('title'));
    }

    public function down()
    {
        $this->dropColumn('book_chapter', 'is_published');
        $this->dropColumn('book_section', 'is_published');
    }
}

<?php

use yii\db\Migration;

class m260906_143220_add_seo_settings extends Migration
{
    public function up()
    {
        $this->addColumn('site_setting', 'site_name', $this->string(255)->null()->after('ege_date'));
        $this->addColumn('site_setting', 'default_meta_description', $this->string(500)->null());
        $this->addColumn('site_setting', 'default_og_image', $this->string(500)->null());
        $this->addColumn('site_setting', 'yandex_metrika_id', $this->string(20)->null());
        $this->addColumn('site_setting', 'google_analytics_id', $this->string(30)->null());
        $this->addColumn('site_setting', 'google_search_console_code', $this->string(255)->null());
        $this->addColumn('site_setting', 'yandex_webmaster_code', $this->string(255)->null());
        $this->addColumn('site_setting', 'robots_extra', $this->text()->null());
    }

    public function down()
    {
        $this->dropColumn('site_setting', 'robots_extra');
        $this->dropColumn('site_setting', 'yandex_webmaster_code');
        $this->dropColumn('site_setting', 'google_search_console_code');
        $this->dropColumn('site_setting', 'google_analytics_id');
        $this->dropColumn('site_setting', 'yandex_metrika_id');
        $this->dropColumn('site_setting', 'default_og_image');
        $this->dropColumn('site_setting', 'default_meta_description');
        $this->dropColumn('site_setting', 'site_name');
    }
}

<?php

class AddColumnInSiteSettingForFrozenTime extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("site_settings", "restaurant_order_frozen_time", "timestamp");
    }//up()

    public function down()
    {
    	$this->remove_column("site_settings", "restaurant_order_frozen_time", "timestamp");
    }//down()
}

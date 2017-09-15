<?php

class CreateIndexMenuAddonSettings extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `menu_addon_settings` ADD INDEX ( `menu_id` )");
        $this->execute("ALTER TABLE  `menu_addon_settings` ADD INDEX (  `addon_id` )");
    }//up()

    public function down()
    {
    }//down()
}

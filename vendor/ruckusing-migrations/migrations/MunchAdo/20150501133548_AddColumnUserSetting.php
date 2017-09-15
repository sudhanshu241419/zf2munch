<?php

class AddColumnUserSetting extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_settings` ADD `new_order` TINYINT NULL AFTER `user_id` ,
ADD `new_reservation` TINYINT NULL AFTER `new_order` ;");
    }//up()

    public function down()
    {
    }//down()
}

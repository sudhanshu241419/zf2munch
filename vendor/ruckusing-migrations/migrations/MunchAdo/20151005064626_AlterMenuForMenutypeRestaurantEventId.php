<?php

class AlterMenuForMenutypeRestaurantEventId extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `menus` ADD `menuType` TINYINT NOT NULL DEFAULT '1' COMMENT '1=restaurant menu,2=restaurant event menu'AFTER `status`");
        $this->execute("ALTER TABLE  `menus` ADD  `restaurantEventId` BIGINT NOT NULL AFTER  `menuType`");
    }//up()

    public function down()
    {
    }//down()
}

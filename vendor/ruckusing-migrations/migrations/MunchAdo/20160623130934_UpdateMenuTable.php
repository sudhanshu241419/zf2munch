<?php

class UpdateMenuTable extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `menus` ADD `parentMenuOrder` INT( 4 ) NOT NULL DEFAULT '0' AFTER `spice_indicator` ;");
        $this->execute("ALTER TABLE  `menus` ADD  `user_deals` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `spice_indicator`;");
    }//up()

    public function down()
    {
    }//down()
}

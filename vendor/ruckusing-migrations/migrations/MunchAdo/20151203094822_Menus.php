<?php

class Menus extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `menus` ADD `parentMenuOrder` INT( 11 ) NOT NULL DEFAULT '0' AFTER `spice_indicator` ;"); 
    }//up()

    public function down()
    {
    }//down()
}

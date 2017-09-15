<?php

class UpdateMenuAddonsTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `menu_addons` MODIFY price double(5,2) null");
    }//up()

    public function down()
    {
    }//down()
}

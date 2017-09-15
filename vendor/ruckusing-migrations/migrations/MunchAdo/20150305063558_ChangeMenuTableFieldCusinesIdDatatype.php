<?php

class ChangeMenuTableFieldCusinesIdDatatype extends Ruckusing_Migration_Base
{
    public function up()
    {   
        $this->execute("ALTER TABLE `menus` CHANGE `cuisines_id` `cuisines_id` VARCHAR( 255 ) NOT NULL");
    }//up()

    public function down()
    {
    }//down()
}

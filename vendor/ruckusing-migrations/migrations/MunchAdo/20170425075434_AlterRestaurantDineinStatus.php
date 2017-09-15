<?php

class AlterRestaurantDineinStatus extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `restaurant_dinein` CHANGE `status` `status` TINYINT(2) NOT NULL DEFAULT '0' COMMENT '0=new,1=confirm,2=reject,3=alternate time,4=not respond,5=cancel'");
    }//up()

    public function down()
    {
    }//down()
}

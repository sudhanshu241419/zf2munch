<?php

class AddColumnNameRemarksAgentInUserOrdersNReservation extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE  `user_reservations` ADD  `remarks` VARCHAR( 255 ) NULL AFTER  `cron_status` ,
                        ADD  `agent` VARCHAR( 255 ) NULL AFTER  `remarks`");
    	
    	$this->execute("ALTER TABLE  `user_orders` ADD  `remarks` VARCHAR( 255 ) NULL ,
                        ADD  `agent` VARCHAR( 255 ) NULL");
    }//up()

    public function down()
    {
    }//down()
}

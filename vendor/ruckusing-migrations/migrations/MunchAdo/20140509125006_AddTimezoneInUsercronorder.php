<?php

class AddTimezoneInUsercronorder extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE  `cron_order` ADD  `time_zone` VARCHAR( 100 ) NULL AFTER  `archive_time`");
    }//up()

    public function down()
    {
    }//down()
}
<?php

class AddLongitudeLatitudeInUseraddress extends Ruckusing_Migration_Base
{
    public function up()
    {
         $this->execute("ALTER TABLE `user_addresses` ADD `latitude` DOUBLE NOT NULL AFTER `apt_suite` ,
ADD `longitude` DOUBLE NOT NULL AFTER `latitude`");
    }//up()

    public function down()
    {
    }//down()
}

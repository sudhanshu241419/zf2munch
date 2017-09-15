<?php

class AlterRestaurantTableForAllowedZip extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `restaurants` ADD `allowed_zip` TEXT NULL DEFAULT NULL AFTER `fax` ;");
    }//up()

    public function down()
    {
    }//down()
}

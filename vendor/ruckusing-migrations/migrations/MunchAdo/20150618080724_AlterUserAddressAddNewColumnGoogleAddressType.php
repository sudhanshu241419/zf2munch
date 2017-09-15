<?php

class AlterUserAddressAddNewColumnGoogleAddressType extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_addresses` ADD `google_addrres_type` VARCHAR( 50 ) NULL DEFAULT NULL AFTER `longitude` ;");
    }//up()

    public function down()
    {
    }//down()
}

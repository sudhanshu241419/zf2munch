<?php

class AlterMerchantRegistrationUsername extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `merchant_registration` ADD  `username` VARCHAR( 100 ) NOT NULL AFTER  `fee_structure_id`");
    }//up()

    public function down()
    {
    }//down()
}

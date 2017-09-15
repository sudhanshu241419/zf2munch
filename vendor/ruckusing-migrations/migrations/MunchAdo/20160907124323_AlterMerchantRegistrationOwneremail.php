<?php

class AlterMerchantRegistrationOwneremail extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `merchant_registration` ADD  `owneremail` VARCHAR( 50 ) NULL DEFAULT NULL ,
ADD  `ownercell` VARCHAR( 20 ) NULL DEFAULT NULL");
    }//up()

    public function down()
    {
    }//down()
}

<?php

class AlterMerchantRegistrationChkprepaidres extends Ruckusing_Migration_Base
{
    public function up()
    {
       $this->execute('ALTER TABLE  `merchant_registration` ADD  `chkprepaidres` VARCHAR( 20 ) NULL AFTER  `chkreservations`');
    }//up()

    public function down()
    {
    }//down()
}

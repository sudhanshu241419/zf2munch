<?php

class MerchantRegistration extends Ruckusing_Migration_Base
{
    public function up()
    {
     $this->execute("ALTER TABLE  `merchant_registration` ADD  `delivery_fee_type` VARCHAR( 32 ) NULL DEFAULT NULL AFTER  `delivery_fee` ,ADD  `delivery_fee_mode` VARCHAR( 32 ) NULL DEFAULT NULL AFTER  `delivery_fee_type` ,ADD  `delivey_instrucation` TEXT NULL DEFAULT NULL AFTER  `delivery_fee_mode` ;");
    }//up()

    public function down()
    {
    }//down()
}

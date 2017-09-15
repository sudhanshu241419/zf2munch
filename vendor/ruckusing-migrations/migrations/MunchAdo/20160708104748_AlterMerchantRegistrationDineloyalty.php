<?php

class AlterMerchantRegistrationDineloyalty extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `merchant_registration` ADD  `dineloyalty` VARCHAR( 50 ) NULL DEFAULT NULL");
        $this->execute("ALTER TABLE  `merchant_registration` ADD  `loyaltyduration` TINYINT( 4 ) NULL DEFAULT NULL");
        $this->execute("ALTER TABLE  `merchant_registration` ADD  `loyaltypay` VARCHAR( 20 ) NULL DEFAULT NULL COMMENT  'Loyalty payment'");
    }//up()

    public function down()
    {
    }//down()
}

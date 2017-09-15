<?php

class AlterMerchantRegistrationAgreement extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `merchant_registration` ADD `agreement_copy` VARCHAR( 255 ) NOT NULL COMMENT 'Agreement copy name' AFTER `ecom_price` ,
ADD `discount` INT( 5 ) NOT NULL AFTER `agreement_copy`");
    }//up()

    public function down()
    {
    }//down()
}

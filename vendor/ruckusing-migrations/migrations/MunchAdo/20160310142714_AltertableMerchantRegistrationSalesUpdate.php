<?php

class AltertableMerchantRegistrationSalesUpdate extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `merchant_registration` ADD  `associatename` VARCHAR( 100 ) NULL DEFAULT NULL ,
ADD  `associateemail` VARCHAR( 100 ) NULL DEFAULT NULL");
    }//up()

    public function down()
    {
    }//down()
}

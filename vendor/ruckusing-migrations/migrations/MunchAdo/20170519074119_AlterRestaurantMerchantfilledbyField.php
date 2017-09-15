<?php

class AlterRestaurantMerchantfilledbyField extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `merchant_registration` ADD  `filled_by` VARCHAR( 50 ) NOT NULL , ADD  `ecom_price` INT NOT NULL");
    }//up()

    public function down()
    {
    }//down()
}

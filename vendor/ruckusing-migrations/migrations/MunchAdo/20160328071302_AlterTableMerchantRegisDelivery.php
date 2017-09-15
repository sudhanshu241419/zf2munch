<?php

class AlterTableMerchantRegisDelivery extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `merchant_registration` ADD  `delivery_by_ma` VARCHAR( 10 ) NULL DEFAULT NULL ,
ADD  `fees_waived_off` VARCHAR( 10 ) NULL DEFAULT NULL");
    }//up()

    public function down()
    {
    }//down()
}

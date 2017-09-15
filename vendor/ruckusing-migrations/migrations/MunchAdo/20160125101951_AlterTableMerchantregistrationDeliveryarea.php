<?php

class AlterTableMerchantregistrationDeliveryarea extends Ruckusing_Migration_Base
{
    public function up()
    {
       $this->execute("ALTER TABLE  `merchant_registration` CHANGE  `delivery_area`  `delivery_area` VARCHAR( 255 ) NULL DEFAULT ''");
        $this->execute("ALTER TABLE  `merchant_registration` CHANGE  `min_delivery_amt`  `min_delivery_amt` VARCHAR( 10 ) NULL DEFAULT ''");
    }//up()
    public function down()
    {
    }//down()
}

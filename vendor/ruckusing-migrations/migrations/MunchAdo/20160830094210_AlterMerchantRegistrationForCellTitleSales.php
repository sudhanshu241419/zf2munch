<?php

class AlterMerchantRegistrationForCellTitleSales extends Ruckusing_Migration_Base
{
    public function up()
    {
        //$this->execute(" ALTER TABLE  `merchant_registration` ADD  `cell_phone` VARCHAR( 20 ) NULL DEFAULT NULL,ADD  `title_name` VARCHAR( 20 ) NULL DEFAULT NULL,ADD  `sales_region` VARCHAR( 50 ) NULL DEFAULT NULL");
    }//up()

    public function down()
    {
    }//down()
}

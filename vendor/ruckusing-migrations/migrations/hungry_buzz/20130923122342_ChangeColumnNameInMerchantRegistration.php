<?php

class ChangeColumnNameInMerchantRegistration extends Ruckusing_Migration_Base
{
    public function up()
    {
    	/*$this->execute("ALTER TABLE `merchant_registration`DROP `cvv`");
        $this->execute("ALTER TABLE `merchant_registration` ADD `amount` FLOAT( 5 )
    	           NOT NULL AFTER `cardno`");
        $this->execute("ALTER TABLE `merchant_registration` CHANGE `cardname` `name_oncard` VARCHAR( 50 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ");*/
    }//up()

    public function down()
    {
    }//down()
}

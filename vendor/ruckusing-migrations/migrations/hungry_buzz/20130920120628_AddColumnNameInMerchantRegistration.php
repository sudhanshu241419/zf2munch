<?php

class AddColumnNameInMerchantRegistration extends Ruckusing_Migration_Base
{
    public function up()
    {
    	/*$this->execute("ALTER TABLE `merchant_registration`
						  DROP `operation_fname`,
						  DROP `operation_lname`,
						  DROP `operation_email`,
						  DROP `account_fname`,
						  DROP `account_lname`,
						  DROP `account_email`,
						  DROP `bank_fname`,
						  DROP `bank_lname`,
						  DROP `bank_email`");
    	$this->execute("ALTER TABLE `merchant_registration` ADD `cardname` VARCHAR( 50 ) NULL AFTER `email` ,
						ADD `cardno` VARCHAR( 20 ) NULL AFTER `cardname` ,
						ADD `cvv` VARCHAR( 6 ) NULL AFTER `cardno` ,
						ADD `exp_month` VARCHAR( 3 ) NULL AFTER `cvv` ,
						ADD `exp_year` VARCHAR( 4 ) NULL AFTER `exp_month` ,
						ADD `billingzip` VARCHAR( 10 ) NULL AFTER `exp_year` ,
						ADD `stripes_token` VARCHAR( 256 ) NULL AFTER `billingzip` ,
						ADD `stripe_card_id` VARCHAR( 256 ) NULL AFTER `stripes_token` ,
						ADD `cardtype` VARCHAR( 10 ) NULL AFTER `stripe_card_id` ");*/
    }//up()

    public function down()
    {
    }//down()
}

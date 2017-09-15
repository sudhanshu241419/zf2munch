<?php

class AddColumnBillingzipIntoUserOrderTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute('ALTER TABLE `user_orders` ADD `billing_zip` VARCHAR( 50 ) NOT NULL AFTER `expired_on` ');
    }//up()

    public function down()
    {
    }//down()
}

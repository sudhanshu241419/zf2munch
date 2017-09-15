<?php

class AddCardcolumnUserOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_orders` ADD `card_number` VARCHAR( 20 ) NULL AFTER `stripes_token` ,
		ADD `name_on_card` VARCHAR( 100 ) NULL AFTER `card_number` ,
		ADD `card_type` VARCHAR( 50 ) NULL AFTER `name_on_card` ,
		ADD `expired_on` VARCHAR( 10 ) NULL AFTER `card_type` ");
    }//up()

    public function down()
    {
    	$this->remove_column("user_orders","card_number");
    	$this->remove_column("user_orders","name_on_card");
    	$this->remove_column("user_orders","card_type");
    	$this->remove_column("user_orders","expired_on");
    }//down()
}

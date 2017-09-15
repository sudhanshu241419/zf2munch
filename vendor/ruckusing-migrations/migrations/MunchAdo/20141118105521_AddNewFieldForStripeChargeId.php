<?php

class AddNewFieldForStripeChargeId extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_orders` ADD `stripe_charge_id` VARCHAR( 200 ) NOT NULL AFTER `stripes_token`");
    }//up()

    public function down()
    {
    }//down()
}

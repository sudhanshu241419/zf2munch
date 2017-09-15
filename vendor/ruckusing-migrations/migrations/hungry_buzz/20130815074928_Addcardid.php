<?php

class Addcardid extends Ruckusing_Migration_Base
{
    public function up()
    {

    	$this->add_column("user_orders", 'stripe_card_id', 'string');

    }//up()

    public function down()
    {
    	$this->remove_column("user_orders", 'stripe_card_id');
    }//down()
}

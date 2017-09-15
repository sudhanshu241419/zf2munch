<?php

class Addstripetoken extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column('user_cards', 'stripe_user_id', 'string');
    }//up()

    public function down()
    {
    	$this->remove_column('user_cards', 'stripe_user_id');
    }//down()
}

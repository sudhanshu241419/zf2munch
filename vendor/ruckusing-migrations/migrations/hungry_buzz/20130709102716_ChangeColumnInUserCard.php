<?php

class ChangeColumnInUserCard extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->remove_column("user_cards", "cvv");
    	$this->add_column("user_cards","stripe_token_id","string");
    }//up()

    public function down()
    {
    	$this->remove_column("user_cards", "stripe_token_id");
        $this->add_column("user_cards", "cvv", "integer");

    }//down()
}

<?php

class Addcardidinuserorder extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column('user_orders', 'user_card_id', 'integer');
    }//up()

    public function down()
    {
    	$this->remove_column('user_orders', 'user_card_id');

    }//down()
}

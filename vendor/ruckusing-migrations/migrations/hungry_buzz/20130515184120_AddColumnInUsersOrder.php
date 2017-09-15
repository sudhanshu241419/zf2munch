<?php

class AddColumnInUsersOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("user_order_details", "order_addon_data", "text");
    }//up()

    public function down()
    {
    	$this->remove_column("user_order_details", "order_addon_data", "text");
    }//down()
}

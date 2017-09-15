<?php

class AddColumnNameNewOrderInUserOrders extends Ruckusing_Migration_Base
{
    public function up()
    {
    $this->execute("ALTER TABLE  `user_orders` ADD  `crm_update_at` DATETIME NOT NULL AFTER  `total_amount` ");
    $this->execute("ALTER TABLE  `user_orders` ADD  `appproved_by` INT( 11 ) NOT NULL AFTER  `total_amount` ");
    $this->execute("ALTER TABLE  `user_orders` ADD  `new_order` INT( 1 ) NOT NULL AFTER  `total_amount`");

    }

    public function down()
    {
    }//down()
}
 
 

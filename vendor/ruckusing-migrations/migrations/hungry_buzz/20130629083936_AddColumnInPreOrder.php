<?php

class AddColumnInPreOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `pre_order` ADD `order_submitted_permission` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `order_payee_email` ");
    }//up()

    public function down()
    {
    	$this->remove_column("pre_order","order_submitted_permission");
    }//down()
}

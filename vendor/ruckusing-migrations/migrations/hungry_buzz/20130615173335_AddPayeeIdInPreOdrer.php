<?php

class AddPayeeIdInPreOdrer extends Ruckusing_Migration_Base
{
    public function up()
    {
	$this->add_column("pre_order", "order_payee_id", "integer");
    }//up()

    public function down()
    {
	$this->remove_column("pre_order", "order_payee_id");
    }//down()
}

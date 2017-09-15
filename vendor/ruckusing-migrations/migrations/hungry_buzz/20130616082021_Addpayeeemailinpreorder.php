<?php

class Addpayeeemailinpreorder extends Ruckusing_Migration_Base
{
    public function up()
    {
	$this->add_column("pre_order", "order_payee_email", "string");
    }//up()

    public function down()
    {
	$this->remove_column("pre_order", "order_payee_email");
    }//down()
}

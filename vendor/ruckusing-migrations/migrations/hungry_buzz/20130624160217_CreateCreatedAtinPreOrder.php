<?php

class CreateCreatedAtinPreOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("pre_order", "created_at", "datetime");
    	$this->add_column("pre_order", "updated_at", "datetime");

    }//up()

    public function down()
    {
    	$this->remove_column("pre_order", "created_at");
    	$this->remove_column("pre_order", "updated_at");
    }//down()
}

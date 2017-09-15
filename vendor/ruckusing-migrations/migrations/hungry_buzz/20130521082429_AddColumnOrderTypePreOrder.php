<?php

class AddColumnOrderTypePreOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("pre_order", "order_type1", "string", array('limit' => 1,'comment' => 'I=>Individual,G=>Group'));
    }//up()

    public function down()
    {
    	$this->remove_column("pre_order", "order_type1", "string", array('limit' => 1,'comment' => 'I=>Individual,G=>Group'));
    }//down()
}

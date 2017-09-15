<?php

class AddpriorityinCuisine extends Ruckusing_Migration_Base
{
    public function up()
    {

    	$this->add_column('cuisines', 'priority', 'integer', array('default' => 0));

    }//up()

    public function down()
    {
    	$this->remove_column('cuisines', 'priority');
    }//down()
}

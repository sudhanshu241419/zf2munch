<?php

class AddcolumninUserOrdertable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	//$this->add_column("user_orders", "fname", "string", array('limit' => 100));
    	//$this->add_column("user_orders", "lname", "string", array('limit' => 100));
    	//$this->add_column("user_orders", "city_code", "string", array('limit' => 10));
    	//$this->add_column("user_orders", "city", "string", array('limit' => 50));
    	//$this->add_column("user_orders", "apt_suite", "string", array('limit' => 150));
    	$this->add_column("user_orders", "user_sess_id", "string", array('limit' => 20));
    	$this->add_column("user_orders", "stripes_token", "string", array('limit' => 100));
    	
    	//$this->add_column("user_orders", "zipcode", "integer");
    	//$this->add_column("user_orders", "special_checks", "string");

    	$this->add_column("user_order_details", "item_id", "integer");
    	$this->add_column("user_order_details", "user_id", "integer");
    	
    }//up()

    public function down()
    {
    	//$this->remove_column("user_orders", "fname", "string", array('limit' => 100));
    	//$this->remove_column("user_orders", "lname", "string", array('limit' => 100));
    	//$this->remove_column("user_orders", "city_code", "string", array('limit' => 10));
    	//$this->remove_column("user_orders", "city", "string", array('limit' => 50));
    	//$this->remove_column("user_orders", "apt_suite", "string", array('limit' => 150));
    	$this->remove_column("user_orders", "user_sess_id", "string", array('limit' => 20));
    	$this->remove_column("user_orders", "stripes_token", "string", array('limit' => 100));
    	
    	//$this->remove_column("user_orders", "zipcode", "integer");
    	//$this->remove_column("user_orders", "special_checks", "string");

    	$this->remove_column("user_order_details", "item_id", "integer");
    	$this->remove_column("user_order_details", "user_id", "integer");
    }//down()
}

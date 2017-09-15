<?php

class AddColumnInUserOrderInvitation extends Ruckusing_Migration_Base
{
    public function up()
    {  
    	/*$this->add_column("user_order_invitation", "pre_order_id", "integer");
    	$this->add_column("user_order_invitation", "order_token", "string", array('limit' => 50));
    	$this->add_column("user_order_invitation", "friend_email", "string", array('limit' => 150));
    	$this->add_column("user_order_invitation", "pay_anyone", "integer");


    	$this->add_column("user_order_invitation", "user_type", "tinyint", array('limit' => 2,'comment' => '0=>External,1=>Internal'));*/

    	$this->add_column("user_order_invitation", "user_type", "boolean", array('limit' => 2,'comment' => '0=>External,1=>Internal'));


    }//up()
    /*ALTER TABLE `user_order_invitation` ADD `pre_order_id` INT NOT NULL AFTER `from_id` ,
	ADD `order_token` VARCHAR( 25 ) NOT NULL AFTER `pre_order_id` ,
	ADD `friend_email` VARCHAR( 255 ) NOT NULL AFTER `order_token` ,
	ADD `pay_anyone` TINYINT( 1 ) NOT NULL AFTER `friend_email` ,
	ADD `user_type` ENUM( '0', '1' ) NOT NULL COMMENT '0=>External User, 1=>Internal User' AFTER `pay_anyone` 
	*/
    public function down()
    {
    	/*$this->remove_column("user_order_invitation", "pre_order_id", "integer");
    	$this->remove_column("user_order_invitation", "order_token", "string", array('limit' => 50));
    	$this->remove_column("user_order_invitation", "friend_email", "string", array('limit' => 150));
    	$this->remove_column("user_order_invitation", "pay_anyone", "integer");


    	$this->remove_column("user_order_invitation", "user_type", "tinyint", array('limit' => 2,'comment' => '0=>External,1=>Internal'));*/

    	$this->remove_column("user_order_invitation", "user_type", "boolean", array('limit' => 2,'comment' => '0=>External,1=>Internal'));



    	$this->remove_column("user_order_invitation", "user_type", "tinyint", array('limit' => 2,'comment' => '0=>External,1=>Internal'));

    }//down()
}

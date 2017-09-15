<?php

class CreateOrderBookmarks extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$sql = "CREATE TABLE `order_bookmorks` (
  				`id` int(11) NOT NULL AUTO_INCREMENT,
  				`review_id` int(11) NOT NULL,
	   			`menu_id` int(11) NOT NULL,
		  		`like` int(11) DEFAULT '0' COMMENT 'Like => value 1 means Like',
			   	PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1";
		$this->execute($sql);
    }//up()

    public function down()
    {
    }//down()
}

<?php

class ReportAbuseRestaurants extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$sql = "CREATE TABLE report_abuse_restaurants (
		  id int(11) NOT NULL AUTO_INCREMENT,
		  user_id int(11) NOT NULL,
		  review_id int(11) DEFAULT NULL,
		  restaurant_id int(11) NOT NULL,
		  created_on timestamp NULL DEFAULT NULL,
		  PRIMARY KEY (id)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1";
		$this->execute($sql);
    }//up()

    public function down()
    {
    }//down()
}

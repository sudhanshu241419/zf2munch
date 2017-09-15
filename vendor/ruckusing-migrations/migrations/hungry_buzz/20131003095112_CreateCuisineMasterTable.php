<?php

class CreateCuisineMasterTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("CREATE TABLE IF NOT EXISTS `cuisine_master` ( 
    	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `Menu_Id` int(11) NOT NULL,
		  `PID` int(11) DEFAULT NULL,
		  `Category` varchar(255) NOT NULL,
		  `Category_Desc` varchar(255) DEFAULT NULL,
		  `Rest_Id` int(11) NOT NULL,
		  `Rest_Code` varchar(40) NOT NULL,
		  `Cuisine_id` int(11) NOT NULL,
		   PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;"); 

        $this->execute("CREATE TABLE IF NOT EXISTS `cuisine_master_temp` ( 
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `Menu_ID` int(11) NOT NULL,
		  `PID` int(11) DEFAULT NULL,
		  `Category` varchar(255) NOT NULL,
		  `Category_Desc` varchar(255) DEFAULT NULL,
		  `Rest_id` int(11) NOT NULL,
		  `Rest_Code` varchar(40) NOT NULL,
		  `Cuisines` varchar(100) NOT NULL,
		  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;"); 

    	$this->execute("CREATE TABLE IF NOT EXISTS `cuisine_master_temp_error` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `Menu_ID` int(11) DEFAULT NULL,
		  `PID` int(11) DEFAULT NULL,
		  `Category` varchar(255) DEFAULT NULL,
		  `Category_Desc` varchar(255) DEFAULT NULL,
		  `Rest_id` int(11) DEFAULT NULL,
		  `Rest_Code` varchar(40) DEFAULT NULL,
		  `Cuisines` varchar(100) DEFAULT NULL,
		  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;"); 
    }//up()

    public function down()
    {
        $this->drop_table("cuisine_master");
    	$this->drop_table("cuisine_master_temp");
    	$this->drop_table("cuisine_master_temp_error");
    }//down()
}

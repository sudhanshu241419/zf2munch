<?php

class CreateFeatureMapTempTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("CREATE TABLE IF NOT EXISTS `sm_features_temp` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `Restaurant_Code` varchar(50) NOT NULL,
		  `chain_code` varchar(50) NOT NULL,
		  `restaurant_name` varchar(100) NOT NULL,
		  `restaurant_address` varchar(100) NOT NULL,
		  `Good_For_Business` varchar(10) ,
		  `Good_For_Date` varchar(10) ,
		  `Good_For_Singles` varchar(10) ,
		  `Good_For_Groups` varchar(10) ,
		  `Good_For_Kids` varchar(10) ,
		  `Description` varchar(255) ,
		  `Happy_Hour` varchar(10) ,
		  `Live_Entertainment` varchar(100) ,
		  `Live_Entertainment_Description` varchar(255) ,
		  `Notable_Chef` varchar(10) ,
		  `Notable_Chef_Description` varchar(255) ,
		  `Outdoor_Seating` varchar(10) ,
		  `Prix_fixe` varchar(10) ,
		  `celebs_spotting` varchar(10) ,
		  `wifi` varchar(10) ,
		  `Facebook_Account` varchar(100) NOT NULL,
		  `twitter_account` varchar(100) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;"); 

        $this->execute("CREATE TABLE IF NOT EXISTS `sm_features_temp_error` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `Restaurant_Code` varchar(50),
		  `chain_code` varchar(50)  ,
		  `restaurant_name` varchar(100)  ,
		  `restaurant_address` varchar(100)  ,
		  `Good_For_Business` varchar(10)  ,
		  `Good_For_Date` varchar(10)  ,
		  `Good_For_Singles` varchar(10)  ,
		  `Good_For_Groups` varchar(10)  ,
		  `Good_For_Kids` varchar(10)  ,
		  `Description` varchar(255) ,
		  `Happy_Hour` varchar(10) ,
		  `Live_Entertainment` varchar(100) ,
		  `Live_Entertainment_Description` varchar(255) ,
		  `Notable_Chef` varchar(10) ,
		  `Notable_Chef_Description` varchar(255) ,
		  `Outdoor_Seating` varchar(10) ,
		  `Prix_fixe` varchar(10) ,
		  `celebs_spotting` varchar(10) ,
		  `wifi` varchar(10) ,
		  `Facebook_Account` varchar(100) ,
		  `twitter_account` varchar(100) ,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;"); 
    }//up()

    public function down()
    {
    	$this->drop_table("sm_features_temp");
    	$this->drop_table("sm_features_temp_error");
    }//down()
}

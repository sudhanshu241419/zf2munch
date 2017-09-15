<?php

class ModificationInRestaurantFeatureTableRows extends Ruckusing_Migration_Base
{
    public function up()
    {
    
    $this->execute("UPDATE `features` SET `features` = 'Beer & Wine' WHERE `features`.`features` = 'Beer, Wine'");
    $this->execute("INSERT INTO `features`(`features`,`feature_type`,`features_key`,`feature_desc`,`status`) VALUES ('Good For Date', 'Restaurant Features', '', '1', '1')");
    //$this->execute("ALTER TABLE `restaurant_calendars` CHANGE `open_close_status` `open_close_status` ENUM( '0', '1', '2' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL COMMENT '0=>open.1=>close'");
    }//up()

    public function down()
    {
    	$this->execute("UPDATE `features` SET `features` = 'Beer, Wine' WHERE `features`.`features` = 'Beer & Wine'");
    }//down()
}
